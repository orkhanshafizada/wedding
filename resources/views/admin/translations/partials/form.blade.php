<div class="row g-4">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="mb-4">
                    <label for="key" class="form-label">{{ __('Key') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control @error('key') is-invalid @enderror"
                            id="key"
                            name="key"
                            value="{{ old('key', $translation->key) }}"
                            placeholder="{{ __('Example: The selected file extension is not supported: :name') }}"
                            required
                        >
                        <button type="button" class="btn btn-light" id="normalize-key-btn">{{ __('Normalize') }}</button>
                    </div>
                    @error('key')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        {{ __('Normalization only cleans spacing and keeps placeholders such as :name, :attribute, :ext unchanged.') }}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-muted fs-13 mb-2">{{ __('Preview') }}</div>
                    <div class="border rounded p-3 bg-light-subtle" id="key-preview" style="min-height: 72px; white-space: pre-wrap;">
                        {{ old('key', $translation->key) ?: '—' }}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-muted fs-13 mb-2">{{ __('Languages') }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($languages as $language)
                            @php
                                $currentValue = old('translations.' . $language->code . '.value', optional($group->get($language->code))->value);
                                $filled = filled($currentValue);
                            @endphp
                            <span class="badge {{ $filled ? 'bg-success-subtle text-success' : 'bg-light text-body' }}">
                                {{ strtoupper($language->code) }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-light" id="open-form-google-translate-modal">
                        <i class="ri-google-line align-bottom me-1"></i>{{ __('Google translate values') }}
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-bottom me-1"></i>{{ __('Save') }}
                    </button>

                    <a href="{{ route('admin.translations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <ul class="nav nav-pills gap-2" role="tablist">
                    @foreach($languages as $index => $language)
                        @php
                            $currentValue = old('translations.' . $language->code . '.value', optional($group->get($language->code))->value);
                            $filled = filled($currentValue);
                        @endphp

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                id="translation-tab-{{ $language->id }}"
                                data-bs-toggle="tab"
                                data-bs-target="#translation-pane-{{ $language->id }}"
                                type="button"
                                role="tab"
                                aria-controls="translation-pane-{{ $language->id }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            >
                                {{ strtoupper($language->code) }}
                                <span class="ms-1 badge {{ $filled ? 'bg-success-subtle text-success' : 'bg-light text-body' }}">
                                    {{ $filled ? __('Done') : __('Draft') }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    @foreach($languages as $index => $language)
                        @php
                            $currentValue = old('translations.' . $language->code . '.value', optional($group->get($language->code))->value);
                        @endphp

                        <div
                            class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                            id="translation-pane-{{ $language->id }}"
                            role="tabpanel"
                            aria-labelledby="translation-tab-{{ $language->id }}"
                        >
                            <div class="mb-3">
                                <label class="form-label">{{ __('Language') }}</label>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <input type="text" class="form-control" value="{{ $language->name ?: strtoupper($language->code) }}" readonly>
                                    <button
                                        type="button"
                                        class="btn btn-light btn-sm js-translate-single-language"
                                        data-target="{{ $language->code }}"
                                    >
                                        <i class="ri-google-line align-bottom me-1"></i>{{ __('Translate') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">{{ __('Value') }} ({{ strtoupper($language->code) }})</label>
                                <textarea
                                    class="form-control js-translation-value @error('translations.' . $language->code . '.value') is-invalid @enderror"
                                    name="translations[{{ $language->code }}][value]"
                                    rows="12"
                                    data-locale="{{ $language->code }}"
                                    placeholder="{{ __('Enter translation text') }}"
                                >{{ $currentValue }}</textarea>

                                @error('translations.' . $language->code . '.value')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="formGoogleTranslateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Google Translate Values') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('Source language') }}</label>
                        <select id="form-translate-source" class="form-select">
                            @foreach($languages as $language)
                                <option value="{{ $language->code }}" {{ $language->code === $defaultSourceLocale ? 'selected' : '' }}>
                                    {{ $language->name ?: strtoupper($language->code) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ __('Target language') }}</label>
                        <select id="form-translate-target" class="form-select">
                            @foreach($languages as $language)
                                <option value="{{ $language->code }}">
                                    {{ $language->name ?: strtoupper($language->code) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="hidden" id="form-translate-mode" value="single">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="run-form-google-translate">
                    <i class="ri-google-line align-bottom me-1"></i>{{ __('Translate') }}
                </button>
            </div>
        </div>
    </div>
</div>
