@extends('admin.layouts.app')

@section('title', __('Order Statuses'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.order.order_statuses.index') }}">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $filters['q'] }}"
                                    class="form-control"
                                    placeholder="{{ __('Order status name') }}"
                                >
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="is_active" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}" @selected((string) $filters['is_active'] === (string) $key)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                                    <a href="{{ route('admin.order.order_statuses.index') }}" class="btn btn-light w-100">{{ __('Reset') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ __('Order Statuses') }}</h5>

                    @can('order.status.create')
                        <a href="{{ route('admin.order.order_statuses.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> {{ __('New Order Status') }}
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                            <tr>
                                <th width="80">ID</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Mail Template Key') }}</th>
                                <th width="120">{{ __('Translations') }}</th>
                                <th width="180">{{ __('Status') }}</th>
                                <th width="220">{{ __('Send Email') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($orderStatuses as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td class="fw-medium">{{ $item->translation?->name ?? $item->translations->first()?->name ?? '-' }}</td>
                                    <td><code>{{ $item->mail_template_key ?? '-' }}</code></td>
                                    <td>{{ (int) $item->translations_count }}</td>
                                    <td>
                                        @can('order.status.edit')
                                            <div class="form-check form-switch form-switch-md">
                                                <input
                                                    class="form-check-input js-toggle-order-status-active"
                                                    type="checkbox"
                                                    data-url="{{ route('admin.order.order_statuses.toggle-active', $item) }}"
                                                    @checked($item->is_active)
                                                >
                                                <label class="form-check-label">
                                                    {{ $item->is_active ? __('Active') : __('Passive') }}
                                                </label>
                                            </div>
                                        @else
                                            @if($item->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Passive') }}</span>
                                            @endif
                                        @endcan
                                    </td>
                                    <td>
                                        @can('order.status.edit')
                                            <div class="form-check form-switch form-switch-md">
                                                <input
                                                    class="form-check-input js-toggle-order-status-email"
                                                    type="checkbox"
                                                    data-url="{{ route('admin.order.order_statuses.toggle-send-email', $item) }}"
                                                    @checked($item->send_email)
                                                >
                                                <label class="form-check-label">
                                                    {{ $item->send_email ? __('Enabled') : __('Disabled') }}
                                                </label>
                                            </div>
                                        @else
                                            @if($item->send_email)
                                                <span class="badge bg-primary">{{ __('Enabled') }}</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ __('Disabled') }}</span>
                                            @endif
                                        @endcan
                                    </td>
                                    <td class="text-end">
                                        @can('order.status.edit')
                                            <a href="{{ route('admin.order.order_statuses.edit', $item) }}" class="btn btn-sm btn-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        @endcan

                                        @can('order.status.delete')
                                            <form
                                                action="{{ route('admin.order.order_statuses.destroy', $item) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}')"
                                            >
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
                                        {{ __('No order statuses found.') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($orderStatuses->hasPages())
                        <div class="mt-3">
                            {{ $orderStatuses->links() }}
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const updateSwitchLabel = function (input, enabledText, disabledText) {
                const label = input.closest('.form-check')?.querySelector('.form-check-label');

                if (!label) {
                    return;
                }

                label.textContent = input.checked ? enabledText : disabledText;
            };

            const bindSwitch = function (selector, payloadKey, enabledText, disabledText) {
                document.querySelectorAll(selector).forEach(function (input) {
                    input.addEventListener('change', function () {
                        const checked = input.checked;
                        const previous = !checked;

                        fetch(input.dataset.url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                [payloadKey]: checked ? 1 : 0,
                            })
                        })
                            .then(async function (response) {
                                const data = await response.json();

                                if (!response.ok || !data.success) {
                                    throw new Error(data.message || 'Request failed.');
                                }

                                updateSwitchLabel(input, enabledText, disabledText);
                            })
                            .catch(function () {
                                input.checked = previous;
                                updateSwitchLabel(input, enabledText, disabledText);
                            });
                    });

                    updateSwitchLabel(input, enabledText, disabledText);
                });
            };

            bindSwitch('.js-toggle-order-status-active', 'is_active', '{{ __('Active') }}', '{{ __('Passive') }}');
            bindSwitch('.js-toggle-order-status-email', 'send_email', '{{ __('Enabled') }}', '{{ __('Disabled') }}');
        });
    </script>
@endpush
