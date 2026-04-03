<?php

/**
 * URLA / 1003-style field inventory: logical paths → DB + “required for stage”.
 * Stages are ordered; each stage adds more required fields (cumulative optional per design below).
 */

return [

    /*
    | Ordered stages for conversational flow (voice/AI can advance one at a time).
    */
    'stages' => [
        'intake',
        'identity',
        'employment',
        'assets',
        'declarations',
        'review',
    ],

    /*
    | Required field paths per stage (non-cumulative: only fields that must be complete before leaving stage).
    | Paths use dot notation; employment uses first row index 0 for primary job.
    */
    'required_by_stage' => [
        'intake' => [
            'borrower.display_name',
            'borrower.email',
            'borrower.phone',
        ],
        'identity' => [
            'identity.first_name',
            'identity.last_name',
            'identity.date_of_birth',
            'identity.address_line1',
            'identity.city',
            'identity.state',
            'identity.postal_code',
            'identity.citizenship_status',
        ],
        'employment' => [
            'employment.0.employer_name',
            'employment.0.monthly_income_cents',
        ],
        'assets' => [
            // At least one asset row with type + value (voice can add one row)
            'assets.0.asset_type',
            'assets.0.value_cents',
        ],
        'declarations' => [
            'declaration.outstanding_judgments',
            'declaration.bankruptcy_past_seven_years',
            'declaration.foreclosure_past_seven_years',
            'declaration.party_to_lawsuit',
            'declaration.obligated_on_loan_resulting_foreclosure',
            'declaration.delinquent_on_federal_debt',
        ],
        'review' => [],
    ],

    /*
    | Human labels + hints for prompts (missing-field list only).
    */
    'fields' => [
        'borrower.display_name' => ['label' => 'Borrower display name', 'section' => 'borrower'],
        'borrower.email' => ['label' => 'Email', 'section' => 'borrower'],
        'borrower.phone' => ['label' => 'Phone', 'section' => 'borrower'],
        'borrower.status' => ['label' => 'Application status', 'section' => 'borrower'],

        'identity.first_name' => ['label' => 'Legal first name', 'section' => 'identity'],
        'identity.middle_name' => ['label' => 'Middle name', 'section' => 'identity'],
        'identity.last_name' => ['label' => 'Legal last name', 'section' => 'identity'],
        'identity.date_of_birth' => ['label' => 'Date of birth', 'section' => 'identity'],
        'identity.ssn_last4' => ['label' => 'SSN last 4 digits', 'section' => 'identity'],
        'identity.address_line1' => ['label' => 'Street address', 'section' => 'identity'],
        'identity.address_line2' => ['label' => 'Address line 2', 'section' => 'identity'],
        'identity.city' => ['label' => 'City', 'section' => 'identity'],
        'identity.state' => ['label' => 'State', 'section' => 'identity'],
        'identity.postal_code' => ['label' => 'ZIP / postal code', 'section' => 'identity'],
        'identity.country' => ['label' => 'Country', 'section' => 'identity'],
        'identity.citizenship_status' => ['label' => 'Citizenship status', 'section' => 'identity'],

        'employment.0.employer_name' => ['label' => 'Current employer name', 'section' => 'employment'],
        'employment.0.job_title' => ['label' => 'Job title', 'section' => 'employment'],
        'employment.0.monthly_income_cents' => ['label' => 'Monthly income (cents)', 'section' => 'employment'],
        'employment.0.years_in_line_of_work' => ['label' => 'Years in line of work', 'section' => 'employment'],

        'assets.0.asset_type' => ['label' => 'Primary asset type', 'section' => 'assets'],
        'assets.0.description' => ['label' => 'Primary asset description', 'section' => 'assets'],
        'assets.0.value_cents' => ['label' => 'Primary asset value (cents)', 'section' => 'assets'],

        'declaration.outstanding_judgments' => ['label' => 'Outstanding judgments (yes/no)', 'section' => 'declarations'],
        'declaration.bankruptcy_past_seven_years' => ['label' => 'Bankruptcy in past 7 years (yes/no)', 'section' => 'declarations'],
        'declaration.foreclosure_past_seven_years' => ['label' => 'Foreclosure in past 7 years (yes/no)', 'section' => 'declarations'],
        'declaration.party_to_lawsuit' => ['label' => 'Party to lawsuit (yes/no)', 'section' => 'declarations'],
        'declaration.obligated_on_loan_resulting_foreclosure' => ['label' => 'Obligated on loan resulting in foreclosure (yes/no)', 'section' => 'declarations'],
        'declaration.delinquent_on_federal_debt' => ['label' => 'Delinquent on federal debt (yes/no)', 'section' => 'declarations'],
    ],

    /*
    | Prompt packs: system + per-section instructions ({placeholders} replaced at runtime).
    */
    'prompts' => [
        'system' => <<<'TXT'
You are a mortgage loan assistant helping complete a URLA-style 1003 application.
Rules: stay in Fair Lending guardrails; do not give legal advice; confirm numbers clearly; use tools to read/write CRM data only.
Current stage and section are provided separately—focus questions on MISSING fields first.
TXT,

        'sections' => [
            'borrower' => 'Collect contact and application basics: display name, email, phone. Ask one cluster at a time.',
            'identity' => 'Collect legal name, DOB, address, and citizenship. Ask for SSN last 4 only when appropriate for your process.',
            'employment' => 'Collect current employment: employer, title, monthly income (confirm cents if stored as integer).',
            'assets' => 'Collect at least one liquid or major asset with type and value.',
            'declarations' => 'Ask each declaration question as a clear yes/no; record answers exactly.',
            'review' => 'Summarize captured data and confirm corrections before submission.',
        ],
    ],

];
