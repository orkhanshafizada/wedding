@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Sifarişlərin transferi</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.transfer.index') }}">Transfer</a></li>
                                <li class="breadcrumb-item active">Sifarişlər</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-3 align-middle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Tapılan sifariş sayı</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-primary fs-14 mb-0">OpenCart</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['count'] }}</h4>
                                    <span class="text-muted">oc_order</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-shopping-cart-2-line text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Online ödəniş</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-success fs-14 mb-0">Kapital Bank</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['online_count'] }}</h4>
                                    <span class="text-muted">online payment aşkarlanan sifarişlər</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-bank-card-line text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Məhsul sətrləri</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-info fs-14 mb-0">oc_order_product</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['item_count'] }}</h4>
                                    <span class="text-muted">order_items üçün source sətirlər</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-shopping-bag-3-line text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">Number formatı</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-warning fs-14 mb-0">Target</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">OC-{id}</h4>
                                    <span class="text-muted">orders.number unique formatı</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-price-tag-3-line text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Transfer əməliyyatı</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-border-left mb-4" role="alert">
                                Sifarişlər <strong>orders</strong>, məhsullar <strong>order_items</strong>, status keçidləri <strong>order_status_histories</strong>, ünvan <strong>order_addresses</strong>, ödəniş isə <strong>order_payments</strong> cədvəllərinə yazılacaq. Mövcud sifariş tapılarsa number üzrə update və tam resync olunacaq.
                            </div>

                            <form action="{{ route('admin.transfer.orders.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>
                                    Transferə başla
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1">Preview</h4>
                                <span class="badge bg-primary-subtle text-primary">{{ $preview['count'] }} qeyd</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Number</th>
                                        <th>Email</th>
                                        <th>Məbləğ</th>
                                        <th>Valyuta</th>
                                        <th>Status</th>
                                        <th>Payment status</th>
                                        <th>Payment method</th>
                                        <th>Tarix</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['orders'] as $order)
                                        <tr>
                                            <td>{{ $order['order_id'] }}</td>
                                            <td>{{ $order['number'] }}</td>
                                            <td>{{ $order['customer_email'] }}</td>
                                            <td>{{ number_format($order['total'], 2, '.', '') }}</td>
                                            <td>{{ $order['currency'] }}</td>
                                            <td><span class="badge bg-primary-subtle text-primary">{{ $order['status'] }}</span></td>
                                            <td><span class="badge bg-success-subtle text-success">{{ $order['payment_status'] }}</span></td>
                                            <td>{{ $order['payment_method'] }}</td>
                                            <td>{{ $order['placed_at'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">Sifariş tapılmadı.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
