# Como Testar Credenciais Autoconf no Postman

## Passo 1: Criar Nova Requisição

1. Abra o Postman
2. Clique em "New" → "HTTP Request"
3. Ou use o atalho `Ctrl+N` (Windows/Linux) ou `Cmd+N` (Mac)

## Passo 2: Configurar Método e URL

1. Selecione o método: **POST**
2. Digite a URL: `https://api.autoconf.com.br/api/v1/revenda`
   - Este é o endpoint mais simples para testar credenciais

## Passo 3: Configurar Headers

1. Vá para a aba **"Headers"**
2. Adicione o header:
   - **Key**: `Authorization`
   - **Value**: `Bearer NnM5PRDXLLnwhEUSqRi8SbYTC3TIXsaCKXLUMU0Czy3C9MzonCTA90980nOTOFqs12lbTE9gOH2gsoZbGPWPWiMfRuFb3iCwq9bMlRkSaCk1csXm6aD6gTL2f7v1ZQn48hnyWK1SHuSIMCqeFkJpvJ9LEL74KdQLC5RvLykiBpAOtU9r8FcXohe32gwFeP2wEiAuAQw3v54OzD7zXXlZndOOqxmSSXA12lW8leKc0stnlg2ouVRB4oNMqn8pP7iQ`

## Passo 4: Configurar Body

1. Vá para a aba **"Body"**
2. Selecione **"x-www-form-urlencoded"**
3. Adicione os campos:
   - **Key**: `token`
   - **Value**: `2UuJ6S5enN8qzKfnadV9PxDmtjg9SvQIPy9ppNQb`

## Passo 5: Enviar Requisição

1. Clique no botão **"Send"**
2. Verifique a resposta:
   - **Status 200**: Credenciais válidas! ✓
   - **Status 403**: Credenciais inválidas ou sem permissão ✗
   - **Status 401**: Token Bearer inválido ou expirado ✗

---

## Teste 2: Endpoint de Veículo

Para testar o endpoint de veículo (que precisa de um ID):

1. **Método**: POST
2. **URL**: `https://api.autoconf.com.br/api/v1/veiculo`
3. **Headers**: Mesmo header `Authorization` do teste anterior
4. **Body** (x-www-form-urlencoded):
   - **Key**: `token` → **Value**: `2UuJ6S5enN8qzKfnadV9PxDmtjg9SvQIPy9ppNQbq`
   - **Key**: `id` → **Value**: `295110` (ou um ID real do seu Autoconf)

---

## Exemplo de Requisição Completa (cURL)

Se preferir usar cURL no terminal:

```bash
curl --location 'https://api.autoconf.com.br/api/v1/revenda' \
--header 'Authorization: Bearer NnM5PRDXLLnwhEUSqRi8SbYTC3TIXsaCKXLUMU0Czy3C9MzonCTA90980nOTOFqs12lbTE9gOH2gsoZbGPWPWiMfRuFb3iCwq9bMlRkSaCk1csXm6aD6gTL2f7v1ZQn48hnyWK1SHuSIMCqeFkJpvJ9LEL74KdQLC5RvLykiBpAOtU9r8FcXohe32gwFeP2wEiAuAQw3v54OzD7zXXlZndOOqxmSSXA12lW8leKc0stnlg2ouVRB4oNMqn8pP7iQ' \
--data-urlencode 'token=2UuJ6S5enN8qzKfnadV9PxDmtjg9SvQIPy9ppNQbq'
```

---

## O que Verificar na Resposta

### Resposta de Sucesso (200):
```json
{
  "id": 7,
  "nome": "BellosCar",
  "email": "vendas@belloscar.com.br",
  ...
}
```

### Resposta de Erro (403):
- HTML com mensagem de erro
- Ou JSON com `{"message": "Forbidden"}`

### Resposta de Erro (401):
- Indica que o token Bearer está incorreto ou expirado

---

## Dicas

1. **Salve a requisição**: Clique em "Save" para reutilizar depois
2. **Use variáveis**: No Postman, você pode criar variáveis de ambiente para os tokens
3. **Teste diferentes endpoints**: Se `/api/v1/revenda` funcionar, teste `/api/v1/veiculo`
4. **Verifique a documentação**: Consulte a documentação do Autoconf no Postman para outros endpoints


