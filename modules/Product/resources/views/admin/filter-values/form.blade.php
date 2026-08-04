@php
    $allowedImageExtensions = \App\Support\Settings::get('file_manager', 'allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

    $acceptMimeMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'bmp' => 'image/bmp',
        'avif' => 'image/avif',
    ];

    $acceptValues = collect($allowedImageExtensions)
        ->map(fn ($extension) => strtolower(trim((string) $extension)))
        ->map(fn ($extension) => $acceptMimeMap[$extension] ?? null)
        ->filter()
        ->unique()
        ->values()
        ->implode(',');

    $currentImage = isset($value) && $value->image ? asset('storage/' . $value->image) : null;
@endphp

<style>
    .filter-value-card {
        border: 1px solid #e9ebec;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        overflow: hidden;
    }

    .filter-value-card .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eff2f7;
        background: transparent;
    }

    .filter-value-card .card-body {
        padding: 1.25rem;
    }

    .filter-value-label {
        font-size: .875rem;
        font-weight: 600;
        color: #344054;
        margin-bottom: .5rem;
    }

    .filter-value-hint {
        font-size: .75rem;
        color: #667085;
        margin-top: .35rem;
    }

    .filter-value-nav.nav-tabs {
        border-bottom: 1px solid #eaecf0;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .filter-value-nav .nav-link {
        border: 1px solid #e9ebec;
        border-bottom: 0;
        border-radius: 10px 10px 0 0;
        color: #475467;
        background: #f8fafc;
        padding: .65rem 1rem;
    }

    .filter-value-nav .nav-link.active {
        background: #fff;
        color: #101828;
        border-color: #e9ebec;
        font-weight: 600;
    }

    .filter-value-pane {
        border: 1px solid #eaecf0;
        border-top: 0;
        border-radius: 0 0 14px 14px;
        padding: 1.25rem;
        background: #fff;
    }

    .filter-value-seo-box {
        border: 1px solid #eaecf0;
        border-radius: 14px;
        background: #fcfcfd;
        padding: 1rem;
        height: 100%;
    }

    .filter-value-media-box {
        border: 1px solid #eaecf0;
        border-radius: 14px;
        background: #fff;
        padding: 1rem;
        height: 100%;
    }

    .filter-value-media-preview {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #eaecf0;
        background: #f8fafc;
    }

    .filter-value-media-empty {
        width: 100%;
        height: 180px;
        border-radius: 14px;
        border: 1px dashed #d0d5dd;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #98a2b3;
        font-size: 2rem;
    }

    .filter-value-color-box {
        border: 1px solid #eaecf0;
        border-radius: 14px;
        background: #fff;
        padding: 1rem;
        height: 100%;
    }

    .filter-keywords-box {
        border: 1px solid #d0d5dd;
        border-radius: 12px;
        background: #fff;
        padding: .75rem;
    }

    .filter-keywords-tags {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: .75rem;
        min-height: 24px;
    }

    .filter-keyword-tag {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .65rem;
        border-radius: 999px;
        background: #f2f4f7;
        color: #344054;
        font-size: .8rem;
        line-height: 1;
        border: 1px solid #eaecf0;
    }

    .filter-keyword-remove {
        border: 0;
        background: transparent;
        padding: 0;
        color: #667085;
        line-height: 1;
        cursor: pointer;
        font-size: .9rem;
    }

    .filter-keyword-input {
        border: 0;
        box-shadow: none !important;
        padding: 0;
        min-height: 38px;
    }

    .filter-keyword-input:focus {
        border: 0;
        box-shadow: none !important;
    }

    .filter-value-generate-btn {
        min-width: 46px;
    }
</style>

