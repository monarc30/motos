<?php
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? $titulo . ' - ' : '' ?>Sistema de Gerenciamento de Vendas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .nav {
            display: flex;
            gap: 1rem;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav a:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .nav a.active {
            background-color: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .main-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .footer {
            text-align: center;
            padding: 2rem;
            color: #666;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
    <script>window.APP_BASE = <?= json_encode($baseUrl) ?>;</script>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">🏍️ Sistema de Motos</div>
            <nav class="nav">
                <a href="<?= htmlspecialchars($baseUrl) ?>/" class="<?= ($paginaAtual ?? '') === 'home' ? 'active' : '' ?>">Início</a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/intencao-venda" class="<?= ($paginaAtual ?? '') === 'intencao-venda' ? 'active' : '' ?>">Intenção de Venda</a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/comunicado-venda" class="<?= ($paginaAtual ?? '') === 'comunicado-venda' ? 'active' : '' ?>">Comunicado de Venda</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">

