<?php

$projectRoot = __DIR__;
$logDir = $projectRoot . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/php_errors.log';
@file_put_contents($logFile, date('Y-m-d H:i:s') . " [boot] index.php iniciado\n", FILE_APPEND | LOCK_EX);

$logError = static function (\Throwable $e) use ($logFile) {
    $line = date('Y-m-d H:i:s') . ' ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
};

$logLastError = static function () use ($logFile) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $line = date('Y-m-d H:i:s') . ' FATAL ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line'] . "\n";
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
};

register_shutdown_function($logLastError);

set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($logFile) {
    $fatal = (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) & $severity;
    if ($fatal) {
        return false;
    }
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " [{$severity}] {$message} in {$file}:{$line}\n", FILE_APPEND | LOCK_EX);
    return true;
}, E_ALL);

set_exception_handler(static function (\Throwable $e) use ($logError, $logFile) {
    $logError($e);
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    $debug = (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') || !isset($_ENV['APP_DEBUG']);
    if ($debug) {
        echo '<h1>Erro 500</h1><pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString()) . '</pre>';
        echo '<p>Log gravado em: ' . htmlspecialchars($logFile) . '</p>';
    } else {
        echo '<h1>Erro interno do servidor</h1><p>Verifique o log em storage/logs/php_errors.log</p>';
    }
});

try {
    require_once $projectRoot . '/vendor/autoload.php';

    \App\Services\EnvService::load();

    use App\Controllers\IntencaoVendaController;
    use App\Controllers\ComunicadoVendaController;

    session_start();

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    $basePath = trim($_ENV['APP_BASE_URL'] ?? '/motos') ?: '/motos';
    if ($path !== '' && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    $path = trim($path, '/');

    if (empty($path) || $path === 'index.php') {
        $path = 'home';
    }

    $pathParts = explode('/', $path);
    $controllerName = $pathParts[0] ?? 'home';
    $action = $pathParts[1] ?? 'index';

    switch ($controllerName) {
        case 'home':
            $controller = new \App\Controllers\HomeController();
            break;
        case 'intencao-venda':
            $controller = new IntencaoVendaController();
            break;
        case 'comunicado-venda':
            $controller = new ComunicadoVendaController();
            break;
        default:
            $controller = new \App\Controllers\HomeController();
            break;
    }

    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        http_response_code(404);
        echo 'Página não encontrada';
    }
} catch (\Throwable $e) {
    $logError($e);
    throw $e;
}
