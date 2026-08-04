@extends('admin.layouts.app')

@section('title', __('Payment Methods'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ __('Payment Methods') }}</h4>
                        <a href="{{ route('admin.order.payment_methods.create') }}" class="btn btn-primary">{{ __('Add Payment Method') }}</a>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.order.payment_methods.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="{{ __('Key or name') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('Type') }}</label>
                                <select name="type" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($typeOptions as $typeKey => $typeLabel)
                                        <option value="{{ $typeKey }}" @selected($filters['type'] === $typeKey)>{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1" @selected((string) $filters['status'] === '1')>{{ __('Active') }}</option>
                                    <option value="0" @selected((string) $filters['status'] === '0')>{{ __('Inactive') }}</option>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                                    <a href="{{ route('admin.order.payment_methods.index') }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Key') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Gateway') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Sort Order') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($paymentMethods as $paymentMethod)
                                <tr>
                                    <td>{{ $paymentMethod->id }}</td>
                                    <td>{{ $paymentMethod->getDisplayName() }}</td>
                                    <td><code>{{ $paymentMethod->key }}</code></td>
                                    <td>{{ $typeOptions[$paymentMethod->type] ?? $paymentMethod->type }}</td>
                                    <td>{{ $paymentMethod->gateway_code ?: '-' }}</td>
                                    <td>
                                        @if($paymentMethod->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $paymentMethod->sort_order }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('admin.order.payment_methods.edit', $paymentMethod) }}" class="btn btn-sm btn-outline-primary">
                                                {{ __('Edit') }}
                                            </a>

                                            <form method="POST" action="{{ route('admin.order.payment_methods.destroy', $paymentMethod) }}" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">{{ __('No data found.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $paymentMethods->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
