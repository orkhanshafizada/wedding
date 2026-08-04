@php
    $sliderTranslations = !empty($slider) && $slider->relationLoaded('translations')
        ? $slider->translations->keyBy('locale')
        : collect();

    $oldTranslations = collect(old('translations', []))
        ->keyBy(fn ($item) => $item['locale'] ?? null);
@endphp

<div class="row mb-3">
    <label class="col-sm-3 col-form-label">{{ __('Status') }}</label>
    <div class="col-sm-9">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch">
            <input
                class="form-check-input"
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                {{ old('is_active', $slider?->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
        </div>
    </div>
</div>

<hr class="my-4">

<ul class="nav nav-tabs" role="tablist">
    @foreach($languages as $index => $language)
        <li class="nav-item" role="presentation">
            <button
                class="nav-link {{ $index === 0 ? 'active' : '' }}"
                data-bs-toggle="tab"
                data-bs-target="#lang-{{ $language->code }}"
                type="button"
                role="tab"
            >
                {{ $language->native_name }}
                @if($requiredLanguageCodes->contains($language->code))
                    <span class="text-danger">*</span>
                @endif
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content pt-3">
    @foreach($languages as $index => $language)
        @php
            $translation = $sliderTranslations->get($language->code);
            $oldTranslation = $oldTranslations->get($language->code, []);
            $actionType = $oldTranslation['action_type'] ?? $translation?->action_type ?? 'link';
        @endphp

        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="lang-{{ $language->code }}" role="tabpanel">
            <input type="hidden" name="translations[{{ $index }}][locale]" value="{{ $language->code }}">

            @if(!empty($slider))
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Current Image') }}</label>
                    <div class="col-sm-9">
                        @if($translation?->image)
                            <img src="{{ Storage::disk('public')->url($translation->image) }}" alt="{{ $translation?->title ?? 'Slider' }}" style="max-width: 300px; height: auto; border-radius: 4px;">
                        @else
                            <p class="text-muted mb-0">{{ __('No image') }}</p>
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
                    <input
                        type="file"
                        name="translations[{{ $index }}][image]"
                        class="form-control @error('translations.' . $index . '.image') is-invalid @enderror"
                        accept="image/*"
                    >
                    @error('translations.' . $index . '.image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(!empty($slider))
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Current Mobile Image') }}</label>
                    <div class="col-sm-9">
                        @if($translation?->mobile_image)
                            <img src="{{ Storage::disk('public')->url($translation->mobile_image) }}" alt="{{ $translation?->title ?? 'Slider Mobile' }}" style="max-width: 260px; height: auto; border-radius: 4px;">
                        @else
                            <p class="text-muted mb-0">{{ __('No mobile image') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Mobile Image') }}</label>
                <div class="col-sm-9">
                    <input
                        type="file"
                        name="translations[{{ $index }}][mobile_image]"
                        class="form-control @error('translations.' . $index . '.mobile_image') is-invalid @enderror"
                        accept="image/*"
                    >
                    @error('translations.' . $index . '.mobile_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Title') }}</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        name="translations[{{ $index }}][title]"
                        class="form-control @error('translations.' . $index . '.title') is-invalid @enderror"
                        value="{{ $oldTranslation['title'] ?? $translation?->title }}"
                    >
                    @error('translations.' . $index . '.title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Button Text') }}</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        name="translations[{{ $index }}][button_text]"
                        class="form-control @error('translations.' . $index . '.button_text') is-invalid @enderror"
                        value="{{ $oldTranslation['button_text'] ?? $translation?->button_text }}"
                    >
                    @error('translations.' . $index . '.button_text')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Action') }}</label>
                <div class="col-sm-9">
                    <select
                        name="translations[{{ $index }}][action_type]"
                        class="form-select js-action-type @error('translations.' . $index . '.action_type') is-invalid @enderror"
                        data-index="{{ $index }}"
                    >
                        <option value="link" {{ $actionType === 'link' ? 'selected' : '' }}>{{ __('Link') }}</option>
                        <option value="content" {{ $actionType === 'content' ? 'selected' : '' }}>{{ __('Content') }}</option>
                    </select>
                    @error('translations.' . $index . '.action_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3 js-link-row-{{ $index }}" style="{{ $actionType === 'content' ? 'display:none;' : '' }}">
                <label class="col-sm-3 col-form-label">{{ __('Button Link') }}</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        name="translations[{{ $index }}][button_link]"
                        class="form-control @error('translations.' . $index . '.button_link') is-invalid @enderror"
                        value="{{ $oldTranslation['button_link'] ?? $translation?->button_link }}"
                        placeholder="https://example.com"
                    >
                    @error('translations.' . $index . '.button_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3 js-content-row-{{ $index }}" style="{{ $actionType === 'link' ? 'display:none;' : '' }}">
                <label class="col-sm-3 col-form-label">{{ __('Description') }}</label>
                <div class="col-sm-9">
                    <textarea
                        name="translations[{{ $index }}][description]"
                        class="form-control js-editor @error('translations.' . $index . '.description') is-invalid @enderror"
                        rows="8"
                    >{{ $oldTranslation['description'] ?? $translation?->description }}</textarea>
                    @error('translations.' . $index . '.description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">{{ __('Hide Text For Mobile') }}</label>
                <div class="col-sm-9">
                    <input type="hidden" name="translations[{{ $index }}][hide_text_mobile]" value="0">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="translations[{{ $index }}][hide_text_mobile]"
                            value="1"
                            id="hide_text_mobile_{{ $index }}"
                            {{ !empty($oldTranslation) ? (!empty($oldTranslation['hide_text_mobile']) ? 'checked' : '') : ((int) ($translation?->hide_text_mobile ?? 0) ? 'checked' : '') }}
                        >
                        <label class="form-check-label" for="hide_text_mobile_{{ $index }}">{{ __('Yes') }}</label>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-primary">
            {{ !empty($slider) ? __('Update') : __('Create') }}
        </button>
        <a href="{{ route('admin.slider.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function toggleRows(index, type) {
                const linkRow = document.querySelector('.js-link-row-' + index);
                const contentRow = document.querySelector('.js-content-row-' + index);

                if (!linkRow || !contentRow) {
                    return;
                }

                if (type === 'content') {
                    linkRow.style.display = 'none';
                    contentRow.style.display = '';
                    return;
                }

                linkRow.style.display = '';
                contentRow.style.display = 'none';
            }

            document.querySelectorAll('.js-action-type').forEach(function (element) {
                toggleRows(element.dataset.index, element.value);

                element.addEventListener('change', function () {
                    toggleRows(element.dataset.index, element.value);
                });
            });
        });
    </script>
@endpush
