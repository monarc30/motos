document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formComunicadoVenda');
    const btnBuscar = document.getElementById('btnBuscarAutoconf');
    const btnEtiqueta = document.getElementById('btnGerarEtiqueta');
    const vendaIdInput = document.getElementById('venda_id');

    btnBuscar.addEventListener('click', function() {
        const vendaId = vendaIdInput.value.trim();
        
        if (!vendaId) {
            alert('Por favor, informe o ID da venda');
            return;
        }

        btnBuscar.disabled = true;
        btnBuscar.textContent = 'Buscando...';

        fetch('/comunicado-venda/buscarAutoconf', {
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
        
        fetch('/comunicado-venda/gerar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert('Comunicado gerado com sucesso!');
            } else {
                alert('Erro: ' + (data.erro || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao gerar comunicado');
        });
    });

    btnEtiqueta.addEventListener('click', function() {
        const formData = new FormData(form);
        
        fetch('/comunicado-venda/gerarEtiqueta', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert('Etiqueta gerada com sucesso!');
            } else {
                alert('Erro: ' + (data.erro || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao gerar etiqueta');
        });
    });

    function preencherDados(dados) {
        if (dados.cliente) {
            document.getElementById('nome_cliente').value = dados.cliente.nome || '';
            document.getElementById('cpf_cliente').value = dados.cliente.cpf || '';
        }

        if (dados.veiculo) {
            document.getElementById('placa_moto').value = dados.veiculo.placa || '';
            document.getElementById('modelo_moto').value = dados.veiculo.modelo || '';
        }
    }
});

