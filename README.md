# Sistema de Gerenciamento de Vendas e Comunicações de Motocicletas

Sistema desenvolvido em PHP para gerenciar processos de venda e comunicação de motocicletas.

## Requisitos

- PHP 8.1 ou superior
- MySQL 8.0 ou MariaDB 10.5 ou superior
- Composer
- Extensões PHP: PDO, cURL, GD, mbstring, OpenSSL

## Instalação

1. Instalar dependências:
```bash
composer install
```

2. Configurar banco de dados em `config/database.php`

3. Executar migrations (quando disponíveis)

4. Configurar integrações em:
   - `config/autoconf.php`
   - `config/comven.php`

## Estrutura do Projeto

- `app/` - Código da aplicação (Controllers, Models, Views, Services)
- `config/` - Arquivos de configuração
- `index.php` - Entrada da aplicação (raiz do projeto)
- `assets/` - CSS e JavaScript (raiz do projeto)
- `storage/` - Arquivos gerados (documentos, logs, cache)
- `database/` - Migrations e seeds

## Acesso local

- **Com virtual host** (recomendado): configure `motos.conf` e `/etc/hosts` (127.0.0.1 motos) e acesse **http://motos** ou **http://motos.local**.
- **Em subpasta**: coloque o projeto em `htdocs/motos` (ou equivalente), use no `.htaccess` `RewriteBase /motos`, no `.env` `APP_BASE_URL=/motos` e acesse **http://localhost/motos/**.
- **Na raiz do localhost**: se o DocumentRoot for a pasta do projeto, use `APP_BASE_URL=` (vazio) e **http://localhost/**.

Ver `INSTALACAO_APACHE.md` para detalhes do Apache.

## Desenvolvimento

O sistema está sendo desenvolvido passo a passo conforme o plano de desenvolvimento.


