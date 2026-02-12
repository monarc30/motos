<?php
header('Content-Type: text/plain; charset=utf-8');
$logDir = __DIR__ . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logDir . '/php_errors.log', date('Y-m-d H:i:s') . " check.php executado\n", FILE_APPEND);
echo "OK - PHP rodando em " . __DIR__;
