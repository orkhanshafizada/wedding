@extends('admin.layouts.app')

@section('title', __('Customer Details'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('Customer Details') }}</h4>

                <div class="d-flex gap-2">
                    @can('customers.edit')
                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
                            <i class="ri-pencil-line align-bottom me-1"></i>{{ __('Edit Customer') }}
                        </a>
                    @endcan

                    @can('customers.edit')
                        <a href="{{ route('admin.customers.addresses.create', $customer) }}" class="btn btn-soft-primary">
                            <i class="ri-map-pin-add-line align-bottom me-1"></i>{{ __('Add Address') }}
                        </a>
                    @endcan
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xxl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            @if($customer->avatar_url)
                                <img
                                    src="{{ $customer->avatar_url }}"
                                    alt="avatar"
                                    class="rounded-circle avatar-xl img-thumbnail mb-3"
                                    style="object-fit: cover;"
                                >
                            @endif

                            <h5 class="mb-1">{{ trim($customer->name . ' ' . $customer->surname) }}</h5>
                            <p class="text-muted mb-3">{{ $customer->email }}</p>

                            @if($customer->is_active)
                                <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Summary') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-nowrap align-middle mb-0">
                                    <tbody>
                                    <tr>
                                        <th>{{ __('Customer ID') }}</th>
                                        <td>{{ $customer->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Phone') }}</th>
                                        <td>{{ $customer->phone ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Country') }}</th>
                                        <td>{{ $customer->country_label ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Passport FIN') }}</th>
                                        <td>{{ $customer->passport_fin ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Date of Birth') }}</th>
                                        <td>{{ $customer->date_of_birth ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Orders Count') }}</th>
                                        <td>{{ $customer->orders->count() }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Email Verified At') }}</th>
                                        <td>{{ $customer->email_verified_at ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Created At') }}</th>
                                        <td>{{ $customer->created_at }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Updated At') }}</th>
                                        <td>{{ $customer->updated_at }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-8">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">{{ __('Addresses') }}</h5>

                            @can('customers.edit')
                                <a href="{{ route('admin.customers.addresses.index', $customer) }}" class="btn btn-sm btn-soft-info">
                                    {{ __('Manage All') }}
                                </a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.customers.show', $customer) }}" class="row g-3 align-items-end mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Type') }}</label>
                                    <select name="type" class="form-select">
                                        <option value="">{{ __('All') }}</option>
                                        <option value="billing" @selected(($addressFilters['type'] ?? '') === 'billing')>{{ __('Billing') }}</option>
                                        <option value="shipping" @selected(($addressFilters['type'] ?? '') === 'shipping')>{{ __('Shipping') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <select name="status" class="form-select">
                                        <option value="">{{ __('All') }}</option>
                                        <option value="1" @selected(($addressFilters['status'] ?? '') === '1')>{{ __('Active') }}</option>
                                        <option value="0" @selected(($addressFilters['status'] ?? '') === '0')>{{ __('Inactive') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-primary">{{ __('Filter') }}</button>
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-link">{{ __('Reset') }}</a>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Type') }}</th>
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
                                            <td class="text-capitalize">{{ $address->type }}</td>
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
                                            <td colspan="6" class="text-center py-4">{{ __('No addresses found.') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                {{ $addresses->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
