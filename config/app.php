<?php

return [
    'app_name' => 'Sistema de Gerenciamento de Vendas',
    'app_version' => '1.0.0',
    'base_url' => isset($_ENV['APP_BASE_URL']) ? trim($_ENV['APP_BASE_URL']) : '/motos',
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    'charset' => 'UTF-8',
    'debug' => true,
    'storage_path' => __DIR__ . '/../storage',
    'documents_path' => __DIR__ . '/../storage/documentos',
];


