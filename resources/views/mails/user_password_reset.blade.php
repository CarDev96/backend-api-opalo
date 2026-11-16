<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }

        .card {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .header {
            background: linear-gradient(135deg, #07254A 0%, #140032 100%);
            padding: 32px 24px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            letter-spacing: 1px;
        }

        .header p {
            color: #cbd5e1;
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: 600;
        }

        .content {
            padding: 32px 24px;
        }

        .credentials {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #D97706;
            border-radius: 8px;
            padding: 18px;
            margin: 20px 0;
        }

        .field-title {
            font-size: 12px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
        }

        .field-value {
            font-size: 15px;
            font-weight: 700;
            color: #07254A;
            margin-bottom: 10px;
        }

        .btn-container {
            text-align: center;
            margin-top: 28px;
        }

        .btn {
            background-color: #07254A;
            color: #ffffff !important;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    @php
        $loginUrl = rtrim(config('app.frontend_url'), '/') . '/login';
    @endphp
    <div class="card">
        <div class="header">
            <h1>GCD ÓPALO</h1>
            <p>CERTIFICADOS DE CONFIANZA</p>
        </div>
        <div class="content">
            <h2 style="font-size: 18px; margin-top: 0;">Hola, {{ $user->name }}</h2>
            <p style="font-size: 14px; line-height: 1.5; color: #475569;">
                El área de administración ha restablecido tus credenciales de acceso interno en la plataforma. A continuación encontrarás tu nueva contraseña temporal:
            </p>

            <div class="credentials">
                <div class="field-title">Portal de Acceso:</div>
                <div class="field-value">{{ $loginUrl }}</div>

                <div class="field-title">Correo Institucional:</div>
                <div class="field-value">{{ $user->email }}</div>

                <div class="field-title">Nueva Contraseña Temporal:</div>
                <div class="field-value" style="font-family: monospace; font-size: 16px; margin-bottom: 0;">{{ $plainPassword }}</div>
            </div>

            <p style="font-size: 13px; color: #64748b;">
                * Por seguridad, te recomendamos cambiar esta contraseña temporal por una personal desde tu perfil una vez que ingreses.
            </p>

            <div class="btn-container">
                <a href="{{ $loginUrl }}" class="btn" target="_blank">Ingresar al Sistema</a>
            </div>
        </div>
        <div class="footer">
            Este correo se generó de forma automática. Por favor no respondas a este mensaje.
        </div>
    </div>
</body>

</html>
