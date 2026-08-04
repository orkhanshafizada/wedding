@extends('admin.layouts.app')

@section('title')
    {{ __('OTP Codes') }}
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1">{{ __('OTP Codes') }}</h4>
                            <p class="text-muted mb-0">{{ __('Monitor customer verification and reset codes.') }}</p>
                        </div>
                        <div>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">{{ __('Customers') }}</li>
                                <li class="breadcrumb-item active">{{ __('OTP Codes') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('admin.otp-codes.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-6">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    value="{{ $filters['search'] }}"
                                    placeholder="{{ __('Customer name, email, phone, ID or code') }}"
                                >
                            </div>

                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select name="type" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="email_verification" @selected($filters['type'] === 'email_verification')>
                                        {{ __('Email verification') }}
                                    </option>
                                    <option value="password_reset" @selected($filters['type'] === 'password_reset')>
                                        {{ __('Password reset') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="active" @selected($filters['status'] === 'active')>{{ __('Active') }}</option>
                                    <option value="used" @selected($filters['status'] === 'used')>{{ __('Used') }}</option>
                                    <option value="expired" @selected($filters['status'] === 'expired')>{{ __('Expired') }}</option>
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <label class="form-label">{{ __('Per page') }}</label>
                                <select name="per_page" class="form-select">
                                    <option value="20" @selected($filters['per_page'] === 20)>20</option>
                                    <option value="50" @selected($filters['per_page'] === 50)>50</option>
                                    <option value="100" @selected($filters['per_page'] === 100)>100</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-search-line align-bottom me-1"></i>
                                        {{ __('Filter') }}
                                    </button>

                                    <a href="{{ route('admin.otp-codes.index') }}" class="btn btn-light">
                                        <i class="ri-refresh-line align-bottom me-1"></i>
                                        {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Attempts') }}</th>
                                <th>{{ __('Expires At') }}</th>
                                <th>{{ __('Used At') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created At') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($codes as $code)
                                @php
                                    $customerName = trim(($code->customer->name ?? '') . ' ' . ($code->customer->surname ?? ''));
                                    $isUsed = !is_null($code->used_at);
                                    $isExpired = !$isUsed && $code->expires_at && $code->expires_at->isPast();
                                    $statusLabel = $isUsed ? __('Used') : ($isExpired ? __('Expired') : __('Active'));
                                    $statusClass = $isUsed ? 'success' : ($isExpired ? 'danger' : 'warning');
                                @endphp
                                <tr>
                                    <td class="fw-semibold">#{{ $code->id }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $customerName !== '' ? $customerName : __('N/A') }}</span>
                                            <span class="text-muted">#{{ $code->customer_id }}</span>
                                            <span class="text-muted">{{ $code->customer->email ?? '-' }}</span>
                                            <span class="text-muted">{{ $code->customer->phone ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                            <span class="badge bg-info-subtle text-info text-uppercase">
                                                {{ str_replace('_', ' ', $code->type) }}
                                            </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $code->code }}</span>
                                    </td>
                                    <td>{{ $code->attempts }}</td>
                                    <td>{{ optional($code->expires_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                    <td>{{ optional($code->used_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                    <td>
                                            <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                    </td>
                                    <td>{{ optional($code->created_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        {{ __('No OTP codes found.') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($codes->hasPages())
                    <div class="card-footer bg-white">
                        {{ $codes->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
