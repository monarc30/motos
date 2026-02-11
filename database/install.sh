#!/bin/bash

echo "=== Instalando Banco de Dados - Sistema Motos ==="
echo ""

DB_USER="root"
DB_PASS=""
DB_NAME="motos_vendas"

if [ -z "$DB_PASS" ]; then
    MYSQL_CMD="mysql -u $DB_USER"
else
    MYSQL_CMD="mysql -u $DB_USER -p$DB_PASS"
fi

echo "1. Criando banco de dados..."
$MYSQL_CMD < database/migrations/001_create_database.sql
echo "   ✓ Banco de dados criado"

echo "2. Criando tabela clientes..."
$MYSQL_CMD $DB_NAME < database/migrations/002_create_clientes.sql
echo "   ✓ Tabela clientes criada"

echo "3. Criando tabela veiculos..."
$MYSQL_CMD $DB_NAME < database/migrations/003_create_veiculos.sql
echo "   ✓ Tabela veiculos criada"

echo "4. Criando tabela intencoes_venda..."
$MYSQL_CMD $DB_NAME < database/migrations/004_create_intencoes_venda.sql
echo "   ✓ Tabela intencoes_venda criada"

echo "5. Criando tabela comunicados_venda..."
$MYSQL_CMD $DB_NAME < database/migrations/005_create_comunicados_venda.sql
echo "   ✓ Tabela comunicados_venda criada"

echo "6. Criando tabela logs_integracao..."
$MYSQL_CMD $DB_NAME < database/migrations/006_create_logs_integracao.sql
echo "   ✓ Tabela logs_integracao criada"

echo ""
echo "=== Instalação do banco de dados concluída! ==="


