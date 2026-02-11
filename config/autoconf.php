<?php

\App\Services\EnvService::load();

return [
    'api_url' => $_ENV['AUTOCONF_API_URL'] ?? '',
    'token' => $_ENV['AUTOCONF_TOKEN'] ?? '',
    'token2' => $_ENV['AUTOCONF_TOKEN2'] ?? '',
    'timeout' => (int) ($_ENV['AUTOCONF_TIMEOUT'] ?? 30),
    'retry_attempts' => (int) ($_ENV['AUTOCONF_RETRY_ATTEMPTS'] ?? 3),
];

