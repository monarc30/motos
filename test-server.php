<?php
header('Content-Type: text/html; charset=utf-8');
echo '<h1>OK</h1>';
echo '<p>PHP está funcionando.</p>';
echo '<p>Document root / diretório atual: <code>' . htmlspecialchars(__DIR__) . '</code></p>';
echo '<p>Se você vê esta página, o servidor está executando PHP a partir desta pasta.</p>';
echo '<p><a href="' . (strpos(__DIR__, 'motos') !== false ? '/motos/' : '/') . '">Ir para o sistema</a></p>';
