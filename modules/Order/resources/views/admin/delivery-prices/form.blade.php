<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('Parent Delivery Price') }}</label>
        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="0">{{ __('Main') }}</option>
            @foreach($parentOptions->where('parent_id', 0) as $parentOption)
                @include('order::admin.delivery-prices.partials.parent-option', [
                    'deliveryPriceOption' => $parentOption,
                    'parentOptions' => $parentOptions,
                    'selectedParentId' => (int) old('parent_id', $deliveryPrice->parent_id),
                    'level' => 0,
                ])
            @endforeach
        </select>
        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ __('Name') }}</label>
        <input type="text" name="name" value="{{ old('name', $deliveryPrice->name) }}" class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ __('Price') }}</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $deliveryPrice->price) }}" class="form-control @error('price') is-invalid @enderror">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ __('Status') }}</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $deliveryPrice->status) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
    <a href="{{ route('admin.order.delivery_prices.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
</div>
