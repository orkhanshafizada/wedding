@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Məhsulların transferi</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.transfer.index') }}">Transfer</a></li>
                                <li class="breadcrumb-item active">Products</li>
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
                                    <p class="text-uppercase fw-medium text-muted mb-0">Tapılan məhsul sayı</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-success fs-14 mb-0">OpenCart</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['count'] }}</h4>
                                    <span class="text-muted">store_id = 0, language_id = 3</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-shopping-bag-3-line text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-9">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Transfer əməliyyatı</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-border-left mb-4" role="alert">
                                Hər OpenCart məhsulu <strong>1 product</strong> və <strong>1 variation</strong> kimi yazılacaq.
                                Əsas kateqoriya olaraq ən son seçilmiş leaf category götürüləcək.
                                Brand mövcud <strong>Brend</strong> filterinə bağlanacaq.
                                Digər filterlər tapılmasa avtomatik yaradılacaq.
                            </div>

                            <form action="{{ route('admin.transfer.products.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>
                                    Transferə başla
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
                                <h4 class="card-title mb-0 flex-grow-1">Preview</h4>
                                <span class="badge bg-primary-subtle text-primary">{{ $preview['count'] }} qeyd</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Məhsul</th>
                                        <th>Slug</th>
                                        <th>SKU / Model</th>
                                        <th>Əsas kateqoriya</th>
                                        <th>Brand</th>
                                        <th>Filter</th>
                                        <th>Gallery</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['products'] as $product)
                                        <tr>
                                            <td>{{ $product['product_id'] }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $product['name'] }}</div>
                                                <div class="text-muted small mt-1">
                                                    {{ number_format((float) $product['price'], 2, '.', '') }}
                                                    @if($product['special_price'] !== null)
                                                        / {{ number_format((float) $product['special_price'], 2, '.', '') }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $product['slug'] }}</td>
                                            <td>
                                                <div>{{ $product['sku'] ?? '-' }}</div>
                                                <div class="text-muted small">{{ $product['model'] ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div>Source: {{ $product['main_category_source_id'] ?? '-' }}</div>
                                                <div class="text-muted small">Target: {{ $product['main_category_target_id'] ?? '-' }}</div>
                                            </td>
                                            <td>{{ $product['manufacturer_name'] ?? '-' }}</td>
                                            <td>{{ $product['filter_count'] }}</td>
                                            <td>{{ $product['gallery_count'] }}</td>
                                            <td>
                                                @if($product['status'] === 'Active')
                                                    <span class="badge bg-success-subtle text-success">Aktiv</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Passiv</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">Məhsul tapılmadı.</td>
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
