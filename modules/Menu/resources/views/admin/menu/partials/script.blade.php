<script>
    (function () {
        const viewTypeMap = @json($viewTypeOptionsByType ?? []);
        const currentViewType = @json(old('view_type', $menuViewTypeValue));

        const typeSelect = document.querySelector('select[name="type"]');
        const viewWrap = document.getElementById('viewTypeWrap');
        const viewSelect = document.getElementById('viewTypeSelect');

        const includedMenuPicker = document.getElementById('includedMenuPicker');
        const includedMenuPickerControl = document.getElementById('includedMenuPickerControl');
        const includedMenuPickerControlText = document.getElementById('includedMenuPickerControlText');
        const includedMenuSearch = document.getElementById('includedMenuSearch');
        const includedMenuOptionsList = document.getElementById('includedMenuOptionsList');
        const includedMenuEmptyResult = document.getElementById('includedMenuEmptyResult');
        const includedMenusList = document.getElementById('includedMenusList');
        const includedMenusEmptyState = document.getElementById('includedMenusEmptyState');

        function clearSelect(select) {
            while (select.firstChild) {
                select.removeChild(select.firstChild);
            }
        }

        function refreshViewType() {
            if (! typeSelect || ! viewWrap || ! viewSelect) {
                return;
            }

            const selectedType = (typeSelect.value || '').toString();
            const options = viewTypeMap[selectedType] || [];

            if (! Array.isArray(options) || options.length === 0) {
                viewWrap.style.display = 'none';
                clearSelect(viewSelect);
                viewSelect.value = '';
                return;
            }

            viewWrap.style.display = '';
            clearSelect(viewSelect);

            options.forEach(function (option) {
                const element = document.createElement('option');
                element.value = option.value;
                element.textContent = option.label ?? option.value;
                viewSelect.appendChild(element);
            });

            if (currentViewType) {
                viewSelect.value = currentViewType;
            }
        }

        function parseKeywords(value) {
            if (! value) {
                return [];
            }

            return value.split(',')
                .map(function (item) {
                    return item.trim();
                })
                .filter(Boolean);
        }

        function setHidden(hiddenInput, items) {
            hiddenInput.value = items.join(', ');
        }

        function renderChip(wrap, hiddenInput, keyword) {
            const chip = document.createElement('span');
            chip.className = 'badge bg-light text-dark d-inline-flex align-items-center gap-2';
            chip.style.padding = '0.45rem 0.6rem';
            chip.innerHTML = '<span></span><button type="button" class="btn btn-sm p-0" style="line-height:1"><i class="ri-close-line"></i></button>';
            chip.querySelector('span').textContent = keyword;

            chip.querySelector('button').addEventListener('click', function () {
                const current = parseKeywords(hiddenInput.value);
                const next = current.filter(function (item) {
                    return item.toLowerCase() !== keyword.toLowerCase();
                });

                setHidden(hiddenInput, next);
                chip.remove();
            });

            wrap.appendChild(chip);
        }

        function initKeywordInputs() {
            document.querySelectorAll('.js-meta-keyword-input').forEach(function (input) {
                if (input.dataset.bound === '1') {
                    return;
                }

                input.dataset.bound = '1';

                const hidden = document.getElementById(input.dataset.hiddenId);
                const wrap = document.getElementById(input.dataset.wrapId);

                if (! hidden || ! wrap) {
                    return;
                }

                const existing = parseKeywords(hidden.value);
                wrap.innerHTML = '';

                existing.forEach(function (keyword) {
                    renderChip(wrap, hidden, keyword);
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();

                    const keyword = (input.value || '').trim();
                    if (! keyword) {
                        return;
                    }

                    const current = parseKeywords(hidden.value);
                    const exists = current.some(function (item) {
                        return item.toLowerCase() === keyword.toLowerCase();
                    });

                    if (exists) {
                        input.value = '';
                        return;
                    }

                    current.push(keyword);
                    setHidden(hidden, current);
                    renderChip(wrap, hidden, keyword);
                    input.value = '';
                });

                input.addEventListener('blur', function () {
                    const keyword = (input.value || '').trim();
                    if (! keyword) {
                        return;
                    }

                    const current = parseKeywords(hidden.value);
                    const exists = current.some(function (item) {
                        return item.toLowerCase() === keyword.toLowerCase();
                    });

                    if (! exists) {
                        current.push(keyword);
                        setHidden(hidden, current);
                        renderChip(wrap, hidden, keyword);
                    }

                    input.value = '';
                });
            });
        }

        function refreshIncludedItemsEmptyState() {
            if (! includedMenusList || ! includedMenusEmptyState) {
                return;
            }

            includedMenusEmptyState.classList.toggle('d-none', includedMenusList.children.length > 0);
        }

        function reindexIncludedItems() {
            if (! includedMenusList) {
                return;
            }

            includedMenusList.querySelectorAll('.included-menu-card').forEach(function (item, index) {
                const typeInput = item.querySelector('[data-included-item-type-input]');
                const idInput = item.querySelector('[data-included-item-id-input]');

                if (typeInput) {
                    typeInput.name = 'included_items[' + index + '][type]';
                }

                if (idInput) {
                    idInput.name = 'included_items[' + index + '][id]';
                }
            });
        }

        function createIncludedMenuItem(type, id, label) {
            const item = document.createElement('div');
            item.className = 'included-menu-card';
            item.dataset.type = String(type);
            item.dataset.id = String(id);
            item.innerHTML = `
                <div class="included-menu-card__left">
                    <span class="included-menu-card__drag js-included-menu-sort-handle">
                        <i class="ri-drag-move-2-line"></i>
                    </span>
                    <div class="included-menu-card__text"></div>
                </div>
                <div class="included-menu-card__right">
                    <input type="hidden" data-included-item-type-input value="">
                    <input type="hidden" data-included-item-id-input value="">
                    <button type="button" class="included-menu-card__remove js-remove-included-menu">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `;

            item.querySelector('.included-menu-card__text').textContent = label;
            item.querySelector('[data-included-item-type-input]').value = String(type);
            item.querySelector('[data-included-item-id-input]').value = String(id);

            return item;
        }

        function hasIncludedMenu(type, id) {
            if (! includedMenusList) {
                return false;
            }

            return !! includedMenusList.querySelector(
                '.included-menu-card[data-type="' + String(type) + '"][data-id="' + String(id) + '"]'
            );
        }

        function bindIncludedMenuRemove(scope) {
            scope.querySelectorAll('.js-remove-included-menu').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }

                button.dataset.bound = '1';

                button.addEventListener('click', function () {
                    const item = this.closest('.included-menu-card');

                    if (! item) {
                        return;
                    }

                    item.remove();
                    reindexIncludedItems();
                    refreshIncludedItemsEmptyState();
                    updateIncludedMenuPickerPlaceholder();
                });
            });
        }

        function openIncludedMenuPicker() {
            if (! includedMenuPicker || ! includedMenuPickerControl) {
                return;
            }

            includedMenuPicker.classList.add('is-open');
            includedMenuPickerControl.setAttribute('aria-expanded', 'true');

            if (includedMenuSearch) {
                setTimeout(function () {
                    includedMenuSearch.focus();
                }, 10);
            }
        }

        function closeIncludedMenuPicker() {
            if (! includedMenuPicker || ! includedMenuPickerControl) {
                return;
            }

            includedMenuPicker.classList.remove('is-open');
            includedMenuPickerControl.setAttribute('aria-expanded', 'false');
        }

        function updateIncludedMenuPickerPlaceholder() {
            if (! includedMenuPickerControlText || ! includedMenusList) {
                return;
            }

            const count = includedMenusList.querySelectorAll('.included-menu-card').length;

            if (count > 0) {
                includedMenuPickerControlText.textContent = '{{ __('Add another included item') }}';
                return;
            }

            includedMenuPickerControlText.textContent = '{{ __('Select included item') }}';
        }

        function filterIncludedMenuOptions() {
            if (! includedMenuOptionsList || ! includedMenuSearch) {
                return;
            }

            const keyword = (includedMenuSearch.value || '').trim().toLowerCase();
            let visibleCount = 0;

            includedMenuOptionsList.querySelectorAll('.included-menu-picker__item').forEach(function (item) {
                const haystack = (
                    (item.dataset.search || '') + ' ' +
                    (item.dataset.label || '') + ' ' +
                    (item.dataset.type || '')
                ).toLowerCase();

                const isVisible = keyword === '' || haystack.indexOf(keyword) !== -1;

                item.classList.toggle('is-hidden', ! isVisible);

                if (isVisible) {
                    visibleCount++;
                }
            });

            if (includedMenuEmptyResult) {
                includedMenuEmptyResult.classList.toggle('d-none', visibleCount > 0);
            }

            if (includedMenuOptionsList) {
                includedMenuOptionsList.classList.toggle('d-none', visibleCount === 0);
            }
        }

        function addIncludedMenu(type, id, label) {
            if (! includedMenusList || ! type || id < 0) {
                return;
            }

            if (hasIncludedMenu(type, id)) {
                closeIncludedMenuPicker();
                return;
            }

            const item = createIncludedMenuItem(type, id, label);

            includedMenusList.appendChild(item);
            bindIncludedMenuRemove(includedMenusList);
            reindexIncludedItems();
            refreshIncludedItemsEmptyState();
            updateIncludedMenuPickerPlaceholder();
            closeIncludedMenuPicker();

            if (includedMenuSearch) {
                includedMenuSearch.value = '';
                filterIncludedMenuOptions();
            }
        }

        function initIncludedItems() {
            if (! includedMenuPicker || ! includedMenuPickerControl || ! includedMenuOptionsList || ! includedMenusList) {
                return;
            }

            if (window.Sortable) {
                Sortable.create(includedMenusList, {
                    animation: 150,
                    handle: '.js-included-menu-sort-handle',
                    onEnd: function () {
                        reindexIncludedItems();
                    },
                });
            }

            bindIncludedMenuRemove(document);
            reindexIncludedItems();
            refreshIncludedItemsEmptyState();
            updateIncludedMenuPickerPlaceholder();
            filterIncludedMenuOptions();

            includedMenuPickerControl.addEventListener('click', function () {
                if (includedMenuPicker.classList.contains('is-open')) {
                    closeIncludedMenuPicker();
                    return;
                }

                openIncludedMenuPicker();
            });

            document.addEventListener('click', function (event) {
                if (! includedMenuPicker.contains(event.target)) {
                    closeIncludedMenuPicker();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeIncludedMenuPicker();
                }
            });

            if (includedMenuSearch) {
                includedMenuSearch.addEventListener('input', filterIncludedMenuOptions);
            }

            includedMenuOptionsList.querySelectorAll('.included-menu-picker__item').forEach(function (item) {
                item.addEventListener('click', function () {
                    const type = this.dataset.type || '';
                    const id = parseInt(this.dataset.id || '0', 10);
                    const label = this.dataset.label || this.textContent || ('#' + id);

                    if (! type || id < 0) {
                        return;
                    }

                    addIncludedMenu(type, id, label);
                });
            });

            const form = includedMenusList.closest('form');

            if (form) {
                form.addEventListener('submit', function () {
                    reindexIncludedItems();
                });
            }
        }

        function initFilePreviewInputs() {
            document.querySelectorAll('.js-file-preview-input').forEach(function (input) {
                if (input.dataset.bound === '1') {
                    return;
                }

                input.dataset.bound = '1';

                const previewImage = document.querySelector(input.dataset.previewImage || '');
                const previewWrapper = document.querySelector(input.dataset.previewWrapper || '');
                const fileNameElement = document.querySelector(input.dataset.fileName || '');

                input.addEventListener('change', function () {
                    const file = input.files && input.files[0] ? input.files[0] : null;

                    if (! file) {
                        if (fileNameElement) {
                            fileNameElement.textContent = '';
                        }

                        return;
                    }

                    if (fileNameElement) {
                        fileNameElement.textContent = file.name;
                    }

                    if (! previewImage || ! previewWrapper || ! file.type.startsWith('image/')) {
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function (event) {
                        previewImage.src = event.target?.result || '';
                        previewWrapper.classList.remove('d-none');
                    };

                    reader.readAsDataURL(file);
                });
            });
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', refreshViewType);
        }

        initKeywordInputs();
        refreshViewType();
        initIncludedItems();
        initFilePreviewInputs();
    })();
</script>
