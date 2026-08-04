@extends('admin.layouts.app')

@section('title', __('Logos Partners'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">{{ __('Logos Partners') }} - {{ $menu->name }}</h5>
                    <div>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary me-2">
                            <i class="ri-arrow-left-line me-1"></i> {{ __('Back to Menus') }}
                        </a>
                        <a href="{{ route('admin.logospartners.create', $menu) }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> {{ __('Add New') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th style="width: 30px;"></th>
                                <th>#</th>
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-logospartners">
                            @forelse ($logosPartners as $logosPartner)
                                <tr data-id="{{ $logosPartner->id }}">
                                    <td>
                                        <i class="ri-drag-move-fill drag-handle" style="cursor: move; font-size: 1.2rem; color: #999;"></i>
                                    </td>
                                    <td>{{ $logosPartner->id }}</td>
                                    <td>
                                        @if($logosPartner->image)
                                            <img src="{{ asset('storage/' . $logosPartner->image) }}"
                                                 alt="{{ json_decode($logosPartner->getRawOriginal('name'), true)['az'] ?? '' }}"
                                                 style="width: 60px; height: 60px; object-fit: contain;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px;">
                                                <i class="ri-image-line text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            {{ json_decode($logosPartner->getRawOriginal('name'), true)[$adminLang ?? 'az'] ?? '-' }}
                                        </div>
                                        @php
                                            $link = json_decode($logosPartner->getRawOriginal('link'), true)[$adminLang ?? 'az'] ?? null;
                                        @endphp
                                        @if($link)
                                            <small class="text-muted">
                                                <i class="ri-link"></i>
                                                <a href="{{ $link }}" target="_blank">{{ Str::limit($link, 40) }}</a>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ \App\Enums\StatusEnum::getBadgeClass($logosPartner->is_active) }}">
                                            {{ \App\Enums\StatusEnum::getLabel($logosPartner->is_active) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('admin.logospartners.edit', [$menu, $logosPartner]) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.logospartners.destroy', [$menu, $logosPartner]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        {{ __('No records found') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($logosPartners->hasPages())
                        <div class="mt-3">
                            {{ $logosPartners->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortableList = document.getElementById('sortable-logospartners');

    if (sortableList) {
        new Sortable(sortableList, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                const rows = sortableList.querySelectorAll('tr[data-id]');
                const order = Array.from(rows).map((row, index) => ({
                    id: row.getAttribute('data-id'),
                    sort_order: index
                }));

                // AJAX request to update order
                fetch('{{ route("admin.logospartners.update-order", $menu) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Order updated successfully');
                    }
                })
                .catch(error => {
                    console.error('Error updating order:', error);
                });
            }
        });
    }
});
</script>
@endpush
