<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Dashboard') }} — {{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; background: #f8fafc; color: #0f172a; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        th, td { text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        form.inline { display: inline; }
        button.link { background: none; border: none; color: #2563eb; cursor: pointer; text-decoration: underline; font-size: inherit; }
    </style>
</head>
<body>
    <header>
        <h1 style="margin:0;font-size:1.25rem;">{{ __('VoiceLoan Copilot') }}</h1>
        <form class="inline" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="link" type="submit">{{ __('Log out') }}</button>
        </form>
    </header>
    <p>{{ __('Signed in as') }} <strong>{{ auth()->user()->email }}</strong></p>
    <h2 style="font-size:1rem;margin-top:2rem;">{{ __('Recent borrowers') }}</h2>
    @if($borrowers->isEmpty())
        <p style="color:#64748b;">{{ __('No borrowers yet. Create records via API or seeders.') }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>{{ __('UUID') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Display name') }}</th>
                    <th>{{ __('Updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($borrowers as $b)
                    <tr>
                        <td><code>{{ substr($b->uuid, 0, 13) }}…</code></td>
                        <td>{{ $b->status }}</td>
                        <td>{{ $b->display_name ?? '—' }}</td>
                        <td>{{ $b->updated_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
