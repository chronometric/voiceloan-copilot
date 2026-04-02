@extends('layouts.guest', ['title' => __('Register')])

@section('content')
    <h1>{{ __('Register') }}</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
            <label for="name">{{ __('Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button type="submit">{{ __('Register') }}</button>
    </form>
    <p style="margin-top:1rem;text-align:center;font-size:0.875rem;">
        <a href="{{ route('login') }}">{{ __('Already registered?') }}</a>
    </p>
@endsection
