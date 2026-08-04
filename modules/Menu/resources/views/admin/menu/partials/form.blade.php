@php
    use App\Support\Settings;

    $requiredLanguageCodesArray = collect($requiredLanguageCodes ?? [])->map(fn ($code) => (string) $code)->values()->all();
    $menuTypeValue = $menu?->type instanceof \Modules\Menu\Enums\MenuType ? $menu->type->value : ($menu->type ?? null);
    $menuViewTypeValue = $menu?->view_type;

    $allowedImages = collect(Settings::get('file_manager', 'allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']))
        ->map(fn ($extension) => '.' . ltrim((string) $extension, '.'))
        ->filter()
        ->unique()
        ->implode(',');

    $selectedIncludedItems = old(
        'included_items',
        isset($menu) && $menu
            ? $menu->includedItems->map(fn ($item) => [
                'type' => $item->included_type instanceof \Modules\Menu\Enums\MenuIncludedItemType ? $item->included_type->value : (string) $item->included_type,
                'id' => (int) $item->included_id,
            ])->all()
            : []
    );

$selectedIncludedItems = collect((array) $selectedIncludedItems)
    ->map(function ($item) {
        if (! is_array($item)) {
            return null;
        }

        $type = trim((string) ($item['type'] ?? ''));
        $id = (int) ($item['id'] ?? 0);

        if ($type === '') {
            return null;
        }

        $zeroIdIncludedItemTypes = [
            \Modules\Menu\Enums\MenuIncludedItemType::SLIDER->value,
            \Modules\Menu\Enums\MenuIncludedItemType::SELF->value,
        ];

        if (! in_array($type, $zeroIdIncludedItemTypes, true) && $id <= 0) {
            return null;
        }

        if (in_array($type, $zeroIdIncludedItemTypes, true)) {
            $id = 0;
        }

        return [
            'type' => $type,
            'id' => $id,
        ];
    })
    ->filter()
    ->unique(fn ($item) => $item['type'] . ':' . $item['id'])
    ->values()
    ->all();

    $includedItemOptionsMap = collect($includedItemOptions ?? [])->mapWithKeys(
        fn ($item) => [(string) $item['type'] . ':' . (int) $item['id'] => [
            'label' => (string) $item['label'],
            'search' => (string) ($item['search'] ?? ''),
        ]]
    )->all();
@endphp

