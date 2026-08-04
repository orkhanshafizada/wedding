@extends('admin.layouts.app')

@section('title', __('Add Payment Method'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Add Payment Method') }}</h4>
                        <a href="{{ route('admin.order.payment_methods.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.order.payment_methods.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('order::admin.payment-methods.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
