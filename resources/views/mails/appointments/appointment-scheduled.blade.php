<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
			background-color: #f8fafc;
			color: #1e293b;
			margin: 0;
			padding: 20px;
		}

		.container {
			max-width: 600px;
			margin: 0 auto;
			background-color: #ffffff;
			border-radius: 12px;
			border: 1px solid #e2e8f0;
			overflow: hidden;
		}

		.header {
			background: linear-gradient(135deg, #07254A 0%, #0e1726 100%);
			padding: 24px;
			text-align: center;
			color: #ffffff;
		}

		.header h1 {
			margin: 0;
			font-size: 20px;
			font-weight: 700;
		}

		.header p {
			margin: 4px 0 0;
			font-size: 13px;
			opacity: 0.85;
		}

		.content {
			padding: 28px 24px;
		}

		.info-table {
			width: 100%;
			border-collapse: collapse;
			margin: 16px 0 24px 0;
		}

		.info-table td {
			padding: 10px 12px;
			border-bottom: 1px solid #f1f5f9;
			font-size: 14px;
		}

		.info-table td.label {
			color: #64748b;
			font-weight: 600;
			width: 38%;
		}

		.info-table td.value {
			color: #0f172a;
			font-weight: 700;
		}

		.notes-box {
			background-color: #f8fafc;
			border-left: 4px solid #07254A;
			padding: 12px 16px;
			border-radius: 4px;
			font-size: 13px;
			color: #334155;
			margin-bottom: 20px;
		}

		.footer {
			text-align: center;
			padding: 16px;
			font-size: 11px;
			color: #94a3b8;
			border-top: 1px solid #f1f5f9;
		}
	</style>
</head>

<body>
	<div class="container">
		<div class="header">
			<h1>ÓPALO • Certificados de Confianza</h1>
			<p>Notificación de Cita de Evaluación</p>
		</div>

		<div class="content">
			@if($recipientType === 'psychologist')
			<p style="font-size: 15px; margin-top: 0;">Estimada/o <strong>{{ $appointment->psychologist->name }}</strong>,</p>
			<p style="font-size: 14px; color: #475569;">
				Administración te ha asignado una nueva cita de evaluación en tu agenda operativa:
			</p>
			@else
			<p style="font-size: 15px; margin-top: 0;">Estimados representantes de <strong>{{ $appointment->client->trade_name ?? $appointment->client->company_name }}</strong>,</p>
			<p style="font-size: 14px; color: #475569;">
				Confirmamos que la cita de evaluación para su candidato ha sido agendada con éxito:
			</p>
			@endif

			<table class="info-table">
				<tr>
					<td class="label">Candidato / Evaluado:</td>
					<td class="value">{{ $appointment->examinee->full_name }}</td>
				</tr>
				<tr>
					<td class="label">Puesto:</td>
					<td class="value">{{ $appointment->examinee->position ?? 'No especificado' }}</td>
				</tr>
				<tr>
					<td class="label">Tipo de Evaluación:</td>
					<td class="value">{{ $appointment->evaluationType->name }}</td>
				</tr>
				<tr>
					<td class="label">Modalidad:</td>
					<td class="value" style="text-transform: uppercase;">{{ $appointment->modality }}</td>
				</tr>
				<tr>
					<td class="label">Fecha:</td>
					<td class="value">{{ \Carbon\Carbon::parse($appointment->date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</td>
				</tr>
				<tr>
					<td class="label">Horario:</td>
					<td class="value">{{ substr($appointment->start_time, 0, 5) }} - {{ substr($appointment->end_time, 0, 5) }} hrs</td>
				</tr>
				<tr>
					<td class="label">Evaluador/a Asignada:</td>
					<td class="value">{{ $appointment->psychologist->name }}</td>
				</tr>
			</table>

			@if($appointment->notes)
			<div class="notes-box">
				<strong>Notas adicionales:</strong><br>
				{{ $appointment->notes }}
			</div>
			@endif
		</div>

		<div class="footer">
			Ópalo Sistema de Gestión de Certificaciones de Confianza.
		</div>
	</div>
</body>

</html>
