@extends('layouts.guest', ['title' => __('Login')])

@section('content')
    <h1>{{ __('Log in') }}</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label><input type="checkbox" name="remember"> {{ __('Remember me') }}</label>
        </div>
        <button type="submit">{{ __('Log in') }}</button>
    </form>
    <p style="margin-top:1rem;text-align:center;font-size:0.875rem;">
        <a href="{{ route('register') }}">{{ __('Register') }}</a>
    </p>
@endsection
