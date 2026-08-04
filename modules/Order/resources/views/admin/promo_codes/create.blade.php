@extends('admin.layouts.app')

@section('title', __('Create promo code'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="h4 mb-0">{{ __('Create promo code') }}</div>
                <a href="{{ route('admin.order.promo_codes.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i> {{ __('Back') }}
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.order.promo_codes.store') }}">
                        @csrf

                        @include('order::admin.promo_codes.partials.form', ['promoCode' => $promoCode, 'categories' => $categories, 'products' => $products])

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('admin.order.promo_codes.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-success">
                                <i class="ri-save-3-line me-1"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    @include('order::admin.promo_codes.partials.form-scripts')
@endpush
