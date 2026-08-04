@php
    $allowedImageExtensions = \App\Support\Settings::get('file_manager', 'allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

    if (is_string($allowedImageExtensions)) {
        $allowedImageExtensions = explode(',', $allowedImageExtensions);
    }

    if (!is_array($allowedImageExtensions)) {
        $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    }

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

    $currentImage = isset($filter) && $filter->image ? asset('storage/' . $filter->image) : null;

    $selectedMenuIds = isset($filter) ? $filter->menus->pluck('id')->map(fn ($id) => (int) $id)->all() : [];

    $renderCategoryRows = function ($items, int $level = 0) use (&$renderCategoryRows, $selectedMenuIds) {
        $html = '';

        foreach ($items as $category) {
            $categoryName = $category->translations->firstWhere('language.is_default_admin', true)?->name
                ?? $category->translations->first()?->name
                ?? ('#' . $category->id);

            $isChecked = in_array((int) $category->id, $selectedMenuIds, true);
            $padding = 20 + ($level * 22);
            $hasChildren = !empty($category->children) && count($category->children) > 0;

            $html .= '
                <div class="filter-category-row" data-level="' . $level . '">
                    <div class="filter-category-cell filter-category-name" style="padding-left: ' . $padding . 'px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="filter-category-branch">' . ($level > 0 ? '└─' : '•') . '</span>
                            <span class="filter-category-label">' . e($categoryName) . '</span>
                            ' . ($hasChildren ? '<span class="badge bg-light text-muted">Sub</span>' : '') . '
                        </div>
                    </div>
                    <div class="filter-category-cell filter-category-action text-end">
                        <label class="filter-switch mb-0">
                            <input type="checkbox"
                                   name="categories[' . $category->id . ']"
                                   class="category-checkbox"
                                   ' . ($isChecked ? 'checked' : '') . '>
                            <span class="filter-switch-slider"></span>
                        </label>
                    </div>
                </div>
            ';

            if ($hasChildren) {
                $html .= $renderCategoryRows($category->children, $level + 1);
            }
        }

        return $html;
    };
@endphp

<style>
    .filter-form-section-card {
        border: 1px solid #e9ebec;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        height: 100%;
    }

    .filter-form-section-card .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eff2f7;
        background: transparent;
    }

    .filter-form-section-card .card-body {
        padding: 1.25rem;
    }

    .filter-form-label {
        font-size: .875rem;
        font-weight: 600;
        color: #344054;
        margin-bottom: .5rem;
    }

    .filter-form-hint {
        font-size: .75rem;
        color: #667085;
        margin-top: .35rem;
    }

    .filter-image-box {
        min-height: 100%;
        border: 1px dashed #d0d5dd;
        border-radius: 14px;
        padding: 1rem;
        background: #fcfcfd;
    }

    .filter-image-preview {
        width: 100%;
        max-width: 220px;
        height: 220px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #eaecf0;
        background: #fff;
    }

    .filter-image-empty {
        width: 100%;
        max-width: 220px;
        height: 220px;
        border-radius: 14px;
        border: 1px dashed #d0d5dd;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #98a2b3;
        font-size: 2rem;
    }

    .filter-quick-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .filter-stat-box {
        border: 1px solid #eaecf0;
        border-radius: 12px;
        padding: .85rem .9rem;
        background: #fff;
    }

    .filter-stat-title {
        font-size: .75rem;
        font-weight: 600;
        color: #667085;
        margin-bottom: .25rem;
    }

    .filter-stat-value {
        font-size: .95rem;
        font-weight: 700;
        color: #101828;
    }

    .filter-translation-nav.nav-tabs {
        border-bottom: 1px solid #eaecf0;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .filter-translation-nav .nav-link {
        border: 1px solid #e9ebec;
        border-bottom: 0;
        border-radius: 10px 10px 0 0;
        color: #475467;
        background: #f8fafc;
        padding: .65rem 1rem;
    }

    .filter-translation-nav .nav-link.active {
        background: #fff;
        color: #101828;
        border-color: #e9ebec;
        font-weight: 600;
    }

    .filter-translation-pane {
        border: 1px solid #eaecf0;
        border-top: 0;
        border-radius: 0 0 14px 14px;
        padding: 1.25rem;
        background: #fff;
    }

    .filter-slug-btn {
        min-width: 46px;
    }

    .filter-option-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .filter-option-card {
        border: 1px solid #eaecf0;
        border-radius: 14px;
        padding: 1rem;
        background: #fff;
        min-height: 100%;
    }

    .filter-option-card .form-label {
        font-size: .85rem;
        font-weight: 700;
        margin-bottom: .65rem;
        color: #344054;
    }

    .filter-category-card {
        border: 1px solid #e9ebec;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .filter-category-toolbar {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eff2f7;
        background: linear-gradient(180deg, #fcfcfd 0%, #f8fafc 100%);
    }

    .filter-category-list {
        max-height: 520px;
        overflow-y: auto;
    }

    .filter-category-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 90px;
        align-items: center;
        min-height: 54px;
        border-bottom: 1px solid #f2f4f7;
    }

    .filter-category-row:last-child {
        border-bottom: 0;
    }

    .filter-category-cell {
        padding: .85rem 1rem;
    }

    .filter-category-name {
        min-width: 0;
    }

    .filter-category-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #101828;
        font-weight: 500;
    }

    .filter-category-branch {
        color: #98a2b3;
        font-size: .9rem;
        min-width: 20px;
    }

    .filter-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .filter-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .filter-switch-slider {
        position: absolute;
        inset: 0;
        cursor: pointer;
        background: #d0d5dd;
        transition: .2s ease-in-out;
        border-radius: 999px;
    }

    .filter-switch-slider:before {
        position: absolute;
        content: "";
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background: #fff;
        transition: .2s ease-in-out;
        border-radius: 50%;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.18);
    }

    .filter-switch input:checked + .filter-switch-slider {
        background: #f04438;
    }

    .filter-switch input:checked + .filter-switch-slider:before {
        transform: translateX(24px);
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

    @media (max-width: 991.98px) {
        .filter-option-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .filter-option-grid {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .filter-category-row {
            grid-template-columns: minmax(0, 1fr) 72px;
        }
    }
</style>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card filter-form-section-card">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Filter configuration') }}</h5>
                    </div>
                    <span class="badge bg-soft-primary text-primary">{{ isset($filter) && $filter->exists ? __('Edit mode') : __('Create mode') }}</span>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label for="input_type" class="filter-form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                        <select name="input_type"
                                id="input_type"
                                class="form-select @error('input_type') is-invalid @enderror"
                                required>
                            <option value="">{{ __('Select') }}...</option>
                            <option value="multi" @selected(old('input_type', $filter->input_type ?? '') === 'multi')>{{ __('Multi Select') }}</option>
                            <option value="single" @selected(old('input_type', $filter->input_type ?? '') === 'single')>{{ __('Single Select') }}</option>
                            <option value="freeform" @selected(old('input_type', $filter->input_type ?? '') === 'freeform')>{{ __('Free form') }}</option>
                        </select>
                        @error('input_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="filter-form-hint">{{ __('Choose how this filter behaves on product selection and listing.') }}</div>
                    </div>
                </div>

                <div class="filter-option-grid mt-3">
                    <div class="filter-option-card">
                        <label for="is_color_filter" class="form-label">{{ __('Color filter') }}</label>
                        <select name="is_color_filter" id="is_color_filter" class="form-select @error('is_color_filter') is-invalid @enderror">
                            <option value="0" @selected((int) old('is_color_filter', $filter->is_color_filter ?? 0) === 0)>No</option>
                            <option value="1" @selected((int) old('is_color_filter', $filter->is_color_filter ?? 0) === 1)>Yes</option>
                        </select>
                        @error('is_color_filter')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="filter-option-card">
                        <label for="show_in_sidebar" class="form-label">{{ __('Show in sidebar') }}</label>
                        <select name="show_in_sidebar" id="show_in_sidebar" class="form-select @error('show_in_sidebar') is-invalid @enderror">
                            <option value="0" @selected((int) old('show_in_sidebar', $filter->show_in_sidebar ?? 0) === 0)>No</option>
                            <option value="1" @selected((int) old('show_in_sidebar', $filter->show_in_sidebar ?? 0) === 1)>Yes</option>
                        </select>
                        @error('show_in_sidebar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="filter-option-card">
                        <label for="is_required" class="form-label">{{ __('Required') }}</label>
                        <select name="is_required" id="is_required" class="form-select @error('is_required') is-invalid @enderror">
                            <option value="0" @selected((int) old('is_required', $filter->is_required ?? 0) === 0)>No</option>
                            <option value="1" @selected((int) old('is_required', $filter->is_required ?? 0) === 1)>Yes</option>
                        </select>
                        @error('is_required')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="filter-option-card">
                        <label for="is_clickable" class="form-label">{{ __('Clickable') }}</label>
                        <select name="is_clickable" id="is_clickable" class="form-select @error('is_clickable') is-invalid @enderror">
                            <option value="0" @selected((int) old('is_clickable', $filter->is_clickable ?? 0) === 0)>No</option>
                            <option value="1" @selected((int) old('is_clickable', $filter->is_clickable ?? 0) === 1)>Yes</option>
                        </select>
                        @error('is_clickable')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card filter-form-section-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">{{ __('Filter image') }}</h5>
            </div>

            <div class="card-body">
                <div class="filter-image-box d-flex flex-column align-items-center justify-content-center text-center">
                    @if($currentImage)
                        <img src="{{ $currentImage }}"
                             alt="{{ $filter->name }}"
                             class="filter-image-preview mb-3">
                    @else
                        <div class="filter-image-empty mb-3">
                            <i class="ri-image-2-line"></i>
                        </div>
                    @endif

                    <label for="image" class="filter-form-label w-100 text-start">{{ __('Image') }}</label>
                    <input type="file"
                           name="image"
                           id="image"
                           class="form-control @error('image') is-invalid @enderror"
                           accept="{{ $acceptValues }}">
                    @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="filter-form-hint w-100 text-start">{{ __('Primary image for the filter card and listing preview.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card filter-form-section-card mt-4">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <h5 class="card-title mb-1">{{ __('Translations and SEO') }}</h5>
                <p class="text-muted mb-0">{{ __('Manage name, slug and SEO content per language.') }}</p>
            </div>
            <div class="text-muted small">{{ __('Slug is auto-synced from name and can be regenerated manually.') }}</div>
        </div>
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs filter-translation-nav mb-0" role="tablist">
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
                    $translation = isset($filter) && $filter->exists
                        ? $filter->translations->firstWhere('language.code', $language->code)
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

                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="translation-tab-{{ $language->code }}" role="tabpanel">
                    <div class="filter-translation-pane">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label for="name_{{ $language->code }}" class="filter-form-label">{{ __('Name') }}</label>
                                <input type="text"
                                       name="name[{{ $language->code }}]"
                                       id="name_{{ $language->code }}"
                                       class="form-control @error('name.' . $language->code) is-invalid @enderror"
                                       value="{{ $nameValue }}"
                                       placeholder="Filter name in {{ strtoupper($language->code) }}"
                                       data-lang-name="{{ $language->code }}"
                                    {{ $isRequired ? 'required' : '' }}>
                                @error('name.' . $language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                <label for="slug_{{ $language->code }}" class="filter-form-label">{{ __('Slug') }}</label>
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
                                            class="btn btn-outline-secondary filter-slug-btn js-generate-slug"
                                            data-lang="{{ $language->code }}"
                                            title="{{ __('Generate from name') }}">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                                @error('slug.' . $language->code)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                <label for="meta_title_{{ $language->code }}" class="filter-form-label">{{ __('Meta title') }}</label>
                                <input type="text"
                                       name="meta_title[{{ $language->code }}]"
                                       id="meta_title_{{ $language->code }}"
                                       class="form-control @error('meta_title.' . $language->code) is-invalid @enderror"
                                       value="{{ $metaTitleValue }}"
                                       placeholder="Meta title in {{ strtoupper($language->code) }}">
                                @error('meta_title.' . $language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                <label class="filter-form-label">{{ __('Meta keywords') }}</label>

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

                                <div class="filter-form-hint">{{ __('Keywords will be saved as comma-separated values.') }}</div>

                                @error('meta_keywords.' . $language->code)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="meta_description_{{ $language->code }}" class="filter-form-label">{{ __('Meta description') }}</label>
                                <textarea name="meta_description[{{ $language->code }}]"
                                          id="meta_description_{{ $language->code }}"
                                          class="form-control @error('meta_description.' . $language->code) is-invalid @enderror"
                                          rows="4"
                                          placeholder="Meta description in {{ strtoupper($language->code) }}">{{ $metaDescriptionValue }}</textarea>
                                @error('meta_description.' . $language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card filter-form-section-card mt-4">
    <div class="card-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <h5 class="card-title mb-1">{{ __('Categories') }}</h5>
                <p class="text-muted mb-0">{{ __('Choose where this filter should be available.') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="checkAll" class="form-check-input">
                <label for="checkAll" class="mb-0 text-muted small">{{ __('Select all') }}</label>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="filter-category-card">
            <div class="filter-category-toolbar d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">{{ __('Category visibility map') }}</div>
                    <div class="text-muted small">{{ __('Parent selection automatically affects its children.') }}</div>
                </div>
                <span class="badge bg-soft-info text-info">{{ count($selectedMenuIds) }} {{ __('selected') }}</span>
            </div>

            <div class="filter-category-list">
                {!! $renderCategoryRows($categories ?? [], 0) !!}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('checkAll');
        const categoryCheckboxes = Array.from(document.querySelectorAll('.category-checkbox'));

        if (checkAll) {
            const syncCheckAllState = function () {
                if (!categoryCheckboxes.length) {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                    return;
                }

                const checkedCount = categoryCheckboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                checkAll.checked = checkedCount === categoryCheckboxes.length;
                checkAll.indeterminate = checkedCount > 0 && checkedCount < categoryCheckboxes.length;
            };

            checkAll.addEventListener('change', function () {
                categoryCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = checkAll.checked;
                });

                syncCheckAllState();
            });

            syncCheckAllState();

            categoryCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const row = this.closest('.filter-category-row');
                    const currentLevel = parseInt(row.getAttribute('data-level') || '0', 10);

                    let nextRow = row.nextElementSibling;

                    while (nextRow) {
                        const nextLevel = parseInt(nextRow.getAttribute('data-level') || '0', 10);

                        if (nextLevel > currentLevel) {
                            const childCheckbox = nextRow.querySelector('.category-checkbox');

                            if (childCheckbox) {
                                childCheckbox.checked = this.checked;
                            }

                            nextRow = nextRow.nextElementSibling;
                            continue;
                        }

                        break;
                    }

                    syncCheckAllState();
                });
            });
        }

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
