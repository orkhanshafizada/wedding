@extends('admin.layouts.app')
@section('title', 'Edit Delivery Price')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Edit Delivery Price') }}</h4>
                        <a href="{{ route('admin.order.delivery_prices.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.order.delivery_prices.update', $deliveryPrice) }}">
                        @csrf
                        @method('PUT')
                        @include('order::admin.delivery-prices.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
