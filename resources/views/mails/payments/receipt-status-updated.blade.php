<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatus de Comprobante de Pago</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: {{ $paymentFile->status === 'approved' ? 'linear-gradient(135deg, #07254A 0%, #0e1726 100%)' : 'linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%)' }};
            padding: 24px 28px;
            color: #ffffff;
        }
        .brand-title {
            font-size: 19px;
            font-weight: 800;
            margin: 0 0 4px 0;
        }
        .brand-subtitle {
            font-size: 13px;
            opacity: 0.85;
            margin: 0;
        }
        .content {
            padding: 28px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
            background-color: {{ $paymentFile->status === 'approved' ? '#10b981' : '#ef4444' }};
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 9px 0;
            font-size: 14px;
            border-bottom: 1px solid #f8fafc;
        }
        .label {
            font-weight: 600;
            color: #64748b;
            width: 38%;
        }
        .value {
            font-weight: 700;
            color: #0f172a;
        }
        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .rejection-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 14px;
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.45;
        }
        .total-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: right;
        }
        .total-label {
            font-size: 12px;
            color: #475569;
            margin-bottom: 4px;
        }
        .total-amount {
            font-size: 22px;
            font-weight: 800;
            color: #07254A;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .btn-container {
            text-align: center;
            margin: 24px 0 10px 0;
        }
        .btn {
            background-color: #07254A;
            color: #ffffff !important;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
        }
        .footer {
            background-color: #f8fafc;
            padding: 18px 28px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Encabezado Institucional -->
        <div class="header">
            <h1 class="brand-title">Gcd Opalo certificaciones</h1>
            <p class="brand-subtitle">Control de Confianza • Notificación de Estatus de Pago</p>
        </div>

        <!-- Contenido del Correo -->
        <div class="content">
            <div class="status-badge">
                {{ $paymentFile->status === 'approved' ? 'PAGO VALIDADO Y APROBADO' : 'COMPROBANTE RECHAZADO' }}
            </div>

            <p style="font-size: 14px; color: #334155; line-height: 1.5; margin-top: 0;">
                Estimado/a <strong>{{ $paymentFile->client?->trade_name ?? $paymentFile->client?->company_name ?? 'Empresa Cliente' }}</strong>,
            </p>

            @if($paymentFile->status === 'approved')
                <p style="font-size: 14px; color: #334155; line-height: 1.5;">
                    Te confirmamos que tu comprobante de pago ha sido revisado y <strong>aprobado satisfactoriamente</strong> por el área administrativa. La orden pasa a fase de programación para agendado de evaluaciones.
                </p>
            @else
                <p style="font-size: 14px; color: #334155; line-height: 1.5;">
                    Te informamos que tu comprobante de pago <strong>no pudo ser validado</strong> por la administración.
                </p>
                <div class="rejection-box">
                    <strong>Motivo de rechazo:</strong><br>
                    {{ $paymentFile->evaluationOrder?->rejection_reason ?? $paymentFile->notes ?? 'El comprobante presentado no coincide con el total facturado o no es legible.' }}
                </div>
                <p style="font-size: 13px; color: #64748b; line-height: 1.4;">
                    Por favor, ingresa a tu portal de cliente y sube una nueva ficha de depósito o comprobante SPEI correspondiente para continuar con el servicio.
                </p>
            @endif

            <h3 class="section-title">Detalle de la Solicitud</h3>
            <table class="info-table">
                <tr>
                    <td class="label">Candidato Evaluado:</td>
                    <td class="value">{{ $paymentFile->evaluationOrder?->examinee?->full_name ?? 'Candidato General' }}</td>
                </tr>
                <tr>
                    <td class="label">CURP del Evaluado:</td>
                    <td class="value mono">{{ $paymentFile->evaluationOrder?->examinee?->curp ?? 'SIN CURP' }}</td>
                </tr>
                <tr>
                    <td class="label">Tipo de Prueba:</td>
                    <td class="value">
                        {{ $paymentFile->evaluationOrder?->evaluationType?->name ?? 'Evaluación de Control de Confianza' }}
                        @if($paymentFile->evaluationOrder?->evaluationType?->modality)
                            ({{ $paymentFile->evaluationOrder->evaluationType->modality }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Cuenta Destino:</td>
                    <td class="value">{{ $paymentFile->bankAccount?->bank_name ?? 'BBVA México' }} (Fiscal)</td>
                </tr>
            </table>

            <!-- Desglose de Pago -->
            <div class="total-box">
                <div class="total-label">Monto de la Orden (c/ 16% IVA):</div>
                <div class="total-amount">
                    ${{ number_format($paymentFile->amount ?? $paymentFile->evaluationOrder?->total ?? 0, 2) }} MXN
                </div>
            </div>

            <!-- Botón hacia el portal -->
            <div class="btn-container">
                <a href="{{ config('app.url') }}/portal/orders" class="btn">
                    {{ $paymentFile->status === 'approved' ? 'Consultar Mis Órdenes' : 'Subir Nuevo Comprobante' }}
                </a>
            </div>
        </div>

        <div class="footer">
            Este es un correo automático generado por el Sistema Institucional de Control de Confianza Gcd Opalo certificaciones.
        </div>
    </div>
</body>
</html>
