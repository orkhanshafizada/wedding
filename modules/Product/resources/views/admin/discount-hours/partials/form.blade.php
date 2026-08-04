@php
    $startsAtValue = old('starts_at', optional($discountHour->starts_at)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'));
    $endsAtValue = old('ends_at', optional($discountHour->ends_at)->format('Y-m-d H:i:s') ?? now()->addHour()->format('Y-m-d H:i:s'));
    $statusValue = old('status', $discountHour->status ?? 'Active');

    $prefillItems = [];

    if ($mode === 'edit') {
        foreach ($discountHour->items as $discountHourItem) {
            if ($discountHourItem->product_id) {
                $product = $discountHourItem->product;
                $productName = '#' . $discountHourItem->product_id;

                foreach ($product?->variations ?? [] as $variation) {
                    $translation = $variation->translations->first();

                    if ($translation?->name) {
                        $productName = (string) $translation->name;
                        break;
                    }
                }

                $prefillItems[] = [
                    'type' => 'product',
                    'id' => (int) $discountHourItem->product_id,
                    'text' => $productName,
                    'subtitle' => 'Product',
                    'image_url' => '',
                ];
            }

            if ($discountHourItem->product_variation_id) {
                $variation = $discountHourItem->variation;

                $variationTranslation = $variation?->translations?->first();
                $variationName = (string) ($variationTranslation?->name ?? ('#' . $discountHourItem->product_variation_id));

                $productName = '';

                foreach ($variation?->product?->variations ?? [] as $productVariation) {
                    $productTranslation = $productVariation->translations->first();

                    if ($productTranslation?->name) {
                        $productName = (string) $productTranslation->name;
                        break;
                    }
                }

                $mainMedia = $variation?->media?->first();
                $imageUrl = (string) ($mainMedia?->url ?? '');

                $prefillItems[] = [
                    'type' => 'variation',
                    'id' => (int) $discountHourItem->product_variation_id,
                    'text' => $productName !== '' ? ($productName . ' — ' . $variationName) : $variationName,
                    'subtitle' => 'Variation',
                    'image_url' => $imageUrl,
                ];
            }
        }
    }
@endphp

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('Start date') }}</label>
                <div class="input-group">
                    <span class="input-group-text js-dh-start-open"><i class="ri-calendar-line"></i></span>
                    <input type="hidden" name="starts_at" id="dhStartsAt" value="{{ $startsAtValue }}">
                    <input type="text" id="dhStartsAtPicker" class="form-control" value="{{ $startsAtValue }}" autocomplete="off">
                </div>
                @error('starts_at')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('End date') }}</label>
                <div class="input-group">
                    <span class="input-group-text js-dh-end-open"><i class="ri-calendar-line"></i></span>
                    <input type="hidden" name="ends_at" id="dhEndsAt" value="{{ $endsAtValue }}">
                    <input type="text" id="dhEndsAtPicker" class="form-control" value="{{ $endsAtValue }}" autocomplete="off">
                </div>
                @error('ends_at')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('Status') }}</label>
                <select name="status" class="form-select">
                    <option value="Active" @selected($statusValue === 'Active')>{{ __('Active') }}</option>
                    <option value="Inactive" @selected($statusValue === 'Inactive')>{{ __('Inactive') }}</option>
                </select>
                @error('status')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{ __('Enter Product Name') }}</h5>
        </div>
    </div>
    <div class="card-body">
        <div class="discount-hour-target">
            <select id="discountHourTargetSelect"
                    class="form-select"
                    data-placeholder="{{ __('Search product or variation...') }}">
                <option value="">{{ __('Search product or variation...') }}</option>
            </select>
        </div>

        <div class="mt-3">
            <div class="row g-3" id="discountHourSelectedItems"></div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success w-100">
                <i class="ri-save-3-line me-1"></i>{{ __('Save') }}
            </button>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .discount-hour-target .choices {
                width: 100%;
                margin-bottom: 0;
            }

            .discount-hour-target .choices__inner {
                min-height: 38px;
                padding: .375rem .75rem;
                border-radius: .25rem;
                border: 1px solid var(--vz-border-color);
                background-color: var(--vz-input-bg);
                font-size: .875rem;
                line-height: 1.5;
            }

            .discount-hour-target .choices__input {
                background-color: transparent;
                margin: 0;
                padding: 0;
                font-size: .875rem;
            }

            .discount-hour-target .choices__list--single {
                padding: 0;
            }

            .discount-hour-target .choices__placeholder {
                opacity: .6;
            }

            .discount-hour-target .choices__list--dropdown,
            .discount-hour-target .choices__list[aria-expanded] {
                width: 100% !important;
                border-radius: .25rem;
                border: 1px solid var(--vz-border-color);
                box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
                z-index: 1056;
            }

            .discount-hour-target .choices__list--dropdown .choices__item {
                padding: .5rem 1.75rem;
                font-size: .875rem;
                white-space: normal;
            }

            .discount-hour-target .is-focused .choices__inner,
            .discount-hour-target .is-open .choices__inner {
                border-color: var(--vz-primary);
                box-shadow: 0 0 0 .15rem rgba(var(--vz-primary-rgb), .15);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                if (typeof flatpickr === 'undefined') {
                    return;
                }

                const startsHidden = document.getElementById('dhStartsAt');
                const endsHidden = document.getElementById('dhEndsAt');
                const startsPicker = document.getElementById('dhStartsAtPicker');
                const endsPicker = document.getElementById('dhEndsAtPicker');

                if (!startsHidden || !endsHidden || !startsPicker || !endsPicker) {
                    return;
                }

                const commonOptions = {
                    enableTime: true,
                    time_24hr: true,
                    enableSeconds: true,
                    dateFormat: 'Y-m-d H:i:S',
                    allowInput: true,
                    clickOpens: true,
                    altInput: false,
                    disableMobile: true
                };

                const startFlatpickr = flatpickr(startsPicker, {
                    ...commonOptions,
                    defaultDate: startsHidden.value || null,
                    onChange: function (selectedDates, dateString) {
                        startsHidden.value = dateString;

                        if (endFlatpickr) {
                            endFlatpickr.set('minDate', dateString || null);
                        }
                    }
                });

                const endFlatpickr = flatpickr(endsPicker, {
                    ...commonOptions,
                    defaultDate: endsHidden.value || null,
                    onChange: function (selectedDates, dateString) {
                        endsHidden.value = dateString;
                    }
                });

                if (startsHidden.value) {
                    endFlatpickr.set('minDate', startsHidden.value);
                }

                startsPicker.addEventListener('click', function () {
                    startFlatpickr.open();
                });

                endsPicker.addEventListener('click', function () {
                    endFlatpickr.open();
                });

                const startIcon = document.querySelector('.js-dh-start-open');
                const endIcon = document.querySelector('.js-dh-end-open');

                if (startIcon) {
                    startIcon.style.cursor = 'pointer';
                    startIcon.addEventListener('click', function () {
                        startFlatpickr.open();
                    });
                }

                if (endIcon) {
                    endIcon.style.cursor = 'pointer';
                    endIcon.addEventListener('click', function () {
                        endFlatpickr.open();
                    });
                }
            })();

            (function () {
                const initialItems = @json($prefillItems);
                const selectedItems = new Map();

                const container = document.getElementById('discountHourSelectedItems');
                const selectElement = document.getElementById('discountHourTargetSelect');

                if (!container || !selectElement || typeof Choices === 'undefined') {
                    return;
                }

                if (selectElement.dataset.choicesBound === '1') {
                    return;
                }

                selectElement.dataset.choicesBound = '1';

                const searchUrl = "{{ route('admin.product.ajax.discount-hours.targets') }}";
                const metaMap = new Map();

                function itemKey(item) {
                    return item.type + ':' + item.id;
                }

                function renderSelectedItems() {
                    container.innerHTML = '';

                    Array.from(selectedItems.values()).forEach(function (item, index) {
                        const column = document.createElement('div');
                        column.className = 'col-xl-3 col-lg-4 col-md-6';

                        const card = document.createElement('div');
                        card.className = 'card border mb-0';

                        const body = document.createElement('div');
                        body.className = 'card-body';

                        const top = document.createElement('div');
                        top.className = 'd-flex align-items-start gap-2';

                        const avatarWrap = document.createElement('div');
                        avatarWrap.className = 'avatar-sm flex-shrink-0';

                        const avatar = document.createElement('div');
                        avatar.className = 'avatar-title bg-light text-muted rounded overflow-hidden';

                        if (item.image_url) {
                            const image = document.createElement('img');
                            image.src = item.image_url;
                            image.alt = item.text;
                            image.style.width = '100%';
                            image.style.height = '100%';
                            image.style.objectFit = 'cover';
                            avatar.appendChild(image);
                        } else {
                            avatar.innerHTML = item.type === 'variation'
                                ? '<i class="ri-price-tag-3-line"></i>'
                                : '<i class="ri-shopping-bag-3-line"></i>';
                        }

                        avatarWrap.appendChild(avatar);

                        const info = document.createElement('div');
                        info.className = 'flex-grow-1';

                        const title = document.createElement('div');
                        title.className = 'fw-semibold';
                        title.textContent = item.text;

                        const subtitle = document.createElement('div');
                        subtitle.className = 'text-muted small';
                        subtitle.textContent = item.subtitle || '';

                        info.appendChild(title);
                        info.appendChild(subtitle);

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className = 'btn btn-sm btn-soft-danger ms-auto';
                        removeButton.innerHTML = '<i class="ri-delete-bin-5-line"></i>';
                        removeButton.addEventListener('click', function () {
                            selectedItems.delete(itemKey(item));
                            renderSelectedItems();
                        });

                        const hiddenType = document.createElement('input');
                        hiddenType.type = 'hidden';
                        hiddenType.name = `items[${index}][type]`;
                        hiddenType.value = item.type;

                        const hiddenId = document.createElement('input');
                        hiddenId.type = 'hidden';
                        hiddenId.name = `items[${index}][id]`;
                        hiddenId.value = item.id;

                        top.appendChild(avatarWrap);
                        top.appendChild(info);
                        top.appendChild(removeButton);

                        body.appendChild(top);
                        body.appendChild(hiddenType);
                        body.appendChild(hiddenId);

                        card.appendChild(body);
                        column.appendChild(card);
                        container.appendChild(column);
                    });
                }

                initialItems.forEach(function (item) {
                    selectedItems.set(itemKey(item), item);
                });

                renderSelectedItems();

                const choices = new Choices(selectElement, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    itemSelectText: '',
                    allowHTML: false,
                    searchResultLimit: 20,
                    searchPlaceholderValue: "{{ __('Search product or variation...') }}"
                });

                let abortController = null;
                let typingTimer = null;

                async function loadChoices(term) {
                    const searchTerm = (term || '').trim();

                    if (abortController) {
                        abortController.abort();
                    }

                    abortController = new AbortController();

                    const url = new URL(searchUrl, window.location.origin);
                    url.searchParams.set('limit', '20');

                    if (searchTerm !== '') {
                        url.searchParams.set('q', searchTerm);
                    }

                    try {
                        const response = await fetch(url.toString(), {
                            signal: abortController.signal,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            choices.setChoices([
                                {
                                    value: '',
                                    label: "{{ __('No results') }}",
                                    disabled: true
                                }
                            ], 'value', 'label', true);
                            return;
                        }

                        const json = await response.json();
                        const rows = json.data || [];

                        metaMap.clear();

                        const choiceItems = rows.map(function (row) {
                            const value = row.type + ':' + row.id;

                            metaMap.set(value, row);

                            return {
                                value: value,
                                label: row.text + (row.subtitle ? ' (' + row.subtitle + ')' : '')
                            };
                        });

                        if (choiceItems.length === 0) {
                            choices.setChoices([
                                {
                                    value: '',
                                    label: "{{ __('No results') }}",
                                    disabled: true
                                }
                            ], 'value', 'label', true);
                            return;
                        }

                        choices.setChoices(choiceItems, 'value', 'label', true);
                    } catch (error) {
                        if (error && error.name === 'AbortError') {
                            return;
                        }

                        choices.setChoices([
                            {
                                value: '',
                                label: "{{ __('No results') }}",
                                disabled: true
                            }
                        ], 'value', 'label', true);
                    }
                }

                selectElement.addEventListener('search', function (event) {
                    const term = event.detail && event.detail.value ? event.detail.value : '';

                    clearTimeout(typingTimer);

                    typingTimer = setTimeout(function () {
                        loadChoices(term);
                    }, 250);
                });

                selectElement.addEventListener('showDropdown', function () {
                    loadChoices('');
                });

                selectElement.addEventListener('change', function () {
                    const value = selectElement.value;

                    if (!value) {
                        return;
                    }

                    const row = metaMap.get(value);

                    if (!row) {
                        selectElement.value = '';
                        return;
                    }

                    const item = {
                        type: row.type,
                        id: row.id,
                        text: row.text,
                        subtitle: row.subtitle || '',
                        image_url: row.image_url || ''
                    };

                    selectedItems.set(itemKey(item), item);
                    renderSelectedItems();

                    selectElement.value = '';
                });

                loadChoices('');
            })();
        </script>
    @endpush
@endonce
