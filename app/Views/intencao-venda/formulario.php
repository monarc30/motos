<?php
$titulo = 'Intenção de Venda';
?>

<h1>Intenção de Venda</h1>

<p style="color: #666; margin-bottom: 1rem;">
    Informe o ID da venda registrada no Autoconf para buscar os dados automaticamente.
</p>

<div id="mensagem" style="margin-bottom: 1rem; padding: 1rem; border-radius: 4px; display: none;"></div>

<form id="formIntencaoVenda" style="margin-top: 1rem;">
    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
        <label for="venda_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
            ID da Venda no Autoconf:
        </label>
        <div style="display: flex; gap: 1rem; align-items: flex-end;">
            <input 
                type="text" 
                id="venda_id" 
                name="venda_id" 
                required 
                placeholder="Ex: 295110"
                style="flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;"
            >
            <button 
                type="button" 
                id="btnBuscarAutoconf"
                style="padding: 0.75rem 2rem; background: #667eea; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.3s;"
            >
                Buscar Dados
            </button>
        </div>
        <small style="color: #666; display: block; margin-top: 0.5rem;">
            O ID da venda é gerado automaticamente quando a venda é registrada no sistema Autoconf.
        </small>
        <p style="margin-top: 1rem;">
            <button type="button" id="btnListarVendas" style="padding: 0.5rem 1rem; background: #5a67d8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                Não sabe o ID? Listar vendas do Autoconf
            </button>
        </p>
        <div id="painelListarVendas" style="display: none; margin-top: 1rem; padding: 1rem; background: #f0f4ff; border-radius: 8px; border: 1px solid #c3dafe;">
            <label for="filtro_vendas" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Filtrar por placa, modelo ou ID (opcional):</label>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                <input type="text" id="filtro_vendas" placeholder="Ex: placa ou modelo" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                <button type="button" id="btnBuscarLista" style="padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">Buscar</button>
            </div>
            <div id="listaVendasContainer" style="display: none;">
                <label for="select_venda" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Escolha a venda:</label>
                <select id="select_venda" size="6" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 0.5rem;"></select>
                <button type="button" id="btnUsarVenda" style="padding: 0.5rem 1rem; background: #38a169; color: white; border: none; border-radius: 4px; cursor: pointer;">Usar esta venda e buscar dados</button>
            </div>
            <div id="listaVendasCarregando" style="display: none; color: #667eea;">Carregando...</div>
            <div id="listaVendasVazia" style="display: none; color: #666;">Nenhuma venda encontrada.</div>
        </div>
    </div>

    <fieldset style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <legend style="font-weight: bold; padding: 0 1rem; color: #667eea;">Dados do Cliente</legend>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <label for="nome_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nome Completo:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="cpf_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">CPF:</label>
                <input type="text" id="cpf_cliente" name="cpf_cliente" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="telefone_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Telefone:</label>
                <input type="text" id="telefone_cliente" name="telefone_cliente" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="whatsapp_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">WhatsApp:</label>
                <input type="text" id="whatsapp_cliente" name="whatsapp_cliente" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        </div>
        <div style="margin-top: 1rem;">
            <label for="endereco_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Endereço:</label>
            <textarea id="endereco_cliente" name="endereco_cliente" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
        </div>
    </fieldset>

    <fieldset style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <legend style="font-weight: bold; padding: 0 1rem; color: #667eea;">Dados da Motocicleta</legend>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <label for="modelo_moto" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Modelo:</label>
                <input type="text" id="modelo_moto" name="modelo_moto" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
            <div>
                <label for="placa_moto" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Placa:</label>
                <input type="text" id="placa_moto" name="placa_moto" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
            <div>
                <label for="ano_moto" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Ano:</label>
                <input type="text" id="ano_moto" name="ano_moto" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
        </div>
    </fieldset>

    <fieldset style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <legend style="font-weight: bold; padding: 0 1rem; color: #667eea;">Dados do CRV</legend>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <label for="numero_crv" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Número do CRV: <span style="color: red;">*</span></label>
                <input type="text" id="numero_crv" name="numero_crv" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="codigo_seguranca_crv" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Código de Segurança do CRV: <span style="color: red;">*</span></label>
                <input type="text" id="codigo_seguranca_crv" name="codigo_seguranca_crv" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        </div>
    </fieldset>

    <div style="margin-top: 2rem; text-align: right;">
        <input type="hidden" id="venda_id_hidden" name="venda_id">
        <button 
            type="submit" 
            style="padding: 1rem 2rem; background: #667eea; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer; transition: background 0.3s;"
        >
            Gerar / Imprimir Intenção
        </button>
    </div>
</form>

