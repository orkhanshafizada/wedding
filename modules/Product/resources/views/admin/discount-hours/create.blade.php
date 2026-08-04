@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Discount hours add') }}</h4>
                        <div class="page-title-right">
                            <a href="{{ route('admin.product.discount_hours.index') }}" class="btn btn-soft-secondary">
                                <i class="ri-arrow-left-line me-1"></i>{{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.product.discount_hours.store') }}" method="post">
                @csrf
                @include('product::admin.discount-hours.partials.form', ['mode' => 'create'])
            </form>
        </div>
    </div>
@endsection
