@php
    $translationsByLanguage = $orderStatus->translations->keyBy('language_id');
    $requiredLanguageIdsArray = isset($requiredLanguageIds)
        ? collect($requiredLanguageIds)->map(static fn ($id) => (int) $id)->all()
        : [];
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('Status') }}</label>
        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected((string) old('is_active', (int) $orderStatus->is_active) === (string) $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('is_active')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Send Email') }}</label>
        <select name="send_email" class="form-select @error('send_email') is-invalid @enderror">
            @foreach($mailOptions as $key => $label)
                <option value="{{ $key }}" @selected((string) old('send_email', (int) $orderStatus->send_email) === (string) $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('send_email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('Mail Template Key') }}</label>
        <input
            type="text"
            name="mail_template_key"
            value="{{ old('mail_template_key', $orderStatus->mail_template_key) }}"
            class="form-control @error('mail_template_key') is-invalid @enderror"
            placeholder="{{ __('order_status_updated') }}"
        >
        @error('mail_template_key')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="card border shadow-none mb-0">
            <div class="card-header bg-light">
                <h5 class="mb-0">{{ __('Translations') }}</h5>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                    @foreach($languages as $index => $language)
                        @php
                            $isRequired = in_array((int) $language->id, $requiredLanguageIdsArray, true);
                            $isActiveTab = $index === 0;
                        @endphp

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $isActiveTab ? 'active' : '' }}"
                                id="order-status-language-tab-{{ $language->id }}"
                                data-bs-toggle="tab"
                                data-bs-target="#order-status-language-pane-{{ $language->id }}"
                                type="button"
                                role="tab"
                                aria-controls="order-status-language-pane-{{ $language->id }}"
                                aria-selected="{{ $isActiveTab ? 'true' : 'false' }}"
                            >
                                {{ $language->name }}
                                @if($isRequired)
                                    <span class="text-danger">*</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content text-muted">
                    @foreach($languages as $index => $language)
                        @php
                            $translation = $translationsByLanguage->get($language->id);
                            $oldTranslation = old('translations.' . $index . '.name');
                            $value = $oldTranslation ?? $translation?->name ?? '';
                            $isRequired = in_array((int) $language->id, $requiredLanguageIdsArray, true);
                            $isActiveTab = $index === 0;
                        @endphp

                        <div
                            class="tab-pane fade {{ $isActiveTab ? 'show active' : '' }}"
                            id="order-status-language-pane-{{ $language->id }}"
                            role="tabpanel"
                            aria-labelledby="order-status-language-tab-{{ $language->id }}"
                            tabindex="0"
                        >
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <input type="hidden" name="translations[{{ $index }}][language_id]" value="{{ $language->id }}">

                                    <label class="form-label">
                                        {{ __('Name') }}
                                        @if($isRequired)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    <input
                                        type="text"
                                        name="translations[{{ $index }}][name]"
                                        value="{{ $value }}"
                                        class="form-control @error('translations.' . $index . '.name') is-invalid @enderror"
                                        placeholder="{{ __('Order status name') }}"
                                    >

                                    @error('translations.' . $index . '.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
    <a href="{{ route('admin.order.order_statuses.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
</div>
