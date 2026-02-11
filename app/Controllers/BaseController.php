<?php

namespace App\Controllers;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        // Determina a página atual para destacar no menu
        $paginaAtual = $this->getPaginaAtual();
        $data['paginaAtual'] = $paginaAtual;
        
        // Inclui o header
        $headerPath = __DIR__ . '/../Views/layout/header.php';
        if (file_exists($headerPath)) {
            extract($data);
            require $headerPath;
        }
        
        // Inclui a view
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }
        
        require $viewPath;
        
        // Inclui o footer
        $footerPath = __DIR__ . '/../Views/layout/footer.php';
        if (file_exists($footerPath)) {
            require $footerPath;
        }
    }
    
    protected function getPaginaAtual(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        if (strpos($path, '/intencao-venda') !== false) {
            return 'intencao-venda';
        }
        
        if (strpos($path, '/comunicado-venda') !== false) {
            return 'comunicado-venda';
        }
        
        return 'home';
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}


