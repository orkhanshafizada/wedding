@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Məhsul filterlərinin transferi</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.transfer.index') }}">Transfer</a></li>
                                <li class="breadcrumb-item active">Product Filters</li>
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
                                    <span class="text-muted">mənbə məhsullar</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-filter-3-line text-primary"></i>
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
                                Bu hissə yalnız bütün filterləri və filter value-ları yaradacaq və normallaşdıracaq.
                                Bütün filterlər <strong>single select</strong> kimi yaradılır.
                                <strong>Rəng</strong> filteri avtomatik color filter olacaq və uyğun olduqda hex rəngi yazılacaq.
                            </div>

                            <form action="{{ route('admin.transfer.products.filters.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-upload-2-line align-bottom me-1"></i>
                                    Filter transferinə başla
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
                                <span class="badge bg-primary-subtle text-primary">{{ count($preview['filters']) }} filter</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Filter</th>
                                        <th>Color filter</th>
                                        <th>Count</th>
                                        <th>Nümunə dəyərlər</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['filters'] as $filter)
                                        <tr>
                                            <td>{{ $filter['filter_name'] }}</td>
                                            <td>
                                                @if($filter['is_color_filter'])
                                                    <span class="badge bg-success-subtle text-success">Bəli</span>
                                                @else
                                                    <span class="badge bg-light text-muted">Xeyr</span>
                                                @endif
                                            </td>
                                            <td>{{ $filter['count'] }}</td>
                                            <td>{{ implode(', ', $filter['values']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Filter tapılmadı.</td>
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
