@props(['field', 'align' => null])
@php
    $currentSort = request('sort');
    $currentDir = request('direction', 'asc');
    $isActive = $currentSort === $field;
    $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->except(['sort', 'direction', 'page']), ['sort' => $field, 'direction' => $nextDir]);
@endphp
<th class="{{ $align === 'end' ? 'text-end' : '' }}">
    <a href="{{ request()->fullUrlWithQuery($query) }}" class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">
        {{ $slot }}
        @if ($isActive)
            <i class="bi bi-caret-{{ $currentDir === 'asc' ? 'up' : 'down' }}-fill" style="font-size:.6rem;"></i>
        @else
            <i class="bi bi-caret-down" style="font-size:.6rem;opacity:.25;"></i>
        @endif
    </a>
</th>
