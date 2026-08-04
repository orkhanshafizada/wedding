@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ __('Brand Transfer') }}</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.transfer.index') }}">{{ __('Transfer') }}</a>
                                </li>
                                <li class="breadcrumb-item active">{{ __('Brands') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-3 align-middle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">{{ __('Found Brands') }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-success fs-14 mb-0">{{ __('OpenCart') }}</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['count'] }}</h4>
                                    <span class="text-muted">{{ __('Source store') }}: 0</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-price-tag-3-line text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">{{ __('Category Connections') }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-info fs-14 mb-0">{{ __('Menu') }}</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['category_menus_count'] }}</h4>
                                    <span class="text-muted">menus.type = categories</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-folder-settings-line text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-medium text-muted mb-0">{{ __('Languages') }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-warning fs-14 mb-0">AZ / EN / RU</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['language_count'] }}</h4>
                                    <span class="text-muted">az = 3, en = 8, ru = 9</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-translate-2 text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Transfer Action') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-border-left mb-4" role="alert">
                                {{ __('The brand filter and brand values will be inserted or updated with multilingual translations.') }}
                            </div>

                            <form action="{{ route('admin.transfer.manufacturers.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>
                                    {{ __('Start Transfer') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1">{{ __('Preview') }}</h4>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ __(':count record(s)', ['count' => $preview['count']]) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('AZ Slug') }}</th>
                                        <th>{{ __('EN Slug') }}</th>
                                        <th>{{ __('RU Slug') }}</th>
                                        <th>{{ __('Sort') }}</th>
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('AZ Meta Title') }}</th>
                                        <th>{{ __('EN Meta Title') }}</th>
                                        <th>{{ __('RU Meta Title') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['manufacturers'] as $manufacturer)
                                        <tr>
                                            <td>{{ $manufacturer['manufacturer_id'] }}</td>
                                            <td>{{ $manufacturer['name'] }}</td>
                                            <td>{{ $manufacturer['az_slug'] }}</td>
                                            <td>{{ $manufacturer['en_slug'] }}</td>
                                            <td>{{ $manufacturer['ru_slug'] }}</td>
                                            <td>{{ $manufacturer['sort_order'] }}</td>
                                            <td>
                                                @if ($manufacturer['image_exists'])
                                                    <span class="badge bg-success-subtle text-success">{{ __('Exists'). ' - '. $manufacturer['org_url']}}</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">{{ __('Missing'). ' - '. $manufacturer['org_url'] }}</span>
                                                @endif
                                            </td>
                                            <td>{{ \Illuminate\Support\Str::limit($manufacturer['az_meta_title'] ?? '', 80) }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($manufacturer['en_meta_title'] ?? '', 80) }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($manufacturer['ru_meta_title'] ?? '', 80) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">{{ __('No brands found.') }}</td>
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
