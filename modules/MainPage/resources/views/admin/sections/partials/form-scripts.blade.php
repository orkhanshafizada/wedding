@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sourceTypeElement = document.getElementById('source_type');
            const menuTypeElement = document.getElementById('menu_type');
            const sourceReferenceElement = document.getElementById('source_reference');

            const menuTypeWrapper = document.querySelector('.menu-type-wrapper');
            const sourceReferenceWrapper = document.querySelector('.source-reference-wrapper');
            const menuViewTypeWrapper = document.querySelector('.menu-view-type-wrapper');

            const menuTypeRequiredMarker = document.querySelector('.menu-type-required-marker');
            const sourceReferenceRequiredMarker = document.querySelector('.source-reference-required-marker');

            const selectedSourceReference = @json(old('source_reference', $section->source_reference ?? null));
            const selectedMenuType = @json(old('menu_type', $section->menu_type ?? null));

            let menuTypeChoicesInstance = null;
            let sourceReferenceChoicesInstance = null;
            let sourceReferenceHasInitialValue = true;

            function createChoicesInstance(element) {
                if (!window.Choices || !element) {
                    return null;
                }

                if (element.choicesInstance) {
                    return element.choicesInstance;
                }

                element.choicesInstance = new Choices(element, {
                    removeItemButton: false,
                    searchEnabled: true,
                    searchPlaceholderValue: '{{ __('Search') }}',
                    noResultsText: '{{ __('No results found') }}',
                    noChoicesText: '{{ __('No choices to choose from') }}',
                    itemSelectText: '',
                    shouldSort: false,
                    allowHTML: false
                });

                return element.choicesInstance;
            }

            function initializeChoices() {
                menuTypeChoicesInstance = createChoicesInstance(menuTypeElement);
                sourceReferenceChoicesInstance = createChoicesInstance(sourceReferenceElement);
            }

            function sourceTypeNeedsSourceReference(sourceType) {
                return [
                    'banner',
                    'product_block',
                    'menu_type'
                ].includes(sourceType);
            }

            function sourceTypeNeedsMenuType(sourceType) {
                return sourceType === 'menu_type';
            }

            function sourceTypeNeedsMenuViewType(sourceType) {
                return sourceType === 'menu_type';
            }

            function setWrapperVisibility(wrapper, visible) {
                if (!wrapper) {
                    return;
                }

                wrapper.style.display = visible ? '' : 'none';
            }

            function setRequiredMarkerVisibility(marker, visible) {
                if (!marker) {
                    return;
                }

                marker.classList.toggle('d-none', !visible);
            }

            function syncFieldVisibility() {
                const sourceType = String(sourceTypeElement?.value || '');

                const needsMenuType = sourceTypeNeedsMenuType(sourceType);
                const needsSourceReference = sourceTypeNeedsSourceReference(sourceType);
                const needsMenuViewType = sourceTypeNeedsMenuViewType(sourceType);

                setWrapperVisibility(menuTypeWrapper, needsMenuType);
                setWrapperVisibility(sourceReferenceWrapper, needsSourceReference);
                setWrapperVisibility(menuViewTypeWrapper, needsMenuViewType);

                setRequiredMarkerVisibility(menuTypeRequiredMarker, needsMenuType);
                setRequiredMarkerVisibility(sourceReferenceRequiredMarker, needsSourceReference);
            }

            function resetSourceReferenceChoices(placeholderLabel) {
                const choices = [
                    {
                        value: '',
                        label: placeholderLabel,
                        selected: true,
                        disabled: false
                    }
                ];

                if (sourceReferenceChoicesInstance) {
                    sourceReferenceChoicesInstance.clearStore();
                    sourceReferenceChoicesInstance.setChoices(choices, 'value', 'label', true);
                    return;
                }

                sourceReferenceElement.innerHTML = '';

                const option = document.createElement('option');
                option.value = '';
                option.textContent = placeholderLabel;
                option.selected = true;

                sourceReferenceElement.appendChild(option);
            }

            function fillSourceReferenceChoices(items, selectedValue) {
                const choices = [
                    {
                        value: '',
                        label: '{{ __('Choose') }}',
                        selected: selectedValue === null || selectedValue === '',
                        disabled: false
                    }
                ];

                (items || []).forEach(function (item) {
                    choices.push({
                        value: String(item.value),
                        label: String(item.label),
                        selected: selectedValue !== null && String(selectedValue) === String(item.value),
                        disabled: false
                    });
                });

                if (sourceReferenceChoicesInstance) {
                    sourceReferenceChoicesInstance.clearStore();
                    sourceReferenceChoicesInstance.setChoices(choices, 'value', 'label', true);
                    return;
                }

                sourceReferenceElement.innerHTML = '';

                choices.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item.value;
                    option.textContent = item.label;
                    option.selected = !!item.selected;
                    sourceReferenceElement.appendChild(option);
                });
            }

            async function loadSourceReferences() {
                if (!sourceTypeElement || !sourceReferenceElement) {
                    return;
                }

                const sourceType = String(sourceTypeElement.value || '');
                const menuType = String(menuTypeElement?.value || '');

                if (!sourceTypeNeedsSourceReference(sourceType)) {
                    resetSourceReferenceChoices('{{ __('Choose') }}');
                    return;
                }

                if (sourceType === 'menu_type' && menuType === '') {
                    resetSourceReferenceChoices('{{ __('Choose menu type first') }}');
                    return;
                }

                const baseUrl = '{{ route('admin.main_page.ajax.source-references', ['sourceType' => '__SOURCE_TYPE__']) }}';
                const requestUrl = new URL(
                    baseUrl.replace('__SOURCE_TYPE__', encodeURIComponent(sourceType)),
                    window.location.origin
                );

                if (sourceType === 'menu_type') {
                    requestUrl.searchParams.set('menu_type', menuType);
                }

                try {
                    const response = await fetch(requestUrl.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        resetSourceReferenceChoices('{{ __('Choose') }}');
                        return;
                    }

                    const data = await response.json();

                    fillSourceReferenceChoices(
                        Array.isArray(data.items) ? data.items : [],
                        sourceReferenceHasInitialValue ? selectedSourceReference : null
                    );

                    sourceReferenceHasInitialValue = false;
                } catch (error) {
                    resetSourceReferenceChoices('{{ __('Choose') }}');
                }
            }

            function handleSourceTypeChange() {
                syncFieldVisibility();
                sourceReferenceHasInitialValue = false;
                loadSourceReferences();
            }

            function handleMenuTypeChange() {
                if (String(sourceTypeElement?.value || '') !== 'menu_type') {
                    return;
                }

                sourceReferenceHasInitialValue = false;
                loadSourceReferences();
            }

            initializeChoices();

            if (menuTypeChoicesInstance && selectedMenuType) {
                menuTypeChoicesInstance.setChoiceByValue(String(selectedMenuType));
            }

            syncFieldVisibility();
            loadSourceReferences();

            sourceTypeElement?.addEventListener('change', handleSourceTypeChange);
            menuTypeElement?.addEventListener('change', handleMenuTypeChange);
        });
    </script>
@endpush
