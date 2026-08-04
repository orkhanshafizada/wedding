@extends('admin.layouts.app')

@section('title', __('Promo codes'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="h4 mb-0">{{ __('Promo codes') }}</div>
                        @can('order.promo_code.create')
                            <a href="{{ route('admin.order.promo_codes.create') }}" class="btn btn-success">
                                <i class="ri-add-line me-1"></i> {{ __('Add new') }}
                            </a>
                        @endcan
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.order.promo_codes.index') }}" class="row g-2 mb-3">
                                <div class="col-lg-4">
                                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="{{ __('Search by code...') }}">
                                </div>
                                <div class="col-lg-3">
                                    <select name="type" class="form-select">
                                        <option value="">{{ __('Type (all)') }}</option>
                                        <option value="percent" @selected(($type ?? '') === 'percent')>{{ __('Percent') }}</option>
                                        <option value="fixed" @selected(($type ?? '') === 'fixed')>{{ __('Fixed') }}</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <select name="status" class="form-select">
                                        <option value="">{{ __('Status (all)') }}</option>
                                        <option value="Active" @selected(($status ?? '') === 'Active')>{{ __('Active') }}</option>
                                        <option value="Inactive" @selected(($status ?? '') === 'Inactive')>{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-search-line me-1"></i> {{ __('Search') }}
                                    </button>
                                    <a href="{{ route('admin.order.promo_codes.index') }}" class="btn btn-light w-100">{{ __('Reset') }}</a>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">{{ __('ID') }}</th>
                                        <th>{{ __('Code') }}</th>
                                        <th style="width: 140px;">{{ __('Value') }}</th>
                                        <th style="width: 120px;">{{ __('Type') }}</th>
                                        <th style="width: 120px;">{{ __('Used') }}</th>
                                        <th style="width: 220px;">{{ __('Created') }}</th>
                                        <th class="text-end" style="width: 140px;">{{ __('Functions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($promoCodes as $promoCode)
                                        <tr>
                                            <td>{{ $promoCode->id }}</td>
                                            <td class="fw-semibold">{{ $promoCode->code }}</td>
                                            <td>{{ number_format((float) $promoCode->value, 2, '.', '') }}</td>
                                            <td>
                                                @if($promoCode->type === 'percent')
                                                    %
                                                @else
                                                    ₼
                                                @endif
                                            </td>
                                            <td>
                                                @if($promoCode->used_count >= 1)
                                                    <span class="badge bg-danger-subtle text-danger">{{ __('Yes') }}</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success">{{ __('No') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($promoCode->created_at)->format('j F Y, H:i') }}</td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    @can('order.promo_code.edit')
                                                        <a href="{{ route('admin.order.promo_codes.edit', $promoCode) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="ri-pencil-line"></i>
                                                        </a>
                                                    @endcan

                                                    @can('order.promo_code.delete')
                                                        <form method="POST" action="{{ route('admin.order.promo_codes.destroy', $promoCode) }}" onsubmit="return confirm('{{ __('Silinsin?') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">{{ __('No records found.') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $promoCodes->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
