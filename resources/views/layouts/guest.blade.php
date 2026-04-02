<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        :root { font-family: system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #0f172a; color: #e2e8f0; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.5); }
        label { display: block; font-size: 0.875rem; margin-bottom: 0.25rem; color: #94a3b8; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #f8fafc; box-sizing: border-box; }
        .field { margin-bottom: 1rem; }
        .error { color: #f87171; font-size: 0.875rem; margin-top: 0.25rem; }
        button { width: 100%; padding: 0.65rem; border: none; border-radius: 8px; background: #3b82f6; color: white; font-weight: 600; cursor: pointer; }
        button:hover { background: #2563eb; }
        a { color: #93c5fd; }
        h1 { margin: 0 0 1.5rem; font-size: 1.25rem; }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>
