@extends('admin.layouts.app')

@section('title', __('Create Product'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Create Product') }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.product.products.index') }}">{{ __('Products') }}</a>
                                </li>
                                <li class="breadcrumb-item active">{{ __('Create') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.product.products.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <a href="{{ route('admin.product.products.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line align-bottom me-1"></i>{{ __('Back') }}
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Save') }}
                    </button>
                </div>

                @include('admin.shared.alerts')

                @include('product::admin.products.form', [
                    'product' => null,
                    'categories' => $categories,
                    'labels' => $labels,
                    'languages' => $languages,
                    'prefillMainCategoryId' => $prefillMainCategoryId ?? null,
                ])
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    @include('product::admin.products.partials.form-scripts')
@endpush
