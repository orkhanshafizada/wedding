@php
    $isEdit = isset($customer);
    $selectedCountryId = old('country_id', $customer->country_id ?? null);
    $avatarUrl = $isEdit ? ($customer->avatar_url ?? null) : null;
@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">{{ __('Account') }}</h5>
            </div>

            <div class="card-body pt-0">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $customer->name ?? '') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="{{ __('Enter name') }}"
                        >
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Surname') }}</label>
                        <input
                            type="text"
                            name="surname"
                            value="{{ old('surname', $customer->surname ?? '') }}"
                            class="form-control @error('surname') is-invalid @enderror"
                            placeholder="{{ __('Enter surname') }}"
                        >
                        @error('surname')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Date of Birth') }}</label>
                        <input
                            type="date"
                            name="date_of_birth"
                            value="{{ old('date_of_birth', isset($customer?->date_of_birth) ? \Illuminate\Support\Carbon::parse($customer->date_of_birth)->format('Y-m-d') : '') }}"
                            class="form-control @error('date_of_birth') is-invalid @enderror"
                        >
                        @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Passport FIN Code') }}</label>
                        <input
                            type="text"
                            name="passport_fin"
                            value="{{ old('passport_fin', $customer->passport_fin ?? '') }}"
                            class="form-control @error('passport_fin') is-invalid @enderror"
                            placeholder="{{ __('Enter passport FIN code') }}"
                        >
                        @error('passport_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Email') }} <span class="text-danger">*</span></label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $customer->email ?? '') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="user@example.com"
                        >
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Phone') }}</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $customer->phone ?? '') }}"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="0511111111"
                        >
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Country') }}</label>
                        <select name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                            <option value="">{{ __('Select country') }}</option>

                            @foreach($countries as $country)
                                @php
                                    $shortNames = $country->short_names ?? [];
                                    $locale = app()->getLocale();
                                    $label = $shortNames[$locale] ?? $shortNames['en'] ?? $country->iso2;
                                @endphp

                                <option value="{{ $country->id }}" @selected((int) $selectedCountryId === (int) $country->id)>
                                    {{ $label }} ({{ $country->iso2 }})
                                </option>
                            @endforeach
                        </select>

                        @error('country_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Status') }}</label>

                        <input type="hidden" name="is_active" value="0">

                        <div class="form-check form-switch m-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="is_active"
                                name="is_active"
                                value="1"
                                @checked((bool) old('is_active', $customer->is_active ?? true))
                            >
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>

                        @error('is_active')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Password') }}</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            placeholder="{{ $isEdit ? __('Leave empty to keep current password') : __('Enter password') }}"
                        >
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Confirm Password') }}</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            autocomplete="new-password"
                            placeholder="{{ __('Confirm password') }}"
                        >
                        @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ __('Avatar') }}</label>

                        <div class="d-flex align-items-start gap-3">
                            <div>
                                <img
                                    src="{{ $avatarUrl ?: 'https://via.placeholder.com/72x72?text=Avatar' }}"
                                    alt="avatar"
                                    class="rounded-circle border js-avatar-thumb"
                                    style="width:72px;height:72px;object-fit:cover"
                                >
                            </div>

                            <div class="flex-grow-1">
                                <input
                                    type="file"
                                    name="avatar"
                                    class="form-control @error('avatar') is-invalid @enderror js-avatar-input"
                                    accept="image/*"
                                >
                                <div class="form-text">
                                    {{ __('PNG/JPG. Square image recommended.') }}
                                </div>
                                @error('avatar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">{{ __('Additional Information') }}</h5>
            </div>

            <div class="card-body pt-0">
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Email Verified At') }}</label>
                    <input
                        type="text"
                        class="form-control"
                        value="{{ old('email_verified_at', $customer->email_verified_at ?? '') }}"
                        disabled
                    >
                    <div class="form-text">
                        {{ __('Email verification is managed by the authentication flow.') }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Remember Token') }}</label>
                    <input
                        type="text"
                        class="form-control"
                        value="{{ old('remember_token', $customer->remember_token ?? '') }}"
                        disabled
                    >
                    <div class="form-text">
                        {{ __('Remember token is managed automatically.') }}
                    </div>
                </div>

                @if($isEdit)
                    <div class="mb-0">
                        <label class="form-label fw-semibold">{{ __('Addresses') }}</label>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.customers.addresses.index', $customer) }}" class="btn btn-soft-info">
                                {{ __('Manage Addresses') }}
                            </a>
                            <a href="{{ route('admin.customers.addresses.create', $customer) }}" class="btn btn-soft-primary">
                                {{ __('Add Address') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@pushOnce('scripts')
    <script>
        (function () {
            const input = document.querySelector('.js-avatar-input');
            const thumb = document.querySelector('.js-avatar-thumb');

            if (!input || !thumb) {
                return;
            }

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];

                if (!file) {
                    return;
                }

                const url = URL.createObjectURL(file);
                thumb.src = url;

                thumb.onload = function () {
                    if (url.startsWith('blob:')) {
                        URL.revokeObjectURL(url);
                    }
                };
            });
        })();
    </script>
@endPushOnce
