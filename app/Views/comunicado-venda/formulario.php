<?php
$titulo = 'Comunicado de Venda';
?>

<h1>Comunicado de Venda</h1>

<p style="color: #666; margin-bottom: 1rem;">
    Informe o ID da venda registrada no Autoconf e a data do comunicado para gerar o documento.
</p>

<div id="mensagem" style="margin-bottom: 1rem; padding: 1rem; border-radius: 4px; display: none;"></div>

<form id="formComunicadoVenda" style="margin-top: 1rem;">
    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border-left: 4px solid #764ba2;">
        <label for="venda_id" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
            ID da Venda no Autoconf:
        </label>
        <div style="display: flex; gap: 1rem; align-items: flex-end;">
            <input 
                type="text" 
                id="venda_id" 
                name="venda_id" 
                placeholder="Ex: 295110"
                style="flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;"
            >
            <button 
                type="button" 
                id="btnBuscarAutoconf"
                style="padding: 0.75rem 2rem; background: #764ba2; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.3s;"
            >
                Buscar Dados
            </button>
        </div>
        <p style="margin-top: 1rem;">
            <button type="button" id="btnListarVendas" style="padding: 0.5rem 1rem; background: #6b46c1; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                Não sabe o ID? Listar vendas do Autoconf
            </button>
        </p>
        <p style="margin-top: 0.75rem;">
            <strong>Ou busque por placa (Innovart):</strong>
            <input type="text" id="placa_busca" placeholder="Ex: ABC1D23" maxlength="8" style="margin-left: 0.5rem; padding: 0.4rem 0.6rem; border: 1px solid #ddd; border-radius: 4px; width: 8rem; text-transform: uppercase;">
            <button type="button" id="btnBuscarPorPlaca" style="margin-left: 0.25rem; padding: 0.4rem 0.8rem; background: #059669; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">Buscar por placa</button>
        </p>
        <div id="painelListarVendas" style="display: none; margin-top: 1rem; padding: 1rem; background: #faf5ff; border-radius: 8px; border: 1px solid #e9d8fd;">
            <label for="filtro_vendas" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Filtrar por placa, modelo ou ID (opcional):</label>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                <input type="text" id="filtro_vendas" placeholder="Ex: placa ou modelo" style="flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                <button type="button" id="btnBuscarLista" style="padding: 0.5rem 1rem; background: #764ba2; color: white; border: none; border-radius: 4px; cursor: pointer;">Buscar</button>
            </div>
            <div id="listaVendasContainer" style="display: none;">
                <label for="select_venda" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Escolha a venda:</label>
                <select id="select_venda" size="6" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 0.5rem;"></select>
                <button type="button" id="btnUsarVenda" style="padding: 0.5rem 1rem; background: #38a169; color: white; border: none; border-radius: 4px; cursor: pointer;">Usar esta venda e buscar dados</button>
            </div>
            <div id="listaVendasCarregando" style="display: none; color: #764ba2;">Carregando...</div>
            <div id="listaVendasVazia" style="display: none; color: #666;">Nenhuma venda encontrada.</div>
        </div>
    </div>

    <div style="margin-bottom: 2rem;">
        <label for="data_comunicado" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">
            Data do Comunicado: <span style="color: red;">*</span>
        </label>
        <input 
            type="date" 
            id="data_comunicado" 
            name="data_comunicado" 
            required
            style="padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; width: 300px;"
        >
    </div>

    <fieldset style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <legend style="font-weight: bold; padding: 0 1rem; color: #764ba2;">Dados do Cliente</legend>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <label for="nome_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nome Completo:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
            <div>
                <label for="cpf_cliente" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">CPF:</label>
                <input type="text" id="cpf_cliente" name="cpf_cliente" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
        </div>
    </fieldset>

    <fieldset style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <legend style="font-weight: bold; padding: 0 1rem; color: #764ba2;">Dados da Motocicleta</legend>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div>
                <label for="placa_moto" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Placa:</label>
                <input type="text" id="placa_moto" name="placa_moto" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
            <div>
                <label for="modelo_moto" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Modelo:</label>
                <input type="text" id="modelo_moto" name="modelo_moto" readonly style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">
            </div>
        </div>
    </fieldset>

    <div style="margin-top: 2rem; text-align: right; display: flex; gap: 1rem; justify-content: flex-end;">
        <input type="hidden" id="venda_id_hidden" name="venda_id">
        <button 
            type="button" 
            id="btnGerarEtiqueta"
            style="padding: 1rem 2rem; background: #9c27b0; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer; transition: background 0.3s;"
        >
            Imprimir Etiqueta do Envelope
        </button>
        <button 
            type="submit" 
            style="padding: 1rem 2rem; background: #764ba2; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer; transition: background 0.3s;"
        >
            Gerar / Imprimir Comunicado
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
    
    fetch((window.APP_BASE || '') + '/comunicado-venda/buscarAutoconf', {
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
            document.getElementById('nome_cliente').value = data.dados.cliente.nome || '';
            document.getElementById('cpf_cliente').value = data.dados.cliente.cpf || '';
            document.getElementById('placa_moto').value = data.dados.veiculo.placa || '';
            document.getElementById('modelo_moto').value = data.dados.veiculo.modelo || '';
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
    var url = base + '/comunicado-venda/listarVendasAutoconf?pagina=1';
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

// Buscar por placa (Innovart)
document.getElementById('btnBuscarPorPlaca').addEventListener('click', function() {
    var placa = document.getElementById('placa_busca').value.trim().replace(/\s/g, '').toUpperCase();
    var mensagem = document.getElementById('mensagem');
    if (!placa) {
        mensagem.textContent = 'Informe a placa para buscar.';
        mensagem.style.display = 'block';
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
        return;
    }
    mensagem.textContent = 'Buscando por placa (Innovart)...';
    mensagem.style.display = 'block';
    mensagem.style.background = '#e3f2fd';
    mensagem.style.color = '#1976d2';
    fetch((window.APP_BASE || '') + '/comunicado-venda/buscarPorPlaca', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'placa=' + encodeURIComponent(placa)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.erro) {
            mensagem.textContent = 'Erro: ' + data.erro;
            mensagem.style.background = '#fee';
            mensagem.style.color = '#c33';
        } else if (data.sucesso && data.dados) {
            var d = data.dados;
            document.getElementById('nome_cliente').value = d.cliente.nome || '';
            document.getElementById('cpf_cliente').value = d.cliente.cpf || '';
            document.getElementById('modelo_moto').value = d.veiculo.modelo || '';
            document.getElementById('placa_moto').value = d.veiculo.placa || placa;
            if (d.venda_id) {
                document.getElementById('venda_id').value = d.venda_id;
                document.getElementById('venda_id_hidden').value = d.venda_id;
            }
            document.getElementById('placa_busca').value = '';
            mensagem.textContent = 'Dados carregados (Innovart). Preencha a data e gere o comunicado.';
            mensagem.style.background = '#e8f5e9';
            mensagem.style.color = '#2e7d32';
        }
    })
    .catch(function(err) {
        mensagem.textContent = 'Erro ao buscar por placa: ' + err.message;
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
    });
});

