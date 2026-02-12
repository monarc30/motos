<?php
// ?debug=1 na URL mostra o erro do PHP na tela (use só para descobrir o 500; remova depois)
if (!empty($_GET['debug']) && $_GET['debug'] === '1') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

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

$renderErrorPage = static function (string $title, string $message, string $file, int $line, array $trace, string $projectRoot) {
    $fallback = static function () use ($title, $message, $file, $line) {
        echo '<h1>Erro 500</h1><pre>' . htmlspecialchars($title . "\n\n" . $message . "\n\n" . $file . ':' . $line) . '</pre>';
    };
    $viewPath = $projectRoot . '/app/Views/errors/debug.php';
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(500);
    }
    if (is_readable($viewPath)) {
        try {
            $errorTitle = $title;
            $errorMessage = $message;
            $errorFile = $file;
            $errorLine = $line;
            $errorTrace = $trace;
            require $viewPath;
        } catch (\Throwable $e) {
            $fallback();
        }
    } else {
        $fallback();
    }
};

$logLastError = static function () use ($logFile, $projectRoot, $renderErrorPage) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $line = date('Y-m-d H:i:s') . ' FATAL ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line'] . "\n";
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        if (!headers_sent()) {
            $renderErrorPage('Erro fatal', $err['message'], $err['file'], $err['line'], [], $projectRoot);
        }
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

set_exception_handler(static function (\Throwable $e) use ($logError, $logFile, $projectRoot, $renderErrorPage) {
    $logError($e);
    $renderErrorPage(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $projectRoot);
});

try {
    require_once $projectRoot . '/vendor/autoload.php';

    \App\Services\EnvService::load();

    session_start();

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    $basePath = isset($_ENV['APP_BASE_URL']) ? trim($_ENV['APP_BASE_URL']) : '/motos';
    if ($basePath !== '' && $path !== '' && strpos($path, $basePath) === 0) {
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
            $controller = new \App\Controllers\IntencaoVendaController();
            break;
        case 'comunicado-venda':
            $controller = new \App\Controllers\ComunicadoVendaController();
            break;
        default:
            $controller = new \App\Controllers\HomeController();
            break;
    }

    if (method_exists($controller, $action)) {
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " [dispatch] {$controllerName}/{$action}\n", FILE_APPEND | LOCK_EX);
        $controller->$action();
    } else {
        http_response_code(404);
        echo 'Página não encontrada';
    }
} catch (\Throwable $e) {
    $logError($e);
    throw $e;
}
