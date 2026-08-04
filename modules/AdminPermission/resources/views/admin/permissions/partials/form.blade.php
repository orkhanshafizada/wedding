<div class="page-content">
    <div class="container-fluid">
        <form action="{{ $action }}" method="post">
            @csrf
            @if($method)
                @method($method)
            @endif

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">{{ $permission->exists ? __('Edit permission') : __('Add permission') }}</h4>
                    <div class="text-muted small">{{ __('Create custom system or menu permissions.') }}</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line align-bottom me-1"></i>{{ __('Save') }}
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ __('Permission name') }}</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $permission->name) }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="product.view">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ __('Display name') }}</label>
                            <input type="text"
                                   name="display_name"
                                   value="{{ old('display_name', $permission->display_name) }}"
                                   class="form-control @error('display_name') is-invalid @enderror"
                                   placeholder="{{ __('Products - View') }}">
                            @error('display_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('Group') }}</label>
                            <input type="text"
                                   name="group"
                                   value="{{ old('group', $permission->group) }}"
                                   class="form-control @error('group') is-invalid @enderror"
                                   placeholder="Catalog">
                            @error('group')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('Scope') }}</label>
                            <select name="scope" class="form-select @error('scope') is-invalid @enderror">
                                @foreach(['system', 'menu'] as $scope)
                                    <option value="{{ $scope }}" @selected(old('scope', $permission->scope ?: 'system') === $scope)>
                                        {{ __(ucfirst($scope)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scope')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('Menu') }}</label>
                            <select name="menu_id" class="form-select @error('menu_id') is-invalid @enderror">
                                <option value="">{{ __('Not selected') }}</option>
                                @foreach($menus as $menu)
                                    @php
                                        $menuTitle = $menu->translations->firstWhere('locale', app()->getLocale())?->name
                                            ?? $menu->translations->first()?->name
                                            ?? ('Menu #' . $menu->id);
                                    @endphp
                                    <option value="{{ $menu->id }}" @selected((int) old('menu_id', $permission->menu_id) === (int) $menu->id)>
                                        {{ $menuTitle }} #{{ $menu->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('menu_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('Module') }}</label>
                            <input type="text"
                                   name="module"
                                   value="{{ old('module', $permission->module) }}"
                                   class="form-control @error('module') is-invalid @enderror"
                                   placeholder="product">
                            @error('module')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('Action') }}</label>
                            <input type="text"
                                   name="action"
                                   value="{{ old('action', $permission->action) }}"
                                   class="form-control @error('action') is-invalid @enderror"
                                   placeholder="view">
                            @error('action')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('Sort order') }}</label>
                            <input type="number"
                                   name="sort_order"
                                   value="{{ old('sort_order', $permission->sort_order ?? 0) }}"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   min="0">
                            @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked((bool) old('is_active', $permission->exists ? $permission->is_active : true))>
                                <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
