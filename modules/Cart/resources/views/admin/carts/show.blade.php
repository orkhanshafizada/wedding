@extends('admin.layouts.app')

@section('title')
    {{ __('Cart #:id', ['id' => $cart->id]) }}
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Cart #:id', ['id' => $cart->id]) }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.cart.carts.index') }}">{{ __('Carts') }}</a></li>
                                <li class="breadcrumb-item active">#{{ $cart->id }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.cart.carts.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line me-1"></i>{{ __('Back') }}
                    </a>

                    @php($status = (string) $cart->status)
                    @if($status === 'active')
                        <span class="badge bg-success">{{ __('Active') }}</span>
                    @elseif($status === 'merged')
                        <span class="badge bg-info">{{ __('Merged') }}</span>
                    @elseif($status === 'expired')
                        <span class="badge bg-warning">{{ __('Expired') }}</span>
                    @elseif($status === 'converted')
                        <span class="badge bg-primary">{{ __('Converted') }}</span>
                    @else
                        <span class="badge bg-dark">{{ $status }}</span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @can('cart.edit')
                        @if($cart->status === 'active')
                            <form method="POST" action="{{ route('admin.cart.carts.expire', $cart) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-warning">
                                    <i class="ri-timer-flash-line me-1"></i>{{ __('Expire') }}
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Summary') }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <div class="text-muted">{{ __('Customer') }}</div>
                                <div class="fw-medium">
                                    @if($cart->customer)
                                        #{{ $cart->customer->id }} {{ $cart->customer->email ?? '' }}
                                    @else
                                        <span class="badge bg-secondary">{{ __('Guest') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted">{{ __('Token') }}</div>
                                <div class="fw-medium text-break">{{ $cart->token ?? '-' }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted">{{ __('Promo code') }}</div>
                                <div class="fw-medium">
                                    @if($cart->promoCode)
                                        <span class="badge bg-light text-dark">{{ $cart->promoCode->code }}</span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ __('Subtotal') }}</span>
                                <span class="fw-medium">{{ number_format((float) $cart->subtotal_snapshot, 2) }}</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ __('Discount') }}</span>
                                <span class="fw-medium">{{ number_format((float) $cart->discount_snapshot, 2) }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">{{ __('Total') }}</span>
                                <span class="fw-bold">{{ number_format((float) $cart->total_snapshot, 2) }}</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">{{ __('Created at') }}</span>
                                <span>{{ optional($cart->created_at)->format('Y-m-d H:i') }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">{{ __('Updated at') }}</span>
                                <span>{{ optional($cart->updated_at)->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h5 class="card-title mb-0">{{ __('Items') }}</h5>
                                <span class="text-muted">{{ __('Count: :count', ['count' => $cart->items->count()]) }}</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">#</th>
                                        <th>{{ __('Variation') }}</th>
                                        <th class="text-end">{{ __('Qty') }}</th>
                                        <th class="text-end">{{ __('Price') }}</th>
                                        <th class="text-end">{{ __('Line total') }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @forelse($cart->items as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>

                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium">#{{ $item->product_variation_id }}</span>
                                                    <div class="text-muted">
                                                        @if($item->product_id)
                                                            <small>{{ __('Product') }}: #{{ $item->product_id }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-end">{{ (int) $item->qty }}</td>
                                            <td class="text-end">{{ number_format((float) $item->price_snapshot, 2) }}</td>
                                            <td class="text-end fw-medium">{{ number_format((float) $item->line_total_snapshot, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                {{ __('This cart has no items.') }}
                                            </td>
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
