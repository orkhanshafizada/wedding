@extends('admin.layouts.app')

@section('title', __('Service Menu Transfer'))

@section('content')
    @php
        $recordCount = (int) ($preview['count'] ?? 0);
        $languageCount = (int) ($preview['language_count'] ?? 0);
    @endphp

    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @if(session('success'))
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-3 align-middle"></i>
                            {{ session('success') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="{{ __('Close') }}"
                            ></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            {{ session('error') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="{{ __('Close') }}"
                            ></button>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <h4 class="card-title mb-1">
                                        {{ __('Service Menu Transfer') }}
                                    </h4>

                                    <p class="text-muted mb-0">
                                        {{ __('Transfer multilingual home service items and their icons from OpenCart.') }}
                                    </p>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <a
                                        href="{{ route('admin.transfer.index') }}"
                                        class="btn btn-light"
                                    >
                                        <i class="ri-arrow-left-line align-bottom me-1"></i>
                                        {{ __('Back') }}
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.transfer.menus.services.import') }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-success"
                                            @disabled($recordCount === 0)
                                        >
                                            <i class="ri-upload-2-line align-bottom me-1"></i>
                                            {{ __('Start Service Transfer') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-info alert-border-left" role="alert">
                                <i class="ri-information-line me-2 align-middle"></i>
                                {{ __('Items are read from oc_uni_setting home.text_banner and transferred with the Services view type.') }}
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">
                                            {{ __('Service Items') }}
                                        </div>

                                        <div class="fs-4 fw-semibold">
                                            {{ $recordCount }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">
                                            {{ __('Languages') }}
                                        </div>

                                        <div class="fs-4 fw-semibold">
                                            {{ $languageCount }}
                                        </div>

                                        <div class="text-muted small">
                                            AZ / EN / RU
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">
                                            {{ __('View Type') }}
                                        </div>

                                        <div class="fs-4 fw-semibold">
                                            {{ $preview['view_type'] ?? 'Services' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Icon') }}</th>
                                        <th>{{ __('AZ') }}</th>
                                        <th>{{ __('EN') }}</th>
                                        <th>{{ __('RU') }}</th>
                                        <th>{{ __('Icon Source') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @forelse($preview['items'] ?? [] as $index => $item)
                                        <tr>
                                            <td>
                                                {{ $index + 1 }}
                                            </td>

                                            <td>
                                                @if($item['icon_exists'])
                                                    <img
                                                        src="{{ asset('uploads/opencart/' . ltrim($item['icon_source'], '/')) }}"
                                                        alt="{{ data_get($item, 'translations.en.name') }}"
                                                        width="48"
                                                        height="48"
                                                    >
                                                @else
                                                    <span class="text-muted">
                                                            —
                                                        </span>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="fw-semibold">
                                                    {{ data_get($item, 'translations.az.name') }}
                                                </div>

                                                <div class="text-muted small">
                                                    {{ data_get($item, 'translations.az.description') }}
                                                </div>

                                                <code>
                                                    {{ data_get($item, 'translations.az.link') }}
                                                </code>
                                            </td>

                                            <td>
                                                <div class="fw-semibold">
                                                    {{ data_get($item, 'translations.en.name') }}
                                                </div>

                                                <div class="text-muted small">
                                                    {{ data_get($item, 'translations.en.description') }}
                                                </div>

                                                <code>
                                                    {{ data_get($item, 'translations.en.link') }}
                                                </code>
                                            </td>

                                            <td>
                                                <div class="fw-semibold">
                                                    {{ data_get($item, 'translations.ru.name') }}
                                                </div>

                                                <div class="text-muted small">
                                                    {{ data_get($item, 'translations.ru.description') }}
                                                </div>

                                                <code>
                                                    {{ data_get($item, 'translations.ru.link') }}
                                                </code>
                                            </td>

                                            <td>
                                                <code>
                                                    {{ $item['icon_source'] }}
                                                </code>
                                            </td>

                                            <td>
                                                @if($item['icon_exists'])
                                                    <span class="badge bg-success">
                                                            {{ __('Ready') }}
                                                        </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                            {{ __('Icon Missing') }}
                                                        </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                {{ __('No service items were found.') }}
                                            </td>
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
