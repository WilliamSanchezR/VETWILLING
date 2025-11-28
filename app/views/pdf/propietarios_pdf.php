<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Propietarios</title>
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
        <img src="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" alt="Logo">
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
                <th>ID Usuario</th>
                <th>Tipo de documento</th>
                <th>Número de documento</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Tipo de usuario</th>
                <th>Rol</th>
                <th>Veterinaria</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($veterinarios)): ?>
                <?php foreach ($veterinarios as $veterinario): ?>
                    <tr>
                        <td>
                            <?php if (!empty($veterinario['img_perfil'])): ?>
                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $veterinario['img_perfil'] ?>" alt="Foto">
                            <?php else: ?>
                                <span>Sin foto</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $veterinario['id_usuario'] ?></td>
                        <td><?= $veterinario['tipo_documento'] ?></td>
                        <td><?= $veterinario['numero_documento'] ?></td>
                        <td><?= $veterinario['nombres'] ?></td>
                        <td><?= $veterinario['apellidos'] ?></td>
                        <td><?= $veterinario['telefono'] ?></td>
                        <td><?= $veterinario['email'] ?></td>
                        <td><?= $veterinario['estado'] ?></td>
                        <td><?= $veterinario['tipo_usuario'] ?></td>
                        <td><?= $veterinario['id_rol'] ?></td>
                        <td><?= $veterinario['id_veterinaria'] ?></td>
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