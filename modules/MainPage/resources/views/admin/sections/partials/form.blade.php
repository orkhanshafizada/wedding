<div class="row mb-4">
    <div class="col-12">
        <label class="form-label">{{ __('Section Title') }} <span class="text-danger">*</span></label>

        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
            @foreach($languages as $index => $language)
                <li class="nav-item" role="presentation">
                    <a href="#title-tab-{{ $language->code }}"
                       class="nav-link {{ $index === 0 ? 'active' : '' }}"
                       data-bs-toggle="tab"
                       role="tab">
                        <span class="fw-semibold">
                            {{ strtoupper($language->code) }}
                            @if((int) ($language->is_required ?? 0) === 1)
                                <span class="text-danger">*</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($languages as $index => $language)
                @php
                    $translation = isset($section) ? $section->translations->firstWhere('language_id', $language->id) : null;
                @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="title-tab-{{ $language->code }}" role="tabpanel">
                    <input type="text"
                           name="title[{{ $language->id }}]"
                           class="form-control @error('title.' . $language->id) is-invalid @enderror"
                           value="{{ old('title.' . $language->id, $translation?->title) }}"
                           placeholder="{{ strtoupper($language->code) }} {{ __('title') }}">
                    @error('title.' . $language->id)
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="source_type" class="form-label">{{ __('Source Type') }} <span class="text-danger">*</span></label>
        <select name="source_type" id="source_type" class="form-select @error('source_type') is-invalid @enderror" required>
            <option value="">{{ __('Choose') }}</option>
            @foreach($sourceTypeOptions as $option)
                <option value="{{ $option['value'] }}" @selected(old('source_type', $section->source_type ?? null) === $option['value'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
        @error('source_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3 menu-type-wrapper" style="display: none;">
        <label for="menu_type" class="form-label">{{ __('Menu Type') }} <span class="text-danger menu-type-required-marker d-none">*</span></label>
        <select name="menu_type" id="menu_type" class="form-select @error('menu_type') is-invalid @enderror">
            <option value="">{{ __('Choose') }}</option>
            @foreach($menuTypeOptions as $option)
                <option value="{{ $option['value'] }}" @selected(old('menu_type', $section->menu_type ?? null) === $option['value'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
        @error('menu_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3 source-reference-wrapper" style="display: none;">
        <label for="source_reference" class="form-label">{{ __('Source Reference') }} <span class="text-danger source-reference-required-marker d-none">*</span></label>
        <select name="source_reference" id="source_reference" class="form-select @error('source_reference') is-invalid @enderror">
            <option value="">{{ __('Choose') }}</option>
        </select>
        @error('source_reference')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3 menu-view-type-wrapper" style="display: none;">
        <label for="menu_view_type" class="form-label">{{ __('Menu View Type') }}</label>
        <input type="text"
               name="menu_view_type"
               id="menu_view_type"
               class="form-control @error('menu_view_type') is-invalid @enderror"
               value="{{ old('menu_view_type', $section->menu_view_type ?? null) }}"
               placeholder="{{ __('Example: Services') }}">
        @error('menu_view_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="limit" class="form-label">{{ __('Limit') }}</label>
        <input type="number"
               name="limit"
               id="limit"
               class="form-control @error('limit') is-invalid @enderror"
               value="{{ old('limit', $section->limit ?? null) }}"
               min="1"
               max="100">
        @error('limit')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(\App\Enums\StatusEnum::getOptions() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $section->status ?? \App\Enums\StatusEnum::ACTIVE) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