<div class="card filter-value-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           id="show_on_main"
                           name="show_on_main"
                           value="1"
                        {{ old('show_on_main', $value?->show_on_main ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_on_main">{{ __('Show on Main Page') }}</label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-check form-switch">
                    <input class="form-check-input"
                           type="checkbox"
                           id="show_on_menu_detail"
                           name="show_on_menu_detail"
                           value="1"
                        {{ old('show_on_menu_detail', $value?->show_on_menu_detail ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_on_menu_detail">{{ __('Show on Menu Detail') }}</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card filter-value-card mb-4">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <h5 class="card-title mb-1">{{ __('Value content and SEO') }}</h5>
                <p class="text-muted mb-0">{{ __('Manage localized name, slug and SEO data for this filter value.') }}</p>
            </div>
            <span
                class="badge bg-soft-primary text-primary">{{ isset($value) && $value->exists ? __('Edit mode') : __('Create mode') }}</span>
        </div>
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs filter-value-nav mb-0" role="tablist">
            @foreach($languages as $index => $language)
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link {{ $index === 0 ? 'active' : '' }}"
                            data-bs-toggle="tab"
                            data-bs-target="#translation-tab-{{ $language->code }}"
                            role="tab">
                        {{ $language->native_name }}
                        @if(in_array($language->code, $requiredLocales ?? []))
                            <span class="text-danger">*</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($languages as $index => $language)
                @php
                    $translation = isset($value) && $value->exists
                        ? $value->translations->firstWhere('language.code', $language->code)
                        : null;

                    $nameValue = old('name.' . $language->code, $translation?->name ?? '');
                    $slugValue = old('slug.' . $language->code, $translation?->slug ?? '');
                    $metaTitleValue = old('meta_title.' . $language->code, $translation?->meta_title ?? '');
                    $metaKeywordsValue = old('meta_keywords.' . $language->code, $translation?->meta_keywords ?? '');
                    $metaDescriptionValue = old('meta_description.' . $language->code, $translation?->meta_description ?? '');
                    $isRequired = in_array($language->code, $requiredLocales ?? []);

                    $metaKeywordsHiddenId = 'meta-keywords-hidden-' . $language->code;
                    $metaKeywordsWrapId = 'meta-keywords-wrap-' . $language->code;
                    $metaKeywordsInputId = 'meta-keywords-input-' . $language->code;
                @endphp

                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                     id="translation-tab-{{ $language->code }}"
                     role="tabpanel">
                    <div class="filter-value-pane">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label for="name_{{ $language->code }}"
                                       class="filter-value-label">{{ __('Name') }}</label>
                                <input type="text"
                                       name="name[{{ $language->code }}]"
                                       id="name_{{ $language->code }}"
                                       class="form-control @error('name.' . $language->code) is-invalid @enderror"
                                       value="{{ $nameValue }}"
                                       placeholder="Value name in {{ strtoupper($language->code) }}"
                                       data-lang-name="{{ $language->code }}"
                                    {{ $isRequired ? 'required' : '' }}>
                                @error('name.' . $language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                <label for="slug_{{ $language->code }}"
                                       class="filter-value-label">{{ __('Slug') }}</label>
                                <div class="input-group">
                                    <input type="text"
                                           name="slug[{{ $language->code }}]"
                                           id="slug_{{ $language->code }}"
                                           class="form-control @error('slug.' . $language->code) is-invalid @enderror"
                                           value="{{ $slugValue }}"
                                           placeholder="Slug in {{ strtoupper($language->code) }}"
                                           data-lang-slug="{{ $language->code }}"
                                        {{ $isRequired ? 'required' : '' }}>
                                    <button type="button"
                                            class="btn btn-outline-secondary filter-value-generate-btn js-generate-slug"
                                            data-lang="{{ $language->code }}"
                                            title="{{ __('Generate from name') }}">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                                @error('slug.' . $language->code)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div
                                    class="filter-value-hint">{{ __('Slug is automatically synchronized from the name field.') }}</div>
                            </div>

                            <div class="col-lg-6">
                                <div class="filter-value-seo-box">
                                    <label for="meta_title_{{ $language->code }}"
                                           class="filter-value-label">{{ __('Meta title') }}</label>
                                    <input type="text"
                                           name="meta_title[{{ $language->code }}]"
                                           id="meta_title_{{ $language->code }}"
                                           class="form-control @error('meta_title.' . $language->code) is-invalid @enderror"
                                           value="{{ $metaTitleValue }}"
                                           placeholder="Meta title in {{ strtoupper($language->code) }}">
                                    @error('meta_title.' . $language->code)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <label class="filter-value-label mt-3">{{ __('Meta keywords') }}</label>

                                    <input type="hidden"
                                           id="{{ $metaKeywordsHiddenId }}"
                                           name="meta_keywords[{{ $language->code }}]"
                                           value="{{ $metaKeywordsValue }}">

                                    <div class="filter-keywords-box">
                                        <div id="{{ $metaKeywordsWrapId }}" class="filter-keywords-tags"></div>

                                        <input type="text"
                                               id="{{ $metaKeywordsInputId }}"
                                               class="form-control filter-keyword-input js-meta-keyword-input"
                                               data-hidden-id="{{ $metaKeywordsHiddenId }}"
                                               data-wrap-id="{{ $metaKeywordsWrapId }}"
                                               placeholder="{{ __('Type keyword and press Enter') }}"
                                               autocomplete="off">
                                    </div>

                                    <div
                                        class="filter-value-hint">{{ __('Keywords will be stored as comma-separated values.') }}</div>

                                    @error('meta_keywords.' . $language->code)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="filter-value-seo-box">
                                    <label for="meta_description_{{ $language->code }}"
                                           class="filter-value-label">{{ __('Meta description') }}</label>
                                    <textarea name="meta_description[{{ $language->code }}]"
                                              id="meta_description_{{ $language->code }}"
                                              class="form-control @error('meta_description.' . $language->code) is-invalid @enderror"
                                              rows="8"
                                              placeholder="Meta description in {{ strtoupper($language->code) }}">{{ $metaDescriptionValue }}</textarea>
                                    @error('meta_description.' . $language->code)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4">
    @if($filter->is_color_filter)
        <div class="col-12 col-lg-4">
            <div class="filter-value-color-box">
                <label for="color" class="filter-value-label">{{ __('Color') }}</label>
                <input type="color"
                       name="color"
                       id="color"
                       class="form-control form-control-color @error('color') is-invalid @enderror"
                       value="{{ old('color', $value->color ?? '#000000') }}">
                @error('color')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div
                    class="filter-value-hint">{{ __('Use this when the filter value represents a direct color swatch.') }}</div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="filter-value-media-box">
                <label for="image" class="filter-value-label">{{ __('Main image') }}</label>

                @if($currentImage)
                    <img src="{{ $currentImage }}" alt="{{ __('Main image') }}" class="filter-value-media-preview mb-3">
                @else
                    <div class="filter-value-media-empty mb-3">
                        <i class="ri-image-2-line"></i>
                    </div>
                @endif

                <input type="file"
                       name="image"
                       id="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="{{ $acceptValues }}">
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div
                    class="filter-value-hint">{{ __('Primary image for the filter value card, swatch or listing preview.') }}</div>
            </div>
        </div>
    @else
        <div class="col-12 col-lg-6">
            <div class="filter-value-media-box">
                <label for="image" class="filter-value-label">{{ __('Main image') }}</label>

                @if($currentImage)
                    <img src="{{ $currentImage }}" alt="{{ __('Main image') }}" class="filter-value-media-preview mb-3">
                @else
                    <div class="filter-value-media-empty mb-3">
                        <i class="ri-image-2-line"></i>
                    </div>
                @endif

                <input type="file"
                       name="image"
                       id="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="{{ $acceptValues }}">
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="filter-value-hint">{{ __('Upload a representative image for this filter value.') }}</div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const parseKeywords = function (value) {
            return String(value || '')
                .split(',')
                .map(function (item) {
                    return item.trim();
                })
                .filter(function (item, index, array) {
                    return item !== '' && array.indexOf(item) === index;
                });
        };

        const renderKeywordTags = function (hiddenInput, wrap) {
            const keywords = parseKeywords(hiddenInput.value);

            wrap.innerHTML = '';

            keywords.forEach(function (keyword, index) {
                const tag = document.createElement('span');
                tag.className = 'filter-keyword-tag';
                tag.innerHTML = '<span>' + keyword + '</span><button type="button" class="filter-keyword-remove" data-index="' + index + '">&times;</button>';
                wrap.appendChild(tag);
            });
        };

        document.querySelectorAll('.js-meta-keyword-input').forEach(function (input) {
            const hiddenInput = document.getElementById(input.dataset.hiddenId);
            const wrap = document.getElementById(input.dataset.wrapId);

            if (!hiddenInput || !wrap) {
                return;
            }

            renderKeywordTags(hiddenInput, wrap);

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' && event.key !== ',') {
                    return;
                }

                event.preventDefault();

                const newKeyword = input.value.trim();

                if (newKeyword === '') {
                    return;
                }

                const keywords = parseKeywords(hiddenInput.value);

                if (!keywords.includes(newKeyword)) {
                    keywords.push(newKeyword);
                    hiddenInput.value = keywords.join(', ');
                    renderKeywordTags(hiddenInput, wrap);
                }

                input.value = '';
            });

            input.addEventListener('blur', function () {
                const newKeyword = input.value.trim();

                if (newKeyword === '') {
                    return;
                }

                const keywords = parseKeywords(hiddenInput.value);

                if (!keywords.includes(newKeyword)) {
                    keywords.push(newKeyword);
                    hiddenInput.value = keywords.join(', ');
                    renderKeywordTags(hiddenInput, wrap);
                }

                input.value = '';
            });

            wrap.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.filter-keyword-remove');

                if (!removeButton) {
                    return;
                }

                const keywords = parseKeywords(hiddenInput.value);
                const index = parseInt(removeButton.getAttribute('data-index') || '-1', 10);

                if (index < 0 || typeof keywords[index] === 'undefined') {
                    return;
                }

                keywords.splice(index, 1);
                hiddenInput.value = keywords.join(', ');
                renderKeywordTags(hiddenInput, wrap);
            });
        });
    });
</script>
