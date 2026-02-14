<?php

\App\Services\EnvService::load();

return [
    'webhook_url' => rtrim($_ENV['INNOVART_WEBHOOK_URL'] ?? 'https://auto.innovart.com.br/webhook', '/'),
    'user' => $_ENV['INNOVART_USER'] ?? '',
    'password' => $_ENV['INNOVART_PASSWORD'] ?? '',
    'timeout' => (int) ($_ENV['INNOVART_TIMEOUT'] ?? 15),
    'enabled' => !empty($_ENV['INNOVART_USER']) && !empty($_ENV['INNOVART_PASSWORD']),
];
