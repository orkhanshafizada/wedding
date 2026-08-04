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
                    <h4 class="card-title mb-0">{{ __('Content Menu Transfer') }}</h4>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.transfer.index') }}" class="btn btn-light">
                            {{ __('Back') }}
                        </a>

                        <form method="POST" action="{{ route('admin.transfer.menus.content.import') }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                {{ __('Start Content Transfer') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info alert-border-left" role="alert">
                        <i class="ri-information-line me-2 align-middle"></i>
                        {{ __('Only OpenCart language_id = 3 records are transferred to locale = az.') }}
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Count') }}</div>
                                <div class="fw-semibold">{{ $preview['count'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-nowrap align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Information ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Link') }}</th>
                                <th>{{ __('Sort') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('In header') }}</th>
                                <th>{{ __('In footer') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($preview['items'] as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['information_id'] }}</td>
                                    <td>{{ $item['title'] }}</td>
                                    <td>{{ $item['keyword'] }}</td>
                                    <td>{{ $item['sort_order'] }}</td>
                                    <td>
                                        <span class="badge {{ $item['status'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ $item['status'] ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item['in_header'] ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ $item['in_header'] ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item['in_footer'] ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ $item['in_footer'] ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No records found.') }}</td>
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
