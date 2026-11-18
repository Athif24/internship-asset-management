@php
    $dateMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $loanDate = $date instanceof \DateTimeInterface ? $date : null;
@endphp
@if ($loanDate){{ $loanDate->format('d') }} {{ $dateMonths[(int) $loanDate->format('m') - 1] }} {{ $loanDate->format('Y, H:i') }}@else-@endif
