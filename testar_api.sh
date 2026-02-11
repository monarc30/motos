#!/bin/bash
# Script para testar a API do Autoconf usando curl

echo "=== TESTE DE CREDENCIAIS AUTOCONF ==="
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Credenciais
BEARER_TOKEN="NnM5PRDXLLnwhEUSqRi8SbYTC3TIXsaCKXLUMU0Czy3C9MzonCTA90980nOTOFqs12lbTE9gOH2gsoZbGPWPWiMfRuFb3iCwq9bMlRkSaCk1csXm6aD6gTL2f7v1ZQn48hnyWK1SHuSIMCqeFkJpvJ9LEL74KdQLC5RvLykiBpAOtU9r8FcXohe32gwFeP2wEiAuAQw3v54OzD7zXXlZndOOqxmSSXA12lW8leKc0stnlg2ouVRB4oNMqn8pP7iQ"
REVENDA_TOKEN="2UuJ6S5enN8qzKfnadV9PxDmtjg9SvQIPy9ppNQbq"
API_URL="https://api.autoconf.com.br"

echo "Teste 1: Endpoint /api/v1/revenda"
echo "-----------------------------------"
RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" \
  --location "${API_URL}/api/v1/revenda" \
  --header "Authorization: Bearer ${BEARER_TOKEN}" \
  --data-urlencode "token=${REVENDA_TOKEN}")

HTTP_STATUS=$(echo "$RESPONSE" | grep "HTTP_STATUS" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_STATUS/d')

if [ "$HTTP_STATUS" = "200" ]; then
    echo -e "${GREEN}✓ SUCESSO! Status: ${HTTP_STATUS}${NC}"
    echo "Resposta:"
    echo "$BODY" | head -20
elif [ "$HTTP_STATUS" = "403" ]; then
    echo -e "${RED}✗ ERRO 403: Credenciais inválidas ou sem permissão${NC}"
    echo "Resposta:"
    echo "$BODY" | head -10
elif [ "$HTTP_STATUS" = "401" ]; then
    echo -e "${RED}✗ ERRO 401: Token Bearer inválido ou expirado${NC}"
else
    echo -e "${YELLOW}Status: ${HTTP_STATUS}${NC}"
    echo "Resposta:"
    echo "$BODY" | head -10
fi

echo ""
echo "Teste 2: Endpoint /api/v1/veiculo (com ID)"
echo "-------------------------------------------"
read -p "Digite o ID do veículo (ou pressione Enter para usar 295110): " VEICULO_ID
VEICULO_ID=${VEICULO_ID:-295110}

RESPONSE2=$(curl -s -w "\nHTTP_STATUS:%{http_code}" \
  --location "${API_URL}/api/v1/veiculo" \
  --header "Authorization: Bearer ${BEARER_TOKEN}" \
  --data-urlencode "token=${REVENDA_TOKEN}" \
  --data-urlencode "id=${VEICULO_ID}")

HTTP_STATUS2=$(echo "$RESPONSE2" | grep "HTTP_STATUS" | cut -d: -f2)
BODY2=$(echo "$RESPONSE2" | sed '/HTTP_STATUS/d')

if [ "$HTTP_STATUS2" = "200" ]; then
    echo -e "${GREEN}✓ SUCESSO! Status: ${HTTP_STATUS2}${NC}"
    echo "Resposta:"
    echo "$BODY2" | head -30
elif [ "$HTTP_STATUS2" = "403" ]; then
    echo -e "${RED}✗ ERRO 403: Credenciais inválidas ou ID sem permissão${NC}"
    echo "Resposta:"
    echo "$BODY2" | head -10
elif [ "$HTTP_STATUS2" = "404" ]; then
    echo -e "${YELLOW}⚠ ERRO 404: Veículo com ID ${VEICULO_ID} não encontrado${NC}"
else
    echo -e "${YELLOW}Status: ${HTTP_STATUS2}${NC}"
    echo "Resposta:"
    echo "$BODY2" | head -10
fi

echo ""
echo "=== CONCLUSÃO ==="
if [ "$HTTP_STATUS" = "200" ]; then
    echo -e "${GREEN}Credenciais estão válidas!${NC}"
else
    echo -e "${RED}Credenciais podem estar incorretas ou expiradas.${NC}"
    echo "Verifique:"
    echo "  - Se os tokens estão corretos"
    echo "  - Se os tokens não expiraram"
    echo "  - Se você tem permissão para acessar a API"
fi


