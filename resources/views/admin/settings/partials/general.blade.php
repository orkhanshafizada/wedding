@php
    $group = 'general';

    $workHourLabels = [
        'mon' => __('Mon'),
        'tue' => __('Tue'),
        'wed' => __('Wed'),
        'thu' => __('Thu'),
        'fri' => __('Fri'),
        'sat' => __('Sat'),
        'sun' => __('Sun'),
    ];

    $mediaFields = [
        'logo' => 'Logo',
        'logo_dark' => 'Logo Dark',
        'footer_logo' => 'Footer Logo',
        'footer_logo_dark' => 'Footer Logo Dark',
        'mobile_logo' => 'Mobile Logo',
        'mobile_logo_dark' => 'Mobile Logo Dark',
        'favicon' => 'Favicon',
        'default_image' => 'Default Image',
        'watermark' => 'Watermark',
    ];
@endphp

<div class="tab-pane fade @if($activeTab === 'general') show active @endif" id="tab-general" role="tabpanel" aria-labelledby="tab-general-tab">
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('Translations') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills arrow-navtabs nav-success bg-light mb-3" role="tablist">
                        @foreach($languages as $index => $language)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($index === 0) active @endif"
                                        id="general-tab-{{ $language->id }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#general-pane-{{ $language->id }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="general-pane-{{ $language->id }}"
                                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    {{ strtoupper($language->name) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $index => $language)
                            @php
                                $languageId = (string) $language->id;
                                $metaKeywordsHiddenId = "general-meta-keywords-hidden-{$language->id}";
                                $metaKeywordsWrapId = "general-meta-keywords-wrap-{$language->id}";
                                $metaKeywordsInputId = "general-meta-keywords-input-{$language->id}";
                            @endphp

                            <div class="tab-pane fade @if($index === 0) show active @endif"
                                 id="general-pane-{{ $language->id }}"
                                 role="tabpanel"
                                 aria-labelledby="general-tab-{{ $language->id }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Site title') }} ({{ $language->code }})</label>
                                        <input type="text"
                                               class="form-control"
                                               name="{{ $group }}[site_title][{{ $languageId }}]"
                                               value="{{ $data['site_title'][$languageId] ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Address') }} ({{ $language->code }})</label>
                                        <input type="text"
                                               class="form-control"
                                               name="{{ $group }}[address][{{ $languageId }}]"
                                               value="{{ $data['address'][$languageId] ?? '' }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">{{ __('Site header text') }} ({{ $language->code }})</label>
                                        <textarea class="form-control js-editor"
                                                  rows="3"
                                                  name="{{ $group }}[site_header_text][{{ $languageId }}]">{{ $data['site_header_text'][$languageId] ?? '' }}</textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Meta title') }} ({{ $language->code }})</label>
                                        <input type="text"
                                               class="form-control"
                                               name="{{ $group }}[meta_title][{{ $languageId }}]"
                                               value="{{ $data['meta_title'][$languageId] ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Meta keywords') }} ({{ $language->code }})</label>

                                        <input type="hidden"
                                               id="{{ $metaKeywordsHiddenId }}"
                                               name="{{ $group }}[meta_keywords][{{ $languageId }}]"
                                               value="{{ $data['meta_keywords'][$languageId] ?? '' }}">

                                        <div id="{{ $metaKeywordsWrapId }}" class="d-flex flex-wrap gap-2 mb-2"></div>

                                        <input type="text"
                                               id="{{ $metaKeywordsInputId }}"
                                               class="form-control js-meta-keyword-input"
                                               data-hidden-id="{{ $metaKeywordsHiddenId }}"
                                               data-wrap-id="{{ $metaKeywordsWrapId }}"
                                               placeholder="{{ __('Type keyword and press Enter') }}"
                                               autocomplete="off">

                                        <div class="form-text">{{ __('Keywords will be saved as comma-separated.') }}</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">{{ __('Meta description') }} ({{ $language->code }})</label>
                                        <textarea class="form-control js-editor"
                                                  rows="3"
                                                  name="{{ $group }}[meta_description][{{ $languageId }}]">{{ $data['meta_description'][$languageId] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Contact') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Map iframe') }}</label>
                                <textarea class="form-control" rows="4" name="{{ $group }}[map_iframe]">{{ $data['map_iframe'] }}</textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">{{ __('Frontend URL') }}</label>
                                <input type="text"
                                       class="form-control"
                                       name="{{ $group }}[frontend_url]"
                                       value="{{ $data['frontend_url'] }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Communication') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <input type="email"
                                           class="form-control"
                                           name="{{ $group }}[email]"
                                           value="{{ $data['email'] }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('HR Email') }}</label>
                                    <input type="email"
                                           class="form-control"
                                           name="{{ $group }}[hr_email]"
                                           value="{{ $data['hr_email'] ?? '' }}">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label d-block">{{ __('Phones') }}</label>

                                <div id="phoneWrap" class="vstack gap-2">
                                    @foreach($data['phones'] as $index => $phone)
                                        <div class="border rounded p-2 d-flex align-items-center gap-2 phone-row">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input ph-whatsapp"
                                                       type="checkbox"
                                                       name="{{ $group }}[phones][{{ $index }}][is_whatsapp]"
                                                       value="1"
                                                    @checked($phone['is_whatsapp'] ?? false)>
                                            </div>

                                            <input class="form-control ph-label"
                                                   style="max-width: 160px"
                                                   name="{{ $group }}[phones][{{ $index }}][label]"
                                                   placeholder="{{ __('Label') }}"
                                                   value="{{ $phone['label'] ?? '' }}">

                                            <input class="form-control ph-number"
                                                   name="{{ $group }}[phones][{{ $index }}][number]"
                                                   placeholder="+994..."
                                                   value="{{ $phone['number'] ?? '' }}">

                                            <button type="button"
                                                    class="btn btn-ghost-danger btn-icon phone-remove"
                                                    title="{{ __('Remove') }}">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button"
                                        class="btn btn-soft-primary btn-sm mt-2 d-inline-flex align-items-center gap-1"
                                        id="addPhoneBtn"
                                        title="{{ __('Add') }}">
                                    <i class="ri-add-line"></i> {{ __('Add') }}
                                </button>

                                <template id="phoneRowTpl">
                                    <div class="border rounded p-2 d-flex align-items-center gap-2 phone-row">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input ph-whatsapp" type="checkbox" value="1">
                                        </div>
                                        <input class="form-control ph-label" style="max-width: 160px" placeholder="{{ __('Label') }}">
                                        <input class="form-control ph-number" placeholder="+994...">
                                        <button type="button"
                                                class="btn btn-ghost-danger btn-icon phone-remove"
                                                title="{{ __('Remove') }}">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Body tag') }}</label>
                                    <textarea class="form-control"
                                              rows="3"
                                              name="{{ $group }}[body_raw]">{{ $data['body_raw'] }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Head tag') }}</label>
                                    <textarea class="form-control"
                                              rows="3"
                                              name="{{ $group }}[head_raw]">{{ $data['head_raw'] }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="card-title mb-0">{{ __('Work hours') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach($workHourLabels as $day => $label)
                                    @php
                                        $workHourValue = $data['work_hours'][$day] ?? '';

                                        if (is_array($workHourValue)) {
                                            $workHourValue = $workHourValue['day'] ?? $workHourValue['value'] ?? '';
                                        }

                                        $workHourValue = is_string($workHourValue) ? $workHourValue : '';
                                    @endphp

                                    <div class="col-4">
                                        <label class="form-label">{{ $label }}</label>
                                        <input type="text"
                                               class="form-control"
                                               name="{{ $group }}[work_hours][{{ $day }}]"
                                               value="{{ $workHourValue }}"
                                               placeholder="09:00 - 18:00">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h6 class="card-title mb-0">{{ __('Media') }}</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($mediaFields as $key => $label)
                    @php
                        $path = $data['images'][$key] ?? null;
                        $url = $path ? (\Illuminate\Support\Str::startsWith($path, 'http') ? $path : asset('storage/' . $path)) : '';
                        $fileName = $path ? basename($path) : '';
                    @endphp

                    <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                        <div class="border rounded p-3 h-100" data-media-key="{{ $key }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-semibold">{{ __($label) }}</div>
                                <button type="button"
                                        class="btn btn-ghost-danger btn-icon btn-sm media-remove"
                                        title="{{ __('Clear') }}">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>

                            <div class="position-relative text-center mb-2">
                                <img class="img-fluid rounded border @if(!$url) d-none @endif media-preview"
                                     src="{{ $url }}"
                                     alt="">
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <button type="button"
                                            class="btn btn-ghost-secondary btn-icon rotate left"
                                            title="{{ __('Rotate left') }}">
                                        <i class="ri-anticlockwise-2-line"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-ghost-secondary btn-icon rotate right"
                                            title="{{ __('Rotate right') }}">
                                        <i class="ri-clockwise-2-line"></i>
                                    </button>
                                </div>
                            </div>

                            <input type="file"
                                   accept="image/*"
                                   class="form-control upload-btn"
                                   name="files[general][images][{{ $key }}]">

                            <input type="checkbox"
                                   class="remove-flag d-none"
                                   name="{{ $group }}[images_remove][{{ $key }}]"
                                   value="1">

                            <div class="small text-muted mt-2 media-filename">{{ $fileName }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            'use strict';

            const addPhoneButton = document.getElementById('addPhoneBtn');
            const phoneWrap = document.getElementById('phoneWrap');
            const phoneTemplate = document.getElementById('phoneRowTpl');

            function normalizeKeywords(value) {
                return value
                    .split(',')
                    .map(function (item) {
                        return item.trim();
                    })
                    .filter(function (item, index, array) {
                        return item !== '' && array.indexOf(item) === index;
                    });
            }

            function updateKeywordHiddenInput(hiddenInput, keywords) {
                hiddenInput.value = keywords.join(',');
            }

            function renderKeywordBadges(wrap, hiddenInput) {
                if (!wrap || !hiddenInput) {
                    return;
                }

                const keywords = normalizeKeywords(hiddenInput.value);

                wrap.innerHTML = '';

                keywords.forEach(function (keyword, index) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-2 px-2 py-2';

                    const text = document.createElement('span');
                    text.textContent = keyword;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-sm btn-link text-danger p-0 lh-1 js-meta-keyword-remove';
                    removeButton.setAttribute('data-keyword-index', String(index));
                    removeButton.innerHTML = '<i class="ri-close-line"></i>';

                    badge.appendChild(text);
                    badge.appendChild(removeButton);
                    wrap.appendChild(badge);
                });

                updateKeywordHiddenInput(hiddenInput, keywords);
            }

            function initMetaKeywords() {
                document.querySelectorAll('.js-meta-keyword-input').forEach(function (input) {
                    const hiddenId = input.getAttribute('data-hidden-id');
                    const wrapId = input.getAttribute('data-wrap-id');
                    const hiddenInput = hiddenId ? document.getElementById(hiddenId) : null;
                    const wrap = wrapId ? document.getElementById(wrapId) : null;

                    if (!hiddenInput || !wrap) {
                        return;
                    }

                    renderKeywordBadges(wrap, hiddenInput);

                    input.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter' && event.key !== ',') {
                            return;
                        }

                        event.preventDefault();

                        const value = input.value.trim();
                        if (value === '') {
                            return;
                        }

                        const keywords = normalizeKeywords(hiddenInput.value);
                        if (keywords.indexOf(value) === -1) {
                            keywords.push(value);
                        }

                        updateKeywordHiddenInput(hiddenInput, keywords);
                        renderKeywordBadges(wrap, hiddenInput);
                        input.value = '';
                    });

                    input.addEventListener('blur', function () {
                        const value = input.value.trim();
                        if (value === '') {
                            return;
                        }

                        const keywords = normalizeKeywords(hiddenInput.value);
                        if (keywords.indexOf(value) === -1) {
                            keywords.push(value);
                        }

                        updateKeywordHiddenInput(hiddenInput, keywords);
                        renderKeywordBadges(wrap, hiddenInput);
                        input.value = '';
                    });
                });

                document.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('.js-meta-keyword-remove');
                    if (!removeButton) {
                        return;
                    }

                    const wrap = removeButton.closest('[id^="general-meta-keywords-wrap-"]');
                    if (!wrap) {
                        return;
                    }

                    const hiddenId = wrap.id.replace('-wrap-', '-hidden-');
                    const hiddenInput = document.getElementById(hiddenId);

                    if (!hiddenInput) {
                        return;
                    }

                    const keywords = normalizeKeywords(hiddenInput.value);
                    const keywordIndex = parseInt(removeButton.getAttribute('data-keyword-index') || '-1', 10);

                    if (keywordIndex >= 0) {
                        keywords.splice(keywordIndex, 1);
                    }

                    updateKeywordHiddenInput(hiddenInput, keywords);
                    renderKeywordBadges(wrap, hiddenInput);
                }, false);
            }

            if (addPhoneButton && phoneWrap && phoneTemplate) {
                addPhoneButton.addEventListener('click', function () {
                    const index = phoneWrap.querySelectorAll('.phone-row').length;
                    const node = phoneTemplate.content.cloneNode(true);

                    const checkbox = node.querySelector('.ph-whatsapp');
                    const label = node.querySelector('.ph-label');
                    const number = node.querySelector('.ph-number');

                    if (checkbox) {
                        checkbox.setAttribute('name', '{{ $group }}[phones][' + index + '][is_whatsapp]');
                    }

                    if (label) {
                        label.setAttribute('name', '{{ $group }}[phones][' + index + '][label]');
                    }

                    if (number) {
                        number.setAttribute('name', '{{ $group }}[phones][' + index + '][number]');
                    }

                    phoneWrap.appendChild(node);
                });

                phoneWrap.addEventListener('click', function (event) {
                    const button = event.target.closest('.phone-remove');
                    if (!button) {
                        return;
                    }

                    const row = button.closest('.phone-row');
                    if (row) {
                        row.remove();
                    }
                }, false);
            }

            document.addEventListener('click', function (event) {
                const removeMediaButton = event.target.closest('.media-remove');
                if (removeMediaButton) {
                    const box = removeMediaButton.closest('[data-media-key]');
                    if (!box) {
                        return;
                    }

                    const preview = box.querySelector('.media-preview');
                    const fileInput = box.querySelector('.upload-btn');
                    const removeFlag = box.querySelector('.remove-flag');
                    const fileName = box.querySelector('.media-filename');

                    if (preview) {
                        preview.classList.add('d-none');
                    }

                    if (fileInput) {
                        fileInput.value = '';
                    }

                    if (removeFlag) {
                        removeFlag.checked = true;
                    }

                    if (fileName) {
                        fileName.textContent = '';
                    }

                    return;
                }

                const rotateLeftButton = event.target.closest('.rotate.left');
                const rotateRightButton = event.target.closest('.rotate.right');
                const rotateButton = rotateLeftButton || rotateRightButton;

                if (rotateButton) {
                    const box = rotateButton.closest('[data-media-key]');
                    const image = box ? box.querySelector('.media-preview') : null;

                    if (!image || image.classList.contains('d-none')) {
                        return;
                    }

                    const direction = rotateLeftButton ? -90 : 90;
                    const currentRotation = parseInt(image.getAttribute('data-rotation') || '0', 10);
                    const nextRotation = (currentRotation + direction) % 360;

                    image.style.transform = 'rotate(' + nextRotation + 'deg)';
                    image.setAttribute('data-rotation', String(nextRotation));
                }
            }, false);

            document.addEventListener('change', function (event) {
                const input = event.target.closest('.upload-btn');
                if (!input || !input.files || !input.files[0]) {
                    return;
                }

                const box = input.closest('[data-media-key]');
                if (!box) {
                    return;
                }

                const preview = box.querySelector('.media-preview');
                const removeFlag = box.querySelector('.remove-flag');
                const fileName = box.querySelector('.media-filename');

                if (removeFlag) {
                    removeFlag.checked = false;
                }

                if (fileName) {
                    fileName.textContent = input.files[0].name;
                }

                const reader = new FileReader();
                reader.onload = function (loadEvent) {
                    if (!preview) {
                        return;
                    }

                    preview.src = loadEvent.target.result;
                    preview.classList.remove('d-none');
                    preview.style.transform = 'rotate(0deg)';
                    preview.setAttribute('data-rotation', '0');
                };
                reader.readAsDataURL(input.files[0]);
            }, false);

            initMetaKeywords();
        })();
    </script>
@endpush
