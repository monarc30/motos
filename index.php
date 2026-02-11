<?php

require_once __DIR__ . '/vendor/autoload.php';

\App\Services\EnvService::load();

use App\Controllers\IntencaoVendaController;
use App\Controllers\ComunicadoVendaController;

session_start();

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$basePath = '/motos';
if (strpos($path, $basePath) === 0) {
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
