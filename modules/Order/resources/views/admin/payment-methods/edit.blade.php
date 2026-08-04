@extends('admin.layouts.app')

@section('title', __('Edit Payment Method'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Edit Payment Method') }}</h4>
                        <a href="{{ route('admin.order.payment_methods.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.order.payment_methods.update', $paymentMethod) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('order::admin.payment-methods.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
