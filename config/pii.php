<?php

return [

    /*
    | Keys removed or replaced in audit_logs JSON (flat attribute keys).
    */
    'audit_redact_keys' => ['ssn_last4', 'password', 'remember_token'],

    /*
    | URLA / voice compact snapshot paths: values never sent to the model in clear text.
    */
    'urla_snapshot_redact_paths' => [
        'identity.ssn_last4',
    ],

    /*
    | Replacement token for redacted values in snapshots and voice JSON.
    */
    'mask_token' => '[redacted]',

];
