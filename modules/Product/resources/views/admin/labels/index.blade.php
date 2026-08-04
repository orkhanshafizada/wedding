@extends('admin.layouts.app')
@section('title',__('Product Labels'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{__('Product Labels')}}</h4>
                <a href="{{ route('admin.product.labels.create') }}" class="btn btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> {{__('New Label')}}
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <i class="ri-drag-move-line"></i>
                                </th>
                                <th>ID</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Status')}}</th>
                                <th class="text-end">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-labels">
                            @forelse($labels as $label)
                                @php
                                    $translation = $label->translations->firstWhere('language.code', $adminLang)
                                                ?? $label->translations->first();
                                @endphp
                                <tr data-id="{{ $label->id }}" style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>
                                    <td>{{ $label->id }}</td>
                                    <td>{{ $translation?->name ?? 'No translation' }}</td>
                                    <td>
                                        <span class="badge {{ \App\Enums\StatusEnum::getBadgeClass($label->status) }}">
                                            {{ \App\Enums\StatusEnum::getLabel($label->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.product.labels.edit', $label) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.product.labels.destroy', $label) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Label silinsin?')">
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
                                    <td colspan="5" class="text-center text-muted py-4">
                                        {{__('No labels found')}}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($labels->hasPages())
                        <div class="mt-3">
                            {{ $labels->links() }}
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
    const sortableList = document.getElementById('sortable-labels');

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
                fetch('{{ route("admin.product.labels.update-order") }}', {
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

