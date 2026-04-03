@extends('layouts.app')

@section('title', __('Borrowers'))

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
            <h1 style="margin:0;font-size:1.25rem">{{ __('Borrowers') }}</h1>
            <a href="{{ route('borrowers.create') }}" style="display:inline-block;text-decoration:none;padding:0.5rem 1rem;background:#2563eb;color:white;border-radius:6px;font-weight:600">{{ __('New borrower') }}</a>
        </div>
        @if($borrowers->isEmpty())
            <p style="color:#64748b">{{ __('No borrowers yet.') }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Updated') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowers as $b)
                        <tr>
                            <td>{{ $b->display_name ?? '—' }}</td>
                            <td>{{ $b->status }}</td>
                            <td>{{ $b->email ?? '—' }}</td>
                            <td>{{ $b->updated_at?->diffForHumans() }}</td>
                            <td><a href="{{ route('borrowers.edit', $b) }}">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:1rem">{{ $borrowers->links() }}</div>
        @endif
    </div>
@endsection
