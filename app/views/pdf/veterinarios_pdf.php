<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Veterinarios</title>
    <style>
        /* Márgenes generales del documento */
        @page {
            margin: 40px 50px;
        }

        body {
            font-size: 12px;
            color: #000;
        }

        /* Encabezado */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #0db339;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }

        .header img {
            width: 80px;
            height: auto;
            position: absolute;
            right: -15px;
            top: -25px;

        }

        .header h1 {
            color: #0db339;
            font-size: 22px;
            margin: 0;
        }

        /* Contenido */
        .descripcion {
            color: #000;
            margin-bottom: 25px;
            text-align: justify;
        }

        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #0db339;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eef9f1;
        }

        td img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Footer */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            line-height: 40px;
            border-top: 1px solid #ccc;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>

    <!-- Encabezado con logo y título -->
    <div class="header">
        <img src="https://raw.githubusercontent.com/MaicBernal11/VetWilling-Imagenes-Correo/refs/heads/main/VETWILLING/LOGO-VERTICAL.png"  alt="Logo">
        <h1>Reporte de Veterinarios Inscritos</h1>
    </div>

    <!-- Descripción -->
    <p class="descripcion">
        Este reporte presenta el listado completo de los veterinarios registrados en el sistema Vetwilling durante el año 2025.
        La información es esencial para el seguimiento, control y verificación de los profesionales vinculados a las diferentes
        veterinarias, contribuyendo a la transparencia y calidad de los servicios ofrecidos.
    </p>

    <!-- Tabla de datos -->
    <table>
        <thead>
            <tr>
                <th>Foto de perfil</th>
                <th>Número de documento</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Rol</th>
                <th>Veterinaria</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($veterinarios)): ?>
                <?php foreach ($veterinarios as $veterinario): ?>
                    <tr>
                        <td>
                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $veterinario['img_perfil'] ?>" alt="">
                        </td>
                        <td><?= $veterinario['tipo_documento'] ?> - <?= $veterinario['numero_documento'] ?></td>
                        <td><?= $veterinario['nombres'] ?></td>
                        <td><?= $veterinario['apellidos'] ?></td>
                        <td><?= $veterinario['telefono'] ?></td>
                        <td><?= $veterinario['email'] ?></td>
                        <td><?= $veterinario['estado'] ?></td>
                        <td><?= $veterinario['nombre_rol'] ?></td>
                        <td><?= $veterinario['nombre_veterinaria'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12">No hay veterinarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <footer>
        &copy; Vetwilling - 2025
    </footer>

</body>

</html>