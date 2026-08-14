<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação Recebida</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            color: #334155;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0284c7 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            color: #bae6fd;
            font-weight: 500;
        }
        .badge {
            display: inline-block;
            background-color: rgba(56, 189, 248, 0.2);
            border: 1px solid #38bdf8;
            color: #7dd3fc;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .content {
            padding: 35px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 25px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0284c7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="badge">Solicitação Recebida</div>
                <h1>Em Análise</h1>
                <p>{{ $tenantName ?? 'Siscoop3F' }}</p>
            </div>

            <div class="content">
                <div class="greeting">Olá, {{ $nome }}!</div>
                <div class="message">
                    Confirmamos o recebimento da sua solicitação de adesão com sucesso!
                </div>

                <div class="info-card">
                    📋 <strong>Próximos Passos:</strong><br>
                    Seus dados e documentos já foram encaminhados para a nossa equipe administrativa. Assim que a análise for concluída, você receberá um e-mail com suas credenciais de acesso ao portal do cliente.
                </div>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} {{ $tenantName ?? 'Siscoop3F' }}. Todos os direitos reservados.<br>
                Este é um e-mail automático. Por favor, não responda a esta mensagem.
            </div>
        </div>
    </div>
</body>
</html>
