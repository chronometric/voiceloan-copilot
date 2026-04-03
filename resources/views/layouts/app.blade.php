<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f1f5f9; color: #0f172a; }
        header { background: #0f172a; color: #f8fafc; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
        header nav a { color: #93c5fd; text-decoration: none; margin-right: 1rem; }
        header nav a:hover { text-decoration: underline; }
        main { max-width: 960px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .status { color: #0c7a3b; margin-bottom: 1rem; }
        label { display: block; font-size: 0.875rem; margin-bottom: 0.25rem; color: #475569; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select, textarea {
            width: 100%; max-width: 28rem; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1; box-sizing: border-box;
        }
        textarea { max-width: 100%; min-height: 4rem; }
        .field { margin-bottom: 1rem; }
        .error { color: #b91c1c; font-size: 0.875rem; margin-top: 0.25rem; }
        button.btn { padding: 0.5rem 1rem; border-radius: 6px; border: none; background: #2563eb; color: white; font-weight: 600; cursor: pointer; }
        button.btn:hover { background: #1d4ed8; }
        button.btn-danger { background: #b91c1c; }
        button.btn-danger:hover { background: #991b1b; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th, td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        th { background: #f8fafc; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        .tabs { display: flex; flex-wrap: wrap; gap: 0.25rem; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }
        .tabs a { padding: 0.5rem 0.75rem; border-radius: 6px 6px 0 0; text-decoration: none; color: #475569; font-size: 0.875rem; }
        .tabs a.active { background: #e0e7ff; color: #1e3a8a; font-weight: 600; }
        .tabs a:hover:not(.active) { background: #f1f5f9; }
        code { font-size: 0.8rem; word-break: break-all; }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            <a href="{{ route('borrowers.index') }}">{{ __('Borrowers') }}</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:#93c5fd;cursor:pointer;text-decoration:underline">{{ __('Log out') }}</button>
        </form>
    </header>
    <main>
        @if(session('status'))
            <p class="status">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>
