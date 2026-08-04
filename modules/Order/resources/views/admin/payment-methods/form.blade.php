@php
    $paymentMethodTranslations = $paymentMethod->relationLoaded('translations')
        ? $paymentMethod->translations->keyBy('language_id')
        : collect();

    $paymentMethodInstallments = old('installments', $paymentMethod->relationLoaded('installments')
        ? $paymentMethod->installments->map(function ($installment) {
            return [
                'month' => $installment->month,
                'percent' => $installment->percent,
                'sort_order' => $installment->sort_order,
            ];
        })->values()->all()
        : []);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('Key') }}</label>
        <input type="text" name="key" value="{{ old('key', $paymentMethod->key) }}" class="form-control @error('key') is-invalid @enderror">
        @error('key')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Type') }}</label>
        <select name="type" id="payment-method-type" class="form-select @error('type') is-invalid @enderror">
            @foreach($typeOptions as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}" @selected(old('type', $paymentMethod->type) === $typeKey)>{{ $typeLabel }}</option>
            @endforeach
        </select>
        @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Sort Order') }}</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $paymentMethod->sort_order ?? 0) }}" class="form-control @error('sort_order') is-invalid @enderror">
        @error('sort_order')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Gateway Code') }}</label>
        <select name="gateway_code" id="payment-method-gateway" class="form-select @error('gateway_code') is-invalid @enderror">
            <option value="">{{ __('Select') }}</option>
            @foreach($gatewayOptions as $gatewayKey => $gatewayLabel)
                <option value="{{ $gatewayKey }}" @selected(old('gateway_code', $paymentMethod->gateway_code) === $gatewayKey)>{{ $gatewayLabel }}</option>
            @endforeach
        </select>
        @error('gateway_code')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Icon') }}</label>
        <input type="file" name="icon" accept=".jpg,.jpeg,.png,.gif,.webp,.svg" class="form-control @error('icon') is-invalid @enderror">
        @error('icon')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if($paymentMethod->icon_path)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $paymentMethod->icon_path) }}" alt="{{ $paymentMethod->key }}" style="max-height: 60px;">
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="d-flex flex-column gap-2 pt-4">
            <input type="hidden" name="is_active" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked((int) old('is_active', $paymentMethod->is_active ? 1 : 0) === 1)>
                <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
            </div>

            <input type="hidden" name="is_online" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_online" id="is_online" value="1" @checked((int) old('is_online', $paymentMethod->is_online ? 1 : 0) === 1)>
                <label class="form-check-label" for="is_online">{{ __('Is Online') }}</label>
            </div>

            <input type="hidden" name="requires_redirect" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="requires_redirect" id="requires_redirect" value="1" @checked((int) old('requires_redirect', $paymentMethod->requires_redirect ? 1 : 0) === 1)>
                <label class="form-check-label" for="requires_redirect">{{ __('Requires Redirect') }}</label>
            </div>

            <input type="hidden" name="card_save" value="0">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="card_save" id="card_save" value="1" @checked((int) old('card_save', $paymentMethod->card_save ? 1 : 0) === 1)>
                <label class="form-check-label" for="card_save">{{ __('Card Save') }}</label>
            </div>
        </div>
        @error('is_active')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('is_online')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('requires_redirect')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @error('card_save')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Gateway Settings') }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">{{ __('Base URL') }}</label>
                <input type="text" name="base_url" value="{{ old('base_url', $paymentMethod->base_url) }}" class="form-control @error('base_url') is-invalid @enderror">
                @error('base_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Callback URL') }}</label>
                <input type="text" name="callback_url" value="{{ old('callback_url', $paymentMethod->callback_url) }}" class="form-control @error('callback_url') is-invalid @enderror">
                @error('callback_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Front Callback URL') }}</label>
                <input type="text" name="front_callback_url" value="{{ old('front_callback_url', $paymentMethod->front_callback_url) }}" class="form-control @error('front_callback_url') is-invalid @enderror">
                @error('front_callback_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Success Redirect URL') }}</label>
                <input type="text" name="success_redirect_url" value="{{ old('success_redirect_url', $paymentMethod->success_redirect_url) }}" class="form-control @error('success_redirect_url') is-invalid @enderror">
                @error('success_redirect_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Error Redirect URL') }}</label>
                <input type="text" name="error_redirect_url" value="{{ old('error_redirect_url', $paymentMethod->error_redirect_url) }}" class="form-control @error('error_redirect_url') is-invalid @enderror">
                @error('error_redirect_url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Public Key') }}</label>
                <input type="text" name="public_key" value="{{ old('public_key', $paymentMethod->public_key) }}" class="form-control @error('public_key') is-invalid @enderror">
                @error('public_key')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Private Key') }}</label>
                <input type="text" name="private_key" value="{{ old('private_key', $paymentMethod->private_key) }}" class="form-control @error('private_key') is-invalid @enderror">
                @error('private_key')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Username') }}</label>
                <input type="text" name="username" value="{{ old('username', $paymentMethod->username) }}" class="form-control @error('username') is-invalid @enderror">
                @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Password') }}</label>
                <input type="text" name="password" value="{{ old('password', $paymentMethod->password) }}" class="form-control @error('password') is-invalid @enderror">
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>




            <div class="col-md-3">
                <label class="form-label">{{ __('Merchant ID') }}</label>
                <input type="text" name="merchant_id" value="{{ old('merchant_id', $paymentMethod->merchant_id) }}" class="form-control @error('merchant_id') is-invalid @enderror">
                @error('merchant_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Language') }}</label>
                <input type="text" name="language" value="{{ old('language', $paymentMethod->language) }}" class="form-control @error('language') is-invalid @enderror">
                @error('language')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Currency') }}</label>
                <input type="text" name="currency" value="{{ old('currency', $paymentMethod->currency) }}" class="form-control @error('currency') is-invalid @enderror">
                @error('currency')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Description Prefix') }}</label>
                <input type="text" name="description_prefix" value="{{ old('description_prefix', $paymentMethod->description_prefix) }}" class="form-control @error('description_prefix') is-invalid @enderror">
                @error('description_prefix')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Translations') }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($languages as $language)
                @php
                    $translation = $paymentMethodTranslations->get($language->id);
                    $locale = trim((string) $language->code);
                @endphp

                <div class="col-12">
                    <div class="border rounded p-3">
                        <h6 class="mb-3">{{ $language->name }}</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Name') }}</label>
                                <input
                                    type="text"
                                    name="name[{{ $locale }}]"
                                    value="{{ old('name.' . $locale, $translation?->name) }}"
                                    class="form-control @error('name.' . $locale) is-invalid @enderror"
                                >
                                @error('name.' . $locale)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea
                                    name="description[{{ $locale }}]"
                                    class="form-control @error('description.' . $locale) is-invalid @enderror"
                                    rows="2"
                                >{{ old('description.' . $locale, $translation?->description) }}</textarea>
                                @error('description.' . $locale)
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

