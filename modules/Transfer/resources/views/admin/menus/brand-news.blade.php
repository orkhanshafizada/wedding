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
                        <div class="card-header border-0 d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">{{ __('Brand News Transfer') }}</h4>

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.transfer.index') }}" class="btn btn-light">
                                    {{ __('Back') }}
                                </a>

                                <form method="POST" action="{{ route('admin.transfer.menus.brand-news.import') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-info">
                                        {{ __('Start Brand News Transfer') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-info alert-border-left" role="alert">
                                <i class="ri-information-line me-2 align-middle"></i>
                                {{ __('OpenCart language mapping: az = 3, en = 8, ru = 9.') }}
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">{{ __('Brand News') }}</div>
                                        <div class="fw-semibold">{{ $preview['count'] }}</div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">{{ __('Languages') }}</div>
                                        <div class="fw-semibold">{{ $preview['language_count'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-3">{{ __('Brand News Preview') }}</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Partner ID') }}</th>
                                        <th>{{ __('Manufacturer ID') }}</th>
                                        <th>{{ __('AZ Name') }}</th>
                                        <th>{{ __('EN Name') }}</th>
                                        <th>{{ __('RU Name') }}</th>
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('Banner') }}</th>
                                        <th>{{ __('Related Products') }}</th>
                                        <th>{{ __('Sort') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($preview['items'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['partner_id'] }}</td>
                                            <td>{{ $item['manufacturer_id'] }}</td>
                                            <td>{{ $item['az_name'] }}</td>
                                            <td>{{ $item['en_name'] }}</td>
                                            <td>{{ $item['ru_name'] }}</td>
                                            <td>
                                                <span class="badge {{ $item['image_exists'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $item['image_exists'] ? __('Exists') : __('Missing') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $item['banner_exists'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $item['banner_exists'] ? __('Exists') : __('Missing') }}
                                                </span>
                                            </td>
                                            <td>{{ $item['related_products_count'] }}</td>
                                            <td>{{ $item['sort_order'] }}</td>
                                            <td>
                                                <span class="badge {{ $item['status'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $item['status'] ? __('Active') : __('Inactive') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">{{ __('No brand news found.') }}</td>
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
