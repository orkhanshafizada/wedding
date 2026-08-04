@extends('admin.layouts.app')

@php
    use Modules\Order\Enums\PaymentStatus;

    $orderStatusLabel = $order->status_name ?: ((string) $order->status ?: '-');
    $paymentStatusLabel = PaymentStatus::tryFrom((string) $order->payment_status)?->label() ?? (string) $order->payment_status;
    $paymentMethodLabel = $paymentMethodModel?->getDisplayName(app()->getLocale()) ?? ($order->payment_method ?: '-');
    $countryName = $order->address?->country?->short_name;
    $isDirectBuy = $order->cart_id === null;
    $isGuestOrder = $order->customer_id === null;
    $payableTotal = ((float) $order->payment_initial_payment_snapshot > 0 || (float) $order->payment_installment_total_snapshot > 0)
        ? ((float) $order->payment_initial_payment_snapshot + (float) $order->payment_installment_total_snapshot)
        : (float) $order->total_snapshot;
@endphp

@section('title', __('Order') . ' #' . $order->number)

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-0">
                            <div class="p-4 p-lg-5 bg-primary bg-gradient text-white">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                                            <span class="badge bg-white text-primary">{{ __('Order detail') }}</span>
                                            <span class="badge bg-primary-subtle text-white border border-white border-opacity-25">#{{ $order->number }}</span>
                                            @if((int) $order->payment_installment_month > 0)
                                                <span class="badge bg-info text-white">
                                                    {{ $order->payment_installment_month }} {{ __('months') }} / {{ number_format((float) $order->payment_installment_percent_snapshot, 2) }}%
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="mb-2 text-white">{{ __('Order') }} #{{ $order->number }}</h3>
                                        <p class="mb-0 text-white text-opacity-75">
                                            {{ __('Order snapshot totals, line items, address, status transitions, and payment/installment details.') }}
                                        </p>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                                            @can('order.edit')
                                                <a href="{{ route('admin.order.orders.edit', $order) }}" class="btn btn-light">
                                                    <i class="ri-pencil-line align-bottom me-1"></i>{{ __('Edit') }}
                                                </a>
                                            @endcan
                                            <a href="{{ route('admin.order.orders.index') }}" class="btn btn-outline-light">
                                                <i class="ri-arrow-left-line align-bottom me-1"></i>{{ __('Back') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-light-subtle border-top">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Order status') }}</div>
                                            <div class="fs-5 fw-bold">{{ $orderStatusLabel }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Payment status') }}</div>
                                            <div class="fs-5 fw-bold">{{ $paymentStatusLabel }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Payment method') }}</div>
                                            <div class="fs-5 fw-bold">{{ $paymentMethodLabel }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Payable total') }}</div>
                                            <div class="fs-5 fw-bold">{{ number_format((float) $payableTotal, 2) }} {{ $order->currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Order type') }}</div>
                                            <div class="fs-5 fw-bold">{{ $isDirectBuy ? __('Buy now') : __('Cart checkout') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Customer type') }}</div>
                                            <div class="fs-5 fw-bold">{{ $isGuestOrder ? __('Guest') : __('Registered') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Initial payment') }}</div>
                                            <div class="fs-5 fw-bold">{{ number_format((float) $order->payment_initial_payment_snapshot, 2) }} {{ $order->currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Monthly amount') }}</div>
                                            <div class="fs-5 fw-bold">{{ number_format((float) $order->payment_installment_monthly_snapshot, 2) }} {{ $order->currency }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="card-title mb-1">{{ __('Items') }}</h5>
                                    <p class="text-muted mb-0">{{ __('All order line items and price snapshots.') }}</p>
                                </div>
                                <span class="badge bg-light text-muted">{{ $order->items->count() }} {{ __('lines') }}</span>
                            </div>
                        </div>
                        <div class="card-body pt-4 px-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Product') }}</th>
                                        <th>{{ __('SKU') }}</th>
                                        <th class="text-center">{{ __('Qty') }}</th>
                                        <th class="text-end">{{ __('Original') }}</th>
                                        <th class="text-end">{{ __('Unit') }}</th>
                                        <th class="text-end">{{ __('Discount') }}</th>
                                        <th class="text-end pe-4">{{ __('Total') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    @if($item->image_snapshot)
                                                        <div class="flex-shrink-0 me-3">
                                                            <img src="{{ $item->image_snapshot }}" alt="{{ $item->product_name_snapshot }}" class="rounded border" style="width:56px;height:56px;object-fit:cover;">
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $item->product_name_snapshot }}</div>
                                                        @if($item->variation_name_snapshot)
                                                            <div class="text-muted small">{{ $item->variation_name_snapshot }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item->sku ?: '-' }}</td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-end">{{ number_format((float) $item->original_unit_price_snapshot, 2) }}</td>
                                            <td class="text-end">{{ number_format((float) $item->unit_price_snapshot, 2) }}</td>
                                            <td class="text-end text-danger">-{{ number_format((float) $item->line_discount_hour_snapshot, 2) }}</td>
                                            <td class="text-end pe-4 fw-semibold">{{ number_format((float) $item->line_total_snapshot, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                            <h5 class="card-title mb-1">{{ __('Status history') }}</h5>
                            <p class="text-muted mb-0">{{ __('All status transitions and internal notes.') }}</p>
                        </div>
                        <div class="card-body pt-4 px-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">{{ __('From') }}</th>
                                        <th>{{ __('To') }}</th>
                                        <th>{{ __('By') }}</th>
                                        <th>{{ __('Note') }}</th>
                                        <th class="pe-4">{{ __('Date') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($order->statusHistories as $history)
                                        @php
                                            $fromLabel = $history->fromOrderStatus?->name ?: ($history->from_status ?: '-');
                                            $toLabel = $history->toOrderStatus?->name ?: ($history->to_status ?: '-');
                                        @endphp
                                        <tr>
                                            <td class="ps-4">{{ $fromLabel }}</td>
                                            <td>{{ $toLabel }}</td>
                                            <td>{{ $history->changedBy?->name ?: ($history->changed_by ?: '-') }}</td>
                                            <td>{{ $history->note ?: '-' }}</td>
                                            <td class="pe-4">{{ $history->created_at?->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">{{ __('No status history') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                            <h5 class="card-title mb-1">{{ __('Financial summary') }}</h5>
                            <p class="text-muted mb-0">{{ __('Main amount breakdown for the order.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Subtotal') }}</span>
                                <span>{{ number_format((float) $order->subtotal_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Discount hour') }}</span>
                                <span class="text-danger">-{{ number_format((float) $order->discount_hour_discount_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Promo discount') }}</span>
                                <span class="text-danger">-{{ number_format((float) $order->promo_discount_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Delivery') }}</span>
                                <span>{{ number_format((float) $order->delivery_price_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Base total') }}</span>
                                <span>{{ number_format((float) $order->total_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Initial payment') }}</span>
                                <span>{{ number_format((float) $order->payment_initial_payment_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ __('Remaining installment total') }}</span>
                                <span>{{ number_format((float) $order->payment_installment_total_snapshot, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="fw-semibold">{{ __('Payable total') }}</span>
                                <span class="fw-bold fs-5">{{ number_format((float) $payableTotal, 2) }} {{ $order->currency }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                            <h5 class="card-title mb-1">{{ __('Installment details') }}</h5>
                            <p class="text-muted mb-0">{{ __('Installment plan snapshot information.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            @if((int) $order->payment_installment_month > 0)
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Month count') }}</span>
                                    <span class="fw-semibold">{{ $order->payment_installment_month }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Percent') }}</span>
                                    <span class="fw-semibold">{{ number_format((float) $order->payment_installment_percent_snapshot, 2) }}%</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Initial payment') }}</span>
                                    <span class="fw-semibold">{{ number_format((float) $order->payment_initial_payment_snapshot, 2) }} {{ $order->currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Interest amount') }}</span>
                                    <span class="fw-semibold">{{ number_format((float) $order->payment_installment_interest_snapshot, 2) }} {{ $order->currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">{{ __('Remaining installment total') }}</span>
                                    <span class="fw-semibold">{{ number_format((float) $order->payment_installment_total_snapshot, 2) }} {{ $order->currency }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <span class="text-muted">{{ __('Monthly payment') }}</span>
                                    <span class="fw-bold">{{ number_format((float) $order->payment_installment_monthly_snapshot, 2) }} {{ $order->currency }}</span>
                                </div>
                            @else
                                <div class="text-muted">{{ __('No installment plan was selected for this order.') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                            <h5 class="card-title mb-1">{{ __('Customer & address') }}</h5>
                            <p class="text-muted mb-0">{{ __('Customer and delivery snapshot information.') }}</p>
                        </div>
                        <div class="card-body pt-4">
                            <div class="mb-3">
                                <div class="text-muted small">{{ __('Customer') }}</div>
                                <div class="fw-semibold">
                                    {{ $order->customer ? trim((string) $order->customer->name . ' ' . (string) $order->customer->surname) : __('Guest order') }}
                                </div>
                                <div class="text-muted">
                                    {{ $isDirectBuy ? __('Buy now') : __('Cart checkout') }} • {{ $isGuestOrder ? __('Guest') : __('Registered') }}
                                </div>
                                @if($order->customer)
                                    <div class="text-muted">{{ $order->customer->phone ?: ($order->customer->email ?: '-') }}</div>
                                @else
                                    <div class="text-muted">{{ $order->guest_token ?: '-' }}</div>
                                @endif
                            </div>

                            @if($order->address)
                                <div class="rounded-4 border p-3">
                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">{{ __('Type') }}</span>
                                        <span class="fw-semibold">{{ $order->address->type ?: '-' }}</span>
                                    </div>
                                    @if($order->address->label)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Label') }}</span>
                                            <span class="fw-semibold">{{ $order->address->label ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->name)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Name') }}</span>
                                            <span class="fw-semibold">{{ $order->address->name ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->surname)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Surname') }}</span>
                                            <span class="fw-semibold">{{ $order->address->surname ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->passport_fin)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('FIN') }}</span>
                                            <span class="fw-semibold">{{ $order->address->passport_fin ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->recipient_name)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Recipient name') }}</span>
                                            <span class="fw-semibold">{{ $order->address->recipient_name ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->phone)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Phone') }}</span>
                                            <span class="fw-semibold">{{ $order->address->phone ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($countryName)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Country name') }}</span>
                                            <span class="fw-semibold">{{ $countryName ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->city || $order->address->region)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Region') }}</span>
                                            <span class="fw-semibold">{{ $order->address->city }}{{ $order->address->region ? ', ' . $order->address->region : '' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->address_line1)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Address line 1') }}</span>
                                            <span class="fw-semibold">{{ $order->address->address_line1 ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->address_line2)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Address line 2') }}</span>
                                            <span class="fw-semibold">{{ $order->address->address_line2 ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->postal_code)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Postal code') }}</span>
                                            <span class="fw-semibold">{{ $order->address->postal_code ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->company)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Company') }}</span>
                                            <span class="fw-semibold">{{ $order->address->company ?: '-' }}</span>
                                        </div>
                                    @endif
                                    @if($order->address->note)
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">{{ __('Note') }}</span>
                                            <span class="fw-semibold">{{ $order->address->note ?: '-' }}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted">{{ __('No address') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0 pt-4 px-4">
                            <h5 class="card-title mb-1">{{ __('Payment details') }}</h5>
                            <p class="text-muted mb-0">{{ __('Latest payment operation and refund section.') }}</p>
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
                                    <span class="text-muted">{{ __('Amount') }}</span>
                                    <span class="fw-semibold">{{ number_format((float) $latestPayment->amount, 2) }} {{ $latestPayment->currency }}</span>
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
                                        <span class="text-muted">{{ __('Payment installment') }}</span>
                                        <span class="fw-semibold">{{ $latestPayment->payment_installment_month }} {{ __('months') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">{{ __('Installment percent') }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $latestPayment->payment_installment_percent_snapshot, 2) }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">{{ __('Installment interest') }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $latestPayment->payment_installment_interest_snapshot, 2) }} {{ $latestPayment->currency }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">{{ __('Installment total') }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $latestPayment->payment_installment_total_snapshot, 2) }} {{ $latestPayment->currency }}</span>
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

                                @if($latestPayment->relationLoaded('events') && $latestPayment->events->isNotEmpty())
                                    <hr>
                                    <div>
                                        <div class="fw-semibold mb-3">{{ __('Payment events') }}</div>
                                        @foreach($latestPayment->events->sortByDesc('id') as $event)
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted">{{ $event->event_type }}</span>
                                                <span class="fw-semibold">{{ $event->occurred_at?->format('Y-m-d H:i') ?: '-' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="text-muted">{{ __('No payment records') }}</div>
                            @endif

                            @can('order.refund')
                                <hr>
                                <form method="POST" action="{{ route('admin.order.orders.refund', $order) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Refund note') }}</label>
                                        <input type="text" name="note" class="form-control" placeholder="{{ __('Optional') }}">
                                    </div>
                                    <button
                                        class="btn btn-outline-danger w-100"
                                        type="submit"
                                        @disabled((string) $order->payment_status !== \Modules\Order\Enums\PaymentStatus::PAID->value)
                                    >
                                        <i class="ri-refund-2-line align-bottom me-1"></i>{{ __('Request refund') }}
                                    </button>
                                    @if((string) $order->payment_status !== \Modules\Order\Enums\PaymentStatus::PAID->value)
                                        <small class="text-muted d-block mt-2">{{ __('Refund is only available when the payment status is paid.') }}</small>
                                    @endif
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
