<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<title>Nueva Cita Agendada</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #334155;">
	<div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

		<!-- Header Ópalo -->
		<div style="background: linear-gradient(135deg, #07254A 0%, #0e1726 100%); padding: 28px; text-align: center;">
			<h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 700;">ÓPALO • Nueva Cita de Evaluación</h1>
			<p style="color: rgba(255, 255, 255, 0.8); margin: 6px 0 0 0; font-size: 13px;">Se ha programado una sesión en la plataforma</p>
		</div>

		<!-- Contenido -->
		<div style="padding: 28px;">
			<p style="font-size: 15px; margin-top: 0;">Hola <strong>{{ $appointment->psychologist?->name }}</strong>,</p>
			<p style="font-size: 14px; line-height: 1.6;">
				La empresa <strong>{{ $appointment->client?->trade_name ?? $appointment->client?->company_name }}</strong> ha agendado una cita de evaluación con los siguientes detalles:
			</p>

			<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #f8fafc; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; font-size: 13px;">
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Candidato / Evaluado:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #07254A;">{{ $appointment->examinee?->full_name }}</td>
				</tr>
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Puesto / Categoría:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ $appointment->examinee?->position }}</td>
				</tr>
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Teléfono de Contacto (WhatsApp):</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #0f172a; font-family: monospace;">{{ $appointment->examinee?->phone }}</td>
				</tr>
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Tipo de Evaluación:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">
						{{ $appointment->evaluationType?->name }} ({{ ucfirst($appointment->modality) }})
					</td>
				</tr>
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0;">Fecha y Horario:</td>
					<td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #10b981;">
						{{ $appointment->date ? $appointment->date->format('d/m/Y') : '' }} | {{ substr($appointment->start_time, 0, 5) }} - {{ substr($appointment->end_time, 0, 5) }}
					</td>
				</tr>
				@if($appointment->notes)
				<tr>
					<td style="padding: 10px 14px; font-weight: bold; color: #64748b;">Notas Adicionales:</td>
					<td style="padding: 10px 14px; color: #0f172a;">{{ $appointment->notes }}</td>
				</tr>
				@endif
			</table>

			<p style="font-size: 13px; color: #64748b; text-align: center; margin-top: 25px;">
				Podrás consultar la lista completa y contactar al evaluado desde tu módulo de <strong>Citas</strong> en la plataforma.
			</p>
		</div>

		<!-- Footer -->
		<div style="background: #f8fafc; padding: 16px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
			© {{ date('Y') }} Ópalo Certificados de Confianza • Notificación automática
		</div>
	</div>
</body>

</html>
