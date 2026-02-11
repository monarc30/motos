<?php

\App\Services\EnvService::load();

return [
    'api_url' => $_ENV['COMVEN_API_URL'] ?? '',
    'api_key' => $_ENV['COMVEN_API_KEY'] ?? '',
    'api_secret' => $_ENV['COMVEN_API_SECRET'] ?? '',
    'timeout' => (int) ($_ENV['COMVEN_TIMEOUT'] ?? 30),
    'retry_attempts' => (int) ($_ENV['COMVEN_RETRY_ATTEMPTS'] ?? 3),
    'enabled' => filter_var($_ENV['COMVEN_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
];

