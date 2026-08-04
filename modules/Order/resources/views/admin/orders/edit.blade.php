@extends('admin.layouts.app')

@php
    use Modules\Order\Enums\PaymentStatus;

    $orderStatusLabel = $order->status_name ?: ((string) $order->status ?: '-');
    $paymentStatusLabel = PaymentStatus::tryFrom((string) $order->payment_status)?->label() ?? (string) $order->payment_status;

    $selectedCountryId = old('address.country_id', $order->address->country_id ?? null);
    $selectedPaymentMethod = old('payment_method', $order->payment_method);
    $selectedDeliveryPriceId = old('delivery_price_id', $order->delivery_price_id);
    $payableTotal = ((float) $order->payment_initial_payment_snapshot > 0 || (float) $order->payment_installment_total_snapshot > 0)
        ? ((float) $order->payment_initial_payment_snapshot + (float) $order->payment_installment_total_snapshot)
        : (float) $order->total_snapshot;
@endphp

@section('title', __('Edit order') . ' #' . $order->number)

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body py-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary-subtle text-primary">{{ __('Order edit') }}</span>
                                        <span class="badge bg-light text-muted">#{{ $order->number }}</span>
                                        <span class="badge bg-success-subtle text-success">{{ $paymentMethodModel?->getDisplayName(app()->getLocale()) ?? ($order->payment_method ?: '-') }}</span>
                                    </div>
                                    <h4 class="mb-1">{{ __('Edit order') }}</h4>
                                    <p class="text-muted mb-0">{{ __('Manage core order data, line items, delivery, and address information.') }}</p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('admin.order.orders.show', $order) }}" class="btn btn-light">
                                        <i class="ri-eye-line align-bottom me-1"></i>{{ __('View') }}
                                    </a>
                                    <a href="{{ route('admin.order.orders.index', $order) }}" class="btn btn-light">
                                        <i class="ri-arrow-left-line align-bottom me-1"></i>{{ __('Back') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            @if(session('success'))
                <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger shadow-sm">
                    <div class="fw-semibold mb-2">{{ __('Please fix the following errors:') }}</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.order.orders.update', $order) }}" class="row g-4" id="orderEditForm">
                @csrf
                @method('PUT')

                <div class="col-xxl-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <h5 class="card-title mb-1">{{ __('Order configuration') }}</h5>
                                    <p class="text-muted mb-0">{{ __('Payment method, delivery, and note information.') }}</p>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-primary-subtle text-primary">{{ $orderStatusLabel }}</span>
                                    <span class="badge bg-warning-subtle text-warning">{{ $paymentStatusLabel }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Delivery price') }}</label>
                                    <select name="delivery_price_id" class="form-select @error('delivery_price_id') is-invalid @enderror" id="deliveryPriceSelect">
                                        <option value="">{{ __('Select delivery price') }}</option>
                                        @foreach($deliveryPrices as $deliveryPrice)
                                            <option
                                                value="{{ $deliveryPrice->id }}"
                                                data-price="{{ number_format((float) $deliveryPrice->price, 2, '.', '') }}"
                                                @selected((string) $selectedDeliveryPriceId === (string) $deliveryPrice->id)
                                            >
                                                {{ $deliveryPrice->name }} — {{ number_format((float) $deliveryPrice->price, 2) }} AZN
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('delivery_price_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Payment method') }}</label>
                                    <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                        <option value="">{{ __('Select payment method') }}</option>
                                        @foreach($paymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->key }}" @selected((string) $selectedPaymentMethod === (string) $paymentMethod->key)>
                                                {{ $paymentMethod->getDisplayName(app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Initial payment') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control @error('payment_initial_payment_snapshot') is-invalid @enderror"
                                        value="{{ number_format((float) $order->payment_initial_payment_snapshot, 2, '.', '') }}"
                                        disabled
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Installment month') }}</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        value="{{ $order->payment_installment_month ?: '-' }}"
                                        disabled
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Installment percent') }}</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        value="{{ $order->payment_installment_month ? number_format((float) $order->payment_installment_percent_snapshot, 2) . '%' : '-' }}"
                                        disabled
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Monthly amount') }}</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        value="{{ number_format((float) $order->payment_installment_monthly_snapshot, 2, '.', '') }}"
                                        disabled
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('Comment') }}</label>
                                    <textarea name="comment" rows="4" class="form-control @error('comment') is-invalid @enderror" placeholder="{{ __('Optional internal note or customer note') }}">{{ old('comment', $order->comment) }}</textarea>
                                    @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <h5 class="card-title mb-1">{{ __('Items') }}</h5>
                                    <p class="text-muted mb-0">{{ __('Update line items, change quantity, and mark items for removal.') }}</p>
                                </div>
                                <div class="badge bg-light text-muted align-self-start">{{ $order->items->count() }} {{ __('items') }}</div>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="orderItemsTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 320px;">{{ __('Product') }}</th>
                                        <th style="min-width: 180px;">{{ __('Variation') }}</th>
                                        <th class="text-center" style="min-width: 120px;">{{ __('Qty') }}</th>
                                        <th class="text-end" style="min-width: 140px;">{{ __('Original') }}</th>
                                        <th class="text-end" style="min-width: 140px;">{{ __('Unit') }}</th>
                                        <th class="text-end" style="min-width: 150px;">{{ __('Discount') }}</th>
                                        <th class="text-end" style="min-width: 150px;">{{ __('Total') }}</th>
                                        <th class="text-center" style="min-width: 120px;">{{ __('Remove') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($order->items as $index => $item)
                                        @php
                                            $removeValue = old("items.$index.remove", '0');
                                            $qtyValue = old("items.$index.qty", $item->qty);
                                            $originalValue = old("items.$index.original_unit_price_snapshot", number_format((float) $item->original_unit_price_snapshot, 2, '.', ''));
                                            $unitValue = old("items.$index.unit_price_snapshot", number_format((float) $item->unit_price_snapshot, 2, '.', ''));
                                            $variationValue = old("items.$index.variation_name_snapshot", $item->variation_name_snapshot);
                                            $isRemoved = (string) $removeValue === '1';
                                        @endphp
                                        <tr class="order-item-row {{ $isRemoved ? 'table-danger' : '' }}">
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                <input type="hidden" name="items[{{ $index }}][remove]" value="{{ $removeValue }}" class="item-remove-input">
                                                <div class="d-flex align-items-center">
                                                    @if($item->image_snapshot)
                                                        <div class="flex-shrink-0 me-3">
                                                            <img src="{{ $item->image_snapshot }}" alt="{{ $item->product_name_snapshot }}" class="rounded border" style="width:56px;height:56px;object-fit:cover;">
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $item->product_name_snapshot }}</div>
                                                        @if($item->sku)
                                                            <div class="text-muted small">{{ __('SKU') }}: {{ $item->sku }}</div>
                                                        @endif
                                                        @if($item->product_variation_id)
                                                            <div class="text-muted small">{{ __('Variation ID') }}: {{ $item->product_variation_id }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="items[{{ $index }}][variation_name_snapshot]"
                                                    value="{{ $variationValue }}"
                                                    class="form-control form-control-sm @error("items.$index.variation_name_snapshot") is-invalid @enderror"
                                                    placeholder="{{ __('Variation name') }}"
                                                >
                                                @error("items.$index.variation_name_snapshot")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-center">
                                                <div class="input-step justify-content-center">
                                                    <button type="button" class="minus item-qty-minus">–</button>
                                                    <input
                                                        type="number"
                                                        class="product-quantity item-qty-input @error("items.$index.qty") is-invalid @enderror"
                                                        name="items[{{ $index }}][qty]"
                                                        min="1"
                                                        value="{{ $qtyValue }}"
                                                    >
                                                    <button type="button" class="plus item-qty-plus">+</button>
                                                </div>
                                                @error("items.$index.qty")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-end">
                                                <div class="input-group input-group-sm">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="items[{{ $index }}][original_unit_price_snapshot]"
                                                        value="{{ $originalValue }}"
                                                        class="form-control text-end item-original-input @error("items.$index.original_unit_price_snapshot") is-invalid @enderror"
                                                    >
                                                    <span class="input-group-text">{{ $order->currency }}</span>
                                                </div>
                                                @error("items.$index.original_unit_price_snapshot")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-end">
                                                <div class="input-group input-group-sm">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="items[{{ $index }}][unit_price_snapshot]"
                                                        value="{{ $unitValue }}"
                                                        class="form-control text-end item-unit-input @error("items.$index.unit_price_snapshot") is-invalid @enderror"
                                                    >
                                                    <span class="input-group-text">{{ $order->currency }}</span>
                                                </div>
                                                @error("items.$index.unit_price_snapshot")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-semibold text-danger item-discount-display">
                                                    {{ number_format((float) $item->line_discount_hour_snapshot, 2) }} {{ $order->currency }}
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold item-total-display">
                                                    {{ number_format((float) $item->line_total_snapshot, 2) }} {{ $order->currency }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm {{ $isRemoved ? 'btn-danger' : 'btn-soft-danger' }} item-remove-toggle">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-light border mt-4 mb-0">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ __('Item editing') }}</div>
                                        <div class="text-muted">{{ __('Quantity and price changes automatically refresh the amount preview on the right side.') }}</div>
                                    </div>
                                    <div class="text-muted">
                                        {{ __('The remove button marks a row for deletion.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-1">{{ __('Shipping address') }}</h5>
                            <p class="text-muted mb-0">{{ __('All editable fields from the order_addresses table.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Type') }}</label>
                                    @php $typeValue = old('address.type', $order->address->type ?? 'shipping'); @endphp
                                    <select name="address[type]" class="form-select @error('address.type') is-invalid @enderror">
                                        <option value="shipping" @selected($typeValue === 'shipping')>{{ __('Shipping') }}</option>
                                        <option value="billing" @selected($typeValue === 'billing')>{{ __('Billing') }}</option>
                                    </select>
                                    @error('address.type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">{{ __('Label') }}</label>
                                    <input
                                        type="text"
                                        name="address[label]"
                                        class="form-control @error('address.label') is-invalid @enderror"
                                        value="{{ old('address.label', $order->address->label ?? '') }}"
                                        placeholder="{{ __('Home, office, warehouse...') }}"
                                    >
                                    @error('address.label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Name') }}</label>
                                    <input
                                        type="text"
                                        name="address[name]"
                                        class="form-control @error('address.name') is-invalid @enderror"
                                        value="{{ old('address.name', $order->address->name ?? '') }}"
                                    >
                                    @error('address.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Surname') }}</label>
                                    <input
                                        type="text"
                                        name="address[surname]"
                                        class="form-control @error('address.surname') is-invalid @enderror"
                                        value="{{ old('address.surname', $order->address->surname ?? '') }}"
                                    >
                                    @error('address.surname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('FIN') }}</label>
                                    <input
                                        type="text"
                                        name="address[passport_fin]"
                                        class="form-control @error('address.passport_fin') is-invalid @enderror"
                                        value="{{ old('address.passport_fin', $order->address->passport_fin ?? '') }}"
                                    >
                                    @error('address.passport_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Recipient name') }}</label>
                                    <input
                                        type="text"
                                        name="address[recipient_name]"
                                        class="form-control @error('address.recipient_name') is-invalid @enderror"
                                        value="{{ old('address.recipient_name', $order->address->recipient_name ?? '') }}"
                                    >
                                    @error('address.recipient_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Phone') }}</label>
                                    <input
                                        type="text"
                                        name="address[phone]"
                                        class="form-control @error('address.phone') is-invalid @enderror"
                                        value="{{ old('address.phone', $order->address->phone ?? '') }}"
                                    >
                                    @error('address.phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Country') }}</label>
                                    <select name="address[country_id]" class="form-select @error('address.country_id') is-invalid @enderror">
                                        <option value="">{{ __('Select country') }}</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" @selected((string) $selectedCountryId === (string) $country->id)>
                                                {{ $country->short_name ?: ('#' . $country->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('address.country_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Region') }}</label>
                                    <input
                                        type="text"
                                        name="address[region]"
                                        class="form-control @error('address.region') is-invalid @enderror"
                                        value="{{ old('address.region', $order->address->region ?? '') }}"
                                    >
                                    @error('address.region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('City') }}</label>
                                    <input
                                        type="text"
                                        name="address[city]"
                                        class="form-control @error('address.city') is-invalid @enderror"
                                        value="{{ old('address.city', $order->address->city ?? '') }}"
                                    >
                                    @error('address.city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Postal code') }}</label>
                                    <input
                                        type="text"
                                        name="address[postal_code]"
                                        class="form-control @error('address.postal_code') is-invalid @enderror"
                                        value="{{ old('address.postal_code', $order->address->postal_code ?? '') }}"
                                    >
                                    @error('address.postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('Address line 1') }}</label>
                                    <input
                                        type="text"
                                        name="address[address_line1]"
                                        class="form-control @error('address.address_line1') is-invalid @enderror"
                                        value="{{ old('address.address_line1', $order->address->address_line1 ?? '') }}"
                                    >
                                    @error('address.address_line1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('Address line 2') }}</label>
                                    <input
                                        type="text"
                                        name="address[address_line2]"
                                        class="form-control @error('address.address_line2') is-invalid @enderror"
                                        value="{{ old('address.address_line2', $order->address->address_line2 ?? '') }}"
                                    >
                                    @error('address.address_line2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Company') }}</label>
                                    <input
                                        type="text"
                                        name="address[company]"
                                        class="form-control @error('address.company') is-invalid @enderror"
                                        value="{{ old('address.company', $order->address->company ?? '') }}"
                                    >
                                    @error('address.company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Note') }}</label>
                                    <input
                                        type="text"
                                        name="address[note]"
                                        class="form-control @error('address.note') is-invalid @enderror"
                                        value="{{ old('address.note', $order->address->note ?? '') }}"
                                    >
                                    @error('address.note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="ri-save-line align-bottom me-1"></i>{{ __('Save changes') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-1">{{ __('Current statuses') }}</h5>
                            <p class="text-muted mb-0">{{ __('Current state and time information.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Order status') }}</span>
                                <span class="fw-semibold">{{ $orderStatusLabel }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Payment status') }}</span>
                                <span class="fw-semibold">{{ $paymentStatusLabel }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Payment method') }}</span>
                                <span class="fw-semibold">{{ $paymentMethodModel?->getDisplayName(app()->getLocale()) ?? ($order->payment_method ?: '-') }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Placed at') }}</span>
                                <span class="fw-semibold">{{ $order->placed_at?->format('Y-m-d H:i') ?: '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">{{ __('Paid at') }}</span>
                                <span class="fw-semibold">{{ $order->paid_at?->format('Y-m-d H:i') ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-1">{{ __('Totals preview') }}</h5>
                            <p class="text-muted mb-0">{{ __('Live preview based on product and delivery changes.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Subtotal') }}</span>
                                <span id="summarySubtotal">{{ number_format((float) $order->subtotal_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Discount hour') }}</span>
                                <span class="text-danger" id="summaryDiscount">-{{ number_format((float) $order->discount_hour_discount_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Promo') }}</span>
                                <span class="text-danger">-{{ number_format((float) $order->promo_discount_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Delivery') }}</span>
                                <span id="summaryDelivery">{{ number_format((float) $order->delivery_price_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Initial payment') }}</span>
                                <span>{{ number_format((float) $order->payment_initial_payment_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Monthly amount') }}</span>
                                <span>{{ number_format((float) $order->payment_installment_monthly_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="fw-semibold">{{ __('Payable total') }}</span>
                                <span class="fw-bold fs-5" id="summaryTotal">{{ number_format((float) $payableTotal, 2) }} {{ $order->currency }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="card-title mb-1">{{ __('Latest payment record') }}</h5>
                            <p class="text-muted mb-0">{{ __('Latest payment log information.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            @if($latestPayment)
                                @php
                                    $latestPaymentStatusLabel = PaymentStatus::tryFrom((string) $latestPayment->status)?->label() ?? (string) $latestPayment->status;
                                @endphp
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Provider') }}</span>
                                    <span class="fw-semibold">{{ $latestPayment->method_code }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Status') }}</span>
                                    <span class="fw-semibold">{{ $latestPaymentStatusLabel }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Reference') }}</span>
                                    <span class="fw-semibold">{{ $latestPayment->provider_reference ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Payment ID') }}</span>
                                    <span class="fw-semibold">{{ $latestPayment->provider_payment_id ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Initial payment') }}</span>
                                    <span class="fw-semibold">{{ number_format((float) $latestPayment->payment_initial_payment_snapshot, 2) }} {{ $latestPayment->currency }}</span>
                                </div>
                                @if((int) $latestPayment->payment_installment_month > 0)
                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">{{ __('Installment month') }}</span>
                                        <span class="fw-semibold">{{ $latestPayment->payment_installment_month }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">{{ __('Monthly amount') }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $latestPayment->payment_installment_monthly_snapshot, 2) }} {{ $latestPayment->currency }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between py-2">
                                    <span class="text-muted">{{ __('Paid at') }}</span>
                                    <span class="fw-semibold">{{ $latestPayment->paid_at?->format('Y-m-d H:i') ?: '-' }}</span>
                                </div>
                            @else
                                <div class="text-muted">{{ __('No payment records') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const currency = @json($order->currency);
            const promoDiscount = Number(@json((float) $order->promo_discount_snapshot));
            const initialPayment = Number(@json((float) $order->payment_initial_payment_snapshot));
            const installmentTotal = Number(@json((float) $order->payment_installment_total_snapshot));
            const table = document.getElementById('orderItemsTable');
            const deliverySelect = document.getElementById('deliveryPriceSelect');

            if (!table) {
                return;
            }

            const formatMoney = function (value) {
                return `${Number(value || 0).toFixed(2)} ${currency}`;
            };

            const getRowData = function (row) {
                const qtyInput = row.querySelector('.item-qty-input');
                const originalInput = row.querySelector('.item-original-input');
                const unitInput = row.querySelector('.item-unit-input');
                const removeInput = row.querySelector('.item-remove-input');

                const isRemoved = removeInput && removeInput.value === '1';

                if (isRemoved) {
                    return {
                        subtotal: 0,
                        discount: 0,
                        total: 0,
                    };
                }

                const qty = Math.max(1, Number(qtyInput?.value || 1));
                const original = Math.max(0, Number(originalInput?.value || 0));
                const unit = Math.max(0, Number(unitInput?.value || 0));

                const subtotal = original * qty;
                const total = unit * qty;
                const discount = Math.max(0, subtotal - total);

                return {
                    subtotal,
                    discount,
                    total,
                };
            };

            const refreshRow = function (row) {
                const data = getRowData(row);
                const discountDisplay = row.querySelector('.item-discount-display');
                const totalDisplay = row.querySelector('.item-total-display');

                if (discountDisplay) {
                    discountDisplay.textContent = formatMoney(data.discount);
                }

                if (totalDisplay) {
                    totalDisplay.textContent = formatMoney(data.total);
                }
            };

            const refreshSummary = function () {
                let subtotal = 0;
                let discount = 0;

                table.querySelectorAll('.order-item-row').forEach(function (row) {
                    const rowData = getRowData(row);
                    subtotal += rowData.subtotal;
                    discount += rowData.discount;
                    refreshRow(row);
                });

                let delivery = 0;

                if (deliverySelect) {
                    const option = deliverySelect.options[deliverySelect.selectedIndex];
                    delivery = Number(option?.dataset?.price || 0);
                }

                const recalculatedBaseTotal = Math.max(0, subtotal - discount - promoDiscount + delivery);
                const total = initialPayment > 0 || installmentTotal > 0
                    ? initialPayment + installmentTotal
                    : recalculatedBaseTotal;

                const subtotalElement = document.getElementById('summarySubtotal');
                const discountElement = document.getElementById('summaryDiscount');
                const deliveryElement = document.getElementById('summaryDelivery');
                const totalElement = document.getElementById('summaryTotal');

                if (subtotalElement) {
                    subtotalElement.textContent = formatMoney(subtotal);
                }

                if (discountElement) {
                    discountElement.textContent = `-${formatMoney(discount)}`;
                }

                if (deliveryElement) {
                    deliveryElement.textContent = formatMoney(delivery);
                }

                if (totalElement) {
                    totalElement.textContent = formatMoney(total);
                }
            };

            document.addEventListener('click', function (event) {
                const minusButton = event.target.closest('.item-qty-minus');
                const plusButton = event.target.closest('.item-qty-plus');
                const removeButton = event.target.closest('.item-remove-toggle');

                if (minusButton) {
                    const row = minusButton.closest('.order-item-row');
                    const input = row.querySelector('.item-qty-input');
                    const current = Math.max(1, Number(input.value || 1));
                    input.value = current > 1 ? current - 1 : 1;
                    refreshSummary();
                    return;
                }

                if (plusButton) {
                    const row = plusButton.closest('.order-item-row');
                    const input = row.querySelector('.item-qty-input');
                    const current = Math.max(1, Number(input.value || 1));
                    input.value = current + 1;
                    refreshSummary();
                    return;
                }

                if (removeButton) {
                    const row = removeButton.closest('.order-item-row');
                    const input = row.querySelector('.item-remove-input');
                    const isRemoved = input.value === '1';

                    input.value = isRemoved ? '0' : '1';
                    row.classList.toggle('table-danger', !isRemoved);
                    removeButton.classList.toggle('btn-danger', !isRemoved);
                    removeButton.classList.toggle('btn-soft-danger', isRemoved);

                    refreshSummary();
                }
            });

            document.addEventListener('input', function (event) {
                if (
                    event.target.closest('.item-qty-input') ||
                    event.target.closest('.item-original-input') ||
                    event.target.closest('.item-unit-input')
                ) {
                    refreshSummary();
                }
            });

            if (deliverySelect) {
                deliverySelect.addEventListener('change', refreshSummary);
            }

            refreshSummary();
        })();
    </script>
@endpush
