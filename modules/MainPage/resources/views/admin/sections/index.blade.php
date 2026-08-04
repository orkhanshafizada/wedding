@extends('admin.layouts.app')
@section('title', __('Main Page Sections'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-sm-0">{{ __('Main Page Sections') }}</h4>
                <a href="{{ route('admin.main_page.sections.create') }}" class="btn btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> {{ __('New Section') }}
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
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Menu Type') }}</th>
                                <th>{{ __('Limit') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody id="sortable-sections">
                            @forelse($sections as $section)
                                @php
                                    $translation = $section->translations->firstWhere('language.code', $adminLang) ?? $section->translations->first();
                                @endphp
                                <tr data-id="{{ $section->id }}" style="cursor: move;">
                                    <td class="drag-handle text-center">
                                        <i class="ri-drag-move-2-fill text-muted"></i>
                                    </td>
                                    <td>{{ $section->id }}</td>
                                    <td>{{ $translation?->title ?? '-' }}</td>
                                    <td>{{ \Modules\MainPage\Enums\MainPageSectionSourceType::tryFrom($section->source_type)?->label() ?? $section->source_type }}</td>
                                    <td>{{ $section->source_reference ?: '-' }}</td>
                                    <td>{{ $section->menu_type ?: '-' }}</td>
                                    <td>{{ $section->limit ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ \App\Enums\StatusEnum::getBadgeClass($section->status) }}">
                                            {{ \App\Enums\StatusEnum::getLabel($section->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.main_page.sections.edit', $section) }}" class="btn btn-sm btn-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.main_page.sections.destroy', $section) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('{{ __('Section silinsin?') }}')">
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
                                    <td colspan="9" class="text-center text-muted py-4">{{ __('No sections found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($sections->hasPages())
                        <div class="mt-3">
                            {{ $sections->links() }}
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
            const sortableList = document.getElementById('sortable-sections');

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

                    fetch('{{ route('admin.main_page.sections.update-order') }}', {
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
