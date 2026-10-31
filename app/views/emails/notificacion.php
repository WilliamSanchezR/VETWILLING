<?php
// Plantilla de los correos de notificación (citas, recordatorios, cancelaciones).
// El mensaje principal viene de la plantilla editable en Medios de envío; aquí va el diseño de marca.
// Variables disponibles:
//   $titulo   (string) título dentro del correo
//   $mensaje  (string) HTML ya renderizado de la plantilla (las variables vienen escapadas)
//   $detalles (array)  ['Etiqueta' => 'valor'] para el recuadro de datos (se escapan aquí)
//   $nota     (string) texto opcional bajo el recuadro
//   $acento   (string) color del recuadro: verde (por defecto), ámbar o rojo
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$colores = [
    'verde' => ['#0a932c', '#eefaf1'],
    'ambar' => ['#f0a500', '#fff8e1'],
    'rojo'  => ['#c62828', '#fdecea'],
];
[$colorBorde, $colorFondo] = $colores[$acento ?? 'verde'] ?? $colores['verde'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($titulo) ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f8f9fa; font-family:Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8f9fa;">
        <tr>
            <td align="center" style="padding:40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; box-shadow:0 4px 20px rgba(10,147,44,0.1);">

                    <!-- ENCABEZADO -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #0a932c 0%, #9de795 100%); padding:32px 20px; text-align:center; border-radius:16px 16px 0 0;">
                            <h1 style="margin:0; font-size:26px; font-weight:700; color:#ffffff;">VetWilling</h1>
                            <p style="margin:8px 0 0; font-size:15px; color:#ffffff; opacity:0.95;">Sistema de Gestión Veterinaria</p>
                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding:36px 30px; color:#424b54;">
                            <h2 style="color:#0a932c; margin:0 0 18px 0; font-size:22px; font-weight:700;">
                                <?= $e($titulo) ?>
                            </h2>

                            <div style="font-size:15px; line-height:1.7; margin:0 0 20px 0;">
                                <?= $mensaje ?>
                            </div>

                            <?php if (!empty($detalles)): ?>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:20px 0;">
                                    <tr>
                                        <td style="background-color:<?= $colorFondo ?>; border-left:4px solid <?= $colorBorde ?>; border-radius:6px; padding:16px 18px;">
                                            <?php foreach ($detalles as $etiqueta => $valor): ?>
                                                <?php if ($valor !== null && $valor !== ''): ?>
                                                    <p style="margin:0 0 6px 0; font-size:14px; line-height:1.6;">
                                                        <strong><?= $e($etiqueta) ?>:</strong> <?= $e($valor) ?>
                                                    </p>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <?php if (!empty($nota)): ?>
                                <p style="font-size:14px; color:#666; line-height:1.6; margin:0;"><?= $e($nota) ?></p>
                            <?php endif; ?>
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
