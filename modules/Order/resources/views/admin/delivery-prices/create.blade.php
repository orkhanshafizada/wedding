@extends('admin.layouts.app')
@section('title', 'Add Delivery Price')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Add Delivery Price') }}</h4>
                        <a href="{{ route('admin.order.delivery_prices.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.order.delivery_prices.store') }}">
                        @csrf
                        @include('order::admin.delivery-prices.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
