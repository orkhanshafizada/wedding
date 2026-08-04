{{-- Type --}}
<div class="row mb-3">
    <div class="col-md-6">
        <label for="type" class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
        <select name="type"
                id="type"
                class="form-select @error('type') is-invalid @enderror"
                required>
            <option value="">{{ __('Select') }}...</option>
            @foreach($types as $value => $label_text)
                <option value="{{ $value }}" {{ old('type', $label->type ?? '') === $value ? 'selected' : '' }}>
                    {{ $label_text }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Is Required --}}
    <div class="col-md-3">
        <label for="is_required" class="form-label">{{ __('Is Required') }}</label>
        <select name="is_required"
                id="is_required"
                class="form-select @error('is_required') is-invalid @enderror">
            <option value="0" {{ old('is_required', $label->is_required ?? false) == false ? 'selected' : '' }}>{{ __('No') }}</option>
            <option value="1" {{ old('is_required', $label->is_required ?? false) == true ? 'selected' : '' }}>{{ __('Yes') }}</option>
        </select>
        @error('is_required')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Send Text Mail (only for email type) --}}
    <div class="col-md-3" id="send-text-mail-wrapper" style="display: none;">
        <label for="send_text_mail" class="form-label">{{ __('Send Text Mail') }}</label>
        <select name="send_text_mail"
                id="send_text_mail"
                class="form-select @error('send_text_mail') is-invalid @enderror">
            <option value="0" {{ old('send_text_mail', $label->send_text_mail ?? false) == false ? 'selected' : '' }}>{{ __('No') }}</option>
            <option value="1" {{ old('send_text_mail', $label->send_text_mail ?? false) == true ? 'selected' : '' }}>{{ __('Yes') }}</option>
        </select>
        @error('send_text_mail')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Name & Information Translations with Tabs --}}
<div class="row mb-3">
    <div class="col-12">
        <label class="form-label">
            {{ __('Name') }} & {{ __('Information') }}
            <span class="text-danger">*</span>
        </label>

        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
            @foreach($languages as $index => $language)
                <li class="nav-item" role="presentation">
                    <a href="#translation-tab-{{ $language->code }}"
                       class="nav-link {{ $index === 0 ? 'active' : '' }}"
                       data-bs-toggle="tab"
                       role="tab">
                        <span class="fw-semibold">
                            {{ $language->native_name }}
                            @if(in_array($language->code, $requiredLocales ?? []))
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
                    $nameValue = '';
                    $informationValue = '';
                    if (isset($label) && $label->exists) {
                        $trans = $label->translations->firstWhere('locale', $language->code);
                        $nameValue = $trans?->name ?? '';
                        $informationValue = $trans?->information ?? '';
                    }
                    $isRequired = in_array($language->code, $requiredLocales ?? []);
                @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                     id="translation-tab-{{ $language->code }}"
                     role="tabpanel">
                    {{-- Name Input --}}
                    <div class="mb-3">
                        <label for="name_{{ $language->code }}" class="form-label">
                            {{ __('Name') }}
                            @if($isRequired)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input type="text"
                               name="name[{{ $language->code }}]"
                               id="name_{{ $language->code }}"
                               class="form-control @error('name.'.$language->code) is-invalid @enderror"
                               value="{{ old('name.'.$language->code, $nameValue) }}"
                               placeholder="{{ __('Label name in') }} {{ strtoupper($language->code) }}"
                               {{ $isRequired ? 'required' : '' }}>
                        @error('name.'.$language->code)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Information Textarea --}}
                    <div class="mb-3">
                        <label for="information_{{ $language->code }}" class="form-label">{{ __('Information') }}</label>
                        <textarea name="information[{{ $language->code }}]"
                                  id="information_{{ $language->code }}"
                                  class="form-control @error('information.'.$language->code) is-invalid @enderror"
                                  rows="3"
                                  placeholder="{{ __('Additional information in') }} {{ strtoupper($language->code) }}">{{ old('information.'.$language->code, $informationValue) }}</textarea>
                        @error('information.'.$language->code)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const sendTextMailWrapper = document.getElementById('send-text-mail-wrapper');
    const emailTypeValue = '{{ \Modules\Form\Enums\FormLabelTypeEnum::Email }}';

    function toggleSendTextMail() {
        if (typeSelect.value === emailTypeValue) {
            sendTextMailWrapper.style.display = 'block';
        } else {
            sendTextMailWrapper.style.display = 'none';
        }
    }

    // Initial check
    toggleSendTextMail();

    // Listen for changes
    typeSelect.addEventListener('change', toggleSendTextMail);
});
</script>
@endpush
