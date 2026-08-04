@extends('admin.layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @if(session('success'))
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-check-double-line me-3 align-middle"></i>
                            {{ session('success') }}
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="{{ __('Close') }}"
                            ></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle"></i>
                            {{ session('error') }}
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="{{ __('Close') }}"
                            ></button>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">{{ __('Blog Transfer') }}</h4>

                            <div class="d-flex gap-2">
                                <a
                                    href="{{ route('admin.transfer.index') }}"
                                    class="btn btn-light"
                                >
                                    {{ __('Back') }}
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.transfer.menus.blogs.import') }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-info"
                                        onclick="this.disabled = true; this.form.submit();"
                                    >
                                        {{ __('Start Blog Transfer') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-info alert-border-left" role="alert">
                                <i class="ri-information-line me-2 align-middle"></i>
                                {{ __('All OpenCart blog languages will be transferred in the background.') }}

                                <div class="mt-2">
                                    <span class="badge bg-primary-subtle text-primary me-1">
                                        AZ: OpenCart 3 → System 2
                                    </span>

                                    <span class="badge bg-primary-subtle text-primary me-1">
                                        EN: OpenCart 8 → System 1
                                    </span>

                                    <span class="badge bg-primary-subtle text-primary">
                                        RU: OpenCart 9 → System 3
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">
                                            {{ __('Categories') }}
                                        </div>

                                        <div class="fw-semibold">
                                            {{ $preview['category_count'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">
                                            {{ __('Blogs') }}
                                        </div>

                                        <div class="fw-semibold">
                                            {{ $preview['story_count'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted small">
                                            {{ __('Source Languages') }}
                                        </div>

                                        <div class="fw-semibold">
                                            {{ count($preview['language_mappings']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-3">{{ __('Categories Preview') }}</h5>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Category ID') }}</th>
                                        <th>{{ __('Parent ID') }}</th>
                                        <th>{{ __('Translations') }}</th>
                                        <th>{{ __('Links') }}</th>
                                        <th>{{ __('Sort') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @forelse($preview['categories'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['category_id'] }}</td>
                                            <td>{{ $item['parent_id'] }}</td>

                                            <td>
                                                @foreach(['az', 'en', 'ru'] as $locale)
                                                    <div class="mb-1">
                                                        <span class="badge bg-light text-dark text-uppercase me-1">
                                                            {{ $locale }}
                                                        </span>

                                                        {{ $item['names'][$locale] ?: '—' }}
                                                    </div>
                                                @endforeach
                                            </td>

                                            <td>
                                                @foreach(['az', 'en', 'ru'] as $locale)
                                                    <div class="mb-1">
                                                        <span class="badge bg-light text-dark text-uppercase me-1">
                                                            {{ $locale }}
                                                        </span>

                                                        {{ $item['keywords'][$locale] ?: '—' }}
                                                    </div>
                                                @endforeach
                                            </td>

                                            <td>{{ $item['sort_order'] }}</td>

                                            <td>
                                                <span class="badge {{ $item['status'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $item['status'] ? __('Active') : __('Inactive') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                {{ __('No categories found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="mb-3">{{ __('Blogs Preview') }}</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('News ID') }}</th>
                                        <th>{{ __('Translations') }}</th>
                                        <th>{{ __('Links') }}</th>
                                        <th>{{ __('Category IDs') }}</th>
                                        <th>{{ __('Related Products') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @forelse($preview['stories'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['news_id'] }}</td>

                                            <td>
                                                @foreach(['az', 'en', 'ru'] as $locale)
                                                    <div class="mb-1">
                                                        <span class="badge bg-light text-dark text-uppercase me-1">
                                                            {{ $locale }}
                                                        </span>

                                                        {{ $item['names'][$locale] ?: '—' }}
                                                    </div>
                                                @endforeach
                                            </td>

                                            <td>
                                                @foreach(['az', 'en', 'ru'] as $locale)
                                                    <div class="mb-1 text-break">
                                                        <span class="badge bg-light text-dark text-uppercase me-1">
                                                            {{ $locale }}
                                                        </span>

                                                        {{ $item['keywords'][$locale] ?: '—' }}
                                                    </div>
                                                @endforeach
                                            </td>

                                            <td>
                                                {{ implode(', ', $item['category_ids']) }}
                                            </td>

                                            <td>
                                                {{ $item['related_products_count'] }}
                                            </td>

                                            <td>
                                                <span class="badge {{ $item['status'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $item['status'] ? __('Active') : __('Inactive') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                {{ __('No blogs found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
