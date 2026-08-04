<script>
    (function () {
        const ctx = window.__productForm = window.__productForm || {};

        ctx.updateMediaSortOrders = function (listEl) {
            const items = Array.from(listEl.querySelectorAll('.variation-media-item'));
            items.forEach(function (item, i) {
                const sort = item.querySelector('.js-media-sort');
                if (sort) sort.value = String(i);
            });
        };

        ctx.clearExistingMainFlags = function (variationEl) {
            variationEl.querySelectorAll('.js-media-is-main').forEach(function (h) {
                h.value = '0';
            });

            variationEl.querySelectorAll('.variation-media-item .border').forEach(function (x) {
                x.classList.remove('border-primary');
            });

            variationEl.querySelectorAll('.js-media-main').forEach(function (r) {
                r.checked = false;
            });
        };

        ctx.initExistingMainToggles = function (root) {
            (root || document).querySelectorAll('.js-media-main').forEach(function (radio) {
                if (radio.dataset.mainInited === '1') return;
                radio.dataset.mainInited = '1';

                radio.addEventListener('change', function () {
                    const itemEl = radio.closest('.variation-media-item');
                    if (!itemEl) return;

                    const variationEl = radio.closest('.variation-item');
                    if (!variationEl) return;

                    const newMainHidden = variationEl.querySelector('.js-new-main-index');
                    if (newMainHidden) newMainHidden.value = '';

                    ctx.clearExistingMainFlags(variationEl);

                    const hidden = itemEl.querySelector('.js-media-is-main');
                    if (hidden) hidden.value = '1';

                    const border = itemEl.querySelector('.border');
                    if (border) border.classList.add('border-primary');

                    radio.checked = true;
                });
            });
        };

        ctx.initExistingDragDrop = function (root) {
            (root || document).querySelectorAll('.variation-media-list').forEach(function (listEl) {
                if (listEl.dataset.dragInited === '1') return;
                listEl.dataset.dragInited = '1';

                let dragged = null;

                listEl.addEventListener('dragstart', function (e) {
                    const item = e.target.closest('.variation-media-item');
                    if (!item) return;

                    dragged = item;
                    item.classList.add('opacity-50');
                    e.dataTransfer.effectAllowed = 'move';
                });

                listEl.addEventListener('dragend', function () {
                    if (dragged) dragged.classList.remove('opacity-50');
                    dragged = null;
                });

                listEl.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                });

                listEl.addEventListener('drop', function (e) {
                    e.preventDefault();
                    if (!dragged) return;

                    const target = e.target.closest('.variation-media-item');
                    if (!target || target === dragged) return;

                    const rect = target.getBoundingClientRect();
                    const isAfter = (e.clientY - rect.top) > (rect.height / 2);

                    if (isAfter) {
                        target.parentNode.insertBefore(dragged, target.nextSibling);
                    } else {
                        target.parentNode.insertBefore(dragged, target);
                    }

                    ctx.updateMediaSortOrders(listEl);
                });

                ctx.updateMediaSortOrders(listEl);
            });
        };

        ctx.initExistingDeleteButtons = function (root) {
            (root || document).querySelectorAll('.js-media-delete').forEach(function (btn) {
                if (btn.dataset.delInited === '1') return;
                btn.dataset.delInited = '1';

                btn.addEventListener('click', function () {
                    const itemEl = btn.closest('.variation-media-item');
                    if (!itemEl) return;

                    const flag = itemEl.querySelector('.js-media-delete-flag');
                    if (flag) flag.value = '1';

                    const isMainHidden = itemEl.querySelector('.js-media-is-main');
                    if (isMainHidden && String(isMainHidden.value) === '1') {
                        isMainHidden.value = '0';
                    }

                    itemEl.classList.add('d-none');

                    const listEl = itemEl.closest('.variation-media-list');
                    if (listEl) ctx.updateMediaSortOrders(listEl);
                });
            });
        };

        function getOrCreateNewMetaFields(variationEl) {
            let sortWrap = variationEl.querySelector('.js-new-sort-wrap');
            let mainHidden = variationEl.querySelector('.js-new-main-index');

            const idx = Number(variationEl.dataset.variationIndex || 0);

            if (!sortWrap) {
                sortWrap = document.createElement('div');
                sortWrap.className = 'js-new-sort-wrap d-none';
                variationEl.appendChild(sortWrap);
            }

            if (!mainHidden) {
                mainHidden = document.createElement('input');
                mainHidden.type = 'hidden';
                mainHidden.className = 'js-new-main-index';
                mainHidden.name = 'variations[' + idx + '][media_new_main]';
                mainHidden.value = '';
                variationEl.appendChild(mainHidden);
            }

            return { sortWrap, mainHidden };
        }

        function buildNewMediaItem(index, srcUrl) {
            const col = document.createElement('div');
            col.className = 'col-6 col-sm-4 col-md-3 col-xl-2 variation-new-media-item';
            col.draggable = true;
            col.dataset.newIndex = String(index);

            col.innerHTML = `
                <div class="border rounded p-2 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-light text-dark js-new-drag" style="cursor: grab;">
                            <i class="ri-drag-move-2-line"></i>
                        </span>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-soft-danger js-new-remove" title="{{ __('Delete') }}">
                                <i class="ri-delete-bin-6-line"></i>
                            </button>
                        </div>
                    </div>

                    <img src="${srcUrl}" alt="" class="img-fluid rounded js-new-preview" style="width:100%; height:90px; object-fit:cover;">

                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input js-new-main-radio" type="radio" name="__NEW_MAIN__">
                            <label class="form-check-label">{{ __('Make main image') }}</label>
                        </div>
                    </div>
                </div>
            `;

            return col;
        }

        function syncNewMetaInputs(variationEl, state) {
            const idx = Number(variationEl.dataset.variationIndex || 0);
            const meta = getOrCreateNewMetaFields(variationEl);
            const sortWrap = meta.sortWrap;
            const mainHidden = meta.mainHidden;

            sortWrap.innerHTML = '';

            state.items.forEach(function (item, i) {
                const s = document.createElement('input');
                s.type = 'hidden';
                s.name = 'variations[' + idx + '][media_new_sort][]';
                s.value = String(i);
                sortWrap.appendChild(s);
            });

            mainHidden.value = (state.mainIndex === null || state.mainIndex === undefined) ? '' : String(state.mainIndex);
        }

        function rebuildFileInputFromState(inputEl, state) {
            const dt = new DataTransfer();
            state.items.forEach(function (x) {
                dt.items.add(x.file);
            });
            inputEl.files = dt.files;
        }

        ctx.initNewMediaManager = function (variationEl) {
            const input = variationEl.querySelector('input[type="file"][name*="[media_files]"]');
            if (!input) return;

            const list = variationEl.querySelector('.js-new-media-list');
            if (!list) return;

            if (!variationEl._newMediaState) {
                variationEl._newMediaState = { items: [], mainIndex: null };
            }

            const state = variationEl._newMediaState;

            function render() {
                list.innerHTML = '';
                const groupName = 'new_main_' + Number(variationEl.dataset.variationIndex || 0);

                state.items.forEach(function (item, i) {
                    const el = buildNewMediaItem(i, item.preview);
                    el.querySelector('.js-new-main-radio').name = groupName;
                    el.querySelector('.js-new-main-radio').checked = (state.mainIndex === i);
                    list.appendChild(el);
                });

                syncNewMetaInputs(variationEl, state);
            }

            function initNewActions() {
                list.querySelectorAll('.variation-new-media-item').forEach(function (itemEl) {
                    const i = Number(itemEl.dataset.newIndex || 0);

                    const btnRemove = itemEl.querySelector('.js-new-remove');
                    const radioMain = itemEl.querySelector('.js-new-main-radio');

                    if (btnRemove) {
                        btnRemove.onclick = function () {
                            const removed = state.items.splice(i, 1);

                            if (removed && removed[0] && removed[0].preview) {
                                try { URL.revokeObjectURL(removed[0].preview); } catch (e) {}
                            }

                            if (state.mainIndex === i) {
                                state.mainIndex = null;
                            } else if (state.mainIndex !== null && state.mainIndex > i) {
                                state.mainIndex = state.mainIndex - 1;
                            }

                            rebuildFileInputFromState(input, state);

                            list.dataset.dragInited = '0';
                            render();
                            initNewDnD();
                            initNewActions();
                        };
                    }

                    if (radioMain) {
                        radioMain.onchange = function () {
                            if (!radioMain.checked) return;

                            const existingMainHidden = variationEl.querySelector('.js-new-main-index');
                            if (existingMainHidden) existingMainHidden.value = String(i);

                            ctx.clearExistingMainFlags(variationEl);

                            state.mainIndex = i;
                            syncNewMetaInputs(variationEl, state);
                        };
                    }
                });
            }

            function initNewDnD() {
                if (list.dataset.dragInited === '1') return;
                list.dataset.dragInited = '1';

                let dragged = null;

                list.addEventListener('dragstart', function (e) {
                    const item = e.target.closest('.variation-new-media-item');
                    if (!item) return;

                    dragged = item;
                    item.classList.add('opacity-50');
                    e.dataTransfer.effectAllowed = 'move';
                });

                list.addEventListener('dragend', function () {
                    if (dragged) dragged.classList.remove('opacity-50');
                    dragged = null;
                });

                list.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                });

                list.addEventListener('drop', function (e) {
                    e.preventDefault();
                    if (!dragged) return;

                    const target = e.target.closest('.variation-new-media-item');
                    if (!target || target === dragged) return;

                    const from = Number(dragged.dataset.newIndex || 0);
                    const to = Number(target.dataset.newIndex || 0);

                    const rect = target.getBoundingClientRect();
                    const isAfter = (e.clientY - rect.top) > (rect.height / 2);
                    const insertIndex = isAfter ? to + 1 : to;

                    const item = state.items.splice(from, 1)[0];
                    state.items.splice(insertIndex > from ? insertIndex - 1 : insertIndex, 0, item);

                    if (state.mainIndex === from) {
                        state.mainIndex = (insertIndex > from ? insertIndex - 1 : insertIndex);
                    } else if (state.mainIndex !== null) {
                        if (from < state.mainIndex && insertIndex > state.mainIndex) state.mainIndex -= 1;
                        if (from > state.mainIndex && insertIndex <= state.mainIndex) state.mainIndex += 1;
                    }

                    rebuildFileInputFromState(input, state);

                    list.dataset.dragInited = '0';
                    render();
                    initNewDnD();
                    initNewActions();
                });
            }

            input.addEventListener('change', function () {
                const files = Array.from(input.files || []);
                state.items = files.map(function (f) {
                    return {
                        file: f,
                        preview: URL.createObjectURL(f),
                    };
                });
                state.mainIndex = null;

                list.dataset.dragInited = '0';
                render();
                initNewDnD();
                initNewActions();
            });

            render();
            initNewDnD();
            initNewActions();
        };
    })();
</script>
