<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DataBridge') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <main class="auth-screen">
        <section class="auth-visual" style="background-image: linear-gradient(90deg, rgba(2, 6, 23, .96), rgba(2, 6, 23, .66), rgba(2, 6, 23, .15)), url('{{ asset('images/data-sync-dashboard.png') }}')">
            <div class="auth-copy">
                <p class="eyebrow">VFP Data Transfer Console</p>
                <h1>Monitor records cleanly from the web dashboard.</h1>
                <p>Authentication and the dashboard base are ready. The interface is intentionally structured for API integration.</p>
            </div>
        </section>

        <section class="auth-panel">
            {{ $slot }}
        </section>
    </main>
</body>
</html>
