<?php

return [
    'api_per_minute' => (int) env('RATE_LIMIT_API_PER_MINUTE', 60),
    'login_per_minute' => (int) env('RATE_LIMIT_LOGIN_PER_MINUTE', 5),
    'login_ip_per_minute' => (int) env('RATE_LIMIT_LOGIN_IP_PER_MINUTE', 30),
    'sensitive_per_minute' => (int) env('RATE_LIMIT_SENSITIVE_PER_MINUTE', 5),
    'pdf_per_minute' => (int) env('RATE_LIMIT_PDF_PER_MINUTE', 10),
];
