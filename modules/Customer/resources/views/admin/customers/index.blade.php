@extends('admin.layouts.app')

@section('title', __('Customers'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('Customers') }}</h4>

                @can('customers.create')
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>{{ __('Add New') }}
                    </a>
                @endcan
            </div>

            <form method="GET" action="{{ route('admin.customers.index') }}" class="card mb-3">
                <div class="card-body row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">{{ __('Search') }}</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            class="form-control"
                            placeholder="{{ __('Name, surname, full name, email or phone') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="is_active" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="1" @selected(($filters['is_active'] ?? '') === '1')>{{ __('Active') }}</option>
                            <option value="0" @selected(($filters['is_active'] ?? '') === '0')>{{ __('Inactive') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary">
                            {{ __('Filter') }}
                        </button>

                        <a href="{{ route('admin.customers.index') }}" class="btn btn-link">
                            {{ __('Reset') }}
                        </a>
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
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Country') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Status Action') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>{{ $customer->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($customer->avatar_url)
                                                <img
                                                    src="{{ $customer->avatar_url }}"
                                                    alt="avatar"
                                                    class="rounded-circle"
                                                    style="width:32px;height:32px;object-fit:cover"
                                                >
                                            @endif

                                            <div>
                                                <div class="fw-semibold">
                                                    {{ trim($customer->name . ' ' . $customer->surname) }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $customer->created_at?->format('d.m.Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->country_label }}</td>
                                    <td>
                                        @if($customer->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @can('customers.edit')
                                            <form action="{{ route('admin.customers.toggle-status', $customer) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button class="btn btn-sm btn-soft-secondary">
                                                    {{ $customer->is_active ? __('Deactivate') : __('Activate') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @can('customers.view')
                                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-soft-info">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            @endcan

                                            @can('customers.edit')
                                                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-soft-primary">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            @endcan

                                            @can('customers.delete')
                                                <form
                                                    action="{{ route('admin.customers.destroy', $customer) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('{{ __('Delete?') }}')"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-soft-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        {{ __('No customers found.') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($customers instanceof \Illuminate\Contracts\Pagination\Paginator)
                    <div class="card-footer">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
