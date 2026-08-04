@php
    $level = $level ?? 0;

    $locale = (string) ($adminDefaultLanguageCode ?: app()->getLocale());

    $name = optional($node->translations->firstWhere('locale', $locale))->name
        ?? optional($node->translations->first())->name
        ?? '—';

    $searchText = $name;

    foreach (($node->translations ?? collect()) as $translation) {
        if (!empty($translation->name)) {
            $searchText .= ' ' . $translation->name;
        }

        if (!empty($translation->link)) {
            $searchText .= ' ' . $translation->link;
        }
    }

    $parentId = (int) ($node->parent_id ?? 0);

    $sub = (string) (
        optional($node->translations->firstWhere('locale', $locale))->link
        ?? optional($node->translations->first())->link
        ?? ''
    );

    $typeValue = $node->type instanceof \Modules\Menu\Enums\MenuType
        ? $node->type->value
        : (string) ($node->type ?? '');

    $typeLabel = $node->type instanceof \Modules\Menu\Enums\MenuType
        ? $node->type->label()
        : ucfirst(str_replace('_', ' ', $typeValue));

    $hasChildren = $node->children->isNotEmpty();
@endphp

<div class="menu-admin-item"
     data-id="{{ $node->id }}"
     data-menu-card="1"
     data-parent-id="{{ $parentId > 0 ? $parentId : '' }}"
     data-menu-type="{{ strtolower($typeValue) }}"
     data-search="{{ $searchText }}">
    <div class="menu-admin-item-row">
        <div class="menu-admin-grid">
            <div class="menu-admin-name-cell" style="padding-left: {{ max(0, $level) * 18 }}px">
                @can('menu.edit', $node)
                    <span class="menu-admin-drag" title="{{ __('Drag to reorder') }}">
                        <i class="ri-drag-move-2-fill"></i>
                    </span>
                @else
                    <span class="menu-admin-drag is-disabled" title="{{ __('No access') }}">
                        <i class="ri-lock-line"></i>
                    </span>
                @endcan

                @if($hasChildren)
                    <button type="button"
                            class="menu-admin-tree-toggle js-tree-toggle"
                            aria-expanded="true"
                            title="{{ __('Collapse / Expand') }}">
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                @else
                    <span class="menu-admin-tree-placeholder"></span>
                @endif

                <div class="menu-admin-name-content">
                    <div class="menu-admin-name-line">
                        <span class="menu-admin-name">{{ $name }}</span>

                        @if($typeValue !== '')
                            <span class="menu-admin-type">{{ $typeLabel }}</span>
                        @endif
                    </div>

                    @if($sub !== '')
                        <div class="menu-admin-slug">{{ $sub }}</div>
                    @endif
                </div>
            </div>

            <div class="menu-admin-content-cell">
                @if(strtolower($typeValue) === 'link')
                    <span class="menu-admin-content-muted">
                        <i class="ri-link"></i>{{ __('External link') }}
                    </span>
                @else
                    @can('menu.content', $node)
                        <a href="{{ route('admin.menus.route', $node) }}" class="menu-admin-content-link">
                            <i class="ri-file-list-3-line"></i>{{ __('Content') }}
                        </a>
                    @else
                        <span class="menu-admin-content-muted">
                            <i class="ri-lock-line"></i>{{ __('No access') }}
                        </span>
                    @endcan
                @endif
            </div>

            <div class="menu-admin-visibility-cell">
                <label class="menu-admin-switch">
                    <input class="toggle"
                           type="checkbox"
                           data-field="status"
                           data-url="{{ route('admin.menus.toggle', $node) }}"
                           @cannot('menu.edit', $node) disabled @endcannot
                        {{ $node->status ? 'checked' : '' }}>
                    <span></span>
                    <strong>{{ __('Status') }}</strong>
                </label>

                <label class="menu-admin-switch">
                    <input class="toggle"
                           type="checkbox"
                           data-field="show_on_main_page"
                           data-url="{{ route('admin.menus.toggle', $node) }}"
                           @cannot('menu.edit', $node) disabled @endcannot
                        {{ $node->show_on_main_page ? 'checked' : '' }}>
                    <span></span>
                    <strong>{{ __('Main') }}</strong>
                </label>

                <label class="menu-admin-switch">
                    <input class="toggle"
                           type="checkbox"
                           data-field="in_header"
                           data-url="{{ route('admin.menus.toggle', $node) }}"
                           @cannot('menu.edit', $node) disabled @endcannot
                        {{ $node->in_header ? 'checked' : '' }}>
                    <span></span>
                    <strong>{{ __('Header') }}</strong>
                </label>

                <label class="menu-admin-switch">
                    <input class="toggle"
                           type="checkbox"
                           data-field="in_footer"
                           data-url="{{ route('admin.menus.toggle', $node) }}"
                           @cannot('menu.edit', $node) disabled @endcannot
                        {{ $node->in_footer ? 'checked' : '' }}>
                    <span></span>
                    <strong>{{ __('Footer') }}</strong>
                </label>

                <label class="menu-admin-switch">
                    <input class="toggle"
                           type="checkbox"
                           data-field="show_in_sitemap"
                           data-url="{{ route('admin.menus.toggle', $node) }}"
                           @cannot('menu.edit', $node) disabled @endcannot
                        {{ $node->show_in_sitemap ? 'checked' : '' }}>
                    <span></span>
                    <strong>{{ __('Sitemap') }}</strong>
                </label>
            </div>

            <div class="menu-admin-actions-cell">
                <div class="dropdown">
                    <button class="btn menu-admin-action-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ __('Actions') }}
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        @can('menu.edit', $node)
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.menus.edit', $node) }}">
                                    <i class="ri-edit-2-line me-1"></i>{{ __('Edit') }}
                                </a>
                            </li>
                        @endcan

                        @can('menu.create')
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.menus.create') }}?parent_id={{ $node->id }}">
                                    <i class="ri-add-line me-1"></i>{{ __('Add child') }}
                                </a>
                            </li>
                        @endcan

                        @can('menu.delete', $node)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.menus.destroy', $node) }}" method="post" onsubmit="return confirm('{{ __('Delete?') }}')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="ri-delete-bin-6-line me-1"></i>{{ __('Delete') }}
                                    </button>
                                </form>
                            </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if($hasChildren)
        <div class="menu-admin-children menu-children" data-parent="{{ $node->id }}">
            @foreach($node->children as $child)
                @include('menu::admin.menu.partials.node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
