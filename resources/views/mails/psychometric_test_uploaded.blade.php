<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Psicométrica Pendiente</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #07254A 0%, #0e1726 100%);
            padding: 24px;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 13px;
            opacity: 0.85;
        }
        .body-content {
            padding: 28px 24px;
        }
        .badge {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin: 20px 0;
        }
        .detail-row {
            margin-bottom: 10px;
            font-size: 13px;
            display: flex;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 700;
            color: #64748b;
            width: 130px;
            flex-shrink: 0;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 600;
        }
        .footer {
            padding: 16px 24px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ópalo Certificaciones</h1>
            <p>Control de Confianza y Evaluación Integral</p>
        </div>

        <div class="body-content">
            <div class="badge">Revisión de Psicometría Requerida</div>

            <p style="font-size: 14px; margin-top: 0; line-height: 1.5;">
                Se ha cargado un nuevo formato psicométrico contestado para el candidato en proceso. Requiere dictamen de aprobación o rechazo en la plataforma.
            </p>

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">Candidato:</span>
                    <span class="detail-value">{{ $examineeName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Empresa:</span>
                    <span class="detail-value">{{ $companyName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Prueba:</span>
                    <span class="detail-value">{{ $testName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Archivo:</span>
                    <span class="detail-value">{{ $originalName }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #475569; margin-bottom: 0; line-height: 1.4;">
                Ingresa al módulo administrativo o de trabajo social para consultar el documento adjunto y dictaminar el estatus correspondiente.
            </p>
        </div>

        <div class="footer">
            Mensaje automatizado del sistema Ópalo Certificados de Confianza.
        </div>
    </div>
</body>
</html>
