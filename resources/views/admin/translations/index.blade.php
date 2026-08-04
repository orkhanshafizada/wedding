@extends('admin.layouts.app')

@section('title', __('Translations'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @include('admin.shared.alerts')

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('Translations') }}</h4>
                    <p class="text-muted mb-0">{{ __('Manage translation keys and multilingual values.') }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.translations.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i>{{ __('Add translation') }}
                    </a>

                    <button
                        id="btn-sync"
                        class="btn btn-light"
                        type="button"
                        data-start-url="{{ route('admin.translations.sync.start') }}"
                        data-progress-url="{{ route('admin.translations.progress', ['token' => '___TOKEN___']) }}"
                    >
                        <i class="ri-refresh-line align-bottom me-1"></i>{{ __('Sync keys') }}
                    </button>

                    <button
                        id="btn-open-google-translate-modal"
                        class="btn btn-light"
                        type="button"
                        data-source="{{ $defaultSourceLocale }}"
                    >
                        <i class="ri-google-line align-bottom me-1"></i>{{ __('Google translate') }}
                    </button>

                    <a
                        href="{{ route('admin.translations.export', ['locale' => $locale]) }}"
                        class="btn btn-light"
                        id="btn-export"
                        data-export-url="{{ route('admin.translations.export', ['locale' => '___LOCALE___']) }}"
                    >
                        <i class="ri-file-excel-2-line align-bottom me-1"></i>{{ __('Export') }}
                    </a>

                    <button
                        class="btn btn-light"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#translationsImportModal"
                    >
                        <i class="ri-upload-2-line align-bottom me-1"></i>{{ __('Import') }}
                    </button>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-13 mb-2">{{ __('Total') }}</div>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-13 mb-2">{{ __('Translated') }}</div>
                            <h3 class="mb-0">{{ number_format($stats['translated']) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-13 mb-2">{{ __('Draft') }}</div>
                            <h3 class="mb-0">{{ number_format($stats['draft']) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-13 mb-2">{{ __('Completion') }}</div>
                            <div class="d-flex align-items-center gap-3">
                                <h3 class="mb-0">{{ $stats['completion_rate'] }}%</h3>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $stats['completion_rate'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.translations.index') }}" class="row g-3 align-items-end">
                        <div class="col-lg-3">
                            <label class="form-label">{{ __('Preview language') }}</label>
                            <select id="language_select" name="locale" class="form-select">
                                @foreach($languages as $language)
                                    <option value="{{ $language->code }}" {{ $language->code === $locale ? 'selected' : '' }}>
                                        {{ $language->name ?: strtoupper($language->code) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input
                                type="text"
                                name="q"
                                value="{{ $q }}"
                                class="form-control"
                                placeholder="{{ __('Search by key or translated value') }}"
                            >
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">{{ __('Per page') }}</label>
                            <select name="per_page" class="form-select">
                                @foreach([10, 20, 50, 100] as $limit)
                                    <option value="{{ $limit }}" {{ $perPage === $limit ? 'selected' : '' }}>{{ $limit }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-search-2-line"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>{{ __('Key') }}</th>
                            <th>{{ __('Preview') }}</th>
                            <th>{{ __('Languages') }}</th>
                            <th>{{ __('Updated') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($translations as $item)
                            <tr>
                                <td style="min-width: 260px;">
                                    <div class="fw-semibold">{{ $item->key }}</div>
                                </td>
                                <td style="min-width: 320px;">
                                    <div class="text-muted" style="white-space: normal;">
                                        {{ \Illuminate\Support\Str::limit((string) $item->preview_value, 160) ?: '—' }}
                                    </div>
                                </td>
                                <td style="min-width: 180px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-body">{{ (int) $item->translated_count }}/{{ (int) $item->locale_count }}</span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div
                                                class="progress-bar"
                                                role="progressbar"
                                                style="width: {{ (int) $item->locale_count > 0 ? (int) round(((int) $item->translated_count / (int) $item->locale_count) * 100) : 0 }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="min-width: 150px;">
                                    {{ \Carbon\Carbon::parse($item->updated_at)->format('d.m.Y H:i') }}
                                </td>
                                <td class="text-end" style="min-width: 150px;">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.translations.edit', $item->id) }}" class="btn btn-sm btn-light">
                                            <i class="ri-pencil-line"></i>
                                        </a>

                                        <form action="{{ route('admin.translations.destroy', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this translation group?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger">
                                                <i class="ri-delete-bin-6-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">{{ __('No translations found.') }}</div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($translations->hasPages())
                    <div class="card-footer bg-white">
                        {{ $translations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="translationsImportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form
                class="modal-content border-0 shadow"
                id="translations-import-form"
                enctype="multipart/form-data"
                data-start-url="{{ route('admin.translations.import.start') }}"
                data-progress-url="{{ route('admin.translations.progress', ['token' => '___TOKEN___']) }}"
            >
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Import Excel') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Excel file') }}</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx" required>
                    </div>

                    <div>
                        <label class="form-label">{{ __('Mode') }}</label>
                        <select name="mode" class="form-select">
                            <option value="upsert">{{ __('Upsert') }}</option>
                            <option value="only_empty">{{ __('Only empty') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Import') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="googleTranslateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form
                class="modal-content border-0 shadow"
                id="google-translate-form"
                data-start-url="{{ route('admin.translations.auto-translate-google.start') }}"
                data-progress-url="{{ route('admin.translations.progress', ['token' => '___TOKEN___']) }}"
                data-default-source="{{ $defaultSourceLocale }}"
            >
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Google Translate') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Source language') }}</label>
                            <select name="source" class="form-select">
                                @foreach($languages as $language)
                                    <option value="{{ $language->code }}" {{ $language->code === $defaultSourceLocale ? 'selected' : '' }}>
                                        {{ $language->name ?: strtoupper($language->code) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('Target language') }}</label>
                            <select name="target" class="form-select" required>
                                @foreach($languages as $language)
                                    <option value="{{ $language->code }}" {{ $language->code === $locale ? 'selected' : '' }}>
                                        {{ $language->name ?: strtoupper($language->code) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('Only empty values for the selected target language will be translated.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-google-line align-bottom me-1"></i>{{ __('Start translation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="operationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <h5 class="mb-1" id="opTitle">{{ __('Processing') }}</h5>
                        <p class="text-muted mb-0" id="opSub">{{ __('Please keep this page open.') }}</p>
                    </div>

                    <div class="progress" style="height: 8px;">
                        <div
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            id="opBar"
                            role="progressbar"
                            style="width: 0%"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                        ></div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted fs-13" id="opHint">{{ __('Preparing...') }}</span>
                        <span class="fw-semibold fs-13"><span id="opPct">0</span>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/assets/js/pages/translations-index.js') }}"></script>
@endpush
