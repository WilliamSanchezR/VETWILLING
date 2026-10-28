<?php
// Plantilla del correo de recuperación. Variables disponibles: $email, $enlace, $minutos
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña</title>
</head>
<body style="margin:0; padding:0; background-color:#f8f9fa; font-family:Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8f9fa;">
        <tr>
            <td align="center" style="padding:40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; box-shadow:0 4px 20px rgba(10,147,44,0.1);">

                    <!-- ENCABEZADO -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #0a932c 0%, #9de795 100%); padding:40px 20px; text-align:center; border-radius:16px 16px 0 0;">
                            <table role="presentation" width="240" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom:20px;">
                                <tr>
                                    <td align="center" style="background-color:#ffffff; border-radius:12px; padding:10px; box-shadow:0 8px 16px rgba(0,0,0,0.1);">
                                        <img src="https://raw.githubusercontent.com/MaicBernal11/VetWilling-Imagenes-Correo/refs/heads/main/VETWILLING/LOGO-VERTICAL.png" alt="VetWilling" width="220" height="170" style="display:block; max-width:100%; height:auto;">
                                    </td>
                                </tr>
                            </table>
                            <h1 style="margin:0; font-size:28px; font-weight:700; color:#ffffff;">VetWilling</h1>
                            <p style="margin:10px 0 0; font-size:16px; color:#ffffff; opacity:0.95;">Sistema de Gestión Veterinaria</p>
                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding:40px 30px; color:#424b54;">
                            <h2 style="text-align:center; color:#0a932c; margin:0 0 20px 0; font-size:24px; font-weight:700;">
                                Restablece tu contraseña
                            </h2>

                            <p style="font-size:15px; line-height:1.7; margin:0 0 20px 0;">
                                Hemos recibido una solicitud para restablecer la contraseña de la cuenta
                                <strong style="color:#0a932c;"><?= $e($email) ?></strong>.
                                Haz clic en el botón para elegir una nueva contraseña.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
                                <tr>
                                    <td align="center" style="background-color:#0a932c; border-radius:30px; box-shadow:0 4px 15px rgba(10,147,44,0.3);">
                                        <a href="<?= $e($enlace) ?>" style="display:inline-block; padding:16px 40px; font-size:16px; color:#ffffff; font-weight:600; text-decoration:none;">
                                            🔐 Crear nueva contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:25px 0;">
                                <tr>
                                    <td style="background-color:#fff8e1; border-left:4px solid #93bedf; border-radius:6px; padding:15px;">
                                        <p style="margin:0; font-size:14px; line-height:1.6;">
                                            ⚠️ <strong>Importante:</strong> este enlace vence en <?= (int) $minutos ?> minutos
                                            y solo puede usarse una vez.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; color:#888; line-height:1.6; margin:0 0 10px 0;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                <span style="word-break:break-all; color:#0a932c;"><?= $e($enlace) ?></span>
                            </p>

                            <p style="font-size:13px; color:#888; line-height:1.6; margin:20px 0 0 0;">
                                Si tú no solicitaste este cambio, ignora este mensaje: tu contraseña actual no ha cambiado.
                            </p>
                        </td>
                    </tr>

                    <!-- PIE -->
                    <tr>
                        <td style="background-color:#f8f9fa; padding:20px; text-align:center; border-top:1px solid #e0e0e0; border-radius:0 0 16px 16px;">
                            <p style="margin:0 0 10px 0; font-size:12px; color:#424b54; font-weight:600;">
                                🐾 Veterinaria VetWilling – Siempre aquí para ti y tus mascotas
                            </p>
                            <p style="margin:0; font-size:12px; color:#888;">
                                © <?= date('Y') ?> VetWilling. Todos los derechos reservados
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
