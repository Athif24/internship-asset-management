@php
    $badgeMap = [
        'AVAILABLE' => ['badge-success', 'AVAILABLE'],
        'BORROWED' => ['badge-warning', 'BORROWED'],
        'MAINTENANCE' => ['badge-secondary', 'MAINTENANCE'],
        'RETURNED' => ['badge-success', 'RETURNED'],
    ];
    [$badgeClass, $badgeLabel] = $badgeMap[$status] ?? ['badge-secondary', $status ?: '-'];
@endphp
<span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
