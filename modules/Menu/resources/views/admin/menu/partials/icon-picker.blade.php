@php
    $selectedFaVersion = old('icon_library_version', $menu?->icon_library_version ?? 'v6');
    $selectedIcon = old('icon', $menu?->icon ?? '');
    $fontAwesomeVersions = [
        'v6' => 'Font Awesome 6',
    ];

    $fontAwesomeIcons = \App\Support\FontAwesome::icons($selectedFaVersion);
@endphp

<div class="col-12 d-none">
    <label class="form-label">{{ __('FontAwesome Version') }}</label>
    <select name="icon_library_version" class="form-select" id="iconLibraryVersion">
        @foreach($fontAwesomeVersions as $versionKey => $versionLabel)
            <option value="{{ $versionKey }}" @selected($selectedFaVersion === $versionKey)>
                {{ $versionLabel }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-12">
    <label class="form-label">{{ __('FontAwesome class') }}</label>

    <input type="hidden" name="icon" id="menuIconValue" value="{{ $selectedIcon }}">

    <div class="fa-picker" id="menuFaPicker" data-version="{{ $selectedFaVersion }}">
        <button type="button" class="fa-picker__control" id="menuFaPickerControl" aria-expanded="false">
            <span class="fa-picker__control-left">
                <span class="fa-picker__preview" id="menuFaPickerPreview">
                    @if($selectedIcon)
                        <i class="{{ $selectedIcon }}"></i>
                    @else
                        <i class="fa-solid fa-icons"></i>
                    @endif
                </span>

                <span class="fa-picker__value" id="menuFaPickerValue">
                    {{ $selectedIcon ?: __('Select icon') }}
                </span>
            </span>

            <span class="fa-picker__arrow">
                <i class="ri-arrow-down-s-line"></i>
            </span>
        </button>

        <div class="fa-picker__dropdown" id="menuFaPickerDropdown">
            <div class="fa-picker__search-wrap">
                <i class="ri-search-line fa-picker__search-icon"></i>
                <input type="text"
                       class="fa-picker__search"
                       id="menuFaPickerSearch"
                       placeholder="{{ __('Search icon') }}"
                       autocomplete="off">
            </div>

            <div class="fa-picker__actions">
                <button type="button" class="btn btn-sm btn-light" id="menuFaPickerClear">
                    {{ __('Clear') }}
                </button>
            </div>

            <div class="fa-picker__list" id="menuFaPickerList">
                @foreach($fontAwesomeIcons as $iconClass)
                    <button type="button"
                            class="fa-picker__item {{ $selectedIcon === $iconClass ? 'is-active' : '' }}"
                            data-icon="{{ $iconClass }}">
                        <span class="fa-picker__item-icon">
                            <i class="{{ $iconClass }}"></i>
                        </span>
                        <span class="fa-picker__item-text">{{ $iconClass }}</span>
                    </button>
                @endforeach
            </div>

            <div class="fa-picker__empty d-none" id="menuFaPickerEmpty">
                {{ __('No icons found') }}
            </div>
        </div>
    </div>
</div>

<div class="col-12 d-none">
    <div class="fa-picker-selected-card" id="menuFaSelectedCard">
        <div class="fa-picker-selected-card__icon" id="menuFaSelectedCardIcon">
            @if($selectedIcon)
                <i class="{{ $selectedIcon }}"></i>
            @else
                <i class="fa-solid fa-icons"></i>
            @endif
        </div>

        <div class="fa-picker-selected-card__content">
            <div class="fa-picker-selected-card__label">{{ __('Selected icon') }}</div>
            <div class="fa-picker-selected-card__value" id="menuFaSelectedCardValue">
                {{ $selectedIcon ?: __('No icon selected') }}
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/menu/css/icon-picker.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('modules/menu/js/icon-picker.js') }}"></script>
@endpush
