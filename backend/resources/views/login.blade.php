<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — AssetKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-body">
<div id="page-root" data-page="login">
    <div class="auth-card">
        <div class="auth-brand">AssetKu</div>

        <h2>Masuk ke Admin Panel</h2>
        <p class="auth-subtitle">Kelola aset, kategori, dan riwayat pinjaman.</p>

        <div id="login-error" class="login-error">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <form id="login-form" method="POST" action="/login" autocomplete="on">
            @csrf

            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email"
                   value="{{ old('email', 'admin@example.com') }}" required autofocus>

            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password"
                   value="Password123!" required>

            <button class="btn btn-primary btn-block" type="submit">Masuk</button>
        </form>

        <div class="auth-hint">
            <strong>Akun demo:</strong> admin@example.com / Password123!
        </div>
    </div>
</div>

<div id="toast-host" class="toast-host"></div>
<script type="module" src="{{ asset('js/app.js') }}"></script>
</body>
</html>
