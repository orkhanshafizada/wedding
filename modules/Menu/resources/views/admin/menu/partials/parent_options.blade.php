@php
$level = $level ?? 0;
@endphp
@foreach($nodes as $node)
    @php
        $labelPrefix = $level > 0 ? str_repeat('--', $level) . ' ' : '';
        $label = optional($node->translations->firstWhere('locale', $locale))->name
            ?? optional($node->translations->first())->name
            ?? "#$node->id";
    @endphp

    <option value="{{ $node->id }}"
        {{ (int)($selected ?? 0) === (int)$node->id ? 'selected' : '' }}
        {{ isset($excludeId) && (int)$excludeId === (int)$node->id ? 'disabled' : '' }}>
        {{ $labelPrefix . $label }}
    </option>

    @if($node->childrenRecursive->isNotEmpty())
        @include('menu::admin.menu.partials.parent_options', [
            'nodes' => $node->childrenRecursive,
            'selected' => $selected ?? null,
            'excludeId' => $excludeId ?? null,
            'locale' => $locale,
            'level' => $level + 1,
        ])
    @endif
@endforeach
