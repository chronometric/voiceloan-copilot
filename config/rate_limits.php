<?php

return [

    'api_per_minute' => (int) env('RATE_LIMIT_API_PER_MINUTE', 60),

    'login_per_minute' => (int) env('RATE_LIMIT_LOGIN_PER_MINUTE', 10),

    'voice_per_minute' => (int) env('RATE_LIMIT_VOICE_PER_MINUTE', 180),

];
