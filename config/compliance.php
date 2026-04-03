<?php

return [

    /*
    | Outbound SMS / automated voice (TCPA / consent).
    |
    | This app does not record consent; configure capture in your CRM or telephony flow.
    | Before production: obtain express written consent where required, honor STOP/OPT-OUT,
    | maintain an internal DNC list, and align `sms_footer` / voice scripts with counsel.
    | See README Phase 5 for env secrets and operational notes.
    */
    'tcpa_consent_note' => env('COMPLIANCE_TCPA_NOTE'),

    /*
    | Injected into URLA/voice system prompts (and exposed in get_urla_context).
    */
    'voice_disclaimer' => env('COMPLIANCE_VOICE_DISCLAIMER', <<<'TXT'
This assistant provides general mortgage information only. It is not legal, tax, or financial advice.
Decisions about credit and loans require review by a licensed loan officer. Equal Credit Opportunity Act (ECOA) applies: applicants are evaluated fairly without discrimination on prohibited bases.
TXT),

    /*
    | Appended to outbound SMS (TCPA-style notice; customize with counsel).
    */
    'sms_footer' => env('COMPLIANCE_SMS_FOOTER', 'Msg & data rates may apply. Reply STOP to opt out.'),

    /*
    | Borrower status set when the AI invokes transfer_to_human.
    */
    'transfer_status' => env('COMPLIANCE_TRANSFER_STATUS', 'escalated'),

];
