<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.order.orders.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-xl-3">
                    <label class="form-label fw-semibold">{{ __('Search') }}</label>
                    <div class="position-relative">
                        <input
                            type="text"
                            name="q"
                            class="form-control ps-5"
                            value="{{ $filters['q'] ?? '' }}"
                            placeholder="{{ __('Order number, promo code, guest token, customer...') }}"
                        >
                        <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label fw-semibold">{{ __('Order status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($statusOptions as $statusOption)
                            <option value="{{ $statusOption['code'] }}" @selected((string) ($filters['status'] ?? '') === (string) $statusOption['code'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label fw-semibold">{{ __('Payment status') }}</label>
                    <select name="payment_status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($paymentStatusOptions as $paymentStatusOption)
                            <option value="{{ $paymentStatusOption['code'] }}" @selected(($filters['payment_status'] ?? '') === $paymentStatusOption['code'])>
                                {{ $paymentStatusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label fw-semibold">{{ __('Payment method') }}</label>
                    <select name="payment_method" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($paymentMethodOptions as $paymentMethodOption)
                            <option value="{{ $paymentMethodOption['code'] }}" @selected(($filters['payment_method'] ?? '') === $paymentMethodOption['code'])>
                                {{ $paymentMethodOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label fw-semibold">{{ __('Delivery') }}</label>
                    <select name="delivery_price_id" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($deliveryPriceOptions as $deliveryPriceOption)
                            <option value="{{ $deliveryPriceOption['id'] }}" @selected((string) ($filters['delivery_price_id'] ?? '') === (string) $deliveryPriceOption['id'])>
                                {{ $deliveryPriceOption['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-1">
                    <label class="form-label fw-semibold">{{ __('From') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>

                <div class="col-6 col-md-3 col-xl-1">
                    <label class="form-label fw-semibold">{{ __('To') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>

                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2 pt-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-3-line align-bottom me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('admin.order.orders.index') }}" class="btn btn-light">
                            <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Reset') }}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
