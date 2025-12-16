<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Mascotas</title>

    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f7f6;
        }

        .top-bar {
            width: 100%;
            background: #1b8f72;
            padding: 10px 0;
        }

        .header {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
        }

        .header img {
            width: 120px;
            margin-bottom: 10px;
        }

        .titulo {
            font-size: 28px;
            margin: 5px 0;
            font-weight: bold;
            color: #1b8f72;
        }

        .subtitulo {
            font-size: 15px;
            color: #5c5c5c;
            margin-top: -5px;
            letter-spacing: 0.5px;
        }

        .contenedor {
            background: #fff;
            margin: 20px auto 40px;
            width: 90%;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .info-usuario {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #1b8f72;
        }

        .info-usuario p {
            margin: 5px 0;
            color: #333;
            font-size: 14px;
        }

        .info-usuario strong {
            color: #1b8f72;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 13px;
            border-radius: 8px;
            overflow: hidden;
        }

        thead tr {
            background: #1b8f72;
            color: white;
        }

        th {
            padding: 12px;
            font-size: 13px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e6e6e6;
            color: #333;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .foto {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #1b8f72;
        }

        .foto-placeholder {
            width: 50px;
            height: 50px;
            background: #e0e0e0;
            border-radius: 50%;
            text-align: center;
            line-height: 50px;
            font-size: 20px;
            color: #666;
            border: 2px solid #1b8f72;
            display: inline-block;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-macho {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-hembra {
            background: #fce4ec;
            color: #c2185b;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }

        .footer {
            text-align: center;
            margin-top: 35px;
            font-size: 11px;
            color: #777;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .total-mascotas {
            background: #1b8f72;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="top-bar"></div>

    <div class="header">
        <?php 
        // Logo en BASE64
        $logoPath = BASE_PATH . '/public/assets/website/img/LOGO-NEGATIVO.png';
        if (file_exists($logoPath)): 
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        ?>
            <img src="<?= $logoSrc ?>" alt="Logo">
        <?php endif; ?>
        
        <h1 class="titulo">Reporte de Mis Mascotas</h1>
        <p class="subtitulo">Generado por el sistema Veterinaria VetWilling</p>
    </div>

    <div class="contenedor">

        <!-- Información del Usuario -->
        <?php if (isset($_SESSION['user'])): ?>
            <div class="info-usuario">
                <p><strong>Propietario:</strong> <?= htmlspecialchars($_SESSION['user']['nombres'] ?? 'N/A') ?></p>
                <p><strong>Correo:</strong> <?= htmlspecialchars($_SESSION['user']['email'] ?? 'N/A') ?></p>
                <p><strong>Fecha de generación:</strong> <?= date("d/m/Y H:i a") ?></p>
            </div>
        <?php endif; ?>

        <div class="total-mascotas">
            Total de mascotas registradas: <?= count($mascotas) ?>
        </div>

        <?php if (empty($mascotas)): ?>
            <div class="no-data">
                📋 No hay mascotas registradas en este momento
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">Foto</th>
                        <th style="width: 18%;">Nombre</th>
                        <th style="width: 15%;">Especie</th>
                        <th style="width: 15%;">Raza</th>
                        <th style="width: 15%;">Edad</th>
                        <th style="width: 12%;">Sexo</th>
                        <th style="width: 19%;">Última Visita</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mascotas as $m): ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php 
                                // ✅ CONVERTIR IMAGEN A BASE64
                                $nombreImagen = !empty($m['img_mascota']) ? $m['img_mascota'] : 'default_pet.jpg';
                                $rutaImagen = BASE_PATH . '/public/uploads/mascotas/' . $nombreImagen;
                                
                                // Verificar si existe y convertir a base64
                                if (file_exists($rutaImagen) && is_readable($rutaImagen)): 
                                    $imageData = file_get_contents($rutaImagen);
                                    
                                    if ($imageData !== false):
                                        // Detectar el tipo de imagen
                                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                        $mimeType = finfo_file($finfo, $rutaImagen);
                                        finfo_close($finfo);
                                        
                                        // Convertir a base64
                                        $base64 = base64_encode($imageData);
                                        $imagenSrc = 'data:' . $mimeType . ';base64,' . $base64;
                                ?>
                                        <img src="<?= $imagenSrc ?>" alt="Mascota" class="foto">
                                    <?php else: ?>
                                        <div class="foto-placeholder">🐾</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="foto-placeholder">🐾</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($m['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($m['especie']) ?></td>
                            <td><?= htmlspecialchars($m['raza']) ?></td>
                            <td><?= htmlspecialchars($m['edad_numero']) ?> <?= htmlspecialchars($m['edad_unidad']) ?></td>
                            <td>
                                <?php 
                                $sexo = strtolower($m['sexo']);
                                $clase = ($sexo === 'macho') ? 'badge-macho' : 'badge-hembra';
                                ?>
                                <span class="badge <?= $clase ?>">
                                    <?= htmlspecialchars(ucfirst($m['sexo'])) ?>
                                </span>
                            </td>
                            <td style="text-align: center; color: #999;">—</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

    <div class="footer">
        Documento generado automáticamente el <?= date("d/m/Y H:i a") ?>
        <br>© <?= date("Y") ?> VetWilling — Todos los derechos reservados.
    </div>

</body>

</html>