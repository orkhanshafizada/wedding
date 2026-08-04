<div class="row g-3 mt-2">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Enter name') }}" value="{{ old('name', $language->name ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Native name') }}</label>
                        <input type="text" name="native_name" class="form-control" placeholder="{{ __('Enter native name') }}" value="{{ old('native_name', $language->native_name ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Locale code') }} <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="en, az, tr-TR" value="{{ old('code', $language->code ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">{{ __('Status') }}</label>
                        @php($st = old('status', $language->status ?? 'Active'))
                        <select name="status" class="form-select">
                            <option value="Active" {{ $st === 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="Inactive" {{ $st === 'Inactive' ? 'selected' : '' }}>{{ __('Deactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">{{ __('Sort') }}</label>
                        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $language->sort_order ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold d-block">{{ __('Text direction') }}</label>
                        <input type="hidden" name="is_rtl" value="0">
                        <div class="form-check form-switch form-switch-md">
                            <input class="form-check-input" type="checkbox" id="rtlSwitch" name="is_rtl" value="1" {{ old('is_rtl', $language->is_rtl ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="rtlSwitch">{{ __('Rtl') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold d-block">{{ __('Required') }}</label>
                        <input type="hidden" name="is_required" value="0">
                        <div class="form-check form-switch form-switch-md">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="requiredSwitch"
                                   name="is_required"
                                   value="1"
                                {{ old('is_required', $language->is_required ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requiredSwitch">{{ __('Yes') }}</label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
