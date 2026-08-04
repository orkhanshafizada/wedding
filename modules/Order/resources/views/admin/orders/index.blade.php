@extends('admin.layouts.app')

@section('title', __('Orders'))

@php
    $totalRevenue = $orders->sum(fn ($order) => (float) $order->total_snapshot);
    $paidOrdersCount = $orders->filter(fn ($order) => (string) $order->payment_status === \Modules\Order\Enums\PaymentStatus::PAID->value)->count();
    $guestOrdersCount = $orders->filter(fn ($order) => $order->customer === null)->count();
@endphp

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
                                            <span class="badge bg-white text-primary">{{ __('Order management') }}</span>
                                            <span class="badge bg-primary-subtle text-white border border-white border-opacity-25">{{ $orders->total() }} {{ __('records') }}</span>
                                        </div>
                                        <h3 class="mb-2 text-white">{{ __('Orders') }}</h3>
                                        <p class="mb-0 text-white text-opacity-75">
                                            {{ __('Manage orders, payment flow, installment plans, and delivery snapshots from a single panel.') }}
                                        </p>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                                            <a href="{{ route('admin.order.delivery_prices.index') }}" class="btn btn-light">
                                                <i class="ri-truck-line align-bottom me-1"></i>{{ __('Delivery prices') }}
                                            </a>
                                            <a href="{{ route('admin.order.payment_methods.index') }}" class="btn btn-light">
                                                <i class="ri-bank-card-line align-bottom me-1"></i>{{ __('Payment methods') }}
                                            </a>
                                            <a href="{{ route('admin.order.promo_codes.index') }}" class="btn btn-light">
                                                <i class="ri-coupon-3-line align-bottom me-1"></i>{{ __('Promo codes') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-light-subtle border-top">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Visible orders') }}</div>
                                            <div class="fs-3 fw-bold">{{ $orders->count() }}</div>
                                            <div class="text-muted">{{ __('Current page result set') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Paid orders') }}</div>
                                            <div class="fs-3 fw-bold">{{ $paidOrdersCount }}</div>
                                            <div class="text-muted">{{ __('Current page paid count') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rounded-4 border bg-white p-3 h-100">
                                            <div class="text-muted text-uppercase fw-semibold fs-12 mb-2">{{ __('Current page turnover') }}</div>
                                            <div class="fs-3 fw-bold">{{ number_format($totalRevenue, 2) }} AZN</div>
                                            <div class="text-muted">{{ __('Guest orders') }}: {{ $guestOrdersCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('order::admin.orders.partials.filters')

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @include('order::admin.orders.partials.table')
                </div>
            </div>

        </div>
    </div>

    @include('order::admin.orders.partials.quick-view-modal')
    @include('order::admin.orders.partials.status-modal')
@endsection

@push('scripts')
    <script>
        (function () {
            const modalEl = document.getElementById('orderQuickViewModal');
            const statusModalEl = document.getElementById('orderStatusModal');
            const quickViewModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const statusModal = bootstrap.Modal.getOrCreateInstance(statusModalEl);

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatMoney(value, currency) {
                const numericValue = Number(value ?? 0);
                return `${numericValue.toFixed(2)} ${currency ?? ''}`.trim();
            }

            function formatDate(value) {
                if (!value) {
                    return '-';
                }

                return value;
            }

            function formatAddress(address) {
                if (!address) {
                    return '-';
                }

                const lines = [
                    [address.name, address.surname, address.passport_fin].filter(Boolean).join(' • '),
                    [address.recipient_name, address.phone].filter(Boolean).join(' • '),
                    [address.country, address.city, address.region].filter(Boolean).join(', '),
                    [address.address_line1, address.address_line2].filter(Boolean).join(', '),
                    [address.postal_code, address.company].filter(Boolean).join(' • '),
                    address.note || ''
                ].filter(Boolean);

                return lines.length ? lines.join('<br>') : '-';
            }

            async function fetchJson(url) {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                return await response.json();
            }

            function setQuickViewLoadingState() {
                modalEl.querySelector('[data-qv="number"]').textContent = '...';
                modalEl.querySelector('[data-qv="status"]').textContent = '...';
                modalEl.querySelector('[data-qv="payment_status"]').textContent = '...';
                modalEl.querySelector('[data-qv="payment"]').textContent = '...';
                modalEl.querySelector('[data-qv="customer"]').textContent = '...';
                modalEl.querySelector('[data-qv="address"]').innerHTML = '...';
                modalEl.querySelector('[data-qv="latest_payment"]').textContent = '...';
                modalEl.querySelector('[data-qv="installment"]').textContent = '...';
                modalEl.querySelector('[data-qv="placed_at"]').textContent = '...';
                modalEl.querySelector('[data-qv="paid_at"]').textContent = '...';
                modalEl.querySelector('[data-qv="subtotal"]').textContent = '...';
                modalEl.querySelector('[data-qv="discount"]').textContent = '...';
                modalEl.querySelector('[data-qv="promo"]').textContent = '...';
                modalEl.querySelector('[data-qv="delivery"]').textContent = '...';
                modalEl.querySelector('[data-qv="total"]').textContent = '...';
                modalEl.querySelector('[data-qv="items"]').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">{{ __('Loading...') }}</td>
                    </tr>
                `;
            }

            async function openQuickView(orderUuid) {
                setQuickViewLoadingState();
                quickViewModal.show();

                try {
                    const url = "{{ route('admin.order.ajax.orders.quick_view', ['order' => '__ORDER__']) }}".replace('__ORDER__', orderUuid);
                    const response = await fetchJson(url);
                    const data = response.data || {};
                    const currency = data.currency || 'AZN';
                    const totals = data.totals || {};
                    const installment = data.installment || {};
                    const payableTotal = Number(totals.payable_total ?? totals.total ?? 0);

                    modalEl.querySelector('[data-qv="number"]').textContent = data.number || '-';
                    modalEl.querySelector('[data-qv="status"]').textContent = data.status_label || data.status || '-';
                    modalEl.querySelector('[data-qv="payment_status"]').textContent = data.payment_status_label || data.payment_status || '-';
                    modalEl.querySelector('[data-qv="payment"]').textContent = data.payment_method_label || data.payment_method || '-';
                    modalEl.querySelector('[data-qv="placed_at"]').textContent = formatDate(data.placed_at);
                    modalEl.querySelector('[data-qv="paid_at"]').textContent = formatDate(data.paid_at);

                    modalEl.querySelector('[data-qv="subtotal"]').textContent = formatMoney(totals.subtotal, currency);
                    modalEl.querySelector('[data-qv="discount"]').textContent = `-${formatMoney(totals.discount_hour_discount, currency)}`;
                    modalEl.querySelector('[data-qv="promo"]').textContent = `-${formatMoney(totals.promo_discount, currency)}`;
                    modalEl.querySelector('[data-qv="delivery"]').textContent = formatMoney(totals.delivery_price, currency);
                    modalEl.querySelector('[data-qv="total"]').textContent = formatMoney(payableTotal, currency);

                    const customerBlock = modalEl.querySelector('[data-qv="customer"]');
                    if (data.customer) {
                        customerBlock.textContent = [
                            data.customer.name,
                            data.customer.phone,
                            data.customer.email
                        ].filter(Boolean).join(' • ');
                    } else {
                        customerBlock.textContent = [
                            '{{ __('Guest order') }}',
                            data.is_direct_buy ? '{{ __('Buy now') }}' : null,
                            data.guest_token
                        ].filter(Boolean).join(' • ');
                    }

                    modalEl.querySelector('[data-qv="address"]').innerHTML = formatAddress(data.address);

                    if (installment && Number(installment.month || 0) > 0) {
                        modalEl.querySelector('[data-qv="installment"]').textContent =
                            `${installment.month} {{ __('months') }} • ${Number(installment.percent || 0).toFixed(2)}% • {{ __('Initial') }}: ${formatMoney(installment.initial_payment, currency)} • {{ __('Monthly') }}: ${formatMoney(installment.monthly, currency)}`;
                    } else {
                        modalEl.querySelector('[data-qv="installment"]').textContent = '{{ __('Standard payment') }}';
                    }

                    const latestPaymentBlock = modalEl.querySelector('[data-qv="latest_payment"]');
                    if (data.latest_payment) {
                        latestPaymentBlock.textContent = [
                            data.latest_payment.method_label || data.latest_payment.method_code,
                            data.latest_payment.status_label || data.latest_payment.status,
                            data.latest_payment.provider_reference,
                            Number(data.latest_payment.payment_initial_payment_snapshot || 0) > 0 ? `{{ __('Initial') }}: ${formatMoney(data.latest_payment.payment_initial_payment_snapshot, currency)}` : null,
                            Number(data.latest_payment.payment_installment_monthly_snapshot || 0) > 0 ? `{{ __('Monthly') }}: ${formatMoney(data.latest_payment.payment_installment_monthly_snapshot, currency)}` : null,
                            data.latest_payment.paid_at
                        ].filter(Boolean).join(' • ');
                    } else {
                        latestPaymentBlock.textContent = '-';
                    }

                    const itemsTbody = modalEl.querySelector('[data-qv="items"]');
                    itemsTbody.innerHTML = '';

                    (data.items || []).forEach(function (item) {
                        const imageHtml = item.image
                            ? `<div class="me-3 flex-shrink-0"><img src="${escapeHtml(item.image)}" alt="" class="rounded border" style="width:48px;height:48px;object-fit:cover;"></div>`
                            : '';

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    ${imageHtml}
                                    <div>
                                        <div class="fw-semibold">${escapeHtml(item.product_name || '')}</div>
                                        <div class="text-muted small">${escapeHtml(item.variation_name || '')}</div>
                                        <div class="text-muted small">${escapeHtml(item.sku || '')}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">${escapeHtml(item.qty ?? '')}</td>
                            <td class="text-end">${escapeHtml(Number(item.original_unit_price ?? 0).toFixed(2))}</td>
                            <td class="text-end">${escapeHtml(Number(item.unit_price ?? 0).toFixed(2))}</td>
                            <td class="text-end text-danger">-${escapeHtml(Number(item.discount ?? 0).toFixed(2))}</td>
                            <td class="text-end fw-semibold">${escapeHtml(Number(item.line_total ?? 0).toFixed(2))}</td>
                        `;
                        itemsTbody.appendChild(tr);
                    });

                    if (!itemsTbody.children.length) {
                        itemsTbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">{{ __('No items found') }}</td>
                            </tr>
                        `;
                    }

                    modalEl.querySelector('[data-qv="show_link"]').setAttribute(
                        'href',
                        "{{ route('admin.order.orders.show', ['order' => '__ORDER__']) }}".replace('__ORDER__', orderUuid)
                    );

                    modalEl.querySelector('[data-qv="edit_link"]').setAttribute(
                        'href',
                        "{{ route('admin.order.orders.edit', ['order' => '__ORDER__']) }}".replace('__ORDER__', orderUuid)
                    );
                } catch (error) {
                    modalEl.querySelector('[data-qv="items"]').innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4 text-danger">{{ __('Quick view data could not be loaded.') }}</td>
                        </tr>
                    `;
                }
            }

            function openStatusModal(orderUuid, currentStatus, currentPaymentStatus, currentOrderStatusId) {
                statusModalEl.querySelector('form').setAttribute(
                    'action',
                    "{{ route('admin.order.orders.status', ['order' => '__ORDER__']) }}".replace('__ORDER__', orderUuid)
                );

                const statusField = statusModalEl.querySelector('[name="to_status"]');

                if (statusField) {
                    statusField.value = currentOrderStatusId || currentStatus || '';
                }

                statusModalEl.querySelector('[name="payment_status"]').value = currentPaymentStatus || '';
                statusModalEl.querySelector('[name="note"]').value = '';
                statusModal.show();
            }

            document.addEventListener('click', function (event) {
                const quickViewButton = event.target.closest('[data-action="quick-view"]');
                if (quickViewButton) {
                    event.preventDefault();
                    openQuickView(quickViewButton.getAttribute('data-order'));
                    return;
                }

                const statusButton = event.target.closest('[data-action="status-modal"]');
                if (statusButton) {
                    event.preventDefault();
                    openStatusModal(
                        statusButton.getAttribute('data-order'),
                        statusButton.getAttribute('data-status'),
                        statusButton.getAttribute('data-payment-status'),
                        statusButton.getAttribute('data-order-status-id')
                    );
                }
            });
        })();
    </script>
@endpush
