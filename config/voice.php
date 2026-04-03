<?php

return [

    /*
    | Shared secret for the Node voice-bridge service (X-Voice-Bridge-Key header).
    */
    'bridge_key' => env('VOICE_BRIDGE_KEY'),

    /*
    | Default TTL for CallSid → borrower bindings (hours).
    */
    'session_ttl_hours' => (int) env('VOICE_SESSION_TTL_HOURS', 4),

];
