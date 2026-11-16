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
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #07254A 0%, #0e1726 100%);
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .header p {
            color: #cbd5e1;
            margin: 4px 0 0 0;
            font-size: 12px;
            font-weight: 600;
        }
        .content {
            padding: 28px 24px;
        }
        .candidate-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #07254A;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 16px 0;
        }
        .missing-list {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 18px 0;
        }
        .missing-list ul {
            margin: 8px 0 0 0;
            padding-left: 18px;
            color: #78350f;
            font-size: 13px;
        }
        .missing-list li {
            margin-bottom: 5px;
        }
        .btn-container {
            text-align: center;
            margin-top: 26px;
        }
        .btn {
            background-color: #07254A;
            color: #ffffff !important;
            padding: 12px 26px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 18px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    @php
        $portalUrl = rtrim(config('app.frontend_url'), '/') . '/portal/examinees';
    @endphp
    <div class="card">
        <div class="header">
            <h1>ÓPALO CERTIFICACIONES</h1>
            <p>SEGUIMIENTO DE EXPEDIENTE DIGITAL</p>
        </div>
        <div class="content">
            <h2 style="font-size: 17px; margin-top: 0;">Estimado cliente ({{ $examinee->client?->company_name ?? 'Empresa' }}),</h2>
            <p style="font-size: 14px; line-height: 1.5; color: #475569;">
                Te informamos que el expediente digital del siguiente candidato aún presenta elementos pendientes para continuar con su proceso de evaluación:
            </p>

            <div class="candidate-box">
                <div style="font-size: 11px; color: #64748b; font-weight: bold; text-transform: uppercase;">Candidato Evaluado</div>
                <div style="font-size: 15px; font-weight: 700; color: #07254A;">{{ $examinee->full_name }}</div>
                <div style="font-size: 12px; color: #64748b; font-family: monospace;">CURP: {{ $examinee->curp ?? 'No registrada' }}</div>
            </div>

            <div class="missing-list">
                <div style="font-size: 13px; font-weight: 700; color: #92400e;">Elementos pendientes por cargar en plataforma:</div>
                <ul>
                    @foreach($missingItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>

            <p style="font-size: 13px; color: #475569; line-height: 1.5;">
                Por favor ingresa al portal empresarial para completar la carga de archivos y referencias correspondientes.
            </p>

            <div class="btn-container">
                <a href="{{ $portalUrl }}" class="btn" target="_blank">Completar Expediente en el Portal</a>
            </div>
        </div>
        <div class="footer">
            Este correo fue enviado automáticamente por el área de Trabajo Social y Evaluación de Ópalo.
        </div>
    </div>
</body>
</html>
