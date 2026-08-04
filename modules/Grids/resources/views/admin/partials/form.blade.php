@php
    use Modules\Product\Models\Variation\ProductVariation;

    $grid = $grid ?? null;
    $isEdit = $grid && $grid->exists;
    $requiredLocales = $requiredLocales ?? ($requiredLanguageCodes ?? []);
    $selectedRelatedItems = [];

    if (old('related_product_variation_ids')) {
        $oldVariationIds = array_values(array_unique(array_map('intval', (array) old('related_product_variation_ids', []))));

        $oldVariations = ProductVariation::query()
            ->whereIn('id', $oldVariationIds)
            ->with([
                'translations' => function ($translationQuery) {
                    $translationQuery
                        ->select(['id', 'product_variation_id', 'language_id', 'name', 'slug'])
                        ->orderBy('language_id');
                },
                'product.variations' => function ($variationQuery) {
                    $variationQuery
                        ->select(['id', 'product_id', 'sku', 'model'])
                        ->with([
                            'translations' => function ($translationQuery) {
                                $translationQuery
                                    ->select(['id', 'product_variation_id', 'language_id', 'name', 'slug'])
                                    ->orderBy('language_id');
                            },
                        ])
                        ->orderBy('id');
                },
                'media' => function ($mediaQuery) {
                    $mediaQuery
                        ->select(['id', 'product_variation_id', 'path', 'sort_order', 'is_main'])
                        ->orderByDesc('is_main')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->get()
            ->keyBy('id');

        foreach ($oldVariationIds as $variationId) {
            $variation = $oldVariations->get($variationId);

            if (!$variation) {
                $selectedRelatedItems[] = [
                    'type' => 'variation',
                    'id' => $variationId,
                    'product_id' => 0,
                    'text' => '#' . $variationId,
                    'subtitle' => 'Variation',
                    'image_url' => '',
                ];

                continue;
            }

            $variationName = '';
            foreach ($variation->translations ?? [] as $translation) {
                $name = trim((string) ($translation->name ?? ''));
                if ($name !== '') {
                    $variationName = $name;
                    break;
                }
            }

            if ($variationName === '') {
                $variationName = '#' . $variationId;
            }

            $productName = '';
            foreach ($variation->product?->variations ?? [] as $productVariation) {
                foreach ($productVariation->translations ?? [] as $translation) {
                    $name = trim((string) ($translation->name ?? ''));
                    if ($name !== '') {
                        $productName = $name;
                        break 2;
                    }
                }
            }

            $mainMedia = $variation->media->firstWhere('is_main', true) ?: $variation->media->first();
            $imageUrl = (string) ($mainMedia?->url ?? '');

            $selectedRelatedItems[] = [
                'type' => 'variation',
                'id' => (int) $variationId,
                'product_id' => (int) $variation->product_id,
                'text' => $productName !== '' ? ($productName . ' — ' . $variationName) : $variationName,
                'subtitle' => 'Variation',
                'image_url' => $imageUrl,
            ];
        }
    } elseif ($isEdit) {
        foreach ($grid->relatedProductItems ?? [] as $relatedItem) {
            $variation = $relatedItem->variation;

            $variationName = '';
            foreach ($variation?->translations ?? [] as $translation) {
                $name = trim((string) ($translation->name ?? ''));
                if ($name !== '') {
                    $variationName = $name;
                    break;
                }
            }

            if ($variationName === '') {
                $variationName = '#' . (int) $relatedItem->product_variation_id;
            }

            $productName = '';
            foreach ($variation?->product?->variations ?? [] as $productVariation) {
                foreach ($productVariation->translations ?? [] as $translation) {
                    $name = trim((string) ($translation->name ?? ''));
                    if ($name !== '') {
                        $productName = $name;
                        break 2;
                    }
                }
            }

            $mainMedia = $variation?->media?->firstWhere('is_main', true) ?: $variation?->media?->first();
            $imageUrl = (string) ($mainMedia?->url ?? '');

            $selectedRelatedItems[] = [
                'type' => 'variation',
                'id' => (int) $relatedItem->product_variation_id,
                'product_id' => (int) ($relatedItem->product_id ?? 0),
                'text' => $productName !== '' ? ($productName . ' — ' . $variationName) : $variationName,
                'subtitle' => 'Variation',
                'image_url' => $imageUrl,
            ];
        }
    }
@endphp

<form action="{{ $isEdit ? route('admin.grids.update', [$menu, $grid]) : route('admin.grids.store', $menu) }}"
      method="POST"
      enctype="multipart/form-data"
      id="gridForm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ __('Uploaded Files') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ __('Banner') }}</label>
                        <input type="file"
                               class="form-control @error('banner') is-invalid @enderror"
                               name="banner"
                               accept="image/*">

                        @error('banner')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if($isEdit && $grid->banner_url)
                            <div class="mt-3">
                                <img src="{{ $grid->banner_url }}"
                                     alt=""
                                     class="img-fluid rounded border"
                                     style="max-height: 160px; object-fit: cover;">
                            </div>

                            <div class="form-check mt-2">
                                <input type="hidden" name="remove_banner" value="0">
                                <input class="form-check-input" type="checkbox" name="remove_banner" value="1" id="remove_banner">
                                <label class="form-check-label" for="remove_banner">{{ __('Remove current banner') }}</label>
                            </div>
                        @else
                            <input type="hidden" name="remove_banner" value="0">
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Photos & Files') }}</label>
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-muted fs-12">{{ __('Drag to reorder. Select one main photo.') }}</div>
                            </div>

                            <input type="file"
                                   class="form-control js-grid-media-input"
                                   name="media_files[]"
                                   multiple>

                            @error('media_files')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @error('media_new_main')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @if($isEdit && $grid->media->count() > 0)
                                <div class="mt-3 row g-3 grid-media-list">
                                    @foreach($grid->media as $media)
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-media-item" draggable="true">
                                            <div class="border rounded p-2 h-100 {{ $media->is_main ? 'border-primary' : '' }}">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-light text-dark" style="cursor: grab;">
                                                        <i class="ri-drag-move-2-line"></i>
                                                    </span>
                                                    <button type="button"
                                                            class="btn btn-sm btn-soft-danger js-grid-media-delete"
                                                            title="{{ __('Delete') }}">
                                                        <i class="ri-delete-bin-6-line"></i>
                                                    </button>
                                                </div>

                                                @if($media->type === 'image')
                                                    <img src="{{ $media->url }}"
                                                         alt=""
                                                         class="img-fluid rounded"
                                                         style="width: 100%; height: 90px; object-fit: cover;">
                                                @else
                                                    <div class="text-center py-3">
                                                        <i class="ri-file-line fs-1"></i>
                                                        <div class="small text-truncate">{{ $media->original_name }}</div>
                                                    </div>
                                                @endif

                                                <input type="hidden" name="media_existing[{{ $media->id }}][id]" value="{{ $media->id }}">
                                                <input type="hidden" class="js-grid-media-sort" name="media_existing[{{ $media->id }}][sort_order]" value="{{ $media->sort_order }}">
                                                <input type="hidden" class="js-grid-media-is-main" name="media_existing[{{ $media->id }}][is_main]" value="{{ $media->is_main ? 1 : 0 }}" data-media-id="{{ $media->id }}">
                                                <input type="hidden" class="js-grid-media-delete-flag" name="media_existing[{{ $media->id }}][_delete]" value="0">

                                                @if($media->type === 'image')
                                                    <div class="mt-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input js-grid-media-main"
                                                                   type="radio"
                                                                   name="grid_main_radio"
                                                                   value="{{ $media->id }}"
                                                                   data-media-id="{{ $media->id }}"
                                                                {{ $media->is_main ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ __('Main Photo') }}</label>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-3 row g-3 js-grid-new-media-list"></div>

                            <div class="form-text mt-2">
                                {{ __('Supported: Images and PDF files. Max 10MB per file.') }}
                            </div>
                        </div>

                        <input type="hidden" name="media_new_main" value="">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ __('Content') }}</h5>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                        @foreach($languages as $index => $langObj)
                            <li class="nav-item">
                                <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                   data-bs-toggle="tab"
                                   href="#lang_{{ $langObj->code }}"
                                   role="tab">
                                    <span class="fw-semibold">{{ strtoupper($langObj->name) }}</span>
                                    @if(collect($requiredLanguageCodes ?? [])->contains($langObj->code))
                                        <span class="text-danger">*</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $index => $langObj)
                            @php
                                $lang = $langObj->code;
                                $keywordsHiddenId = "meta-keywords-hidden-{$lang}";
                                $keywordsWrapId = "meta-keywords-wrap-{$lang}";
                                $keywordsInputId = "meta-keywords-input-{$lang}";
                            @endphp

                            <div class="tab-pane {{ $index === 0 ? 'active' : '' }}" id="lang_{{ $lang }}" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            {{ __('Name') }}
                                            @if(collect($requiredLocales)->contains($lang))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input type="text"
                                               name="name[{{ $lang }}]"
                                               class="form-control @error("name.{$lang}") is-invalid @enderror"
                                               value="{{ old("name.{$lang}", $grid?->name[$lang] ?? '') }}"
                                               data-lang-name="{{ $lang }}">
                                        @error("name.{$lang}")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            {{ __('Slug') }}
                                            @if(collect($requiredLocales)->contains($lang))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input type="text"
                                               name="slug[{{ $lang }}]"
                                               class="form-control @error("slug.{$lang}") is-invalid @enderror"
                                               value="{{ old("slug.{$lang}", $grid?->slug[$lang] ?? '') }}"
                                               data-lang-slug="{{ $lang }}">
                                        @error("slug.{$lang}")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">{{ __('Unique per menu & language.') }}</div>
                                    </div>
                                </div>

                                <div class="mb-3 mt-3">
                                    <label class="form-label">
                                        {{ __('Content') }}
                                        @if(collect($requiredLocales)->contains($lang))
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <textarea name="content[{{ $lang }}]"
                                              id="content_{{ $lang }}"
                                              class="form-control @error("content.{$lang}") is-invalid @enderror"
                                              rows="6">{{ old("content.{$lang}", $grid?->content[$lang] ?? '') }}</textarea>
                                    @error("content.{$lang}")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Location or Group') }}</label>
                                    <input type="text"
                                           name="location_or_group[{{ $lang }}]"
                                           class="form-control @error("location_or_group.{$lang}") is-invalid @enderror"
                                           value="{{ old("location_or_group.{$lang}", $grid?->location_or_group[$lang] ?? '') }}">
                                    @error("location_or_group.{$lang}")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Meta Title') }}</label>
                                    <input type="text"
                                           name="meta_title[{{ $lang }}]"
                                           class="form-control @error("meta_title.{$lang}") is-invalid @enderror"
                                           value="{{ old("meta_title.{$lang}", $grid?->meta_title[$lang] ?? '') }}">
                                    @error("meta_title.{$lang}")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Meta Description') }}</label>
                                    <textarea name="meta_description[{{ $lang }}]"
                                              class="form-control @error("meta_description.{$lang}") is-invalid @enderror"
                                              rows="3">{{ old("meta_description.{$lang}", $grid?->meta_description[$lang] ?? '') }}</textarea>
                                    @error("meta_description.{$lang}")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">{{ __('Meta Keywords') }}</label>

                                    <input type="hidden"
                                           id="{{ $keywordsHiddenId }}"
                                           name="meta_keywords[{{ $lang }}]"
                                           value="{{ old("meta_keywords.{$lang}", $grid?->meta_keywords[$lang] ?? '') }}">

                                    <div id="{{ $keywordsWrapId }}" class="d-flex flex-wrap gap-2 mb-2"></div>

                                    <input type="text"
                                           id="{{ $keywordsInputId }}"
                                           class="form-control js-meta-keyword-input @error("meta_keywords.{$lang}") is-invalid @enderror"
                                           data-hidden-id="{{ $keywordsHiddenId }}"
                                           data-wrap-id="{{ $keywordsWrapId }}"
                                           placeholder="{{ __('Type keyword and press Enter') }}"
                                           autocomplete="off">

                                    <div class="form-text">{{ __('Keywords will be saved as comma-separated.') }}</div>
                                    @error("meta_keywords.{$lang}")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ __('General') }}</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Date Time 1') }}</label>
                            <input type="datetime-local"
                                   name="datetime1"
                                   class="form-control @error('datetime1') is-invalid @enderror"
                                   value="{{ old('datetime1', $grid?->datetime1?->format('Y-m-d\TH:i')) }}">
                            @error('datetime1')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Default: created_at') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('Date Time 2') }}</label>
                            <input type="datetime-local"
                                   name="datetime2"
                                   class="form-control @error('datetime2') is-invalid @enderror"
                                   value="{{ old('datetime2', $grid?->datetime2?->format('Y-m-d\TH:i')) }}">
                            @error('datetime2')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Default: null') }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Related Products') }}</label>

                        <div class="grid-related-products-search">
                            <select id="gridRelatedProductSelect"
                                    class="form-select"
                                    data-placeholder="{{ __('Search product or variation...') }}">
                                <option value="">{{ __('Search product or variation...') }}</option>
                            </select>
                        </div>

                        @error('related_product_variation_ids')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                        @error('related_product_variation_ids.*')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror

                        <div class="mt-3">
                            <div class="row g-3" id="gridRelatedSelectedItems"></div>
                        </div>

                        <div class="form-text mt-2">{{ __('Select one variation per product.') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Status') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                   name="is_active"
                                   class="form-check-input"
                                   value="1"
                                {{ old('is_active', $grid?->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Active') }}</label>
                        </div>
                        @error('is_active')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.grids.index', $menu) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line align-bottom me-1"></i>
                        {{ $isEdit ? __('Update') : __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
    <style>
        .meta-keyword-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.375rem 0.625rem;
            border-radius: 0.375rem;
            background-color: #f3f6f9;
            border: 1px solid #dce1e6;
            font-size: 13px;
            line-height: 1;
        }

        .meta-keyword-badge button {
            border: 0;
            background: transparent;
            padding: 0;
            line-height: 1;
            font-size: 14px;
            cursor: pointer;
        }

        .grid-related-products-search .choices {
            width: 100%;
            margin-bottom: 0;
        }

        .grid-related-products-search .choices__inner {
            min-height: 38px;
            padding: .375rem .75rem;
            border-radius: .25rem;
            border: 1px solid var(--vz-border-color);
            background-color: var(--vz-input-bg);
            font-size: .875rem;
            line-height: 1.5;
        }

        .grid-related-products-search .choices__input {
            background-color: transparent;
            margin: 0;
            padding: 0;
            font-size: .875rem;
        }

        .grid-related-products-search .choices__list--single {
            padding: 0;
        }

        .grid-related-products-search .choices__placeholder {
            opacity: .6;
        }

        .grid-related-products-search .choices__list--dropdown,
        .grid-related-products-search .choices__list[aria-expanded] {
            width: 100% !important;
            border-radius: .25rem;
            border: 1px solid var(--vz-border-color);
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            z-index: 1056;
        }

        .grid-related-products-search .choices__list--dropdown .choices__item {
            padding: .5rem 1.75rem;
            font-size: .875rem;
            white-space: normal;
        }

        .grid-related-products-search .is-focused .choices__inner,
        .grid-related-products-search .is-open .choices__inner {
            border-color: var(--vz-primary);
            box-shadow: 0 0 0 .15rem rgba(var(--vz-primary-rgb), .15);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($languages as $lang)
            CKEDITOR.replace('content_{{ $lang->code }}', {
                height: 300,
                removePlugins: 'elementspath',
                resize_enabled: false
            });
            @endforeach

            initializeMetaKeywordInputs();
            initializeGridRelatedProductsSearch();

            if (typeof initGridMediaManager === 'function') {
                initGridMediaManager();
            }

            document.getElementById('gridForm').addEventListener('submit', function () {
                @foreach($languages as $lang)
                if (CKEDITOR.instances['content_{{ $lang->code }}']) {
                    CKEDITOR.instances['content_{{ $lang->code }}'].updateElement();
                }
                @endforeach
            });
        });

        function initializeMetaKeywordInputs() {
            const inputs = document.querySelectorAll('.js-meta-keyword-input');

            inputs.forEach(function (input) {
                const hiddenId = input.dataset.hiddenId;
                const wrapId = input.dataset.wrapId;
                const hidden = document.getElementById(hiddenId);
                const wrap = document.getElementById(wrapId);

                if (!hidden || !wrap || input.dataset.initialized === '1') {
                    return;
                }

                input.dataset.initialized = '1';

                const state = {
                    keywords: parseKeywords(hidden.value)
                };

                renderKeywords(wrap, hidden, state);

                input.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter' && event.key !== ',') {
                        return;
                    }

                    event.preventDefault();

                    const value = input.value.trim();

                    if (value === '') {
                        return;
                    }

                    addKeyword(value, state, wrap, hidden);
                    input.value = '';
                });

                input.addEventListener('blur', function () {
                    const value = input.value.trim();

                    if (value === '') {
                        return;
                    }

                    addKeyword(value, state, wrap, hidden);
                    input.value = '';
                });

                wrap.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-remove-keyword]');
                    if (!button) {
                        return;
                    }

                    const value = button.getAttribute('data-remove-keyword') || '';
                    state.keywords = state.keywords.filter(function (item) {
                        return item.toLocaleLowerCase() !== value.toLocaleLowerCase();
                    });

                    syncHidden(hidden, state);
                    renderKeywords(wrap, hidden, state);
                });
            });
        }

        function addKeyword(value, state, wrap, hidden) {
            const parts = value.split(',')
                .map(function (item) {
                    return item.trim();
                })
                .filter(function (item) {
                    return item !== '';
                });

            parts.forEach(function (part) {
                const exists = state.keywords.some(function (item) {
                    return item.toLocaleLowerCase() === part.toLocaleLowerCase();
                });

                if (!exists) {
                    state.keywords.push(part);
                }
            });

            syncHidden(hidden, state);
            renderKeywords(wrap, hidden, state);
        }

        function parseKeywords(value) {
            return String(value || '')
                .split(',')
                .map(function (item) {
                    return item.trim();
                })
                .filter(function (item) {
                    return item !== '';
                });
        }

        function syncHidden(hidden, state) {
            hidden.value = state.keywords.join(', ');
        }

        function renderKeywords(wrap, hidden, state) {
            wrap.innerHTML = '';

            state.keywords.forEach(function (keyword) {
                const badge = document.createElement('span');
                badge.className = 'meta-keyword-badge';

                const text = document.createElement('span');
                text.textContent = keyword;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.setAttribute('data-remove-keyword', keyword);
                removeButton.innerHTML = '&times;';

                badge.appendChild(text);
                badge.appendChild(removeButton);
                wrap.appendChild(badge);
            });

            hidden.value = state.keywords.join(', ');
        }

        function initializeGridRelatedProductsSearch() {
            const initialItems = @json($selectedRelatedItems);
            const selectedItems = new Map();

            const container = document.getElementById('gridRelatedSelectedItems');
            const selectElement = document.getElementById('gridRelatedProductSelect');

            if (!container || !selectElement || typeof Choices === 'undefined') {
                return;
            }

            if (selectElement.dataset.choicesBound === '1') {
                return;
            }

            selectElement.dataset.choicesBound = '1';

            const searchUrl = "{{ route('admin.grids.ajax.related-products') }}";
            const metaMap = new Map();

            function itemKey(item) {
                return item.type + ':' + item.id;
            }

            function renderSelectedItems() {
                container.innerHTML = '';

                Array.from(selectedItems.values()).forEach(function (item, index) {
                    const column = document.createElement('div');
                    column.className = 'col-xl-12';

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
                        avatar.innerHTML = '<i class="ri-price-tag-3-line"></i>';
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

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `related_product_variation_ids[${index}]`;
                    hiddenInput.value = item.id;

                    top.appendChild(avatarWrap);
                    top.appendChild(info);
                    top.appendChild(removeButton);

                    body.appendChild(top);
                    body.appendChild(hiddenInput);

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
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
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
                    const rows = Array.isArray(json.data) ? json.data : [];

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
                    choices.removeActiveItems();
                    selectElement.value = '';
                    return;
                }

                const duplicateProduct = Array.from(selectedItems.values()).find(function (item) {
                    return String(item.product_id || '') === String(row.product_id || '');
                });

                if (duplicateProduct) {
                    choices.removeActiveItems();
                    selectElement.value = '';
                    return;
                }

                const item = {
                    type: row.type,
                    id: row.id,
                    product_id: row.product_id,
                    text: row.text,
                    subtitle: row.subtitle || '',
                    image_url: row.image_url || ''
                };

                selectedItems.set(itemKey(item), item);
                renderSelectedItems();

                choices.removeActiveItems();
                selectElement.value = '';
            });

            loadChoices('');
        }
    </script>
@endpush
