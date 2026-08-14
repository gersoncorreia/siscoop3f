<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adesão Aprovada</title>
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
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%);
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
            color: #93c5fd;
            font-weight: 500;
        }
        .badge {
            display: inline-block;
            background-color: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            color: #86efac;
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
        .credentials-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .credentials-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .credential-item {
            margin-bottom: 12px;
        }
        .credential-item:last-child {
            margin-bottom: 0;
        }
        .credential-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
        }
        .credential-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            background-color: #ffffff;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            display: inline-block;
        }
        .btn-wrapper {
            text-align: center;
            margin: 35px 0 25px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
        }
        .security-notice {
            background-color: #eff6ff;
            border-radius: 10px;
            padding: 14px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.5;
            text-align: center;
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
                <div class="badge">Adesão Aprovada</div>
                <h1>Bem-vindo!</h1>
                <p>{{ $tenantName ?? 'Siscoop3F' }}</p>
            </div>

            <div class="content">
                <div class="greeting">Olá, {{ $nome }}!</div>
                <div class="message">
                    Temos o prazer de informar que sua solicitação de adesão foi <strong>aprovada com sucesso</strong>. 
                    Sua conta de acesso ao portal do cliente já está ativa e pronta para uso.
                </div>

                <div class="credentials-card">
                    <div class="credentials-title">🔑 Suas Credenciais de Acesso</div>
                    
                    <div class="credential-item">
                        <span class="credential-label">E-MAIL DE LOGIN:</span>
                        <span class="credential-value">{{ $login }}</span>
                    </div>

                    <div class="credential-item" style="margin-top: 15px;">
                        <span class="credential-label">CÓDIGO DE ACESSO TEMPORÁRIO:</span>
                        <span class="credential-value" style="color: #2563eb;">{{ $password }}</span>
                    </div>
                </div>

                <div class="btn-wrapper">
                    <a href="{{ $loginUrl }}" class="btn" target="_blank">Acessar o Portal do Cliente</a>
                </div>

                <div class="security-notice">
                    🔒 <strong>Nota de Segurança:</strong> No seu primeiro acesso, você será solicitado a criar uma senha definitiva e pessoal para garantir a total proteção da sua conta.
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
