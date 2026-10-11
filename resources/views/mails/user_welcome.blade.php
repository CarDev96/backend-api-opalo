<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<style>
		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background-color: #f1f5f9;
			margin: 0;
			padding: 24px;
			color: #1e293b;
		}

		.card {
			max-width: 520px;
			margin: 0 auto;
			background: #ffffff;
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid #e2e8f0;
		}

		.header {
			background: linear-gradient(135deg, #07254A 0%, #0e1726 100%);
			padding: 26px 20px;
			text-align: center;
		}

		.header h1 {
			color: #ffffff;
			margin: 0;
			font-size: 22px;
			letter-spacing: 1px;
		}

		.header p {
			color: #b5a08f;
			margin: 4px 0 0 0;
			font-size: 11px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.8px;
		}

		.content {
			padding: 28px 24px;
		}

		.credentials {
			background-color: #f8fafc;
			border: 1px solid #e2e8f0;
			border-left: 4px solid #07254A;
			border-radius: 8px;
			padding: 16px;
			margin: 20px 0;
		}

		.field-title {
			font-size: 11px;
			color: #64748b;
			font-weight: bold;
			text-transform: uppercase;
			margin-bottom: 2px;
		}

		.field-value {
			font-size: 14px;
			font-weight: 700;
			color: #07254A;
			margin-bottom: 12px;
		}

		.btn-wrap {
			text-align: center;
			margin-top: 24px;
		}

		.btn {
			background-color: #07254A;
			color: #ffffff !important;
			padding: 12px 28px;
			text-decoration: none;
			border-radius: 8px;
			font-weight: bold;
			font-size: 13px;
			display: inline-block;
		}

		.footer {
			text-align: center;
			padding: 14px;
			font-size: 11px;
			color: #94a3b8;
		}
	</style>
</head>

<body>
	<div class="card">
		<div class="header">
			<h1>ÓPALO</h1>
			<p>Certificados de Confianza</p>
		</div>
		<div class="content">
			<h3 style="margin-top: 0; font-size: 17px; color: #0f172a;">Bienvenido(a), {{ $user->name }}</h3>
			<p style="font-size: 13px; line-height: 1.5; color: #475569;">
				Se ha habilitado tu acceso a la plataforma Ópalo con el perfil de <strong>{{ $roleName }}</strong>. Ya puedes ingresar al sistema con tus credenciales asignadas:
			</p>

			<div class="credentials">
				<div class="field-title">Portal de Acceso</div>
				<div class="field-value">{{ $portalUrl }}</div>

				<div class="field-title">Correo Institucional</div>
				<div class="field-value">{{ $user->email }}</div>

				<div class="field-title">Contraseña Temporal</div>
				<div class="field-value" style="font-family: monospace; font-size: 16px; margin-bottom: 0;">{{ $plainPassword }}</div>
			</div>

			<div class="btn-wrap">
				<a href="{{ $portalUrl }}" class="btn" target="_blank">Iniciar Sesión en el Portal</a>
			</div>
		</div>
		<div class="footer">
			Mensaje institucional generado automáticamente por Plataforma Ópalo.
		</div>
	</div>
</body>

</html>
