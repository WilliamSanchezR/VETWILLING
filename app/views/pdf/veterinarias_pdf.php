<?php
$veterinarias = listarVeterinariasRegistradas();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Veterinarias</title>


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
            width: 115px;
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

        .tabla-admin {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        /* Encabezado */
        .tabla-admin thead tr {
            background: #1b8f72;
            color: #fff;
        }

        .tabla-admin th {
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        /* Celdas */
        .tabla-admin td {
            padding: 10px;
            border-bottom: 1px solid #e6e6e6;
            color: #333;
            vertical-align: middle;
        }

        /* Filas alternas */
        .tabla-admin tbody tr:nth-child(even) {
            background: #f9f9f9;
        }


        .foto {
            width: 40px;
            height: 40px;
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

        /* Hover */
        .tabla-admin tbody tr:hover {
            background: #eef7f4;
        }

        /* Foto veterinaria */
        .tb_foto img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #1b8f72;
        }

        /* Icono cuando no hay foto */
        .tb_foto i {
            font-size: 20px;
            color: #bbb;
        }

        /* Estados */
        .tabla-admin td:last-child {
            font-weight: bold;
        }

        .tabla-admin td:last-child:contains("Activo") {
            color: #1b8f72;
        }

        .tabla-admin td:last-child:contains("Inactivo") {
            color: #c0392b;
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
        <h1 class="titulo">Reporte de Veterinarias</h1>
        <p class="subtitulo">Generado por el sistema Veterinaria VetWilling</p>
    </div>

    <div class="contenedor">


        <table id="tablaListaVeterinarias" class="display tabla-admin" style="width:100%">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Nit</th>
                    <th>Razón Social</th>
                    <th>Direccion</th>
                    <th>Ciudad</th>
                    <th>Telefono</th>
                    <th>Email</th>
                    <th>Estado</th>

                </tr>
            </thead>
            <tbody>
                <?php if (!empty($veterinarias)) : ?>
                    <?php foreach ($veterinarias as $datos):  ?>
                        <tr class="fila-blanca">
                            <td class="tb_foto"><?php if (!empty($datos['foto'])): ?><img src="<?= BASE_URL ?>/public/uploads/veterinaria/<?= $datos['foto'] ?>" alt=""><?php else: ?><i class="bi bi-image"></i><?php endif; ?></td>
                            <td><?= $datos['nit'] ?></td>
                            <td><?= $datos['nombre'] ?></td>
                            <td><?= $datos['direccion'] ?></td>
                            <td><?= $datos['ciudad'] ?></td>
                            <td><?= $datos['telefono'] ?></td>
                            <td><?= $datos['email'] ?></td>
                            <td class="<?= $datos['estado'] == 'Activo' ? 'estado-activo' : 'estado-inactivo' ?>"><?= $datos['estado'] ?></td>

                        </tr>
                    <?php endforeach; ?>

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