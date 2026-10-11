<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<title>Estatus de Comprobante de Pago</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #334155;">
	<div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

		<!-- Header -->
		<div style="background: linear-gradient(135deg, #07254A 0%, #0e1726 100%); padding: 28px; text-align: center;">
			<h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 700;">ÓPALO • Certificados de Confianza</h1>
			<p style="color: rgba(255, 255, 255, 0.8); margin: 6px 0 0 0; font-size: 13px;">Actualización de Estatus de Pago</p>
		</div>

		<div style="padding: 28px;">
			<p style="font-size: 15px; margin-top: 0;">
				Estimado equipo de <strong>{{ $paymentFile->client?->trade_name ?? $paymentFile->client?->company_name }}</strong>,
			</p>

			<p style="font-size: 14px; line-height: 1.6;">
				Te informamos que tu comprobante de pago con folio <code>{{ substr($paymentFile->id, 0, 8) }}</code> ha sido revisado por nuestro departamento de administración:
			</p>

			<!-- Badge de Estatus -->
			<div style="text-align: center; margin: 24px 0;">
				@if($paymentFile->status === 'approved')
				<span style="background-color: #10b981; color: #ffffff; font-weight: bold; padding: 8px 20px; border-radius: 20px; font-size: 14px; display: inline-block;">
					✓ PAGO VALIDADO Y APROBADO
				</span>
				<p style="font-size: 13px; color: #047857; margin-top: 10px;">Los fondos han sido acreditados correctamente a tus servicios.</p>
				@else
				<span style="background-color: #ef4444; color: #ffffff; font-weight: bold; padding: 8px 20px; border-radius: 20px; font-size: 14px; display: inline-block;">
					✕ COMPROBANTE NO ACREDITADO / RECHAZADO
				</span>
				@endif
			</div>

			<!-- Observaciones si hubo rechazo o nota -->
			@if($paymentFile->notes)
			<div style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; padding: 14px; border-radius: 6px; margin: 20px 0; font-size: 13px;">
				<strong style="color: #b45309; display: block; margin-bottom: 4px;">Observaciones del Auditor:</strong>
				<span style="color: #78350f;">{{ $paymentFile->notes }}</span>
			</div>
			@endif

			@if($paymentFile->status === 'rejected')
			<p style="font-size: 13px; line-height: 1.5; color: #64748b;">
				Si tienes alguna duda o consideras que se trata de un error, puedes subir un nuevo comprobante corregido desde tu portal o ponerte en contacto directo con tu asesor comercial.
			</p>
			@endif
		</div>

		<!-- Footer -->
		<div style="background: #f8fafc; padding: 16px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
			© {{ date('Y') }} Ópalo Certificados de Confianza • Notificación automática
		</div>
	</div>
</body>

</html>
