<div class="row mb-3">
    <label class="col-sm-3 col-form-label">
        {{ __('Position') }} <span class="text-danger">*</span>
    </label>
    <div class="col-sm-9">
        <select name="position" class="form-control" required>
            <option value="">{{ __('Select Position') }}</option>
            @foreach($positionOptions as $value => $label)
                <option value="{{ $value }}" {{ old('position', $banner?->position) == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('position')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">{{ __('Status') }}</label>
    <div class="col-sm-9">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
        </div>
    </div>
</div>

<hr class="my-4">

<ul class="nav nav-tabs" role="tablist">
    @foreach($languages as $index => $language)
        @php
            $translationIndex = old('translations')
                ? collect(old('translations', []))->search(function ($item) use ($language) {
                    return ($item['locale'] ?? null) === $language->code;
                })
                : false;

            $translation = !empty($banner) ? $banner->translations->firstWhere('locale', $language->code) : null;
        @endphp

        <li class="nav-item" role="presentation">
            <a class="nav-link @if($index === 0) active @endif" data-bs-toggle="tab" href="#lang-{{ $language->code }}" role="tab">
                <span>{{ $language->native_name ?: $language->name }}</span>
                @if($requiredLanguageCodes->contains($language->code))
                    <span class="text-danger">*</span>
                @endif
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content pt-3">
    @foreach($languages as $index => $language)
        @php
            $translationIndex = old('translations')
                ? collect(old('translations', []))->search(function ($item) use ($language) {
                    return ($item['locale'] ?? null) === $language->code;
                })
                : false;

            $translationIndex = $translationIndex !== false ? $translationIndex : $loop->index;
            $translation = !empty($banner) ? $banner->translations->firstWhere('locale', $language->code) : null;
        @endphp

        <div class="tab-pane fade @if($index === 0) show active @endif" id="lang-{{ $language->code }}" role="tabpanel">
            <input type="hidden" name="translations[{{ $translationIndex }}][locale]" value="{{ $language->code }}">

            @if(!empty($banner))
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Current Image') }}</label>
                    <div class="col-sm-9">
                        @if($translation?->image)
                            <img src="{{ Storage::disk('public')->url($translation->image) }}" alt="Banner" style="max-width: 400px; height: auto; border-radius: 4px;">
                        @else
                            <p class="text-muted">{{ __('No image') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">
                    {{ __('Image') }}
                    @if($requiredLanguageCodes->contains($language->code))
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <div class="col-sm-9">
                    <input type="file" name="translations[{{ $translationIndex }}][image]" class="form-control" accept="image/*">
                    @error('translations.' . $translationIndex . '.image')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            @if(!empty($banner))
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Current Mobile Image') }}</label>
                    <div class="col-sm-9">
                        @if($translation?->mobile_image)
                            <img src="{{ Storage::disk('public')->url($translation->mobile_image) }}" alt="Banner Mobile" style="max-width: 260px; height: auto; border-radius: 4px;">
                        @else
                            <p class="text-muted">{{ __('No mobile image') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Mobile Image') }}</label>
                <div class="col-sm-9">
                    <input type="file" name="translations[{{ $translationIndex }}][mobile_image]" class="form-control" accept="image/*">
                    @error('translations.' . $translationIndex . '.mobile_image')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Title') }}</label>
                <div class="col-sm-9">
                    <input type="text" name="translations[{{ $translationIndex }}][title]" class="form-control" value="{{ old('translations.' . $translationIndex . '.title', $translation?->title) }}">
                    @error('translations.' . $translationIndex . '.title')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Description') }}</label>
                <div class="col-sm-9">
                    <textarea name="translations[{{ $translationIndex }}][description]" class="form-control js-editor" rows="3">{{ old('translations.' . $translationIndex . '.description', $translation?->description) }}</textarea>
                    @error('translations.' . $translationIndex . '.description')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">
                    {{ __('Link') }}
                    @if($requiredLanguageCodes->contains($language->code))
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <div class="col-sm-9">
                    <input type="text" name="translations[{{ $translationIndex }}][link]" class="form-control" value="{{ old('translations.' . $translationIndex . '.link', $translation?->link) }}" placeholder="https://example.com">
                    @error('translations.' . $translationIndex . '.link')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Hide Text For Mobile') }}</label>
                <div class="col-sm-9">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="translations[{{ $translationIndex }}][hide_text_mobile]" value="1" id="hide_text_mobile_{{ $language->code }}" {{ old('translations.' . $translationIndex . '.hide_text_mobile', (int) ($translation?->hide_text_mobile ?? 0)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="hide_text_mobile_{{ $language->code }}">{{ __('Yes') }}</label>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-primary">{{ !empty($banner) ? __('Update') : __('Create') }}</button>
        <a href="{{ route('admin.banner.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
    </div>
</div>
