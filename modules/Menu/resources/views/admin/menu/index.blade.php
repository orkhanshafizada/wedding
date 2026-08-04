@extends('admin.layouts.app')
@section('title', __('menus'))
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="menu-admin-page">
                <div class="menu-admin-header">
                    <div>
                        <h4 class="menu-admin-title">{{ __('Menu settings') }}</h4>
                        <p class="menu-admin-subtitle">{{ __('Manage menu structure, content links, visibility and ordering.') }}</p>
                    </div>

                    @can('menu.create')
                        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary menu-admin-create-button">
                            <i class="ri-add-line align-bottom me-1"></i>{{ __('Add new') }}
                        </a>
                    @endcan
                </div>

                <div class="menu-admin-toolbar">
                    <div class="menu-admin-toolbar-row">
                        <div class="menu-admin-search">
                            <i class="ri-search-line"></i>
                            <input type="text"
                                   id="menu-search"
                                   class="form-control"
                                   placeholder="{{ __('Search menus...') }}"
                                   autocomplete="off">
                        </div>

                        <div class="menu-admin-toolbar-meta">
                            <span class="menu-admin-count" id="menu-search-count" style="display:none;"></span>
                            <span class="menu-admin-hint">
                                <i class="ri-drag-move-2-line"></i>
                                {{ __('Drag rows to reorder') }}
                            </span>
                        </div>
                    </div>

                    <div class="menu-admin-tabs" id="menu-type-tabs">
                        <button type="button" class="menu-admin-tab active js-menu-type-tab" data-type="all">
                            {{ __('All') }}
                        </button>

                        @foreach($types as $type)
                            @php
                                $typeValue = $type instanceof \Modules\Menu\Enums\MenuType ? $type->value : (string) $type;
                                $typeLabel = $type instanceof \Modules\Menu\Enums\MenuType ? $type->label() : ucfirst(str_replace('_', ' ', $typeValue));
                            @endphp

                            <button type="button" class="menu-admin-tab js-menu-type-tab" data-type="{{ $typeValue }}">
                                {{ $typeLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="menu-admin-table">
                    <div class="menu-admin-table-head">
                        <div class="menu-admin-grid">
                            <div>{{ __('Menu') }}</div>
                            <div>{{ __('Content') }}</div>
                            <div>{{ __('Visibility') }}</div>
                            <div class="text-end">{{ __('Actions') }}</div>
                        </div>
                    </div>

                    <div id="menu-root" class="menu-admin-table-body menu-children" data-parent="">
                        @foreach($tree as $node)
                            @include('menu::admin.menu.partials.node', ['node' => $node, 'level' => 0])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/menu/css/menu.css') }}">
{{--    <link href="{{ route('modules.assets', ['module' => 'menu', 'path' => 'css/menu.css']) }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet" type="text/css">--}}
@endpush
@push('scripts')
    <script>
        window.menuAdminRoutes = {
            reorder: @json(route('admin.menus.reorder')),
            savedText: @json(__('Saved')),
            orderUpdatedText: @json(__('Order updated')),
            errorText: @json(__('Error')),
            foundText: @json(__('Found'))
        };
    </script>
    <script src="{{ asset('modules/menu/js/menu.js') }}"></script>
{{--    <script src="{{ route('modules.assets', ['module' => 'menu', 'path' => 'js/menu.js']) }}?v={{ config('app.asset_version', '1') }}"></script>--}}
@endpush
