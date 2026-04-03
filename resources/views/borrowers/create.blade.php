@extends('layouts.app')

@section('title', __('New borrower'))

@section('content')
    <div class="card">
        <h1 style="margin:0 0 1rem;font-size:1.25rem">{{ __('New borrower') }}</h1>
        <form method="POST" action="{{ route('borrowers.store') }}">
            @csrf
            <div class="field">
                <label for="display_name">{{ __('Display name') }}</label>
                <input id="display_name" type="text" name="display_name" value="{{ old('display_name') }}">
                @error('display_name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="email">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
                @error('email')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="phone">{{ __('Phone') }}</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}">
                @error('phone')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="status">{{ __('Status') }}</label>
                <select id="status" name="status">
                    @foreach(['draft' => __('Draft'), 'in_progress' => __('In progress'), 'submitted' => __('Submitted')] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', 'draft') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn">{{ __('Create') }}</button>
            <a href="{{ route('borrowers.index') }}" style="margin-left:1rem">{{ __('Cancel') }}</a>
        </form>
    </div>
@endsection
