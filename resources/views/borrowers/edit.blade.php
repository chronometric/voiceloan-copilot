@extends('layouts.app')

@section('title', __('Edit borrower'))

@section('content')
    @php
        $identity = $borrower->identity;
        $declaration = $borrower->declaration;
        $tabs = [
            'main' => __('Main'),
            'identity' => __('Identity'),
            'employment' => __('Employment'),
            'assets' => __('Assets'),
            'declarations' => __('Declarations'),
            'audit' => __('Audit'),
        ];
    @endphp
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem">
            <h1 style="margin:0;font-size:1.25rem">{{ __('Borrower') }} — {{ $borrower->display_name ?? $borrower->uuid }}</h1>
            <div>
                <a href="{{ route('borrowers.index') }}">{{ __('Back to list') }}</a>
            </div>
        </div>

        <nav class="tabs" aria-label="{{ __('Sections') }}">
            @foreach($tabs as $key => $label)
                <a href="{{ route('borrowers.edit', $borrower) }}?tab={{ $key }}"
                   class="{{ ($tab ?? 'main') === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>

        @if(($tab ?? 'main') === 'main')
            <h2 style="font-size:1rem;margin:0 0 1rem">{{ __('Main') }}</h2>
            <form method="POST" action="{{ route('borrowers.update', $borrower) }}">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label for="display_name">{{ __('Display name') }}</label>
                    <input id="display_name" type="text" name="display_name" value="{{ old('display_name', $borrower->display_name) }}">
                    @error('display_name')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="email">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $borrower->email) }}">
                    @error('email')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="phone">{{ __('Phone') }}</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $borrower->phone) }}">
                    @error('phone')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="status">{{ __('Status') }}</label>
                    <select id="status" name="status">
                        @foreach(['draft' => __('Draft'), 'in_progress' => __('In progress'), 'submitted' => __('Submitted'), 'escalated' => __('Escalated (human)')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $borrower->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn">{{ __('Save') }}</button>
            </form>

            <hr style="margin:2rem 0;border:none;border-top:1px solid #e2e8f0">
            <form method="POST" action="{{ route('borrowers.destroy', $borrower) }}" onsubmit="return confirm('{{ __('Delete this borrower?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('Delete borrower') }}</button>
            </form>
        @endif

        @if(($tab ?? 'main') === 'identity')
            <h2 style="font-size:1rem;margin:0 0 1rem">{{ __('Identity') }}</h2>
            <form method="POST" action="{{ route('borrowers.identity.update', $borrower) }}">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label for="first_name">{{ __('First name') }}</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $identity->first_name) }}">
                    @error('first_name')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="middle_name">{{ __('Middle name') }}</label>
                    <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name', $identity->middle_name) }}">
                </div>
                <div class="field">
                    <label for="last_name">{{ __('Last name') }}</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $identity->last_name) }}">
                </div>
                <div class="field">
                    <label for="date_of_birth">{{ __('Date of birth') }}</label>
                    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($identity->date_of_birth)->format('Y-m-d')) }}">
                    @error('date_of_birth')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="ssn_last4">{{ __('SSN last 4') }}</label>
                    <input id="ssn_last4" type="text" name="ssn_last4" maxlength="4" value="{{ old('ssn_last4', $identity->ssn_last4) }}">
                    @error('ssn_last4')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="address_line1">{{ __('Address line 1') }}</label>
                    <input id="address_line1" type="text" name="address_line1" value="{{ old('address_line1', $identity->address_line1) }}">
                </div>
                <div class="field">
                    <label for="address_line2">{{ __('Address line 2') }}</label>
                    <input id="address_line2" type="text" name="address_line2" value="{{ old('address_line2', $identity->address_line2) }}">
                </div>
                <div class="field">
                    <label for="city">{{ __('City') }}</label>
                    <input id="city" type="text" name="city" value="{{ old('city', $identity->city) }}">
                </div>
                <div class="field">
                    <label for="state">{{ __('State') }}</label>
                    <input id="state" type="text" name="state" value="{{ old('state', $identity->state) }}">
                </div>
                <div class="field">
                    <label for="postal_code">{{ __('Postal code') }}</label>
                    <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $identity->postal_code) }}">
                </div>
                <div class="field">
                    <label for="country">{{ __('Country') }}</label>
                    <input id="country" type="text" name="country" maxlength="2" value="{{ old('country', $identity->country ?? 'US') }}">
                </div>
                <div class="field">
                    <label for="citizenship_status">{{ __('Citizenship status') }}</label>
                    <input id="citizenship_status" type="text" name="citizenship_status" value="{{ old('citizenship_status', $identity->citizenship_status) }}">
                </div>
                <button type="submit" class="btn">{{ __('Save identity') }}</button>
            </form>
        @endif

        @if(($tab ?? 'main') === 'employment')
            <h2 style="font-size:1rem;margin:0 0 1rem">{{ __('Employment') }}</h2>
            @foreach($borrower->employments as $employment)
                <div style="border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:1rem">
                    <form method="POST" action="{{ route('borrowers.employments.update', [$borrower, $employment]) }}">
                        @csrf
                        @method('PATCH')
                        <div class="field">
                            <label>{{ __('Employer') }}</label>
                            <input type="text" name="employer_name" value="{{ old('employer_name', $employment->employer_name) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Job title') }}</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $employment->job_title) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Years in line of work') }}</label>
                            <input type="number" name="years_in_line_of_work" min="0" max="100" value="{{ old('years_in_line_of_work', $employment->years_in_line_of_work) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Monthly income (cents)') }}</label>
                            <input type="number" name="monthly_income_cents" min="0" value="{{ old('monthly_income_cents', $employment->monthly_income_cents) }}">
                        </div>
                        <div class="field">
                            <input type="hidden" name="is_current" value="0">
                            <label><input type="checkbox" name="is_current" value="1" @checked(old('is_current', $employment->is_current))> {{ __('Current job') }}</label>
                        </div>
                        <button type="submit" class="btn">{{ __('Update') }}</button>
                    </form>
                    <form method="POST" action="{{ route('borrowers.employments.destroy', [$borrower, $employment]) }}" style="margin-top:0.5rem" onsubmit="return confirm('{{ __('Remove this employment?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('Remove') }}</button>
                    </form>
                </div>
            @endforeach
            <h3 style="font-size:0.9rem">{{ __('Add employment') }}</h3>
            <form method="POST" action="{{ route('borrowers.employments.store', $borrower) }}">
                @csrf
                <div class="field">
                    <label>{{ __('Employer') }}</label>
                    <input type="text" name="employer_name" value="{{ old('employer_name') }}">
                </div>
                <div class="field">
                    <label>{{ __('Job title') }}</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}">
                </div>
                <div class="field">
                    <label>{{ __('Years in line of work') }}</label>
                    <input type="number" name="years_in_line_of_work" min="0" max="100" value="{{ old('years_in_line_of_work') }}">
                </div>
                <div class="field">
                    <label>{{ __('Monthly income (cents)') }}</label>
                    <input type="number" name="monthly_income_cents" min="0" value="{{ old('monthly_income_cents') }}">
                </div>
                <div class="field">
                    <input type="hidden" name="is_current" value="0">
                    <label><input type="checkbox" name="is_current" value="1" @checked(old('is_current', true))> {{ __('Current job') }}</label>
                </div>
                <button type="submit" class="btn">{{ __('Add employment') }}</button>
            </form>
        @endif

        @if(($tab ?? 'main') === 'assets')
            <h2 style="font-size:1rem;margin:0 0 1rem">{{ __('Assets') }}</h2>
            @foreach($borrower->assets as $asset)
                <div style="border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:1rem">
                    <form method="POST" action="{{ route('borrowers.assets.update', [$borrower, $asset]) }}">
                        @csrf
                        @method('PATCH')
                        <div class="field">
                            <label>{{ __('Type') }}</label>
                            <input type="text" name="asset_type" value="{{ old('asset_type', $asset->asset_type) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Description') }}</label>
                            <input type="text" name="description" value="{{ old('description', $asset->description) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Value (cents)') }}</label>
                            <input type="number" name="value_cents" min="0" value="{{ old('value_cents', $asset->value_cents) }}">
                        </div>
                        <button type="submit" class="btn">{{ __('Update') }}</button>
                    </form>
                    <form method="POST" action="{{ route('borrowers.assets.destroy', [$borrower, $asset]) }}" style="margin-top:0.5rem" onsubmit="return confirm('{{ __('Remove this asset?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('Remove') }}</button>
                    </form>
                </div>
            @endforeach
            <h3 style="font-size:0.9rem">{{ __('Add asset') }}</h3>
            <form method="POST" action="{{ route('borrowers.assets.store', $borrower) }}">
                @csrf
                <div class="field">
                    <label>{{ __('Type') }}</label>
                    <input type="text" name="asset_type" value="{{ old('asset_type') }}">
                </div>
                <div class="field">
                    <label>{{ __('Description') }}</label>
                    <input type="text" name="description" value="{{ old('description') }}">
                </div>
                <div class="field">
                    <label>{{ __('Value (cents)') }}</label>
                    <input type="number" name="value_cents" min="0" value="{{ old('value_cents') }}">
                </div>
                <button type="submit" class="btn">{{ __('Add asset') }}</button>
            </form>
        @endif

        @if(($tab ?? 'main') === 'declarations')
            <h2 style="font-size:1rem;margin:0 0 1rem">{{ __('Declarations') }}</h2>
            <form method="POST" action="{{ route('borrowers.declaration.update', $borrower) }}">
                @csrf
                @method('PATCH')
                @php
                    $b = fn ($key) => old($key, $declaration->{$key});
                @endphp
                <div class="field">
                    <label><input type="hidden" name="outstanding_judgments" value="0"><input type="checkbox" name="outstanding_judgments" value="1" @checked($b('outstanding_judgments'))> {{ __('Outstanding judgments') }}</label>
                </div>
                <div class="field">
                    <label><input type="hidden" name="bankruptcy_past_seven_years" value="0"><input type="checkbox" name="bankruptcy_past_seven_years" value="1" @checked($b('bankruptcy_past_seven_years'))> {{ __('Bankruptcy in past 7 years') }}</label>
                </div>
                <div class="field">
                    <label><input type="hidden" name="foreclosure_past_seven_years" value="0"><input type="checkbox" name="foreclosure_past_seven_years" value="1" @checked($b('foreclosure_past_seven_years'))> {{ __('Foreclosure in past 7 years') }}</label>
                </div>
                <div class="field">
                    <label><input type="hidden" name="party_to_lawsuit" value="0"><input type="checkbox" name="party_to_lawsuit" value="1" @checked($b('party_to_lawsuit'))> {{ __('Party to lawsuit') }}</label>
                </div>
                <div class="field">
                    <label><input type="hidden" name="obligated_on_loan_resulting_foreclosure" value="0"><input type="checkbox" name="obligated_on_loan_resulting_foreclosure" value="1" @checked($b('obligated_on_loan_resulting_foreclosure'))> {{ __('Obligated on loan resulting in foreclosure') }}</label>
                </div>
                <div class="field">
                    <label><input type="hidden" name="delinquent_on_federal_debt" value="0"><input type="checkbox" name="delinquent_on_federal_debt" value="1" @checked($b('delinquent_on_federal_debt'))> {{ __('Delinquent on federal debt') }}</label>
                </div>
                <div class="field">
                    <label for="additional_answers">{{ __('Additional answers (JSON)') }}</label>
                    <textarea id="additional_answers" name="additional_answers">{{ old('additional_answers', json_encode($declaration->additional_answers ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                    @error('additional_answers')<div class="error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn">{{ __('Save declarations') }}</button>
            </form>
        @endif

        @if(($tab ?? 'main') === 'audit')
            <h2 style="font-size:1rem;margin:0 0 1rem">{{ __('Audit log') }}</h2>
            @if($auditLogs->isEmpty())
                <p style="color:#64748b">{{ __('No audit entries yet.') }}</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Entity') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $log->user?->email ?? '—' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>
                                    <code>{{ class_basename($log->entity_type) }}</code> #{{ $log->entity_id }}
                                    @if($log->old_values || $log->new_values)
                                        <details style="margin-top:0.25rem">
                                            <summary>{{ __('Changes') }}</summary>
                                            <pre style="white-space:pre-wrap;font-size:0.75rem;background:#f8fafc;padding:0.5rem;border-radius:4px">{{ json_encode(['old' => $log->old_values, 'new' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
@endsection
