@extends('admin.layouts.app')

@section('title', 'Delivery Prices')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.order.delivery_prices.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="{{ __('Name') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('Parent Delivery Price') }}</label>
                                <select name="parent_id" class="form-select">
                                    <option value="-1">{{ __('All') }}</option>
                                    <option value="0" @selected((int) $filters['parent_id'] === 0)>{{ __('Main') }}</option>
                                    @foreach($parentOptions as $parentOption)
                                        <option value="{{ $parentOption->id }}" @selected((int) $filters['parent_id'] === (int) $parentOption->id)>
                                            {{ $parentOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                                    <a href="{{ route('admin.order.delivery_prices.index') }}" class="btn btn-light w-100">{{ __('Reset') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ __('Delivery Prices') }}</h5>
                    @can('order.delivery_price.create')
                        <a href="{{ route('admin.order.delivery_prices.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> {{ __('New Delivery Price') }}
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <i class="ri-drag-move-line"></i>
                                </th>
                                <th>ID</th>
                                <th>{{ __('Parent') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th width="160">{{ __('Price') }}</th>
                                <th width="120">{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>

                            <tbody id="sortable-delivery-prices">
                            @forelse($deliveryPrices as $item)
                                <tr data-id="{{ $item->id }}" style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        @if((int) $item->parent_id === 0)
                                            <span class="badge bg-info-subtle text-info">{{ __('Main') }}</span>
                                        @else
                                            <span>{{ $item->parent?->name ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-medium">{{ $item->name }}</td>
                                    <td>{{ number_format((float) $item->price, 2) }}</td>
                                    <td>
                                        @if($item->status === 'Active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Passive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('order.delivery_price.edit')
                                            <a href="{{ route('admin.order.delivery_prices.edit', $item) }}" class="btn btn-sm btn-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        @endcan

                                        @can('order.delivery_price.delete')
                                            <form action="{{ route('admin.order.delivery_prices.destroy', $item) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Silinsin?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ __('No delivery prices found') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($deliveryPrices->hasPages())
                        <div class="mt-3">
                            {{ $deliveryPrices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sortableList = document.getElementById('sortable-delivery-prices');

            if (!sortableList) {
                return;
            }

            new Sortable(sortableList, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function () {
                    const rows = sortableList.querySelectorAll('tr[data-id]');
                    const order = Array.from(rows).map((row, index) => ({
                        id: row.getAttribute('data-id'),
                        sort_order: index
                    }));

                    fetch('{{ route('admin.order.delivery_prices.update-order') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ order: order })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                console.error('Order update failed');
                            }
                        })
                        .catch(error => console.error(error));
                }
            });
        });
    </script>
@endpush
