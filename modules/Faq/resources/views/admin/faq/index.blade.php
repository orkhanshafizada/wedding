@extends('admin.layouts.app')

@section('title', 'FAQ - Tez-tez Verilən Suallar')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">FAQ - {{ $menu->name }}</h5>
                    <div>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary me-2">
                            <i class="ri-arrow-left-line me-1"></i> Menulara qayıt
                        </a>
                        <a href="{{ route('admin.faq.create', $menu) }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Yeni FAQ
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
                                <th>Sual</th>
                                <th>Status</th>
                                <th class="text-end">Əməliyyatlar</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-faqs">
                            @forelse ($faqs as $faq)
                                <tr data-id="{{ $faq->id }}">
                                    <td>
                                        <i class="ri-drag-move-fill drag-handle" style="cursor: move; font-size: 1.2rem; color: #999;"></i>
                                    </td>
                                    <td>{{ $faq->id }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $faq->getTranslatedQuestion($adminLang ?? 'az') }}</div>
                                        <small class="text-muted">{{ Str::limit($faq->getTranslatedAnswer($adminLang ?? 'az'), 80) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ \App\Enums\StatusEnum::getBadgeClass($faq->is_active) }}">
                                            {{ \App\Enums\StatusEnum::getLabel($faq->is_active) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('admin.faq.edit', [$menu, $faq]) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.faq.destroy', [$menu, $faq]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('FAQ silinsin?')">
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
                                        Heç bir FAQ tapılmadı
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($faqs->hasPages())
                        <div class="mt-3">
                            {{ $faqs->links() }}
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
    const sortableList = document.getElementById('sortable-faqs');

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
                fetch('{{ route("admin.faq.update-order", $menu) }}', {
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