// Submissão do formulário
document.getElementById('formComunicadoVenda').addEventListener('submit', function(e) {
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
    
    fetch((window.APP_BASE || '') + '/comunicado-venda/gerar', {
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
            
            window.location.href = (window.APP_BASE || '') + '/comunicado-venda/download?id=' + data.id;
        }
    })
    .catch(error => {
        mensagem.textContent = 'Erro ao gerar PDF: ' + error.message;
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
    });
});

// Botão de etiqueta
document.getElementById('btnGerarEtiqueta').addEventListener('click', function() {
    const vendaId = document.getElementById('venda_id_hidden').value;
    const dataComunicado = document.getElementById('data_comunicado').value;
    const nomeCliente = document.getElementById('nome_cliente').value;
    const cpfCliente = document.getElementById('cpf_cliente').value;
    const placaMoto = document.getElementById('placa_moto').value;
    const modeloMoto = document.getElementById('modelo_moto').value;
    
    if (!vendaId || !dataComunicado || !nomeCliente || !cpfCliente || !placaMoto || !modeloMoto) {
        alert('Por favor, busque os dados primeiro e preencha a data do comunicado.');
        return;
    }
    
    const formData = new FormData();
    formData.append('venda_id', vendaId);
    formData.append('data_comunicado', dataComunicado);
    formData.append('nome_cliente', nomeCliente);
    formData.append('cpf_cliente', cpfCliente);
    formData.append('placa_moto', placaMoto);
    formData.append('modelo_moto', modeloMoto);
    
    const mensagem = document.getElementById('mensagem');
    mensagem.textContent = 'Gerando etiqueta...';
    mensagem.style.display = 'block';
    mensagem.style.background = '#e3f2fd';
    mensagem.style.color = '#1976d2';
    
    fetch((window.APP_BASE || '') + '/comunicado-venda/gerarEtiqueta', {
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
            mensagem.textContent = 'Etiqueta gerada com sucesso! Fazendo download...';
            mensagem.style.background = '#e8f5e9';
            mensagem.style.color = '#2e7d32';
            
            window.location.href = (window.APP_BASE || '') + '/comunicado-venda/download?id=' + data.id + '&tipo=etiqueta';
        }
    })
    .catch(error => {
        mensagem.textContent = 'Erro ao gerar etiqueta: ' + error.message;
        mensagem.style.background = '#fee';
        mensagem.style.color = '#c33';
    });
});
</script>
