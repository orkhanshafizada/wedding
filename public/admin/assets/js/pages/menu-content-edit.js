(function () {
    function csrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    function toast(type, message) {
        if (window.Swal && Swal.fire) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                icon: type,
                title: message
            });
            return;
        }
        if (type === 'error') {
            alert(message);
        } else {
            console.log(message);
        }
    }

    function faIconByExt(ext) {
        ext = (ext || '').toLowerCase();

        if (ext === 'pdf') return 'fa-regular fa-file-pdf';
        if (['doc','docx'].includes(ext)) return 'fa-regular fa-file-word';
        if (['xls','xlsx','csv'].includes(ext)) return 'fa-regular fa-file-excel';
        if (['ppt','pptx'].includes(ext)) return 'fa-regular fa-file-powerpoint';
        if (['zip','rar','7z'].includes(ext)) return 'fa-regular fa-file-zipper';
        if (['mp3','wav','ogg','aac','m4a','flac'].includes(ext)) return 'fa-regular fa-file-audio';
        if (['mp4','mov','avi','mkv'].includes(ext)) return 'fa-regular fa-file-video';

        return ''; // icon yoxdursa heç nə
    }

    function isImageExt(ext) {
        ext = (ext || '').toLowerCase();
        return ['jpg','jpeg','png','gif','webp','svg','bmp','tiff'].includes(ext);
    }


    function formatSizeKB(size) {
        if (!size) return '';
        return Math.round(size / 1024) + ' KB';
    }

    function buildItemHTML(file) {
        const ext = (file.extension || '').toLowerCase();
        const url = file.url || '#';
        const sizeLabel = formatSizeKB(file.size);

        const left = isImageExt(ext)
            ? `<a href="${url}" target="_blank" class="d-inline-block">
                <img src="${url}" alt=""
                     class="rounded-2 border"
                     style="width:44px;height:44px;object-fit:cover;">
           </a>`
            : (faIconByExt(ext)
                ? `<i class="${faIconByExt(ext)} fs-3"></i>`
                : ``);

        return `
        <div class="d-flex align-items-center justify-content-between border rounded-3 p-2 mb-2 js-file-item"
             style="background:#fff;"
             draggable="true"
             data-id="${file.id}"
             data-delete-url="${file.delete_url}">
            <div class="d-flex align-items-center gap-2">
                ${left}
                <div class="d-flex flex-column">
                    <a href="${url}" target="_blank" class="fw-semibold text-decoration-none">
                        ${escapeHtml(file.original_name || '')}
                    </a>
                    <div class="text-muted small">
                        ${(ext ? ext.toUpperCase() : 'FILE')}
                        ${sizeLabel ? ` · ${sizeLabel}` : ''}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="ri-drag-move-2-line text-muted"></i>
                <button type="button" class="btn btn-sm btn-outline-danger js-file-delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
    `;
    }


    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function currentOrderIds(listEl) {
        return Array.from(listEl.querySelectorAll('.js-file-item'))
            .map(el => parseInt(el.getAttribute('data-id') || '0', 10))
            .filter(id => id > 0);
    }

    function saveReorder(listEl) {
        const url = listEl.getAttribute('data-reorder-url');
        if (!url) return;

        const ids = currentOrderIds(listEl);

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
            .then(async (r) => {
                if (!r.ok) {
                    const data = await r.json().catch(() => null);
                    throw data || new Error('Reorder failed');
                }
                return r.json();
            })
            .then(() => {
                // silent success
            })
            .catch(() => {
                toast('error', 'Order update failed.');
            });
    }

    function initDrag(listEl) {
        let dragEl = null;

        listEl.addEventListener('dragstart', function (e) {
            const item = e.target.closest('.js-file-item');
            if (!item) return;
            dragEl = item;
            item.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
        });

        listEl.addEventListener('dragend', function (e) {
            const item = e.target.closest('.js-file-item');
            if (!item) return;
            item.classList.remove('opacity-50');
            dragEl = null;
        });

        listEl.addEventListener('dragover', function (e) {
            e.preventDefault();
            const over = e.target.closest('.js-file-item');
            if (!over || !dragEl || over === dragEl) return;

            const rect = over.getBoundingClientRect();
            const next = (e.clientY - rect.top) > rect.height / 2;
            listEl.insertBefore(dragEl, next ? over.nextSibling : over);
        });

        listEl.addEventListener('drop', function (e) {
            e.preventDefault();
            if (!dragEl) return;
            saveReorder(listEl);
        });
    }

    function initDelete(listEl) {
        listEl.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-file-delete');
            if (!btn) return;

            const item = btn.closest('.js-file-item');
            if (!item) return;

            const url = item.getAttribute('data-delete-url');
            if (!url) return;

            const doDelete = () => {
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    }
                })
                    .then(async (r) => {
                        if (!r.ok) {
                            const data = await r.json().catch(() => null);
                            throw data || new Error('Delete failed');
                        }
                        return r.json();
                    })
                    .then(() => {
                        item.remove();
                        if (listEl.querySelectorAll('.js-file-item').length === 0) {
                            if (!listEl.querySelector('.js-empty-hint')) {
                                const div = document.createElement('div');
                                div.className = 'text-muted small js-empty-hint';
                                div.textContent = 'No files uploaded yet.';
                                listEl.appendChild(div);
                            }
                        }
                        saveReorder(listEl);
                        toast('success', 'File deleted.');
                    })
                    .catch(() => {
                        toast('error', 'File delete failed.');
                    });
            };

            if (window.Swal && Swal.fire) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Delete?',
                    text: 'This file will be removed.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then((res) => {
                    if (res.isConfirmed) doDelete();
                });
                return;
            }

            if (confirm('Delete this file?')) doDelete();
        });
    }

    function showUploading(dropzone, text) {
        const overlay = dropzone.querySelector('.js-uploading-overlay');
        const t = dropzone.querySelector('.js-uploading-text');
        if (t) t.textContent = text || '';
        if (overlay) overlay.classList.remove('d-none');
    }

    function hideUploading(dropzone) {
        const overlay = dropzone.querySelector('.js-uploading-overlay');
        if (overlay) overlay.classList.add('d-none');
    }

    function uploadFiles(listEl, dropzone, files) {
        const url = listEl.getAttribute('data-upload-url');
        if (!url) {
            toast('error', 'Upload URL not found.');
            return;
        }

        const fd = new FormData();
        Array.from(files).forEach((f) => fd.append('files[]', f));

        showUploading(dropzone, `${files.length} file(s)`);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function () {
            hideUploading(dropzone);

            let data = null;
            try { data = JSON.parse(xhr.responseText || '{}'); } catch (e) {}

            if (xhr.status >= 200 && xhr.status < 300 && data && data.ok) {
                const emptyHint = listEl.querySelector('.js-empty-hint');
                if (emptyHint) emptyHint.remove();

                (data.files || []).forEach((f) => {
                    listEl.insertAdjacentHTML('beforeend', buildItemHTML(f));
                });

                saveReorder(listEl);

                const errorsEl = document.querySelector('.js-upload-errors');
                if (errorsEl) errorsEl.innerHTML = '';

                const skipped = data.skipped || [];
                if (skipped.length) {
                    toast('warning', data.message || 'Uploaded with warnings.');

                    if (errorsEl) {
                        const items = skipped
                            .map(x => `<li class="mb-1">${escapeHtml(x.reason || '')}</li>`)
                            .join('');

                        errorsEl.innerHTML = `
                <div class="alert alert-warning py-2 mb-0">
                    <div class="fw-semibold mb-1">Some files were skipped:</div>
                    <ul class="mb-0 ps-3">${items}</ul>
                </div>
            `;
                    }
                } else {
                    toast('success', data.message || 'Uploaded successfully.');
                }

                return;
            }

            const msg =
                (data && data.message) ||
                (data && data.errors && Object.values(data.errors)[0] && Object.values(data.errors)[0][0]) ||
                'Upload failed.';

            toast('error', msg);
        };

        xhr.onerror = function () {
            hideUploading(dropzone);
            toast('error', 'Upload failed.');
        };

        xhr.send(fd);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const dropzone = document.querySelector('.js-file-dropzone');
        const input = document.getElementById('contentFilesInput');
        const listEl = document.querySelector('.js-files-list');
        const pickBtn = document.querySelector('.js-files-pick');

        if (!dropzone || !input || !listEl) return;

        if (pickBtn) {
            pickBtn.addEventListener('click', function () {
                input.click();
            });
        }

        dropzone.addEventListener('click', function () {
            input.click();
        });

        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('border-primary');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('border-primary');
        });

        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('border-primary');

            const files = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
            if (files && files.length) {
                uploadFiles(listEl, dropzone, files);
                input.value = '';
            }
        });

        input.addEventListener('change', function () {
            const files = input.files;
            if (files && files.length) {
                uploadFiles(listEl, dropzone, files);
                input.value = '';
            }
        });

        initDrag(listEl);
        initDelete(listEl);
    });
})();
