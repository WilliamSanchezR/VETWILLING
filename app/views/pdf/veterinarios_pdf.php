<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Veterinarios</title>

    <style>
        @page {
            margin: 40px 50px;
        }

        body {
            font-size: 12px;
            color: #000;
        }

        .header { 
            display: flex;
            align-items: center;
            border-bottom: 2px solid #0db339;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            height: auto;
            margin-right: 15px;
        }

        .header h1 {
            color: #0db339;
            font-size: 22px;
            margin: 0;
        }

        .descripcion {
            margin-bottom: 25px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #0db339;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        td img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #ccc;
            font-size: 12px;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <div class="header">
        <!-- CAMBIO IMPORTANTE: Usar ruta absoluta del sistema -->
        <img src="<?= BASE_PATH ?>/public/assets/webSite/img/FAVICON.png" alt="Logo">
        <h1>Reporte de Veterinarios Inscritos</h1>
    </div>

    <p class="descripcion">
        Este reporte presenta el listado completo de los veterinarios registrados en el sistema Vetwilling durante el año 2025.
    </p>

    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Tipo documento</th>
                <th>Número documento</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Teléfono</th>
                <th>Fecha contratación</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($veterinarios)): ?>
                <?php foreach ($veterinarios as $v): ?>
                    <tr>
                        <td>
                            <?php if (!empty($v['img_perfil'])): ?>
                                <!-- CAMBIO IMPORTANTE: Usar ruta absoluta del sistema -->
                                <img src="<?= BASE_PATH ?>/public/uploads/usuarios/<?= $v['img_perfil'] ?>">
                            <?php else: ?>
                                Sin foto
                            <?php endif; ?>
                        </td>
                        <td><?= $v['tipo_documento'] ?></td>
                        <td><?= $v['numero_documento'] ?></td>
                        <td><?= $v['nombres'] ?></td>
                        <td><?= $v['apellidos'] ?></td>
                        <td><?= $v['telefono'] ?></td>
                        <td><?= $v['fecha_contratacion'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No hay veterinarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>
        © Vetwilling - 2025
    </footer>

</body>
</html>