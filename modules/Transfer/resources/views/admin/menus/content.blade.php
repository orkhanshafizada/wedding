@extends('admin.layouts.app')

@section('title', __('Content Menu Transfer'))

@section('content')
    @php
        $excludedInformationIds = $preview['excluded_information_ids'] ?? [];
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
                                        {{ __('Content Menu Transfer') }}
                                    </h4>

                                    <p class="text-muted mb-0">
                                        {{ __('Transfer the remaining OpenCart information pages without duplicating previously transferred menus.') }}
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
                                        action="{{ route('admin.transfer.menus.content.import') }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-success"
                                            @disabled($recordCount === 0)
                                        >
                                            <i class="ri-upload-2-line align-bottom me-1"></i>
                                            {{ __('Start Content Transfer') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-info alert-border-left" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="ri-information-line me-2 mt-1"></i>

                                    <div>
                                        <div class="fw-semibold mb-1">
                                            {{ __('Multilingual content transfer') }}
                                        </div>

                                        <div>
                                            {{ __('Content from OpenCart will be transferred for AZ, EN, and RU locales. Previously transferred information records are excluded automatically.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-xl-4 col-md-6">
                                    <div class="card card-animate border mb-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <p class="text-uppercase fw-medium text-muted mb-0">
                                                        {{ __('Remaining Records') }}
                                                    </p>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        OpenCart
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                                        {{ $recordCount }}
                                                    </h4>

                                                    <span class="text-muted">
                                                        oc_information
                                                    </span>
                                                </div>

                                                <div class="avatar-sm flex-shrink-0">
                                                    <span class="avatar-title bg-light rounded fs-3">
                                                        <i class="ri-file-list-3-line text-primary"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6">
                                    <div class="card card-animate border mb-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <p class="text-uppercase fw-medium text-muted mb-0">
                                                        {{ __('Languages') }}
                                                    </p>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span class="badge bg-warning-subtle text-warning">
                                                        AZ / EN / RU
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                                        {{ $languageCount }}
                                                    </h4>

                                                    <span class="text-muted">
                                                        az = 3, en = 8, ru = 9
                                                    </span>
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

                                <div class="col-xl-4 col-md-6">
                                    <div class="card card-animate border mb-0 h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <p class="text-uppercase fw-medium text-muted mb-0">
                                                        {{ __('Excluded Records') }}
                                                    </p>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <span class="badge bg-success-subtle text-success">
                                                        {{ __('Already Transferred') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                                        {{ count($excludedInformationIds) }}
                                                    </h4>

                                                    <span class="text-muted">
                                                        {{ $excludedInformationIds !== [] ? implode(', ', $excludedInformationIds) : '—' }}
                                                    </span>
                                                </div>

                                                <div class="avatar-sm flex-shrink-0">
                                                    <span class="avatar-title bg-light rounded fs-3">
                                                        <i class="ri-filter-off-line text-success"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Information ID') }}</th>
                                        <th>{{ __('AZ Name') }}</th>
                                        <th>{{ __('EN Name') }}</th>
                                        <th>{{ __('RU Name') }}</th>
                                        <th>{{ __('AZ Link') }}</th>
                                        <th>{{ __('EN Link') }}</th>
                                        <th>{{ __('RU Link') }}</th>
                                        <th>{{ __('Sort') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @forelse($preview['items'] ?? [] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>

                                            <td>
                                                    <span class="fw-semibold">
                                                        {{ $item['information_id'] }}
                                                    </span>
                                            </td>

                                            <td>
                                                {{ data_get($item, 'translations.az.title') ?: '—' }}
                                            </td>

                                            <td>
                                                {{ data_get($item, 'translations.en.title') ?: '—' }}
                                            </td>

                                            <td>
                                                {{ data_get($item, 'translations.ru.title') ?: '—' }}
                                            </td>

                                            <td>
                                                @if(data_get($item, 'translations.az.keyword'))
                                                    <code>{{ data_get($item, 'translations.az.keyword') }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if(data_get($item, 'translations.en.keyword'))
                                                    <code>{{ data_get($item, 'translations.en.keyword') }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if(data_get($item, 'translations.ru.keyword'))
                                                    <code>{{ data_get($item, 'translations.ru.keyword') }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td>{{ $item['sort_order'] }}</td>

                                            <td>
                                                @if($item['status'])
                                                    <span class="badge bg-success">
                                                            {{ __('Active') }}
                                                        </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                            {{ __('Inactive') }}
                                                        </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                                                    {{ __('No remaining content records were found for transfer.') }}
                                                </div>
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
