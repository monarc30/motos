#!/bin/bash

echo "=== Configurando Apache para o projeto Motos ==="
echo ""

# Copiar arquivo de configuração
echo "1. Copiando arquivo de configuração..."
sudo cp /var/www/motos/motos.conf /etc/apache2/sites-available/motos.conf
sudo chmod 644 /etc/apache2/sites-available/motos.conf
echo "   ✓ Arquivo copiado"

# Habilitar o site
echo "2. Habilitando o site..."
sudo a2ensite motos.conf
echo "   ✓ Site habilitado"

# Habilitar módulo rewrite
echo "3. Habilitando módulo rewrite..."
sudo a2enmod rewrite
echo "   ✓ Módulo habilitado"

# Recarregar Apache
echo "4. Recarregando Apache..."
sudo systemctl reload apache2
echo "   ✓ Apache recarregado"

# Adicionar ao /etc/hosts
echo "5. Adicionando ao /etc/hosts..."
if ! grep -q "127.0.0.1.*motos" /etc/hosts; then
    echo "127.0.0.1 motos" | sudo tee -a /etc/hosts
    echo "   ✓ Adicionado ao /etc/hosts"
else
    echo "   ✓ Já existe no /etc/hosts"
fi

echo ""
echo "=== Configuração concluída! ==="
echo ""
echo "Acesse: http://motos"
echo ""
echo "Não esqueça de instalar as dependências do Composer:"
echo "  cd /var/www/motos && composer install"


