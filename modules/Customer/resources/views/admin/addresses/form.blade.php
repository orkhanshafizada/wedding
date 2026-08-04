@php
    $isEdit = isset($address);
    $selectedCountryId = old('country_id', $address->country_id ?? null);
    $selectedDeliveryPriceId = old('delivery_price_id', $address->delivery_price_id ?? null);
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h5 class="mb-1">{{ __('Address Information') }}</h5>
                        <p class="text-muted mb-0">{{ __('Organize the customer address with a clean and structured layout.') }}</p>
                    </div>
                </div>

                @error('status')
                <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                @error('is_default')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="card-body pt-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-0">{{ __('Classification') }}</h6>
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-medium">{{ __('Type') }} <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                                        <option value="">{{ __('Select type') }}</option>

                                        @foreach($types as $type)
                                            <option value="{{ $type->value }}" @selected(old('type', $address->type ?? '') === $type->value)>
                                                {{ ucfirst($type->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-medium">{{ __('Label') }}</label>
                                    <input
                                        type="text"
                                        name="label"
                                        value="{{ old('label', $address->label ?? '') }}"
                                        class="form-control @error('label') is-invalid @enderror"
                                        placeholder="{{ __('Home, Office, Warehouse') }}"
                                    >
                                    @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-medium">{{ __('Sort Order') }}</label>
                                    <input
                                        type="number"
                                        min="0"
                                        name="sort_order"
                                        value="{{ old('sort_order', $address->sort_order ?? 0) }}"
                                        class="form-control @error('sort_order') is-invalid @enderror"
                                        placeholder="0"
                                    >
                                    @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="border rounded-3 p-3 p-lg-4 h-100">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-0">{{ __('Recipient Details') }}</h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ __('Recipient Name') }}</label>
                                    <input
                                        type="text"
                                        name="recipient_name"
                                        value="{{ old('recipient_name', $address->recipient_name ?? '') }}"
                                        class="form-control @error('recipient_name') is-invalid @enderror"
                                        placeholder="{{ __('Enter recipient name') }}"
                                    >
                                    @error('recipient_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ __('Phone') }}</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        value="{{ old('phone', $address->phone ?? '') }}"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="{{ __('Enter phone') }}"
                                    >
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ __('Company') }}</label>
                                    <input
                                        type="text"
                                        name="company"
                                        value="{{ old('company', $address->company ?? '') }}"
                                        class="form-control @error('company') is-invalid @enderror"
                                        placeholder="{{ __('Enter company') }}"
                                    >
                                    @error('company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="border rounded-3 p-3 p-lg-4 h-100">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-0">{{ __('Location Details') }}</h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ __('Country') }}</label>
                                    <select name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                        <option value="">{{ __('Select country') }}</option>

                                        @foreach($countries as $country)
                                            @php
                                                $shortNames = $country->short_names ?? [];
                                                $locale = app()->getLocale();
                                                $label = $shortNames[$locale] ?? $shortNames['en'] ?? $country->iso2;
                                            @endphp

                                            <option value="{{ $country->id }}" @selected((int) $selectedCountryId === (int) $country->id)>
                                                {{ $label }} ({{ $country->iso2 }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ __('Delivery Price') }}</label>
                                    <select name="delivery_price_id" class="form-select @error('delivery_price_id') is-invalid @enderror">
                                        <option value="">{{ __('Select delivery price') }}</option>

                                        @foreach($deliveryPrices as $deliveryPrice)
                                            <option value="{{ $deliveryPrice->id }}" @selected((int) $selectedDeliveryPriceId === (int) $deliveryPrice->id)>
                                                #{{ $deliveryPrice->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('delivery_price_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 p-3 p-lg-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-0">{{ __('Address Details') }}</h6>
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-medium">{{ __('Region') }}</label>
                                    <input
                                        type="text"
                                        name="region"
                                        value="{{ old('region', $address->region ?? '') }}"
                                        class="form-control @error('region') is-invalid @enderror"
                                        placeholder="{{ __('Enter region') }}"
                                    >
                                    @error('region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-medium">{{ __('City') }} <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="city"
                                        value="{{ old('city', $address->city ?? '') }}"
                                        class="form-control @error('city') is-invalid @enderror"
                                        placeholder="{{ __('Enter city') }}"
                                    >
                                    @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-medium">{{ __('Postal Code') }}</label>
                                    <input
                                        type="text"
                                        name="postal_code"
                                        value="{{ old('postal_code', $address->postal_code ?? '') }}"
                                        class="form-control @error('postal_code') is-invalid @enderror"
                                        placeholder="{{ __('Enter postal code') }}"
                                    >
                                    @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label fw-medium">{{ __('Address Line 1') }} <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="address_line1"
                                        value="{{ old('address_line1', $address->address_line1 ?? '') }}"
                                        class="form-control @error('address_line1') is-invalid @enderror"
                                        placeholder="{{ __('Enter address line 1') }}"
                                    >
                                    @error('address_line1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label fw-medium">{{ __('Address Line 2') }}</label>
                                    <input
                                        type="text"
                                        name="address_line2"
                                        value="{{ old('address_line2', $address->address_line2 ?? '') }}"
                                        class="form-control @error('address_line2') is-invalid @enderror"
                                        placeholder="{{ __('Enter address line 2') }}"
                                    >
                                    @error('address_line2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 p-3 p-lg-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-0">{{ __('Additional Note') }}</h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ __('Note') }}</label>
                                    <textarea
                                        name="note"
                                        rows="5"
                                        class="form-control @error('note') is-invalid @enderror"
                                        placeholder="{{ __('Enter note') }}"
                                    >{{ old('note', $address->note ?? '') }}</textarea>
                                    @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-light border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-0">
                            <div>
                                <h6 class="mb-1">{{ __('Address Settings') }}</h6>
                                <p class="text-muted mb-0">{{ __('Use the switches to control availability and default selection for this address type.') }}</p>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <div class="form-check form-switch form-switch-md m-0">
                                    <input type="hidden" name="status" value="0">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="status_footer"
                                        name="status"
                                        value="1"
                                        @checked((bool) old('status', $address->status ?? true))
                                    >
                                    <label class="form-check-label ms-2 fw-medium" for="status_footer">{{ __('Active') }}</label>
                                </div>

                                <div class="form-check form-switch form-switch-md m-0">
                                    <input type="hidden" name="is_default" value="0">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="is_default_footer"
                                        name="is_default"
                                        value="1"
                                        @checked((bool) old('is_default', $address->is_default ?? false))
                                    >
                                    <label class="form-check-label ms-2 fw-medium" for="is_default_footer">{{ __('Set as default') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pairs = [
                ['status', 'status_footer'],
                ['is_default', 'is_default_footer']
            ];

            pairs.forEach(function (pair) {
                const first = document.getElementById(pair[0]);
                const second = document.getElementById(pair[1]);

                if (!first || !second) {
                    return;
                }

                const sync = function (source, target) {
                    target.checked = source.checked;
                };

                first.addEventListener('change', function () {
                    sync(first, second);
                });

                second.addEventListener('change', function () {
                    sync(second, first);
                });
            });
        });
    </script>
@endPushOnce
