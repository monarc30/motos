#!/bin/bash
# Script para iniciar o Postman

POSTMAN_PATH="$HOME/postman/Postman/app/Postman"

if [ -f "$POSTMAN_PATH" ]; then
    echo "Iniciando Postman..."
    "$POSTMAN_PATH" &
    echo "Postman iniciado!"
    echo "Aguarde alguns segundos para a interface abrir..."
else
    echo "Postman não encontrado em $POSTMAN_PATH"
    echo ""
    echo "Para instalar via snap (recomendado):"
    echo "  sudo snap install postman"
    echo ""
    echo "Ou use o script de teste:"
    echo "  ./testar_api.sh"
fi

