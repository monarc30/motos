<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';

echo "Testando conexão com MySQL...\n";
echo "Host: {$config['host']}\n";
echo "Database: {$config['database']}\n";
echo "User: {$config['username']}\n\n";

try {
    $dsn = sprintf(
        'mysql:host=%s;charset=%s',
        $config['host'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    echo "✓ Conexão estabelecida com sucesso!\n\n";
    
    echo "Criando banco de dados se não existir...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Banco de dados '{$config['database']}' verificado/criado\n\n";
    
    $pdo->exec("USE {$config['database']}");
    
    echo "Criando tabelas...\n";
    $migrations = [
        '002_create_clientes.sql',
        '003_create_veiculos.sql',
        '004_create_intencoes_venda.sql',
        '005_create_comunicados_venda.sql',
        '006_create_logs_integracao.sql',
    ];
    
    foreach ($migrations as $migration) {
        $sql = file_get_contents(__DIR__ . '/migrations/' . $migration);
        $pdo->exec($sql);
        echo "✓ {$migration}\n";
    }
    
    echo "\n✓ Todas as tabelas criadas com sucesso!\n";
    
    echo "\nVerificando tabelas criadas:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    echo "\nVerifique a senha do MySQL no arquivo config/database.php\n";
    exit(1);
}


