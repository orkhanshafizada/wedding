@extends('admin.layouts.app')

@section('title', __('Customer Addresses'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0">{{ __('Customer Addresses') }}</h4>
                    <p class="text-muted mb-0">{{ trim($customer->name . ' ' . $customer->surname) }}</p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light">
                        {{ __('Back') }}
                    </a>

                    @can('customers.edit')
                        <a href="{{ route('admin.customers.addresses.create', $customer) }}" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i>{{ __('Add Address') }}
                        </a>
                    @endcan
                </div>
            </div>

            <form method="GET" action="{{ route('admin.customers.addresses.index', $customer) }}" class="card mb-3">
                <div class="card-body row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Type') }}</label>
                        <select name="type" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="billing" @selected(($filters['type'] ?? '') === 'billing')>{{ __('Billing') }}</option>
                            <option value="shipping" @selected(($filters['type'] ?? '') === 'shipping')>{{ __('Shipping') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="1" @selected(($filters['status'] ?? '') === '1')>{{ __('Active') }}</option>
                            <option value="0" @selected(($filters['status'] ?? '') === '0')>{{ __('Inactive') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('admin.customers.addresses.index', $customer) }}" class="btn btn-link">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Label') }}</th>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th>{{ __('Default') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($addresses as $address)
                                <tr>
                                    <td>{{ $address->id }}</td>
                                    <td class="text-capitalize">{{ $address->type }}</td>
                                    <td>{{ $address->label ?: '—' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $address->recipient_name ?: '—' }}</div>
                                        <div class="text-muted small">{{ $address->phone ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $address->address_line1 }}</div>
                                        @if($address->address_line2)
                                            <div>{{ $address->address_line2 }}</div>
                                        @endif
                                        <div class="text-muted small">
                                            {{ $address->city }}{{ $address->region ? ', '.$address->region : '' }}{{ $address->postal_code ? ', '.$address->postal_code : '' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($address->is_default)
                                            <span class="badge bg-primary-subtle text-primary">{{ __('Default') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($address->status)
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @can('customers.edit')
                                                @if(!$address->is_default)
                                                    <form method="POST" action="{{ route('admin.customers.addresses.default', [$customer, $address]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-soft-success">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('admin.customers.addresses.edit', [$customer, $address]) }}" class="btn btn-sm btn-soft-primary">
                                                    <i class="ri-pencil-line"></i>
                                                </a>

                                                <form method="POST" action="{{ route('admin.customers.addresses.destroy', [$customer, $address]) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">{{ __('No addresses found.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $addresses->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
