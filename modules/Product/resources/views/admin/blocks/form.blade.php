@php
    $currentAdminLanguageId = $adminDefaultLanguageId
        ?? optional($languages->firstWhere('code', app()->getLocale()))->id
        ?? optional($languages->firstWhere('is_default_admin', 1))->id
        ?? optional($languages->first())->id;

    $requiredLanguageIds = collect($requiredLanguageIds ?? [])->map(fn ($id) => (int) $id)->all();

    $selectedCategoryIds = collect(old('category_ids', isset($block) ? $block->selectedCategories->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $selectedBrandIds = collect(old('brand_value_ids', isset($block) ? $block->selectedBrands->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $selectedProductIds = collect(old('product_variation_ids', isset($block) ? $block->selectedProducts->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $onlyDiscountProducts = (int) old('only_discount_products', isset($block) ? (int) $block->only_discount_products : 0);
    $onlyNewProducts = (int) old('only_new_products', isset($block) ? (int) $block->only_new_products : 0);
    $bestSellerProducts = (int) old('best_seller_products', isset($block) ? (int) $block->best_seller_products : 0);

    $renderCategoryOptions = function ($items, $level = 0) use (&$renderCategoryOptions, $selectedCategoryIds, $currentAdminLanguageId) {
        $html = '';

        foreach ($items as $item) {
            $translation = $item->translations->firstWhere('language_id', $currentAdminLanguageId) ?? $item->translations->first();
            $label = str_repeat('— ', $level) . ($translation?->name ?? ('#' . $item->id));
            $selected = in_array((int) $item->id, $selectedCategoryIds, true) ? 'selected' : '';

            $html .= '<option value="' . e($item->id) . '" ' . $selected . '>' . e($label) . '</option>';

            if ($item->relationLoaded('childrenRecursive') && $item->childrenRecursive->isNotEmpty()) {
                $html .= $renderCategoryOptions($item->childrenRecursive, $level + 1);
            }
        }

        return $html;
    };
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border shadow-sm mb-0">
            <div class="card-header bg-light-subtle">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Block Title') }}</h5>
                        <p class="text-muted mb-0">{{ __('Add the block title for each active admin language.') }}</p>
                    </div>
                    <span class="badge bg-danger-subtle text-danger">{{ __('Required') }}</span>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom nav-success mb-4" role="tablist">
                    @foreach($languages as $index => $language)
                        @php
                            $isRequiredLanguage = in_array((int) $language->id, $requiredLanguageIds, true);
                        @endphp
                        <li class="nav-item" role="presentation">
                            <a href="#title-tab-{{ $language->code }}"
                               class="nav-link {{ $index === 0 ? 'active' : '' }}"
                               data-bs-toggle="tab"
                               role="tab">
                                <span class="fw-semibold">{{ strtoupper($language->code) }}</span>
                                @if($isRequiredLanguage)
                                    <span class="text-danger ms-1">*</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach($languages as $index => $language)
                        @php
                            $translation = isset($block) ? $block->translations->firstWhere('language_id', $language->id) : null;
                            $isRequiredLanguage = in_array((int) $language->id, $requiredLanguageIds, true);
                        @endphp
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="title-tab-{{ $language->code }}" role="tabpanel">
                            <div class="row g-3 align-items-start">
                                <div class="col-lg-2">
                                    <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                        <div class="fw-semibold">{{ strtoupper($language->code) }}</div>
                                        <div class="text-muted small">{{ __('Language') }}</div>
                                        @if($isRequiredLanguage)
                                            <div class="mt-2">
                                                <span class="badge bg-danger-subtle text-danger">{{ __('Required') }}</span>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="badge bg-secondary-subtle text-secondary">{{ __('Optional') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <label for="title_{{ $language->id }}" class="form-label">{{ __('Title') }}</label>
                                    <input type="text"
                                           id="title_{{ $language->id }}"
                                           name="title[{{ $language->id }}]"
                                           class="form-control form-control-lg @error('title.' . $language->id) is-invalid @enderror"
                                           value="{{ old('title.' . $language->id, $translation?->title) }}"
                                           placeholder="{{ __('Enter block title') }}">
                                    @error('title.' . $language->id)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('This title will be used in the selected language context.') }}</div>
                                        @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border shadow-sm mb-0">
            <div class="card-header bg-light-subtle">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Selection Rules') }}</h5>
                        <p class="text-muted mb-0">{{ __('Configure category, brand, and variation scopes. All selectors stay synchronized with each other.') }}</p>
                    </div>
                    <span class="badge bg-info-subtle text-info">{{ __('Dynamic') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="border rounded-3 p-3 p-lg-4">
                            <div class="row g-4 align-items-start">
                                <div class="col-lg-4">
                                    <label for="category_scope" class="form-label fw-semibold">{{ __('Category Selection') }} <span class="text-danger">*</span></label>
                                    <select name="category_scope" id="category_scope" class="form-select @error('category_scope') is-invalid @enderror">
                                        <option value="all" @selected(old('category_scope', $block->category_scope ?? 'all') === 'all')>{{ __('All Categories') }}</option>
                                        <option value="selected" @selected(old('category_scope', $block->category_scope ?? 'all') === 'selected')>{{ __('Selected Categories') }}</option>
                                    </select>
                                    @error('category_scope')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('Use selected scope to restrict results to specific categories.') }}</div>
                                        @enderror
                                </div>

                                <div class="col-lg-8 block-category-select-wrapper">
                                    <label for="category_ids" class="form-label fw-semibold">{{ __('Categories') }}</label>
                                    <select name="category_ids[]" id="category_ids" class="form-select @error('category_ids') is-invalid @enderror" multiple>
                                        {!! $renderCategoryOptions($categories) !!}
                                    </select>
                                    @error('category_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('Only categories related to selected brands and variations will remain available.') }}</div>
                                        @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 p-3 p-lg-4">
                            <div class="row g-4 align-items-start">
                                <div class="col-lg-4">
                                    <label for="brand_scope" class="form-label fw-semibold">{{ __('Brand Selection') }} <span class="text-danger">*</span></label>
                                    <select name="brand_scope" id="brand_scope" class="form-select @error('brand_scope') is-invalid @enderror">
                                        <option value="all" @selected(old('brand_scope', $block->brand_scope ?? 'all') === 'all')>{{ __('All Brands') }}</option>
                                        <option value="selected" @selected(old('brand_scope', $block->brand_scope ?? 'all') === 'selected')>{{ __('Selected Brands') }}</option>
                                    </select>
                                    @error('brand_scope')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('Brand options are filtered by selected categories and variations.') }}</div>
                                        @enderror
                                </div>

                                <div class="col-lg-8 block-brand-select-wrapper">
                                    <label for="brand_value_ids" class="form-label fw-semibold">{{ __('Brands') }}</label>
                                    <select name="brand_value_ids[]" id="brand_value_ids" class="form-select @error('brand_value_ids') is-invalid @enderror" multiple data-selected='@json($selectedBrandIds)'>
                                        @foreach($brandValues as $brandValue)
                                            @php
                                                $brandTranslation = $brandValue->translations->firstWhere('language_id', $currentAdminLanguageId) ?? $brandValue->translations->first();
                                            @endphp
                                            <option value="{{ $brandValue->id }}" @selected(in_array((int) $brandValue->id, $selectedBrandIds, true))>
                                                {{ $brandTranslation?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_value_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('Visible brands automatically adapt to your current category and variation filters.') }}</div>
                                        @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 p-3 p-lg-4">
                            <div class="row g-4 align-items-start">
                                <div class="col-lg-4">
                                    <label for="product_scope" class="form-label fw-semibold">{{ __('Variation Selection') }} <span class="text-danger">*</span></label>
                                    <select name="product_scope" id="product_scope" class="form-select @error('product_scope') is-invalid @enderror">
                                        <option value="all" @selected(old('product_scope', $block->product_scope ?? 'all') === 'all')>{{ __('All Variations') }}</option>
                                        <option value="selected" @selected(old('product_scope', $block->product_scope ?? 'all') === 'selected')>{{ __('Selected Variations') }}</option>
                                    </select>
                                    @error('product_scope')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('Variation options are filtered by category and brand selection.') }}</div>
                                        @enderror
                                </div>

                                <div class="col-lg-8 block-product-select-wrapper">
                                    <label for="product_variation_ids" class="form-label fw-semibold">{{ __('Variations') }}</label>
                                    <select name="product_variation_ids[]" id="product_variation_ids" class="form-select @error('product_variation_ids') is-invalid @enderror" multiple data-selected='@json($selectedProductIds)'>
                                        @foreach($variations as $variation)
                                            @php
                                                $translation = $variation->translations->first();
                                                $variationLabel = $translation?->name ?? ('#' . $variation->id);
                                                $skuLabel = $variation->sku ? ' [' . $variation->sku . ']' : '';
                                            @endphp
                                            <option value="{{ $variation->id }}" @selected(in_array((int) $variation->id, $selectedProductIds, true))>
                                                {{ $variationLabel . $skuLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_variation_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        <div class="form-text">{{ __('Only compatible variations remain visible after category and brand filtering.') }}</div>
                                        @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="ri-information-line align-middle me-1"></i>
                            {{ __('Selections work together. Choosing categories narrows brands and variations. Choosing brands narrows categories and variations. Choosing variations narrows categories and brands.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border shadow-sm mb-0">
            <div class="card-header bg-light-subtle">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Product Conditions') }}</h5>
                        <p class="text-muted mb-0">{{ __('Define which variation groups should be included in this block.') }}</p>
                    </div>
                    <span class="badge bg-warning-subtle text-warning">{{ __('Filtering') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 h-100">
                            <label class="form-label fw-semibold d-block mb-3">{{ __('Only Discount Products') }} <span class="text-danger">*</span></label>

                            <div class="d-flex flex-wrap gap-2">
                                <input class="btn-check" type="radio" name="only_discount_products" id="only_discount_products_no" value="0" @checked($onlyDiscountProducts === 0)>
                                <label class="btn btn-outline-secondary" for="only_discount_products_no">{{ __('No') }}</label>

                                <input class="btn-check" type="radio" name="only_discount_products" id="only_discount_products_yes" value="1" @checked($onlyDiscountProducts === 1)>
                                <label class="btn btn-outline-warning" for="only_discount_products_yes">{{ __('Yes') }}</label>
                            </div>

                            @error('only_discount_products')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text mt-2">{{ __('Show only variations with discount pricing.') }}</div>
                                @enderror
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 h-100">
                            <label class="form-label fw-semibold d-block mb-3">{{ __('Only New Products') }} <span class="text-danger">*</span></label>

                            <div class="d-flex flex-wrap gap-2">
                                <input class="btn-check" type="radio" name="only_new_products" id="only_new_products_no" value="0" @checked($onlyNewProducts === 0)>
                                <label class="btn btn-outline-secondary" for="only_new_products_no">{{ __('No') }}</label>

                                <input class="btn-check" type="radio" name="only_new_products" id="only_new_products_yes" value="1" @checked($onlyNewProducts === 1)>
                                <label class="btn btn-outline-info" for="only_new_products_yes">{{ __('Yes') }}</label>
                            </div>

                            @error('only_new_products')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text mt-2">{{ __('Order by newest product creation date.') }}</div>
                                @enderror
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 h-100">
                            <label class="form-label fw-semibold d-block mb-3">{{ __('Best Seller Products') }} <span class="text-danger">*</span></label>

                            <div class="d-flex flex-wrap gap-2">
                                <input class="btn-check" type="radio" name="best_seller_products" id="best_seller_products_no" value="0" @checked($bestSellerProducts === 0)>
                                <label class="btn btn-outline-secondary" for="best_seller_products_no">{{ __('No') }}</label>

                                <input class="btn-check" type="radio" name="best_seller_products" id="best_seller_products_yes" value="1" @checked($bestSellerProducts === 1)>
                                <label class="btn btn-outline-success" for="best_seller_products_yes">{{ __('Yes') }}</label>
                            </div>

                            @error('best_seller_products')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text mt-2">{{ __('Prioritize variations with the highest sold quantity.') }}</div>
                                @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border shadow-sm mb-0">
            <div class="card-header bg-light-subtle">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ __('Publication Settings') }}</h5>
                        <p class="text-muted mb-0">{{ __('Set the block limit and publication status.') }}</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">{{ __('Final Step') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <label for="limit" class="form-label fw-semibold">{{ __('Limit') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-list-check-2"></i></span>
                            <input type="number"
                                   name="limit"
                                   id="limit"
                                   class="form-control @error('limit') is-invalid @enderror"
                                   value="{{ old('limit', $block->limit ?? 12) }}"
                                   min="1"
                                   max="100"
                                   required>
                        </div>
                        @error('limit')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <div class="form-text">{{ __('Maximum number of variations that can be returned for this block.') }}</div>
                            @enderror
                    </div>

                    <div class="col-lg-6">
                        <label for="status" class="form-label fw-semibold">{{ __('Status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(\App\Enums\StatusEnum::getOptions() as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $block->status ?? \App\Enums\StatusEnum::ACTIVE) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <div class="form-text">{{ __('Inactive blocks stay saved but will not be used on the storefront.') }}</div>
                            @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
