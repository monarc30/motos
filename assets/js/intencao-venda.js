document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formIntencaoVenda');
    const btnBuscar = document.getElementById('btnBuscarAutoconf');
    const vendaIdInput = document.getElementById('venda_id');

    btnBuscar.addEventListener('click', function() {
        const vendaId = vendaIdInput.value.trim();
        
        if (!vendaId) {
            alert('Por favor, informe o ID da venda');
            return;
        }

        btnBuscar.disabled = true;
        btnBuscar.textContent = 'Buscando...';

        fetch('/intencao-venda/buscarAutoconf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'venda_id=' + encodeURIComponent(vendaId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                preencherDados(data.dados);
            } else {
                alert('Erro: ' + (data.erro || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao buscar dados do Autoconf');
        })
        .finally(() => {
            btnBuscar.disabled = false;
            btnBuscar.textContent = 'Buscar Dados';
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        
        fetch('/intencao-venda/gerar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert('PDF gerado com sucesso!');
            } else {
                alert('Erro: ' + (data.erro || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao gerar PDF');
        });
    });

    function preencherDados(dados) {
        if (dados.cliente) {
            document.getElementById('nome_cliente').value = dados.cliente.nome || '';
            document.getElementById('cpf_cliente').value = dados.cliente.cpf || '';
            document.getElementById('telefone_cliente').value = dados.cliente.telefone || '';
            document.getElementById('whatsapp_cliente').value = dados.cliente.whatsapp || '';
            document.getElementById('endereco_cliente').value = dados.cliente.endereco || '';
        }

        if (dados.veiculo) {
            document.getElementById('modelo_moto').value = dados.veiculo.modelo || '';
            document.getElementById('placa_moto').value = dados.veiculo.placa || '';
            document.getElementById('ano_moto').value = dados.veiculo.ano || '';
        }
    }
});

