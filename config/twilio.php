<?php

return [

    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),

    /*
    | E.164 sender (must be Twilio number or approved sender).
    */
    'from' => env('TWILIO_FROM_NUMBER'),

];
