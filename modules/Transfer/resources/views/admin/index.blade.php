@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @if(session('success'))
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-3 align-middle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Transfer') }}</h4>
                        </div>

                        <div class="card-body">
                            <div class="row g-4">
{{--                                <div class="col-xxl-3 col-md-6">--}}
{{--                                    <div class="card card-animate h-100 mb-0">--}}
{{--                                        <div class="card-body">--}}
{{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
{{--                                                <div>--}}
{{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">--}}
{{--                                                        {{ __('Product filter translations') }}--}}
{{--                                                    </p>--}}

{{--                                                    <h4 class="mb-0">--}}
{{--                                                        {{ count($productFilterPreview['filters'] ?? []) }}--}}
{{--                                                    </h4>--}}
{{--                                                </div>--}}

{{--                                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">--}}
{{--                                                        <i class="ri-translate-2"></i>--}}
{{--                                                    </span>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                            <p class="text-muted mb-2">--}}
{{--                                                {{ __('Translate Azerbaijani product filters and filter values into all other configured languages.') }}--}}
{{--                                            </p>--}}

{{--                                            <p class="text-muted small mb-3">--}}
{{--                                                {{ __('Source language ID') }}: 2.--}}
{{--                                                {{ __('The translation process runs in the background queue.') }}--}}
{{--                                            </p>--}}

{{--                                            <form--}}
{{--                                                action="{{ route('admin.transfer.products.filters.translate') }}"--}}
{{--                                                method="POST"--}}
{{--                                            >--}}
{{--                                                @csrf--}}

{{--                                                <button type="submit" class="btn btn-primary">--}}
{{--                                                    <i class="ri-translate-2 align-bottom me-1"></i>--}}
{{--                                                    {{ __('Translate product filters') }}--}}
{{--                                                </button>--}}
{{--                                            </form>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Sliders') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $sliderPreview['count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-slideshow-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('OpenCart module ID') }}: {{ $sliderPreview['module_id'] }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.sliders.index') }}" class="btn btn-primary">--}}
                                {{--                                                {{ __('Open slider transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                                                <div class="col-xxl-3 col-md-6">
                                                                    <div class="card card-animate h-100 mb-0">
                                                                        <div class="card-body">
                                                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                                                <div>
                                                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Content menus') }}</p>
                                                                                    <h4 class="mb-0">{{ $contentMenuPreview['count'] }}</h4>
                                                                                </div>
                                                                                <div class="avatar-sm flex-shrink-0">
                                                                                    <span class="avatar-title bg-success-subtle text-success rounded-2 fs-2">
                                                                                        <i class="ri-file-text-line"></i>
                                                                                    </span>
                                                                                </div>
                                                                            </div>

                                                                            <p class="text-muted mb-3">
                                                                                {{ __('OpenCart information pages prepared for content type menus.') }}
                                                                            </p>

                                                                            <a href="{{ route('admin.transfer.menus.content.index') }}" class="btn btn-success">
                                                                                {{ __('Open content transfer') }}
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Blogs') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $blogPreview['story_count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-info-subtle text-info rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-article-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Categories') }}: {{ $blogPreview['category_count'] }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.menus.blogs.index') }}" class="btn btn-info">--}}
                                {{--                                                {{ __('Open blog transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Brand News') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $brandNewsPreview['count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-info-subtle text-info rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-newspaper-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Languages') }}: {{ $brandNewsPreview['language_count'] }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.menus.brand-news.index') }}" class="btn btn-info">--}}
                                {{--                                                {{ __('Open brand news transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Categories') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $categoryPreview['count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-warning-subtle text-warning rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-folder-2-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('OpenCart product categories prepared for menu type categories.') }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.categories.index') }}" class="btn btn-warning">--}}
                                {{--                                                {{ __('Open category transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                                                <div class="col-xxl-3 col-md-6">
                                                                    <div class="card card-animate h-100 mb-0">
                                                                        <div class="card-body">
                                                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                                                <div>
                                                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Manufacturers / Brands') }}</p>
                                                                                    <h4 class="mb-0">{{ $manufacturerPreview['count'] }}</h4>
                                                                                </div>
                                                                                <div class="avatar-sm flex-shrink-0">
                                                                                    <span class="avatar-title bg-dark-subtle text-dark rounded-2 fs-2">
                                                                                        <i class="ri-price-tag-3-line"></i>
                                                                                    </span>
                                                                                </div>
                                                                            </div>

                                                                            <p class="text-muted mb-3">
                                                                                {{ __('Category menus') }}: {{ $manufacturerPreview['category_menus_count'] }}
                                                                            </p>

                                                                            <a href="{{ route('admin.transfer.manufacturers.index') }}" class="btn btn-dark">
                                                                                {{ __('Open brand transfer') }}
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Product filters') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ count($productFilterPreview['filters']) }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-filter-3-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Create / normalize all product filters and values first.') }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.products.filters.index') }}" class="btn btn-primary">--}}
                                {{--                                                {{ __('Open product filter transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Products') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $productPreview['count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-shopping-bag-3-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Only sync products, variation, media and existing filter values.') }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.products.index') }}" class="btn btn-danger">--}}
                                {{--                                                {{ __('Open product transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Customers') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $customerPreview['ready_count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-success-subtle text-success rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-user-3-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Total') }}: {{ $customerPreview['total_count'] }}, {{ __('Addresses') }}: {{ $customerPreview['address_count'] }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.customers.index') }}" class="btn btn-success">--}}
                                {{--                                                {{ __('Open customer transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Orders') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $orderPreview['count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-warning-subtle text-warning rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-shopping-cart-2-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Online') }}: {{ $orderPreview['online_count'] }}, {{ __('Items') }}: {{ $orderPreview['item_count'] }}--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.orders.index') }}" class="btn btn-warning">--}}
                                {{--                                                {{ __('Open order transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}

                                {{--                                <div class="col-xxl-3 col-md-6">--}}
                                {{--                                    <div class="card card-animate h-100 mb-0">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex align-items-center justify-content-between mb-3">--}}
                                {{--                                                <div>--}}
                                {{--                                                    <p class="text-uppercase fw-medium text-muted mb-1">{{ __('Settings') }}</p>--}}
                                {{--                                                    <h4 class="mb-0">{{ $settingPreview['mapped_rows_count'] }}</h4>--}}
                                {{--                                                </div>--}}
                                {{--                                                <div class="avatar-sm flex-shrink-0">--}}
                                {{--                                                    <span class="avatar-title bg-secondary-subtle text-secondary rounded-2 fs-2">--}}
                                {{--                                                        <i class="ri-settings-3-line"></i>--}}
                                {{--                                                    </span>--}}
                                {{--                                                </div>--}}
                                {{--                                            </div>--}}

                                {{--                                            <p class="text-muted mb-3">--}}
                                {{--                                                {{ __('Source') }}: oc_uni_setting--}}
                                {{--                                            </p>--}}

                                {{--                                            <a href="{{ route('admin.transfer.settings.index') }}" class="btn btn-secondary">--}}
                                {{--                                                {{ __('Open settings transfer') }}--}}
                                {{--                                            </a>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
