<script>
(function() {
    // ============================================
    // EXISTING FILES MANAGEMENT
    // ============================================

    // Delete existing file
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-file-delete');
        if (!btn) return;

        const item = btn.closest('.team-file-item');
        if (!item) return;

        const flag = item.querySelector('.js-file-delete-flag');
        if (flag) {
            flag.value = '1';
        }

        item.style.opacity = '0.3';
        item.style.pointerEvents = 'none';

        const badge = document.createElement('span');
        badge.className = 'badge bg-danger position-absolute top-0 start-50 translate-middle-x mt-2';
        badge.textContent = '{{ __("Will be deleted") }}';
        item.querySelector('.border').style.position = 'relative';
        item.querySelector('.border').appendChild(badge);
    });

    // Drag and drop for existing files
    const existingList = document.querySelector('.js-existing-files-list');
    if (existingList) {
        let draggedItem = null;

        existingList.addEventListener('dragstart', function(e) {
            const item = e.target.closest('.team-file-item');
            if (!item) return;

            draggedItem = item;
            item.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
        });

        existingList.addEventListener('dragend', function(e) {
            if (draggedItem) {
                draggedItem.classList.remove('opacity-50');
            }
            draggedItem = null;
        });

        existingList.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        existingList.addEventListener('drop', function(e) {
            e.preventDefault();
            if (!draggedItem) return;

            const target = e.target.closest('.team-file-item');
            if (!target || target === draggedItem) return;

            const rect = target.getBoundingClientRect();
            const isAfter = (e.clientY - rect.top) > (rect.height / 2);

            if (isAfter) {
                target.parentNode.insertBefore(draggedItem, target.nextSibling);
            } else {
                target.parentNode.insertBefore(draggedItem, target);
            }

            // Update sort orders
            updateExistingSortOrders();
        });

        function updateExistingSortOrders() {
            const items = existingList.querySelectorAll('.team-file-item');
            items.forEach((item, index) => {
                const sortInput = item.querySelector('.js-file-sort');
                if (sortInput) {
                    sortInput.value = index;
                }
            });
        }

        updateExistingSortOrders();
    }

    // ============================================
    // NEW FILES MANAGEMENT
    // ============================================

    const fileInput = document.querySelector('.js-team-files-input');
    const newFilesList = document.querySelector('.js-new-files-list');

    if (fileInput && newFilesList) {
        let newFilesState = [];

        fileInput.addEventListener('change', function() {
            const files = Array.from(fileInput.files || []);

            newFilesState = files.map(function(file) {
                return {
                    file: file,
                    preview: URL.createObjectURL(file)
                };
            });

            renderNewFiles();
            initNewFilesActions();
        });

        function renderNewFiles() {
            newFilesList.innerHTML = '';

            newFilesState.forEach(function(item, index) {
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 col-md-3 col-xl-2 team-new-file-item';
                col.draggable = true;
                col.dataset.newIndex = index;

                const extension = item.file.name.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);

                let previewHtml = '';
                if (isImage) {
                    previewHtml = `<img src="${item.preview}" alt="" class="img-fluid rounded" style="width: 100%; height: 90px; object-fit: cover;">`;
                } else {
                    previewHtml = `
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 90px;">
                            <i class="ri-file-line fs-1 text-muted"></i>
                        </div>
                    `;
                }

                col.innerHTML = `
                    <div class="border rounded p-2 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-light text-dark" style="cursor: grab;">
                                <i class="ri-drag-move-2-line"></i>
                            </span>

                            <button type="button" class="btn btn-sm btn-soft-danger js-new-file-remove" title="Remove">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>

                        ${previewHtml}

                        <div class="mt-2 small text-truncate" title="${item.file.name}">
                            ${item.file.name}
                        </div>
                    </div>
                `;

                newFilesList.appendChild(col);
            });

            initNewFilesDragDrop();
        }

        function initNewFilesActions() {
            newFilesList.querySelectorAll('.js-new-file-remove').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const item = btn.closest('.team-new-file-item');
                    if (!item) return;

                    const index = parseInt(item.dataset.newIndex);

                    // Revoke object URL to free memory
                    if (newFilesState[index] && newFilesState[index].preview) {
                        URL.revokeObjectURL(newFilesState[index].preview);
                    }

                    newFilesState.splice(index, 1);

                    // Rebuild file input
                    const dt = new DataTransfer();
                    newFilesState.forEach(function(item) {
                        dt.items.add(item.file);
                    });
                    fileInput.files = dt.files;

                    renderNewFiles();
                    initNewFilesActions();
                });
            });
        }

        function initNewFilesDragDrop() {
            let draggedNewItem = null;

            newFilesList.addEventListener('dragstart', function(e) {
                const item = e.target.closest('.team-new-file-item');
                if (!item) return;

                draggedNewItem = item;
                item.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
            });

            newFilesList.addEventListener('dragend', function() {
                if (draggedNewItem) {
                    draggedNewItem.classList.remove('opacity-50');
                }
                draggedNewItem = null;
            });

            newFilesList.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });

            newFilesList.addEventListener('drop', function(e) {
                e.preventDefault();
                if (!draggedNewItem) return;

                const target = e.target.closest('.team-new-file-item');
                if (!target || target === draggedNewItem) return;

                const from = parseInt(draggedNewItem.dataset.newIndex);
                const to = parseInt(target.dataset.newIndex);

                const rect = target.getBoundingClientRect();
                const isAfter = (e.clientY - rect.top) > (rect.height / 2);
                const insertIndex = isAfter ? to + 1 : to;

                // Reorder array
                const item = newFilesState.splice(from, 1)[0];
                const finalIndex = insertIndex > from ? insertIndex - 1 : insertIndex;
                newFilesState.splice(finalIndex, 0, item);

                // Rebuild file input
                const dt = new DataTransfer();
                newFilesState.forEach(function(item) {
                    dt.items.add(item.file);
                });
                fileInput.files = dt.files;

                renderNewFiles();
                initNewFilesActions();
            });
        }
    }
})();
</script>
