# Instruções para Configurar Apache - Projeto Motos

## Passos para Configuração

### 1. Copiar arquivo de configuração
```bash
sudo cp /var/www/motos/motos.conf /etc/apache2/sites-available/motos.conf
sudo chmod 644 /etc/apache2/sites-available/motos.conf
```

### 2. Habilitar o site
```bash
sudo a2ensite motos.conf
```

### 3. Habilitar módulo rewrite (se não estiver habilitado)
```bash
sudo a2enmod rewrite
```

### 4. Recarregar Apache
```bash
sudo systemctl reload apache2
```

### 5. Adicionar ao /etc/hosts (se necessário)
```bash
echo "127.0.0.1 motos" | sudo tee -a /etc/hosts
```

### 6. Verificar se está funcionando
Acesse no navegador: http://motos

## Verificar Status

Para verificar se o Apache está rodando:
```bash
sudo systemctl status apache2
```

Para ver os logs de erro:
```bash
sudo tail -f /var/log/apache2/motos_error.log
```

Para ver os logs de acesso:
```bash
sudo tail -f /var/log/apache2/motos_access.log
```

## Instalar Dependências

Antes de testar, instale as dependências do Composer:
```bash
cd /var/www/motos
composer install
```


