<?php

return [
    'access_ttl_minutes' => (int) env('AUTH_ACCESS_TOKEN_TTL_MINUTES', 15),
    'refresh_ttl_minutes' => (int) env('AUTH_REFRESH_TOKEN_TTL_MINUTES', 10080),
];