<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Main Menu') }}</label>
                    <select name="parent_id" class="form-select">
                        <option value="">{{ __('Main Category') }}</option>
                        @include('menu::admin.menu.partials.parent_options', [
                            'nodes' => $parentTree,
                            'selected' => old('parent_id', $selectedParentId ?? ($menu?->parent_id)),
                            'excludeId' => $excludeId ?? ($menu?->id),
                            'locale' => app()->getLocale(),
                        ])
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Main Type') }}</label>
                    <select name="type" class="form-select" required>
                        @foreach($types as $type)
                            @php
                                $typeValue = $type instanceof \Modules\Menu\Enums\MenuType ? $type->value : (string) $type;
                                $typeLabel = $type instanceof \Modules\Menu\Enums\MenuType ? $type->label() : ucfirst(str_replace('_', ' ', $typeValue));
                                $selectedType = old('type', $menuTypeValue);
                            @endphp
                            <option value="{{ $typeValue }}" @selected($selectedType === $typeValue)>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3" id="viewTypeWrap" style="display:none;">
                    <label class="form-label">{{ __('View Type') }}</label>
                    <select name="view_type" class="form-select" id="viewTypeSelect"></select>
                    <div class="form-text" id="viewTypeHelp">{{ __('Optional. Depends on selected type.') }}</div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label class="form-label">{{ __('Text color') }}</label>
                        <input type="color"
                               name="text_color"
                               class="form-control form-control-color"
                               style="width: 100%; padding:2px;"
                               value="{{ old('text_color', $menu?->text_color ?? '#8cd4f2') }}">
                    </div>

                    <div class="col-6">
                        <label class="form-label">{{ __('Background color') }}</label>
                        <input type="color"
                               name="bg_color"
                               class="form-control form-control-color"
                               style="width: 100%; padding:2px;"
                               value="{{ old('bg_color', $menu?->bg_color ?? '#405189') }}">
                    </div>
                </div>

                <div class="mt-3 d-flex flex-column gap-2">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="statusSwitch"
                                       name="status"
                                       value="1"
                                    {{ old('status', $menu?->status ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusSwitch">{{ __('Status') }}</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="headerSwitch"
                                       name="in_header"
                                       value="1"
                                    {{ old('in_header', $menu?->in_header ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="headerSwitch">{{ __('In header') }}</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="mainPageSwitch"
                                       name="show_in_sitemap"
                                       value="1"
                                    {{ old('show_in_sitemap', $menu?->show_in_sitemap ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label"
                                       for="mainPageSwitch">{{ __('Show in Sitemap') }}</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="mainPageSwitch"
                                       name="show_on_main_page"
                                       value="1"
                                    {{ old('show_on_main_page', $menu?->show_on_main_page ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label"
                                       for="mainPageSwitch">{{ __('Show on main page') }}</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="footerSwitch"
                                       name="in_footer"
                                       value="1"
                                    {{ old('in_footer', $menu?->in_footer ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="footerSwitch">{{ __('In footer') }}</label>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('Main Image') }}</label>
                        <input type="file"
                               name="main_image"
                               class="form-control @error('main_image') is-invalid @enderror js-file-preview-input"
                               accept="{{ $allowedImages }}"
                               data-preview-image="#mainImagePreview"
                               data-preview-wrapper="#mainImagePreviewWrapper"
                               data-file-name="#mainImageFileName">

                        <div id="mainImagePreviewWrapper" class="mt-3 {{ !empty($menu?->main_image) ? '' : 'd-none' }}">
                            <img
                                id="mainImagePreview"
                                src="{{ !empty($menu?->main_image) ? asset('storage/' . $menu->main_image) : '' }}"
                                alt="main-image"
                                class="img-fluid rounded border">
                        </div>

                        <div id="mainImageFileName" class="form-text mt-2"></div>

                        @error('main_image')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6">
                        <label class="form-label">{{ __('Upload Icon (image)') }}</label>
                        <input type="file"
                               name="icon_image"
                               class="form-control @error('icon_image') is-invalid @enderror js-file-preview-input"
                               accept="{{ $allowedImages }}"
                               data-preview-image="#iconImagePreview"
                               data-preview-wrapper="#iconImagePreviewWrapper"
                               data-file-name="#iconImageFileName">

                        <div id="iconImagePreviewWrapper"
                             class="d-flex align-items-center gap-3 mt-3 {{ !empty($menu?->icon_image) ? '' : 'd-none' }}">
                            <img
                                id="iconImagePreview"
                                src="{{ !empty($menu?->icon_image) ? asset('storage/' . $menu->icon_image) : '' }}"
                                alt="icon"
                                class="rounded"
                                style="height:100px">
                        </div>

                        <div id="iconImageFileName" class="form-text mt-2"></div>

                        @error('icon_image')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    @include('menu::admin.menu.partials.icon-picker')
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ __('Included Items') }}</label>

                        <div class="included-menu-picker" id="includedMenuPicker">
                            <button type="button" class="included-menu-picker__control" id="includedMenuPickerControl"
                                    aria-expanded="false">
                            <span class="included-menu-picker__control-text" id="includedMenuPickerControlText">
                                {{ __('Select included item') }}
                            </span>
                                <span class="included-menu-picker__control-icon">
                                <i class="ri-arrow-down-s-line"></i>
                            </span>
                            </button>

                            <div class="included-menu-picker__dropdown" id="includedMenuPickerDropdown">
                                <div class="included-menu-picker__search-wrap">
                                    <i class="ri-search-line included-menu-picker__search-icon"></i>
                                    <input type="text"
                                           id="includedMenuSearch"
                                           class="included-menu-picker__search"
                                           placeholder="{{ __('Search included item') }}"
                                           autocomplete="off">
                                </div>

                                <div class="included-menu-picker__list" id="includedMenuOptionsList">
                                    @foreach(($includedItemOptions ?? []) as $option)
                                        <button type="button"
                                                class="included-menu-picker__item"
                                                data-type="{{ $option['type'] }}"
                                                data-id="{{ (int) $option['id'] }}"
                                                data-label="{{ $option['label'] }}"
                                                data-search="{{ $option['search'] ?? '' }}">
                                            <span class="included-menu-picker__item-text">{{ $option['label'] }}</span>
                                            <span class="included-menu-picker__item-action">
                                            <i class="ri-add-line"></i>
                                        </span>
                                        </button>
                                    @endforeach
                                </div>

                                <div class="included-menu-picker__empty d-none" id="includedMenuEmptyResult">
                                    {{ __('No item found') }}
                                </div>
                            </div>
                        </div>

                        @error('included_items')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror

                        @error('included_items.*.type')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror

                        @error('included_items.*.id')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror

                        <div class="included-menu-selected mt-3">
                            <div class="included-menu-selected__header">
                                <div class="included-menu-selected__title">{{ __('Selected Included Items') }}</div>
                                <div class="included-menu-selected__hint">{{ __('Drag and drop to sort.') }}</div>
                            </div>

                            <div id="includedMenusEmptyState"
                                 class="{{ count($selectedIncludedItems) > 0 ? 'd-none' : '' }} included-menu-selected__empty">
                                {{ __('No included items selected.') }}
                            </div>

                            <div id="includedMenusList" class="included-menu-selected__list">
                                @foreach($selectedIncludedItems as $selectedIncludedItemIndex => $selectedIncludedItem)
                                    @php
                                        $selectedIncludedItemKey = $selectedIncludedItem['type'] . ':' . (int) $selectedIncludedItem['id'];
                                        $selectedIncludedItemLabel = $includedItemOptionsMap[$selectedIncludedItemKey]['label'] ?? ('#' . (int) $selectedIncludedItem['id']);
                                    @endphp

                                    <div class="included-menu-card"
                                         data-type="{{ $selectedIncludedItem['type'] }}"
                                         data-id="{{ (int) $selectedIncludedItem['id'] }}">
                                        <div class="included-menu-card__left">
                                        <span class="included-menu-card__drag js-included-menu-sort-handle">
                                            <i class="ri-drag-move-2-line"></i>
                                        </span>
                                            <div class="included-menu-card__text">{{ $selectedIncludedItemLabel }}</div>
                                        </div>

                                        <div class="included-menu-card__right">
                                            <input type="hidden"
                                                   data-included-item-type-input
                                                   name="included_items[{{ $selectedIncludedItemIndex }}][type]"
                                                   value="{{ $selectedIncludedItem['type'] }}">
                                            <input type="hidden"
                                                   data-included-item-id-input
                                                   name="included_items[{{ $selectedIncludedItemIndex }}][id]"
                                                   value="{{ (int) $selectedIncludedItem['id'] }}">
                                            <button type="button"
                                                    class="included-menu-card__remove js-remove-included-menu">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-text mt-2">
                            {{ __('You can include current menu data, menus, sliders and brand filters.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                    @foreach($languages as $i => $lang)
                        @php
                            $isRequiredLanguage = in_array((string) $lang->code, $requiredLanguageCodesArray, true);
                        @endphp

                        <li class="nav-item">
                            <a class="nav-link {{ $i === 0 ? 'active' : '' }}"
                               data-bs-toggle="tab"
                               href="#tab-{{ $lang->code }}"
                               role="tab">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold text-uppercase">{{ $lang->code }}</span>
                                    <span>{{ $lang->name }}</span>
                                    @if($isRequiredLanguage)
                                        <span class="text-danger">*</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach($languages as $i => $lang)
                        @php
                            $tr = isset($menu) && $menu ? $menu->translations->firstWhere('locale', $lang->code) : null;
                            $isRequiredLanguage = in_array((string) $lang->code, $requiredLanguageCodesArray, true);

                            $keywordsHiddenId = "meta-keywords-hidden-{$lang->code}";
                            $keywordsWrapId = "meta-keywords-wrap-{$lang->code}";
                            $keywordsInputId = "meta-keywords-input-{$lang->code}";
                        @endphp

                        <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="tab-{{ $lang->code }}" role="tabpanel">
                            <input type="hidden" name="translations[{{ $i }}][locale]" value="{{ $lang->code }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        {{ __('Name') }}
                                        @if($isRequiredLanguage)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="text"
                                           name="translations[{{ $i }}][name]"
                                           class="form-control @error("translations.$i.name") is-invalid @enderror"
                                           data-menu-name="{{ $lang->code }}"
                                           value="{{ old("translations.$i.name", $tr?->name) }}"
                                        {{ $isRequiredLanguage ? 'required' : '' }}>
                                    @error("translations.$i.name")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Title') }}</label>
                                    <input type="text"
                                           name="translations[{{ $i }}][title]"
                                           class="form-control @error("translations.$i.title") is-invalid @enderror"
                                           value="{{ old("translations.$i.title", $tr?->title) }}">
                                    @error("translations.$i.title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <textarea name="translations[{{ $i }}][description]"
                                              class="form-control js-editor @error("translations.$i.description") is-invalid @enderror"
                                              rows="4">{{ old("translations.$i.description", $tr?->description) }}</textarea>
                                    @error("translations.$i.description")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('Link') }}</label>
                                    <input type="text"
                                           name="translations[{{ $i }}][link]"
                                           class="form-control @error("translations.$i.link") is-invalid @enderror"
                                           data-menu-link="{{ $lang->code }}"
                                           value="{{ old("translations.$i.link", $tr?->link) }}">
                                    @error("translations.$i.link")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">{{ __('Meta Title') }}</label>
                                <input type="text"
                                       name="translations[{{ $i }}][meta_title]"
                                       class="form-control"
                                       value="{{ old("translations.$i.meta_title", $tr?->meta_title) }}">
                            </div>

                            <div class="mt-3">
                                <label class="form-label">{{ __('Meta Description') }}</label>
                                <textarea name="translations[{{ $i }}][meta_description]"
                                          class="form-control"
                                          rows="4">{{ old("translations.$i.meta_description", $tr?->meta_description) }}</textarea>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">{{ __('Meta Keywords') }}</label>

                                <input type="hidden"
                                       id="{{ $keywordsHiddenId }}"
                                       name="translations[{{ $i }}][meta_keywords]"
                                       value="{{ old("translations.$i.meta_keywords", $tr?->meta_keywords) }}">

                                <div id="{{ $keywordsWrapId }}" class="d-flex flex-wrap gap-2 mb-2"></div>

                                <input type="text"
                                       id="{{ $keywordsInputId }}"
                                       class="form-control js-meta-keyword-input"
                                       data-hidden-id="{{ $keywordsHiddenId }}"
                                       data-wrap-id="{{ $keywordsWrapId }}"
                                       placeholder="{{ __('Type keyword and press Enter') }}"
                                       autocomplete="off">

                                <div class="form-text">{{ __('Keywords will be saved as comma-separated.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.menus.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button class="btn btn-primary">
                    <i class="ri-save-3-line align-bottom me-1"></i>{{ $submitLabel ?? __('Save') }}
                </button>
            </div>
        </div>
    </div>

</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/menu/css/form.css') }}">
@endpush
@push('scripts')
    @include('menu::admin.menu.partials.script')
@endpush
