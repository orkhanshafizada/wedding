@php
    $nameData = isset($teamStaff) && $teamStaff->exists
        ? (json_decode($teamStaff->getRawOriginal('name'), true) ?: [])
        : [];

    $companyData = isset($teamStaff) && $teamStaff->exists
        ? (json_decode($teamStaff->getRawOriginal('company'), true) ?: [])
        : [];

    $positionData = isset($teamStaff) && $teamStaff->exists
        ? (json_decode($teamStaff->getRawOriginal('position'), true) ?: [])
        : [];

    $descriptionData = isset($teamStaff) && $teamStaff->exists
        ? (json_decode($teamStaff->getRawOriginal('description'), true) ?: [])
        : [];

    $selectedColor = old('color', $teamStaff->color ?? '#000000');

    if (! is_string($selectedColor) || trim($selectedColor) === '' || ! preg_match('/^#[0-9A-Fa-f]{6}$/', trim($selectedColor))) {
        $selectedColor = '#000000';
    }

    $selectedColor = strtoupper($selectedColor);

    $socialNetworks = old('social_networks', isset($teamStaff) ? $teamStaff->social_networks : []);

    if (is_string($socialNetworks)) {
        $socialNetworks = json_decode($socialNetworks, true) ?: [];
    }

    if (! is_array($socialNetworks)) {
        $socialNetworks = [];
    }

    $existingFiles = isset($teamStaff) && $teamStaff->exists && ! empty($teamStaff->files)
        ? $teamStaff->files
        : [];

    if (is_string($existingFiles)) {
        $existingFiles = json_decode($existingFiles, true) ?: [];
    }

    if (! is_array($existingFiles)) {
        $existingFiles = [];
    }
@endphp

