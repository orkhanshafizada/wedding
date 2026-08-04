@php
    $active = static fn (string $pattern): string => request()->routeIs($pattern) ? 'active' : '';
@endphp

<div id="scrollbar">
    <div class="container-fluid">
        <div id="two-column-menu"></div>

        <ul class="navbar-nav" id="navbar-nav">
            @can('menu.view')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ $active('admin.menus.*') }}"
                       href="{{ route('admin.menus.index') }}">
                        <i class="ri-menu-2-line"></i>
                        <span>{{ __('Menus') }}</span>
                    </a>
                </li>
            @endcan
        </ul>
    </div>
</div>
