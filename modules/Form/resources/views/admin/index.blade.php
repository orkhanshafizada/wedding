@extends('admin.layouts.app')

@section('title', __('Form'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('Form') }} - {{ $menu->title }}</h4>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('Back to Menus') }}
                </a>
            </div>

            {{-- Action Buttons --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('admin.form.view-data', $menu) }}" class="btn btn-primary w-100 py-3">
                                <i class="ri-database-2-line me-2"></i>
                                {{ __('View Data') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.form.text.edit', $menu) }}" class="btn btn-info w-100 py-3">
                                <i class="ri-file-text-line me-2"></i>
                                {{ __('Form Text') }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.form.label.create', $menu) }}" class="btn btn-success w-100 py-3">
                                <i class="ri-add-circle-line me-2"></i>
                                {{ __('Add New Label') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Labels List --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">{{ __('Form Labels') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <i class="ri-drag-move-line"></i>
                                </th>
                                <th>ID</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Required') }}</th>
                                <th>{{ __('Send Text Mail') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-labels">
                            @forelse ($labels as $label)
                                @php
                                    $translation = $label->translation(app()->getLocale())
                                        ?? $label->translations->first();
                                @endphp
                                <tr data-id="{{ $label->id }}" style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>
                                    <td>{{ $label->id }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $translation?->name ?? __('No translation') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ \Modules\Form\Enums\FormLabelTypeEnum::getDescription($label->type) }}</span>
                                    </td>
                                    <td>
                                        @if($label->is_required)
                                            <i class="ri-checkbox-circle-fill text-success"></i> {{ __('Yes') }}
                                        @else
                                            <i class="ri-close-circle-line text-muted"></i> {{ __('No') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($label->type === \Modules\Form\Enums\FormLabelTypeEnum::Email && $label->send_text_mail)
                                            <i class="ri-checkbox-circle-fill text-success"></i> {{ __('Yes') }}
                                        @else
                                            <i class="ri-close-circle-line text-muted"></i> {{ __('No') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.form.label.edit', [$menu, $label]) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.form.label.destroy', [$menu, $label]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this label?') }}')">
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ __('No labels found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortableList = document.getElementById('sortable-labels');

    if (sortableList) {
        new Sortable(sortableList, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                const rows = sortableList.querySelectorAll('tr[data-id]');
                const order = Array.from(rows).map((row, index) => ({
                    id: row.getAttribute('data-id'),
                    position: index
                }));

                // AJAX request to update order
                fetch('{{ route("admin.form.label.update-order", $menu) }}', {
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

