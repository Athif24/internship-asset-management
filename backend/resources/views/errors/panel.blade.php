@extends('layouts.app')

@section('content')
    <div class="card page-error-panel">
        <h1>{{ $title }}</h1>
        <p class="cell-muted">{{ $message }}</p>
        @if ($status === 403)
            {{-- Akses ditolak: tidak ada retry, sidebar tetap terlihat. --}}
        @else
            <button type="button" class="btn sec" onclick="window.location.reload()">Coba lagi</button>
        @endif
    </div>
@endsection
