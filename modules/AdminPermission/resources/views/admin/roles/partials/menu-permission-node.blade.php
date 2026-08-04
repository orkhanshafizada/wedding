@php
    $permissionsForMenu = collect($menuPermissions->get($menu->id, []))->keyBy('action');
    $name = $menuName($menu);
    $type = $menuTypeValue($menu);
    $search = trim($name . ' ' . $type . ' ' . $menu->id);
@endphp

<div class="menu-permission-row {{ $level === 0 ? 'menu-permission-header' : '' }}" @if($level === 0) data-header-row="1" @endif>
    <div>{{ $level === 0 ? __('Menu') : '' }}</div>
    <div class="menu-permission-action">{{ $level === 0 ? __('All') : '' }}</div>
    <div class="menu-permission-action">{{ $level === 0 ? __('View') : '' }}</div>
    <div class="menu-permission-action">{{ $level === 0 ? __('Content') : '' }}</div>
    <div class="menu-permission-action">{{ $level === 0 ? __('Edit') : '' }}</div>
    <div class="menu-permission-action">{{ $level === 0 ? __('Delete') : '' }}</div>
</div>

<div class="menu-permission-row"
     data-permission-row="1"
     data-menu-permission-row="1"
     data-search="{{ $search }}">
    <div class="menu-permission-title" style="padding-left: {{ max(0, $level) * 18 }}px">
        <div class="menu-permission-name">{{ $name }}</div>
        <div class="menu-permission-meta">
            #{{ $menu->id }}
            @if($type !== '')
                · {{ ucfirst(str_replace('_', ' ', $type)) }}
            @endif
        </div>
    </div>

    <div class="menu-permission-action">
        <div class="form-check form-switch d-inline-flex m-0">
            <input class="form-check-input js-row-master js-menu-row-master" type="checkbox">
        </div>
    </div>

    @foreach($menuPermissionActions as $action)
        @php
            $permission = $permissionsForMenu->get($action);
        @endphp

        <div class="menu-permission-action">
            @if($permission)
                <div class="form-check form-switch d-inline-flex m-0">
                    <input class="form-check-input js-permission"
                           type="checkbox"
                           name="permissions[]"
                           value="{{ $permission->id }}"
                        @checked(in_array((int) $permission->id, $selectedPermissionIds, true))>
                </div>
            @else
                <div class="form-check form-switch d-inline-flex m-0 opacity-50">
                    <input class="form-check-input" type="checkbox" disabled>
                </div>
            @endif
        </div>
    @endforeach
</div>

@if($menu->childrenRecursive->isNotEmpty())
    @foreach($menu->childrenRecursive as $childMenu)
        @include('adminpermission::admin.roles.partials.menu-permission-node', [
            'menu' => $childMenu,
            'level' => $level + 1,
            'menuPermissions' => $menuPermissions,
            'selectedPermissionIds' => $selectedPermissionIds,
            'menuPermissionActions' => $menuPermissionActions,
            'menuName' => $menuName,
            'menuTypeValue' => $menuTypeValue,
        ])
    @endforeach
@endif
