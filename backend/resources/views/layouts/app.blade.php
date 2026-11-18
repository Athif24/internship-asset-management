<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — AssetKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">AssetKu</div>

        <button type="button" class="side-toggle" id="menu-toggle" title="Tutup menu" aria-label="Tutup menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M13 6l-6 6 6 6"></path><path d="M19 6l-6 6 6 6"></path>
            </svg>
            <span class="nav-text">Toggle</span>
        </button>

        <nav class="nav">
            <a href="/dashboard" class="nav-item {{ $page === 'dashboard' ? 'active' : '' }}" title="Dashboard" aria-label="Dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect>
                </svg>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="/assets" class="nav-item {{ $page === 'assets' ? 'active' : '' }}" title="Aset" aria-label="Aset">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 16V8l-9-5-9 5v8l9 5 9-5z"></path><path d="M3 8l9 5 9-5"></path><path d="M12 13v8"></path>
                </svg>
                <span class="nav-text">Aset</span>
            </a>
            <a href="/categories" class="nav-item {{ $page === 'categories' ? 'active' : '' }}" title="Kategori" aria-label="Kategori">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M8 6h13M8 12h13M8 18h13"></path><path d="M3 6h.01M3 12h.01M3 18h.01"></path>
                </svg>
                <span class="nav-text">Kategori</span>
            </a>
            <a href="/loans" class="nav-item {{ $page === 'loans' ? 'active' : '' }}" title="Riwayat Pinjam" aria-label="Riwayat Pinjam">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 3-6.7L3 8"></path><path d="M3 3v5h5"></path><path d="M12 7v5l3 2"></path>
                </svg>
                <span class="nav-text">Riwayat Pinjam</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <button type="button" class="btn-logout" id="btn-logout" title="Logout" aria-label="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path>
                </svg>
                <span class="nav-text">Logout</span>
            </button>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <button type="button" class="mobile-toggle" id="mobile-menu-toggle" title="Buka menu" aria-label="Buka menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16"></path>
        </svg>
    </button>

    <div class="main-wrap">
        <main class="content">
            <div id="page-root" data-page="{{ $page }}"
                 @isset($summary) data-summary='@json($summary)' @endisset
                 @isset($assets) data-assets='@json($assets)' @endisset
                 @isset($categories) data-categories='@json($categories)' @endisset
                 @isset($divisions) data-divisions='@json($divisions)' @endisset
                 @isset($loans) data-loans='@json($loans)' @endisset
                 @isset($total) data-total="{{ $total }}" @endisset
            >
                @yield('content')
            </div>
        </main>
    </div>
</div>

<div id="toast-host" class="toast-host"></div>

<form id="logout-form" action="/logout" method="POST" hidden>
    @csrf
</form>

<script type="module" src="{{ asset('js/app.js') }}"></script>
</body>
</html>