<script>
// JavaScript inline para buscar dados do Autoconf
document.getElementById('btnBuscarAutoconf').addEventListener('click', function() {
    const vendaId = document.getElementById('venda_id').value.trim();
    const mensagem = document.getElementById('mensagem');
    
    if (!vendaId) {
        mensagem.textContent = 'Por favor, informe o ID da venda.';
        mensagem.style.display = 'block';
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
        return;
    }
    
    mensagem.textContent = 'Buscando dados...';
    mensagem.style.display = 'block';
    mensagem.style.background = '#e3f2fd';
    mensagem.style.color = '#1976d2';
    
    fetch((window.APP_BASE || '') + '/intencao-venda/buscarAutoconf', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'venda_id=' + encodeURIComponent(vendaId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            mensagem.textContent = 'Erro: ' + data.erro;
            mensagem.style.background = '#fee';
            mensagem.style.color = '#c33';
        } else if (data.sucesso && data.dados) {
            // Preenche os campos
            document.getElementById('nome_cliente').value = data.dados.cliente.nome || '';
            document.getElementById('cpf_cliente').value = data.dados.cliente.cpf || '';
            document.getElementById('telefone_cliente').value = data.dados.cliente.telefone || '';
            document.getElementById('whatsapp_cliente').value = data.dados.cliente.whatsapp || '';
            document.getElementById('endereco_cliente').value = data.dados.cliente.endereco || '';
            
            document.getElementById('modelo_moto').value = data.dados.veiculo.modelo || '';
            document.getElementById('placa_moto').value = data.dados.veiculo.placa || '';
            document.getElementById('ano_moto').value = data.dados.veiculo.ano || '';
            
            document.getElementById('venda_id_hidden').value = vendaId;
            
            mensagem.textContent = 'Dados carregados com sucesso!';
            mensagem.style.background = '#e8f5e9';
            mensagem.style.color = '#2e7d32';
        }
    })
    .catch(error => {
        mensagem.textContent = 'Erro ao buscar dados: ' + error.message;
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
    });
});

// Listar vendas do Autoconf (quando não sabe o ID)
document.getElementById('btnListarVendas').addEventListener('click', function() {
    var painel = document.getElementById('painelListarVendas');
    painel.style.display = painel.style.display === 'none' ? 'block' : 'none';
    if (painel.style.display === 'block') {
        document.getElementById('listaVendasContainer').style.display = 'none';
        document.getElementById('listaVendasVazia').style.display = 'none';
    }
});
document.getElementById('btnBuscarLista').addEventListener('click', function() {
    var termo = document.getElementById('filtro_vendas').value.trim();
    var base = window.APP_BASE || '';
    var url = base + '/intencao-venda/listarVendasAutoconf?pagina=1';
    if (termo) url += '&termo=' + encodeURIComponent(termo);
    document.getElementById('listaVendasContainer').style.display = 'none';
    document.getElementById('listaVendasVazia').style.display = 'none';
    document.getElementById('listaVendasCarregando').style.display = 'block';
    fetch(url)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('listaVendasCarregando').style.display = 'none';
        var veiculos = data.veiculos || [];
        var sel = document.getElementById('select_venda');
        sel.innerHTML = '';
        veiculos.forEach(function(v) {
            var opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = 'ID ' + v.id + ' - ' + (v.placa || '-') + ' - ' + (v.modelo || '-') + ' ' + (v.ano ? '(' + v.ano + ')' : '');
            sel.appendChild(opt);
        });
        if (veiculos.length === 0) {
            document.getElementById('listaVendasVazia').style.display = 'block';
        } else {
            document.getElementById('listaVendasContainer').style.display = 'block';
        }
    })
    .catch(function(err) {
        document.getElementById('listaVendasCarregando').style.display = 'none';
        document.getElementById('listaVendasVazia').textContent = 'Erro ao carregar: ' + err.message;
        document.getElementById('listaVendasVazia').style.display = 'block';
    });
});
document.getElementById('btnUsarVenda').addEventListener('click', function() {
    var sel = document.getElementById('select_venda');
    var id = sel.value;
    if (!id) return;
    document.getElementById('venda_id').value = id;
    document.getElementById('venda_id_hidden').value = id;
    document.getElementById('painelListarVendas').style.display = 'none';
    document.getElementById('btnBuscarAutoconf').click();
});

// Submissão do formulário
document.getElementById('formIntencaoVenda').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const vendaId = document.getElementById('venda_id').value;
    if (vendaId) {
        formData.append('venda_id', vendaId);
    }
    
    const mensagem = document.getElementById('mensagem');
    mensagem.textContent = 'Gerando PDF...';
    mensagem.style.display = 'block';
    mensagem.style.background = '#e3f2fd';
    mensagem.style.color = '#1976d2';
    
    fetch((window.APP_BASE || '') + '/intencao-venda/gerar', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            mensagem.textContent = 'Erro: ' + data.erro;
            mensagem.style.background = '#fee';
            mensagem.style.color = '#c33';
        } else if (data.sucesso) {
            mensagem.textContent = 'PDF gerado com sucesso! Fazendo download...';
            mensagem.style.background = '#e8f5e9';
            mensagem.style.color = '#2e7d32';
            
            // Faz download do PDF
            window.location.href = (window.APP_BASE || '') + '/intencao-venda/download?id=' + data.id;
        }
    })
    .catch(error => {
        mensagem.textContent = 'Erro ao gerar PDF: ' + error.message;
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
    });
});
</script>

