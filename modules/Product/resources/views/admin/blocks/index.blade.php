@extends('admin.layouts.app')
@section('title', __('Product Blocks'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('Product Blocks') }}</h4>
                <a href="{{ route('admin.product.blocks.create') }}" class="btn btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> {{ __('New Block') }}
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
                                <th width="50"><i class="ri-drag-move-line"></i></th>
                                <th>ID</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Categories') }}</th>
                                <th>{{ __('Brands') }}</th>
                                <th>{{ __('Variations') }}</th>
                                <th>{{ __('Only Discount') }}</th>
                                <th>{{ __('Only New') }}</th>
                                <th>{{ __('Best Sellers') }}</th>
                                <th>{{ __('Limit') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-blocks">
                            @forelse($blocks as $block)
                                @php
                                    $adminLanguageCode = app()->getLocale();
                                    $translation = $block->translations->firstWhere('language.code', $adminLanguageCode) ?? $block->translations->first();
                                @endphp
                                <tr data-id="{{ $block->id }}" style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>
                                    <td>{{ $block->id }}</td>
                                    <td>{{ $translation?->title ?? '-' }}</td>
                                    <td>
                                        @if($block->category_scope === 'all')
                                            <span class="badge bg-primary">{{ __('All') }}</span>
                                        @else
                                            <span class="badge bg-info">{{ $block->selectedCategories->count() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($block->brand_scope === 'all')
                                            <span class="badge bg-primary">{{ __('All') }}</span>
                                        @else
                                            <span class="badge bg-info">{{ $block->selectedBrands->count() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($block->product_scope === 'all')
                                            <span class="badge bg-primary">{{ __('All') }}</span>
                                        @else
                                            <span class="badge bg-info">{{ $block->selectedProducts->count() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $block->only_discount_products ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                            {{ $block->only_discount_products ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $block->only_new_products ? 'bg-info text-dark' : 'bg-secondary' }}">
                                            {{ $block->only_new_products ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $block->best_seller_products ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $block->best_seller_products ? __('Yes') : __('No') }}
                                        </span>
                                    </td>
                                    <td>{{ $block->limit }}</td>
                                    <td>
                                        <span class="badge {{ \App\Enums\StatusEnum::getBadgeClass($block->status) }}">
                                            {{ \App\Enums\StatusEnum::getLabel($block->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.product.blocks.edit', $block) }}" class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.product.blocks.destroy', $block) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('{{ __('Delete this block?') }}')">
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
                                    <td colspan="12" class="text-center text-muted py-4">{{ __('No blocks found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($blocks->hasPages())
                        <div class="mt-3">
                            {{ $blocks->links() }}
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
            const sortableList = document.getElementById('sortable-blocks');

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

                    fetch('{{ route('admin.product.blocks.update-order') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ order })
                    });
                }
            });
        });
    </script>
@endpush
