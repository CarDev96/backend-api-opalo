<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<title>Nuevo Comprobante Reportado</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #334155;">
	<div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

		<!-- Header Corporativo Ópalo -->
		<div style="background: linear-gradient(135deg, #07254A 0%, #0e1726 100%); padding: 28px; text-align: center;">
			<h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 700;">ÓPALO • Notificación de Cobranza</h1>
			<p style="color: rgba(255, 255, 255, 0.8); margin: 6px 0 0 0; font-size: 13px;">Se ha reportado un nuevo comprobante en el portal</p>
		</div>

		<!-- Contenido -->
		<div style="padding: 28px;">
			<p style="font-size: 15px; margin-top: 0;">Hola equipo,</p>
			<p style="font-size: 14px; line-height: 1.6;">
				La empresa <strong>{{ $paymentFile->client?->trade_name ?? $paymentFile->client?->company_name }}</strong> (RFC: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $paymentFile->client?->rfc }}</code>) ha subido un nuevo comprobante de transferencia bancaria para validación.
			</p>

			<!-- Tabla de Detalles -->
			<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #f8fafc; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; font-size: 13px;">
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Banco Destino:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ $paymentFile->bankAccount?->bank_name ?? 'No especificado' }} ({{ $paymentFile->account_type === 'fiscal' ? 'Fiscal' : 'No Fiscal' }})</td>
				</tr>
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Monto Reportado:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #07254A; font-family: monospace; font-size: 14px;">
						{{ $paymentFile->amount ? '$' . number_format($paymentFile->amount, 2) : 'No especificado' }}
					</td>
				</tr>
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Archivo:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ $paymentFile->title }}</td>
				</tr>
				@if($paymentFile->notes)
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b;">Notas del Cliente:</td>
					<td style="padding: 10px 14px; color: #0f172a;">{{ $paymentFile->notes }}</td>
				</tr>
				@endif
			</table>

			<p style="font-size: 13px; color: #64748b; text-align: center; margin-top: 25px;">
				Por favor ingresa al módulo de <strong>Validación de Pagos</strong> en la consola de Ópalo para auditar el movimiento.
			</p>
		</div>

		<!-- Footer -->
		<div style="background: #f8fafc; padding: 16px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
			© {{ date('Y') }} Ópalo Certificados de Confianza • Sistema Automatizado
		</div>
	</div>
</body>

</html>
