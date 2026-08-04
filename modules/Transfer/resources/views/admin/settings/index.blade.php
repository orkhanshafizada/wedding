@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Setting transferi</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.transfer.index') }}">Transfer</a></li>
                                <li class="breadcrumb-item active">Settings</li>
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
                                    <p class="text-uppercase fw-medium text-muted mb-0">Hazırlanan setting sayı</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-success fs-14 mb-0">Target</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['mapped_rows_count'] }}</h4>
                                    <span class="text-muted">settings table rows</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-settings-3-line text-primary"></i>
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
                                    <p class="text-uppercase fw-medium text-muted mb-0">Source store</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-info fs-14 mb-0">OpenCart</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $preview['source_store_id'] }}</h4>
                                    <span class="text-muted">oc_uni_setting.store_id / oc_setting.store_id</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-database-2-line text-info"></i>
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
                                    <p class="text-uppercase fw-medium text-muted mb-0">uni_setting source</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-warning fs-14 mb-0">JSON</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                        {{ $preview['uni_setting_exists'] ? 'Var' : 'Yoxdur' }}
                                    </h4>
                                    <span class="text-muted">oc_uni_setting.data</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-braces-line text-warning"></i>
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
                                    <p class="text-uppercase fw-medium text-muted mb-0">config source</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-dark fs-14 mb-0">Table</h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                        {{ $preview['config_setting_exists'] ? 'Var' : 'Yoxdur' }}
                                    </h4>
                                    <span class="text-muted">oc_setting</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-light rounded fs-3">
                                        <i class="ri-table-line text-dark"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Transfer əməliyyatı</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-border-left mb-4" role="alert">
                                OpenCart <strong>oc_uni_setting.data</strong> və <strong>oc_setting</strong> birlikdə oxunacaq.
                                Uyğun məlumatlar <strong>general</strong>, <strong>og</strong>, <strong>social</strong>,
                                <strong>smtp</strong>, <strong>security</strong>, <strong>seo</strong>,
                                <strong>system</strong>, <strong>file_manager</strong> qruplarına import olunacaq.
                            </div>

                            <form action="{{ route('admin.transfer.settings.import') }}" method="POST">
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
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1">Qruplar üzrə preview</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Group</th>
                                        <th>Count</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['groups'] as $group => $count)
                                        <tr>
                                            <td>{{ $group }}</td>
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Heç bir uyğun setting tapılmadı.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $groupedRows = collect($preview['rows'])->groupBy('group');
                @endphp

                <div class="col-xl-8">
                    <div class="row">
                        @foreach (['general', 'og', 'social', 'smtp', 'security', 'seo', 'system', 'file_manager'] as $groupName)
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header border-0">
                                        <h4 class="card-title mb-0">{{ ucfirst($groupName) }} preview</h4>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $groupRows = $groupedRows->get($groupName, collect())
                                                ->mapWithKeys(fn ($row) => [$row['key'] => $row['value']])
                                                ->all();
                                        @endphp

                                        @if (!empty($groupRows))
                                            <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($groupRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @else
                                            <div class="text-muted">Məlumat tapılmadı.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1">Import olunacaq setting-lər</h4>
                                <span class="badge bg-primary-subtle text-primary">{{ $preview['mapped_rows_count'] }} qeyd</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Group</th>
                                        <th>Key</th>
                                        <th>Value</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($preview['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['group'] }}</td>
                                            <td>{{ $row['key'] }}</td>
                                            <td>
                                                <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($row['value'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Import üçün setting tapılmadı.</td>
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
