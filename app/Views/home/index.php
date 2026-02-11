<h1>Bem-vindo ao Sistema de Gerenciamento de Vendas de Motocicletas</h1>

<div style="margin-top: 2rem;">
    <p style="font-size: 1.1rem; color: #666; margin-bottom: 2rem;">
        Sistema integrado para automatizar e otimizar os processos administrativos e legais 
        relacionados à venda de motocicletas.
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <div style="border: 2px solid #667eea; border-radius: 8px; padding: 2rem; background: #f8f9ff;">
            <h2 style="color: #667eea; margin-bottom: 1rem;">📝 Intenção de Venda</h2>
            <p style="margin-bottom: 1.5rem; color: #666;">
                Crie e gere documentos de intenção de venda de forma automatizada, 
                integrando dados do Autoconf e validando informações.
            </p>
            <a href="<?= htmlspecialchars($baseUrl ?? '/motos') ?>/intencao-venda" style="display: inline-block; background: #667eea; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold; transition: background 0.3s;">
                Acessar Intenção de Venda
            </a>
        </div>
        
        <div style="border: 2px solid #764ba2; border-radius: 8px; padding: 2rem; background: #faf8ff;">
            <h2 style="color: #764ba2; margin-bottom: 1rem;">📨 Comunicado de Venda</h2>
            <p style="margin-bottom: 1.5rem; color: #666;">
                Gere documentos de comunicado de venda e etiquetas para envio, 
                facilitando os processos de comunicação e documentação pós-venda.
            </p>
            <a href="<?= htmlspecialchars($baseUrl ?? '/motos') ?>/comunicado-venda" style="display: inline-block; background: #764ba2; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold; transition: background 0.3s;">
                Acessar Comunicado de Venda
            </a>
        </div>
    </div>
</div>

<div style="margin-top: 3rem; padding: 2rem; background: #f0f0f0; border-radius: 8px;">
    <h2 style="margin-bottom: 1rem;">ℹ️ Como funciona?</h2>
    <ol style="margin-left: 1.5rem; line-height: 2;">
        <li><strong>Venda registrada no Autoconf:</strong> A venda é registrada no sistema Autoconf (sistema externo) e recebe um ID único.</li>
        <li><strong>Buscar dados:</strong> No sistema, informe o ID da venda do Autoconf e os dados serão buscados automaticamente.</li>
        <li><strong>Preencher informações:</strong> O sistema preenche automaticamente os dados do veículo e, se disponível, do cliente.</li>
        <li><strong>Gerar documento:</strong> Após preencher os dados do CRV, gere o PDF que será anexado automaticamente ao processo no Autoconf.</li>
    </ol>
</div>

<style>
    a:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    a {
        transition: all 0.3s;
    }
</style>

