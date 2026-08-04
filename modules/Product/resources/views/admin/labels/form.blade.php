@php
    $requiredLanguageCodes = $requiredLanguageCodes ?? collect();
@endphp

<div class="row mb-3">
    <div class="col-12">
        <label class="form-label">
            {{ __('Name') }}
            <span class="text-danger">*</span>
        </label>

        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
            @foreach($languages as $index => $language)
                <li class="nav-item" role="presentation">
                    <a href="#name-tab-{{ $language->code }}"
                       class="nav-link {{ $index === 0 ? 'active' : '' }}"
                       data-bs-toggle="tab"
                       role="tab">
                        <span class="fw-semibold text-uppercase">
                            {{ $language->code }}
                            @if(in_array($language->code, $requiredLanguageCodes->toArray(), true))
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
                    $translationValue = '';
                    if (isset($label) && $label->exists) {
                        $trans = $label->translations->firstWhere('language_id', $language->id);
                        $translationValue = $trans?->name ?? '';
                    }

                    $isRequired = in_array($language->code, $requiredLanguageCodes->toArray(), true);
                @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                     id="name-tab-{{ $language->code }}"
                     role="tabpanel">
                    <div class="mb-3">
                        <input type="text"
                               name="name[{{ $language->code }}]"
                               id="name_{{ $language->code }}"
                               class="form-control @error('name.'.$language->code) is-invalid @enderror"
                               value="{{ old('name.'.$language->code, $translationValue) }}"
                               placeholder="Label name in {{ strtoupper($language->code) }}"
                            {{ $isRequired ? 'required' : '' }}>
                        @error('name.'.$language->code)
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
        <select name="status"
                class="form-select w-100 @error('status') is-invalid @enderror"
                required>
            @php
                $currentStatus = old('status', isset($label) && $label?->exists ? $label->status : \App\Enums\StatusEnum::ACTIVE);
            @endphp

            @foreach(\App\Enums\StatusEnum::getOptions() as $value => $labelText)
                <option value="{{ $value }}" @selected($currentStatus == $value)>
                    {{ $labelText }}
                </option>
            @endforeach
        </select>

        @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
