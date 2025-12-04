<?php
$id_veterinaria = $_SESSION['user']['id_veterinaria'] ?? 1;
$veterinarios = mostrarVeterinarios($id_veterinaria);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Veterinarios</title>

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
            border-radius: 8px;
            overflow: hidden;
        }

        thead tr {
            background: #1b8f72;
            color: white;
        }

        th {
            padding: 12px;
            font-size: 14px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e6e6e6;
            color: #333;
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

        .estado-activo {
            color: #1b8f72;
            font-weight: bold;
        }

        .estado-inactivo {
            color: #c0392b;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 35px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="top-bar"></div>

    <div class="header">
        <img src="<?= BASE_URL ?>/public/assets/website/img/LOGO-NEGATIVO.png" alt="Logo">
        <h1 class="titulo">Reporte de Veterinarios</h1>
        <p class="subtitulo">Generado por el sistema Veterinaria VetWilling</p>
    </div>

    <div class="contenedor">

        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre Completo</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($veterinarios)) : ?>
                    <?php foreach ($veterinarios as $veterinario) : ?>
                        <tr>
                            <td>
                                <img class="foto" src="<?= BASE_URL ?>/public/uploads/veterinarios/<?= $veterinario['img_perfil'] ?>" alt="">
                            </td>
                            <td><?= $veterinario['nombres'] . ' ' . $veterinario['apellidos'] ?></td>
                            <td><?= $veterinario['tipo_documento'] ?> - <?= $veterinario['numero_documento'] ?></td>
                            <td><?= $veterinario['telefono'] ?></td>
                            <td><?= $veterinario['email'] ?></td>
                            <td class="<?= $veterinario['estado'] == 'Activo' ? 'estado-activo' : 'estado-inactivo' ?>">
                                <?= $veterinario['estado'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:15px;">
                            No hay veterinarios registrados
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>


        </table>
    </div>

    <div class="footer">
        Documento generado automáticamente el <?= date("d/m/Y H:i a") ?>
        <br>© <?= date("Y") ?> VetWilling — Todos los derechos reservados.
    </div>

</body>

</html>