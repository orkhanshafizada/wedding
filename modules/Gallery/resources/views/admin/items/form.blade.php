@php
    $item = $item ?? null;
    $isEdit = $isEdit ?? false;
    $translationData = $translationData ?? [];
@endphp

<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <a href="{{ route('admin.gallery.items.index', [$menu, $album]) }}" class="btn btn-soft-secondary btn-sm">
                        <i class="ri-arrow-left-line align-bottom"></i>
                        <span>{{ __('Back to items') }}</span>
                    </a>
                    <span class="badge bg-primary-subtle text-primary">
                        {{ str_replace('_', ' ', ucfirst($menuType)) }}
                    </span>
                </div>
                <h4 class="mb-1">{{ $pageTitle }} — {{ $album->name }}</h4>
                <p class="text-muted mb-0">{{ __('Manage media and translations from a single form.') }}</p>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-semibold mb-2">{{ __('Please fix the validation errors below.') }}</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <input type="hidden" name="is_active" id="is_active_input" value="{{ old('is_active', $item?->is_active ?? 1) ? 1 : 0 }}">

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">{{ $uploadMeta['section_title'] }}</h5>
                        </div>
                        <div class="card-body">
                            @if($menuType === 'files')
                                <div class="mb-4">
                                    <div class="form-check form-switch form-switch-md">
                                        <input type="hidden" name="publication" value="0">
                                        <input
                                            class="form-check-input @error('publication') is-invalid @enderror"
                                            type="checkbox"
                                            id="publication"
                                            name="publication"
                                            value="1"
                                            {{ old('publication', $item?->publication ?? false) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label fw-medium" for="publication">
                                            {{ __('Publication') }}
                                        </label>
                                    </div>
                                    @error('publication')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="mb-0">
                                <label for="file" class="form-label">
                                    {{ $uploadMeta['file_label'] }}
                                    @if(! $isEdit)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input
                                    type="file"
                                    id="file"
                                    name="file"
                                    class="form-control @error('file') is-invalid @enderror"
                                    accept="{{ $uploadMeta['file_accept'] }}"
                                    {{ $isEdit ? '' : 'required' }}
                                >
                                <div class="form-text">{{ $uploadMeta['help_text'] }}</div>
                                @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($isEdit && $item && $item->file_path)
                                <div class="mt-4 border rounded-3 p-3 bg-light-subtle">
                                    <div class="fw-semibold mb-3">{{ __('Current file') }}</div>

                                    @if($menuType === 'photo_gallery')
                                        <img
                                            src="{{ asset('storage/' . $item->file_path) }}"
                                            alt="{{ $item->title }}"
                                            class="img-fluid rounded border"
                                            style="max-height: 320px; object-fit: contain;"
                                        >
                                    @elseif($menuType === 'video_gallery')
                                        <video
                                            controls
                                            preload="metadata"
                                            class="w-100 rounded border"
                                            style="max-height: 320px;"
                                        >
                                            <source src="{{ asset('storage/' . $item->file_path) }}">
                                        </video>
                                    @elseif($menuType === 'files')
                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="ri-download-2-line align-bottom"></i>
                                                <span>{{ __('Open current file') }}</span>
                                            </a>
                                            <span class="text-muted small">{{ basename($item->file_path) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">{{ __('Translations') }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                @foreach($languages as $index => $language)
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                            id="translation-tab-{{ $language->code }}"
                                            data-bs-toggle="tab"
                                            data-bs-target="#translation-pane-{{ $language->code }}"
                                            type="button"
                                            role="tab"
                                        >
                                            {{ $language->native_name }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content pt-4">
                                @foreach($languages as $index => $language)
                                    <div
                                        class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                        id="translation-pane-{{ $language->code }}"
                                        role="tabpanel"
                                    >
                                        <div class="mb-3">
                                            <label for="title_{{ $language->code }}" class="form-label">
                                                {{ __('Title') }}
                                            </label>
                                            <input
                                                type="text"
                                                id="title_{{ $language->code }}"
                                                name="title[{{ $language->code }}]"
                                                class="form-control @error("title.{$language->code}") is-invalid @enderror"
                                                value="{{ old("title.{$language->code}", $translationData[$language->code]['title'] ?? '') }}"
                                            >
                                            @error("title.{$language->code}")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label for="description_{{ $language->code }}" class="form-label">{{ __('Description') }}</label>
                                            <textarea
                                                id="description_{{ $language->code }}"
                                                name="description[{{ $language->code }}]"
                                                rows="5"
                                                class="form-control @error("description.{$language->code}") is-invalid @enderror"
                                            >{{ old("description.{$language->code}", $translationData[$language->code]['description'] ?? '') }}</textarea>
                                            @error("description.{$language->code}")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">{{ __('Publishing') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <div class="fw-semibold mb-1">{{ __('Validation source') }}</div>
                                <div class="small mb-0">{{ __('Allowed formats and maximum size are loaded from admin settings.') }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">{{ __('Status') }}</label>
                                <div class="form-check form-switch form-switch-md">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="is_active_switch"
                                        {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label fw-medium" for="is_active_switch">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                            </div>

                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="fw-semibold mb-3">{{ __('Upload rules') }}</div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ __('Allowed formats') }}</span>
                                    <span class="text-end">{{ $uploadMeta['allowed_label'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">{{ __('Maximum size') }}</span>
                                    <span>{{ $uploadMeta['max_label'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex flex-column gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line align-bottom"></i>
                                    <span>{{ $submitLabel }}</span>
                                </button>
                                <a href="{{ route('admin.gallery.items.index', [$menu, $album]) }}" class="btn btn-light">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSwitch = document.getElementById('is_active_switch');
            const statusInput = document.getElementById('is_active_input');

            if (statusSwitch && statusInput) {
                statusSwitch.addEventListener('change', function () {
                    statusInput.value = this.checked ? '1' : '0';
                });
            }
        });
    </script>
@endpush