<div class="team-staff-form">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">{{ __('Basic information') }}</h5>
                    <div class="text-muted small">{{ __('Manage status, profile image, and display color.') }}</div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <label for="is_active" class="form-label">{{ __('Status') }}</label>
                    <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                        <option value="1" @selected(old('is_active', $teamStaff->is_active ?? true) == 1)>
                            {{ \App\Enums\StatusEnum::ACTIVE }}
                        </option>
                        <option value="0" @selected(old('is_active', $teamStaff->is_active ?? true) == 0)>
                            {{ \App\Enums\StatusEnum::INACTIVE }}
                        </option>
                    </select>
                    @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="color" class="form-label">{{ __('Color') }}</label>

                    <div class="team-color-picker @error('color') is-invalid @enderror"
                         data-team-color-picker>
                        <div id="team-color-picker-control" class="team-color-picker__control"></div>

                        <input type="hidden"
                               name="color"
                               id="color"
                               value="{{ $selectedColor }}"
                               data-team-color-input>

                        <input type="color"
                               id="team-color-native-fallback"
                               class="team-color-picker__input d-none"
                               value="{{ $selectedColor }}"
                               data-team-color-native-fallback>

                        <div class="team-color-picker__content">
                            <div class="team-color-picker__label">{{ __('Selected color') }}</div>
                            <div class="team-color-picker__value" data-team-color-value>
                                {{ $selectedColor }}
                            </div>
                        </div>
                    </div>

                    @error('color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="profile_picture" class="form-label">{{ __('Profile picture') }}</label>
                    <input type="file"
                           name="profile_picture"
                           id="profile_picture"
                           class="form-control @error('profile_picture') is-invalid @enderror"
                           accept="image/*">
                    @error('profile_picture')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if(isset($teamStaff) && $teamStaff->profile_picture)
                        <div class="team-profile-preview mt-3">
                            <img src="{{ asset('storage/' . $teamStaff->profile_picture) }}" alt="{{ __('Profile picture') }}">
                            <div>
                                <div class="fw-semibold">{{ __('Current image') }}</div>
                                <div class="text-muted small">{{ __('A new upload will replace this image after saving.') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <div>
                <h5 class="mb-1">{{ __('Translations') }}</h5>
                <div class="text-muted small">{{ __('Fill team member information for each active language.') }}</div>
            </div>
        </div>

        <div class="card-body">
            <ul class="nav nav-pills team-language-tabs mb-4" role="tablist">
                @foreach($languages as $index => $language)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                id="lang-{{ $language->code }}-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#lang-{{ $language->code }}"
                                type="button"
                                role="tab"
                                aria-controls="lang-{{ $language->code }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                            <span>{{ $language->native_name }}</span>
                            <span class="text-danger">*</span>
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach($languages as $index => $language)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                         id="lang-{{ $language->code }}"
                         role="tabpanel"
                         aria-labelledby="lang-{{ $language->code }}-tab">

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label for="name_{{ $language->code }}" class="form-label">
                                    {{ __('Name and surname') }} ({{ $language->native_name }})
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="name[{{ $language->code }}]"
                                       id="name_{{ $language->code }}"
                                       class="form-control @error('name.'.$language->code) is-invalid @enderror"
                                       value="{{ old('name.'.$language->code, $nameData[$language->code] ?? '') }}"
                                       autocomplete="off">
                                @error('name.'.$language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                <label for="company_{{ $language->code }}" class="form-label">
                                    {{ __('Company') }} ({{ $language->native_name }})
                                </label>
                                <input type="text"
                                       name="company[{{ $language->code }}]"
                                       id="company_{{ $language->code }}"
                                       class="form-control @error('company.'.$language->code) is-invalid @enderror"
                                       value="{{ old('company.'.$language->code, $companyData[$language->code] ?? '') }}"
                                       autocomplete="off">
                                @error('company.'.$language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="position_{{ $language->code }}" class="form-label">
                                    {{ __('Position') }} ({{ $language->native_name }})
                                </label>
                                <input type="text"
                                       name="position[{{ $language->code }}]"
                                       id="position_{{ $language->code }}"
                                       class="form-control @error('position.'.$language->code) is-invalid @enderror"
                                       value="{{ old('position.'.$language->code, $positionData[$language->code] ?? '') }}"
                                       autocomplete="off">
                                @error('position.'.$language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description_{{ $language->code }}" class="form-label">
                                    {{ __('Description') }} ({{ $language->native_name }})
                                </label>
                                <textarea name="description[{{ $language->code }}]"
                                          id="description_{{ $language->code }}"
                                          rows="5"
                                          class="form-control @error('description.'.$language->code) is-invalid @enderror">{{ old('description.'.$language->code, $descriptionData[$language->code] ?? '') }}</textarea>
                                @error('description.'.$language->code)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <div>
                <h5 class="mb-1">{{ __('Contact information') }}</h5>
                <div class="text-muted small">{{ __('Add phone, email, and social network links.') }}</div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label for="phone" class="form-label">{{ __('Phone') }}</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $teamStaff->phone ?? '') }}"
                           autocomplete="off">
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="mobile" class="form-label">{{ __('Mobile') }}</label>
                    <input type="text"
                           name="mobile"
                           id="mobile"
                           class="form-control @error('mobile') is-invalid @enderror"
                           value="{{ old('mobile', $teamStaff->mobile ?? '') }}"
                           autocomplete="off">
                    @error('mobile')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-4">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $teamStaff->email ?? '') }}"
                           autocomplete="off">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="team-social-grid mt-4">
                <div>
                    <label for="social_facebook" class="form-label">{{ __('Facebook') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-facebook-fill"></i></span>
                        <input type="text"
                               name="social_networks[facebook]"
                               id="social_facebook"
                               class="form-control"
                               placeholder="Facebook URL"
                               value="{{ $socialNetworks['facebook'] ?? '' }}">
                    </div>
                </div>

                <div>
                    <label for="social_twitter" class="form-label">{{ __('Twitter') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-twitter-x-line"></i></span>
                        <input type="text"
                               name="social_networks[twitter]"
                               id="social_twitter"
                               class="form-control"
                               placeholder="Twitter URL"
                               value="{{ $socialNetworks['twitter'] ?? '' }}">
                    </div>
                </div>

                <div>
                    <label for="social_linkedin" class="form-label">{{ __('LinkedIn') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-linkedin-fill"></i></span>
                        <input type="text"
                               name="social_networks[linkedin]"
                               id="social_linkedin"
                               class="form-control"
                               placeholder="LinkedIn URL"
                               value="{{ $socialNetworks['linkedin'] ?? '' }}">
                    </div>
                </div>

                <div>
                    <label for="social_instagram" class="form-label">{{ __('Instagram') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-instagram-line"></i></span>
                        <input type="text"
                               name="social_networks[instagram]"
                               id="social_instagram"
                               class="form-control"
                               placeholder="Instagram URL"
                               value="{{ $socialNetworks['instagram'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <div>
                <h5 class="mb-1">{{ __('Files and photos') }}</h5>
                <div class="text-muted small">{{ __('Upload images, PDFs, or documents. Existing files can be reordered or deleted.') }}</div>
            </div>
        </div>

        <div class="card-body">
            <div class="team-upload-box">
                <input type="file"
                       class="form-control js-team-files-input @error('files.*') is-invalid @enderror"
                       name="files[]"
                       accept="image/*,.pdf,.doc,.docx"
                       multiple>

                @error('files.*')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="form-text mt-2">
                    {{ __('You can upload images, PDFs, or documents. Drag to reorder.') }}
                </div>
            </div>

            @if($existingFiles !== [])
                <div class="mt-4 row g-3 js-existing-files-list">
                    @foreach($existingFiles as $index => $filePath)
                        @php
                            $fileName = basename($filePath);
                            $fileUrl = asset('storage/' . $filePath);
                            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                        @endphp

                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 team-file-item" draggable="true" data-file-index="{{ $index }}">
                            <div class="team-file-card h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="team-file-drag">
                                        <i class="ri-drag-move-2-line"></i>
                                    </span>

                                    <button type="button" class="btn btn-sm btn-soft-danger js-file-delete" title="{{ __('Delete') }}">
                                        <i class="ri-delete-bin-6-line"></i>
                                    </button>
                                </div>

                                @if($isImage)
                                    <img src="{{ $fileUrl }}" alt="{{ $fileName }}" class="team-file-preview">
                                @else
                                    <div class="team-file-placeholder">
                                        <i class="ri-file-line"></i>
                                    </div>
                                @endif

                                <div class="mt-2 small text-truncate" title="{{ $fileName }}">
                                    {{ $fileName }}
                                </div>

                                <input type="hidden" name="existing_files[{{ $index }}][path]" value="{{ $filePath }}">
                                <input type="hidden" class="js-file-sort" name="existing_files[{{ $index }}][sort_order]" value="{{ $index }}">
                                <input type="hidden" class="js-file-delete-flag" name="existing_files[{{ $index }}][_delete]" value="0">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 row g-3 js-new-files-list"></div>

            <div class="alert alert-warning border-0 mt-4 mb-0">
                <i class="ri-error-warning-line align-middle me-1"></i>
                {{ __('Deleted files will be removed after saving.') }}
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/assets/libs/@simonwep/pickr/themes/classic.min.css') }}">

    <style>
        .team-staff-form .card {
            border-radius: 18px;
        }

        .team-staff-form .card-header {
            padding: 1.25rem 1.25rem 0;
        }

        .team-staff-form .card-body {
            padding: 1.25rem;
        }

        .team-staff-form .form-label {
            font-weight: 600;
            color: #344054;
        }

        .team-language-tabs {
            gap: .5rem;
            border-bottom: 1px solid #eef0f4;
            padding-bottom: .75rem;
        }

        .team-language-tabs .nav-link {
            border-radius: 999px;
            background: #f3f6f9;
            color: #405169;
            font-weight: 600;
            padding: .5rem 1rem;
        }

        .team-language-tabs .nav-link.active {
            background: var(--vz-primary);
            color: #fff;
            box-shadow: 0 8px 18px rgba(64, 81, 137, .18);
        }

        .team-language-tabs .nav-link.active .text-danger {
            color: #fff !important;
        }

        .team-color-picker {
            display: flex;
            align-items: center;
            gap: .85rem;
            border: 1px solid var(--team-selected-color, var(--vz-border-color, #e9ebec));
            border-radius: 14px;
            background: #fff;
            padding: .55rem .75rem;
            min-height: 54px;
            box-shadow: 0 8px 18px color-mix(in srgb, var(--team-selected-color, #000000) 12%, transparent);
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .team-color-picker.is-invalid,
        .team-color-picker:has(.is-invalid) {
            border-color: var(--vz-form-invalid-border-color, #f06548);
        }

        .team-color-picker__control .pcr-button {
            width: 42px !important;
            height: 42px !important;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: none !important;
        }

        .team-color-picker__input {
            width: 42px;
            height: 42px;
            padding: 0;
            border: 0;
            border-radius: 12px;
            background: transparent;
            cursor: pointer;
        }

        .team-color-picker__input::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .team-color-picker__input::-webkit-color-swatch {
            border: 0;
            border-radius: 12px;
        }

        .team-color-picker__label {
            font-size: 12px;
            color: #667085;
            line-height: 1.2;
        }

        .team-color-picker__value {
            font-weight: 700;
            color: #344054;
            line-height: 1.2;
        }

        .team-profile-preview {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .75rem;
            border: 1px solid #eef0f4;
            border-radius: 14px;
            background: #f8fafc;
        }

        .team-profile-preview img {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 8px 18px rgba(18, 38, 63, .08);
        }

        .team-social-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .team-upload-box {
            border: 1px dashed #cfd6e4;
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
        }

        .team-file-card {
            border: 1px solid #eef0f4;
            border-radius: 16px;
            background: #fff;
            padding: .65rem;
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }

        .team-file-card:hover {
            border-color: #dbe3ee;
            box-shadow: 0 10px 24px rgba(18, 38, 63, .08);
            transform: translateY(-1px);
        }

        .team-file-drag {
            display: inline-flex;
            width: 30px;
            height: 30px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #f3f6f9;
            color: #667085;
            cursor: grab;
        }

        .team-file-preview {
            width: 100%;
            height: 96px;
            object-fit: cover;
            border-radius: 12px;
            background: #f3f6f9;
        }

        .team-file-placeholder {
            height: 96px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #f3f6f9;
            color: #98a6ad;
        }

        .team-file-placeholder i {
            font-size: 2rem;
        }

        @media (max-width: 1199.98px) {
            .team-social-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .team-social-grid {
                grid-template-columns: 1fr;
            }

            .team-language-tabs .nav-link {
                width: 100%;
            }

            .team-language-tabs .nav-item {
                flex: 1 1 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin/assets/libs/@simonwep/pickr/pickr.min.js') }}"></script>

    <script>
        (function () {
            let teamColorPickr = null;

            function normalizeHexColor(value) {
                value = (value || '').toString().trim();

                if (!/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    return '#000000';
                }

                return value.toUpperCase();
            }

            function updatePickrButtonColor(color) {
                const control = document.getElementById('team-color-picker-control');

                if (!control) {
                    return;
                }

                const button = control.querySelector('.pcr-button');

                if (!button) {
                    return;
                }

                button.style.setProperty('--pcr-color', color);
                button.style.color = color;
                button.style.background = color;
            }

            function setTeamColor(value, shouldSyncPickr) {
                const color = normalizeHexColor(value);
                const input = document.querySelector('[data-team-color-input]');
                const nativeFallback = document.querySelector('[data-team-color-native-fallback]');
                const valueLabel = document.querySelector('[data-team-color-value]');
                const picker = document.querySelector('[data-team-color-picker]');

                if (input) {
                    input.value = color;
                }

                if (nativeFallback) {
                    nativeFallback.value = color;
                }

                if (valueLabel) {
                    valueLabel.textContent = color;
                }

                if (picker) {
                    picker.style.setProperty('--team-selected-color', color);
                }

                updatePickrButtonColor(color);

                if (shouldSyncPickr && teamColorPickr) {
                    teamColorPickr.setColor(color, true);
                }
            }

            function initNativeColorFallback(initialColor) {
                const nativeFallback = document.querySelector('[data-team-color-native-fallback]');
                const control = document.getElementById('team-color-picker-control');

                if (control) {
                    control.classList.add('d-none');
                }

                if (!nativeFallback) {
                    setTeamColor(initialColor, false);
                    return;
                }

                nativeFallback.classList.remove('d-none');

                nativeFallback.addEventListener('input', function () {
                    setTeamColor(this.value, false);
                });

                nativeFallback.addEventListener('change', function () {
                    setTeamColor(this.value, false);
                });

                setTeamColor(initialColor, false);
            }

            function initPickrColorPicker() {
                const control = document.getElementById('team-color-picker-control');
                const input = document.querySelector('[data-team-color-input]');

                if (!control || !input) {
                    return;
                }

                const initialColor = normalizeHexColor(input.value);

                if (typeof Pickr === 'undefined') {
                    initNativeColorFallback(initialColor);
                    return;
                }

                teamColorPickr = Pickr.create({
                    el: control,
                    theme: 'classic',
                    default: initialColor,
                    useAsButton: false,
                    swatches: [
                        '#000000',
                        '#BD0000',
                        '#F06548',
                        '#F7B84B',
                        '#0AB39C',
                        '#299CDB',
                        '#405189',
                        '#8E44AD'
                    ],
                    components: {
                        preview: true,
                        opacity: false,
                        hue: true,
                        interaction: {
                            hex: true,
                            input: true,
                            clear: false,
                            save: false
                        }
                    }
                });

                teamColorPickr.on('init', function () {
                    setTeamColor(initialColor, false);
                });

                teamColorPickr.on('change', function (color) {
                    setTeamColor(color.toHEXA().toString(), false);
                });

                teamColorPickr.on('swatchselect', function (color) {
                    setTeamColor(color.toHEXA().toString(), false);
                });

                teamColorPickr.on('changestop', function () {
                    const color = teamColorPickr.getColor();

                    if (color) {
                        setTeamColor(color.toHEXA().toString(), false);
                    }
                });

                setTimeout(function () {
                    setTeamColor(initialColor, false);
                }, 50);
            }

            document.addEventListener('DOMContentLoaded', initPickrColorPicker);
        })();
    </script>
@endpush
