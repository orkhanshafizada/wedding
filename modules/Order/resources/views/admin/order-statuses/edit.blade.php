@extends('admin.layouts.app')

@section('title', __('Edit Order Status'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Edit Order Status') }}</h4>
                        <a href="{{ route('admin.order.order_statuses.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.order.order_statuses.update', $orderStatus) }}">
                        @csrf
                        @method('PUT')
                        @include('order::admin.order-statuses.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
