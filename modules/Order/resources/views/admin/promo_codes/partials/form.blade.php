@php
    $categoriesScope = old('categories_scope', $promoCode->categories_scope ?? 'all');
    $productsScope = old('products_scope', $promoCode->products_scope ?? 'all');

    $selectedCategories = old('category_ids', $promoCode->relationLoaded('categories') ? $promoCode->categories->pluck('id')->all() : []);
    $selectedProducts = old('product_ids', $promoCode->relationLoaded('products') ? $promoCode->products->pluck('id')->all() : []);
@endphp

<div class="row g-3">
    <div class="col-lg-3">
        <label class="form-label">{{ __('Code') }}</label>
        <div class="input-group">
            <input
                type="text"
                name="code"
                value="{{ old('code', $promoCode->code) }}"
                class="form-control @error('code') is-invalid @enderror"
                placeholder="{{ __('Example: SAVE20') }}"
                autocomplete="off"
            >
            <button type="button" class="btn btn-outline-secondary" id="generatePromoCode">{{ __('Generate') }}</button>
            @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-text">{{ __('Only letters, numbers, "_" and "-" allowed.') }}</div>
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Type') }}</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror">
            <option value="percent" @selected(old('type', $promoCode->type) === 'percent')>{{ __('Percent (%)') }}</option>
            <option value="fixed" @selected(old('type', $promoCode->type) === 'fixed')>{{ __('Fixed (₼)') }}</option>
        </select>
        @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Value') }}</label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="value"
            value="{{ old('value', $promoCode->value) }}"
            class="form-control @error('value') is-invalid @enderror"
            placeholder="0"
        >
        @error('value')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Usage limit') }}</label>
        <input
            type="number"
            step="1"
            min="1"
            name="usage_limit"
            value="{{ old('usage_limit', $promoCode->usage_limit ?? 1) }}"
            class="form-control @error('usage_limit') is-invalid @enderror"
            placeholder="1"
        >
        @error('usage_limit')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">{{ __('For one-time codes set to 1.') }}</div>
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Minimum order total') }}</label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="min_order_total"
            value="{{ old('min_order_total', $promoCode->min_order_total) }}"
            class="form-control @error('min_order_total') is-invalid @enderror"
            placeholder="0"
        >
        @error('min_order_total')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Status') }}</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="Active" @selected(old('status', $promoCode->status) === 'Active')>{{ __('Active') }}</option>
            <option value="Inactive" @selected(old('status', $promoCode->status) === 'Inactive')>{{ __('Inactive') }}</option>
        </select>
        @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Starts at') }}</label>
        <input
            type="datetime-local"
            name="starts_at"
            value="{{ old('starts_at', optional($promoCode->starts_at)->format('Y-m-d\TH:i')) }}"
            class="form-control @error('starts_at') is-invalid @enderror"
        >
        @error('starts_at')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-3">
        <label class="form-label">{{ __('Ends at') }}</label>
        <input
            type="datetime-local"
            name="ends_at"
            value="{{ old('ends_at', optional($promoCode->ends_at)->format('Y-m-d\TH:i')) }}"
            class="form-control @error('ends_at') is-invalid @enderror"
        >
        @error('ends_at')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label">{{ __('Categories apply') }}</label>
        <div class="d-flex gap-3 mt-1">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="categories_scope" id="categories_scope_all" value="all" @checked($categoriesScope === 'all')>
                <label class="form-check-label" for="categories_scope_all">{{ __('All categories') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="categories_scope" id="categories_scope_selected" value="selected" @checked($categoriesScope === 'selected')>
                <label class="form-check-label" for="categories_scope_selected">{{ __('Selected categories') }}</label>
            </div>
        </div>

        <div class="mt-2" id="categoriesSelectWrap">
            <select name="category_ids[]" class="form-select js-select2 @error('category_ids') is-invalid @enderror" multiple>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategories, true))>
                        {{ $category->name }} ({{ $category->id }})
                    </option>
                @endforeach
            </select>
            @error('category_ids')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @error('category_ids.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-lg-6">
        <label class="form-label">{{ __('Products apply') }}</label>
        <div class="d-flex gap-3 mt-1">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="products_scope" id="products_scope_all" value="all" @checked($productsScope === 'all')>
                <label class="form-check-label" for="products_scope_all">{{ __('All products') }}</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="products_scope" id="products_scope_selected" value="selected" @checked($productsScope === 'selected')>
                <label class="form-check-label" for="products_scope_selected">{{ __('Selected products') }}</label>
            </div>
        </div>

        <div class="mt-2" id="productsSelectWrap">
            <select name="product_ids[]" class="form-select js-select2 @error('product_ids') is-invalid @enderror" multiple>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProducts, true))>
                        {{ $product->name }} ({{ $product->id }})
                    </option>
                @endforeach
            </select>
            @error('product_ids')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @error('product_ids.*')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-lg-12">
        <div class="alert alert-info mb-0">
            {{ __('Used count:') }} <strong>{{ (int) ($promoCode->used_count ?? 0) }}</strong>
        </div>
    </div>
</div>