<div class="card mt-4" id="installment-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ __('Installments') }}</h5>
        <button type="button" class="btn btn-sm btn-primary" id="add-installment-row">{{ __('Add Row') }}</button>
    </div>
    <div class="card-body">
        @error('installments')
        <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <div class="table-responsive">
            <table class="table align-middle" id="installment-table">
                <thead>
                <tr>
                    <th>{{ __('Month') }}</th>
                    <th>{{ __('Percent') }}</th>
                    <th>{{ __('Sort') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($paymentMethodInstallments as $index => $installment)
                    <tr>
                        <td>
                            <input type="number" min="1" name="installments[{{ $index }}][month]" value="{{ $installment['month'] ?? '' }}" class="form-control @error('installments.' . $index . '.month') is-invalid @enderror">
                            @error('installments.' . $index . '.month')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="installments[{{ $index }}][percent]" value="{{ $installment['percent'] ?? '' }}" class="form-control @error('installments.' . $index . '.percent') is-invalid @enderror">
                            @error('installments.' . $index . '.percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" min="0" name="installments[{{ $index }}][sort_order]" value="{{ $installment['sort_order'] ?? 0 }}" class="form-control @error('installments.' . $index . '.sort_order') is-invalid @enderror">
                            @error('installments.' . $index . '.sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-installment-row">{{ __('Remove') }}</button>
                        </td>
                    </tr>
                @empty
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
    <a href="{{ route('admin.order.payment_methods.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
</div>

@push('scripts')
    <script>
        (function () {
            const addInstallmentRowButton = document.getElementById('add-installment-row');
            const installmentTableBody = document.querySelector('#installment-table tbody');

            function currentRowIndex() {
                return installmentTableBody.querySelectorAll('tr').length;
            }

            function installmentRowTemplate(index) {
                return `
                    <tr>
                        <td>
                            <input type="number" min="1" name="installments[${index}][month]" class="form-control">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="installments[${index}][percent]" class="form-control">
                        </td>
                        <td>
                            <input type="number" min="0" name="installments[${index}][sort_order]" value="0" class="form-control">
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-installment-row">{{ __('Remove') }}</button>
                        </td>
                    </tr>
                `;
            }

            addInstallmentRowButton.addEventListener('click', function () {
                installmentTableBody.insertAdjacentHTML('beforeend', installmentRowTemplate(currentRowIndex()));
            });

            document.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-installment-row');

                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('tr');

                if (row) {
                    row.remove();
                }
            });
        })();
    </script>
@endpush
