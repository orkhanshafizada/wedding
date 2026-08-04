@php
    $requiredLanguageCodeList = $requiredLanguageCodes instanceof \Illuminate\Support\Collection
        ? $requiredLanguageCodes->all()
        : (array) $requiredLanguageCodes;

    $isEdit = isset($logosPartner) && $logosPartner->exists;

    $nameData = $isEdit ? (json_decode((string) $logosPartner->getRawOriginal('name'), true) ?: []) : [];
    $linkData = $isEdit ? (json_decode((string) $logosPartner->getRawOriginal('link'), true) ?: []) : [];
    $slugData = $isEdit ? (json_decode((string) $logosPartner->getRawOriginal('slug'), true) ?: []) : [];
    $descriptionData = $isEdit ? (json_decode((string) $logosPartner->getRawOriginal('description'), true) ?: []) : [];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-2">{{ __('Validation errors:') }}</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">{{ __('Content') }}</h5>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                    @foreach($languages as $index => $language)
                        @php($code = $language->code)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                    id="lang-{{ $code }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#lang-{{ $code }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="lang-{{ $code }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-uppercase">{{ $code }}</span>
                                    <span>{{ $language->native_name }}</span>
                                    @if(in_array($code, $requiredLanguageCodeList, true))
                                        <span class="text-danger">*</span>
                                    @endif
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach($languages as $index => $language)
                        @php($code = $language->code)
                        @php($isRequired = in_array($code, $requiredLanguageCodeList, true))

                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                             id="lang-{{ $code }}"
                             role="tabpanel"
                             aria-labelledby="lang-{{ $code }}-tab">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name_{{ $code }}" class="form-label">
                                        {{ __('Name') }}
                                        @if($isRequired)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="text"
                                           name="name[{{ $code }}]"
                                           id="name_{{ $code }}"
                                           class="form-control @error('name.'.$code) is-invalid @enderror"
                                           value="{{ old('name.'.$code, $nameData[$code] ?? '') }}"
                                           data-menu-name="{{ $code }}">
                                    @error('name.'.$code)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="slug_{{ $code }}" class="form-label">
                                        {{ __('Slug / Path') }}
                                        @if($isRequired)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="text"
                                           name="slug[{{ $code }}]"
                                           id="slug_{{ $code }}"
                                           class="form-control @error('slug.'.$code) is-invalid @enderror"
                                           value="{{ old('slug.'.$code, $slugData[$code] ?? '') }}"
                                           placeholder="partner-name"
                                           data-menu-link="{{ $code }}">
                                    @error('slug.'.$code)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('This field is auto-generated from Name, but you can edit it.') }}</div>
                                </div>

                                <div class="col-12">
                                    <label for="link_{{ $code }}" class="form-label">
                                        {{ __('Link / URL') }}
                                        @if($isRequired)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="url"
                                           name="link[{{ $code }}]"
                                           id="link_{{ $code }}"
                                           class="form-control @error('link.'.$code) is-invalid @enderror"
                                           value="{{ old('link.'.$code, $linkData[$code] ?? '') }}"
                                           placeholder="https://example.com">
                                    @error('link.'.$code)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description_{{ $code }}" class="form-label">
                                        {{ __('Description') }}
                                        @if($isRequired)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <textarea name="description[{{ $code }}]"
                                              id="description_{{ $code }}"
                                              class="form-control js-editor @error('description.'.$code) is-invalid @enderror"
                                              rows="6">{{ old('description.'.$code, $descriptionData[$code] ?? '') }}</textarea>
                                    @error('description.'.$code)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="text-muted small mt-3">
                    <span class="text-danger">*</span> {{ __('Required fields') }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">{{ __('Settings') }}</h5>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label for="is_active" class="form-label">{{ __('Status') }}</label>
                    <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                            <option value="1" @selected(old('is_active', $logosPartner->is_active ?? true) == 1)>{{ \App\Enums\StatusEnum::ACTIVE }}</option>
                            <option value="0" @selected(old('is_active', $logosPartner->is_active ?? true) == 0)>{{ \App\Enums\StatusEnum::INACTIVE }}</option>
                    </select>
                    @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label for="image" class="form-label">{{ __('Image / Logo') }}</label>
                    <input type="file"
                           name="image"
                           id="image"
                           class="form-control @error('image') is-invalid @enderror"
                           accept="image/*">
                    @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if($isEdit && !empty($logosPartner->image))
                        <div class="mt-3">
                            <div class="border rounded p-2 d-flex align-items-center justify-content-center" style="min-height: 160px;">
                                <img src="{{ asset('storage/' . $logosPartner->image) }}"
                                     alt="{{ __('Current image') }}"
                                     class="img-fluid"
                                     style="max-height: 140px; object-fit: contain;">
                            </div>
                            <div class="form-text">{{ __('Current image') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.logospartners.index', $menu) }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-3-line align-bottom me-1"></i>{{ $isEdit ? __('Update') : __('Save') }}
                </button>
            </div>
        </div>
    </div>
</div>
