<div class="row">
    {{-- Status --}}
    <div class="col-md-12 mb-3">
        <label for="is_active" class="form-label">Status</label>
        <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
            <option
                value="1" @selected(old('is_active', $faq->is_active ?? true) == 1)>{{ \App\Enums\StatusEnum::ACTIVE }}</option>
            <option
                value="0" @selected(old('is_active', $faq->is_active ?? true) == 0)>{{ \App\Enums\StatusEnum::INACTIVE }}</option>
        </select>
        @error('is_active')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@php
    $requiredLanguageCodes = isset($requiredLanguageCodes)
        ? (is_array($requiredLanguageCodes) ? $requiredLanguageCodes : $requiredLanguageCodes->toArray())
        : [];

    $questionData = isset($faq) && $faq->exists
        ? (json_decode((string) $faq->getRawOriginal('question'), true) ?: [])
        : [];

    $answerData = isset($faq) && $faq->exists
        ? (json_decode((string) $faq->getRawOriginal('answer'), true) ?: [])
        : [];
@endphp

{{-- Dil Tab-ları --}}
<ul class="nav nav-tabs mb-3" role="tablist">
    @foreach($languages as $index => $language)
        @php
            $isRequired = in_array($language->code, $requiredLanguageCodes, true);
        @endphp

        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                    id="lang-{{ $language->code }}-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#lang-{{ $language->code }}"
                    type="button"
                    role="tab">
                {{ $language->native_name }}
                @if($isRequired)
                    <span class="text-danger">*</span>
                @endif
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    @foreach($languages as $index => $language)
        @php
            $isRequired = in_array($language->code, $requiredLanguageCodes, true);
        @endphp

        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
             id="lang-{{ $language->code }}"
             role="tabpanel">

            {{-- Sual --}}
            <div class="mb-3">
                <label for="question_{{ $language->code }}" class="form-label">
                    Sual ({{ $language->native_name }})
                    @if($isRequired)
                        <span class="text-danger">*</span>
                    @endif
                </label>

                <input type="text"
                       name="question[{{ $language->code }}]"
                       id="question_{{ $language->code }}"
                       class="form-control @error('question.'.$language->code) is-invalid @enderror"
                       value="{{ old('question.'.$language->code, $questionData[$language->code] ?? '') }}">

                @error('question.'.$language->code)
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Cavab --}}
            <div class="mb-3">
                <label for="answer_{{ $language->code }}" class="form-label">
                    Cavab ({{ $language->native_name }})
                    @if($isRequired)
                        <span class="text-danger">*</span>
                    @endif
                </label>

                <textarea name="answer[{{ $language->code }}]"
                          id="answer_{{ $language->code }}"
                          rows="5"
                          class="form-control @error('answer.'.$language->code) is-invalid @enderror">{{ old('answer.'.$language->code, $answerData[$language->code] ?? '') }}</textarea>

                @error('answer.'.$language->code)
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    @endforeach
</div>
