<?php
// Verificamos que el representante tenga sesion activa
require_once BASE_PATH . '/app/helpers/session_representante.php';

// Cargamos el modelo de inventario para consultar los productos
require_once BASE_PATH . '/app/models/Inventario.php';

// Obtenemos el ID de la veterinaria desde la sesion
$id_veterinaria    = (int) $_SESSION['user']['id_veterinaria'];

// Instanciamos el modelo y pedimos todos los lotes activos con su producto
$modelInv          = new Inventario();
$productos         = $modelInv->listarInventario($id_veterinaria);
$totalAlertasStock = $modelInv->contarAlertasStock($id_veterinaria);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/styleTableAdmin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <!-- Estilos específicos del módulo de inventario (badges, banner de alerta) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/inventario.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_representante.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_representante.php' ?>

        <div class="area-contenido">

            <!-- Titulo del modulo -->
            <div class="encabezado-modulo">
                <h3><i class="bi bi-box-seam me-2"></i>Gestion de Inventario</h3>
            </div>

            <!-- Banner de alerta: solo aparece si hay productos con stock bajo -->
            <?php if ($totalAlertasStock > 0): ?>
            <div class="alerta-stock-banner">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                <span>
                    <strong><?= $totalAlertasStock ?> producto(s)</strong> tienen stock por debajo del minimo.
                </span>
            </div>
            <?php endif; ?>

            <!-- Controles: buscador y boton de agregar -->
            <div class="controles-tabla">
                <div class="controles-izquierda">
                    <div class="campo-buscar">
                        <i class="bi bi-search"></i>
                        <input type="text" id="buscarInventario" placeholder="Buscar producto...">
                    </div>
                </div>
                <div class="controles-derecha">
                    <!-- Boton para ir al formulario de registro de nuevo producto -->
                    <a href="<?= BASE_URL ?>/representante/registro-producto">
                        <button class="btn-agregar">
                            <i class="bi bi-plus-lg"></i> Agregar Producto
                        </button>
                    </a>
                </div>
            </div>

            <!-- Tabla principal de inventario -->
            <div class="contenedor-tabla">
                <table id="tablaInventario" class="display tabla-admin" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Categoria</th>
                            <th>Nro. Lote</th>
                            <th>Stock</th>
                            <th>Minimo</th>
                            <th>Precio</th>
                            <th>Vencimiento</th>
                            <th>Estado Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($productos)): ?>
                            <?php foreach ($productos as $p): ?>
                                <?php
                                    // Calculamos el badge de vencimiento segun los dias restantes
                                    $dias = $p['dias_para_vencer'];
                                    if (is_null($p['fecha_vencimiento'])) {
                                        $bVence = '<span class="badge-sin-vence">Sin vencimiento</span>';
                                    } elseif ($dias < 0) {
                                        $bVence = '<span class="badge-vencido">Vencido (' . abs((int)$dias) . 'd)</span>';
                                    } elseif ($dias <= 15) {
                                        $bVence = '<span class="badge-vence-crit">Critico (' . (int)$dias . 'd)</span>';
                                    } elseif ($dias <= 30) {
                                        $bVence = '<span class="badge-vence-pronto">Pronto (' . (int)$dias . 'd)</span>';
                                    } else {
                                        $bVence = '<span class="badge-vence-ok">' . htmlspecialchars($p['fecha_vencimiento']) . '</span>';
                                    }
                                ?>
                                <tr class="fila-blanca">
                                    <td><?= (int)$p['id_inventario'] ?></td>
                                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><?= htmlspecialchars($p['categoria'] ?? '&mdash;') ?></td>
                                    <td><?= htmlspecialchars($p['numero_lote'] ?? '&mdash;') ?></td>
                                    <td><strong><?= (int)$p['cantidad'] ?></strong></td>
                                    <td><?= (int)$p['stock_minimo'] ?></td>
                                    <td>$<?= number_format((float)$p['precio'], 2) ?></td>
                                    <td><?= $bVence ?></td>
                                    <td>
                                        <?php if ($p['alerta_stock']): ?>
                                            <!-- Stock por debajo del minimo: badge rojo -->
                                            <span class="badge-stock-warn">
                                                <i class="bi bi-exclamation-circle"></i> Stock bajo
                                            </span>
                                        <?php else: ?>
                                            <!-- Stock saludable: badge verde -->
                                            <span class="badge-stock-ok">
                                                <i class="bi bi-check-circle"></i> OK
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="content-action">
                                        <!-- Boton Editar: redirige al formulario de edicion con el ID del lote -->
                                        <button class="btn-accion btn-editar" title="Editar">
                                            <a href="<?= BASE_URL ?>/representante/editar-producto?id=<?= (int)$p['id_inventario'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </button>

                                        <!-- Formulario POST de eliminacion: SweetAlert pide confirmacion antes de enviar -->
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/representante/eliminar-producto"
                                              class="form-eliminar-inv"
                                              style="display:inline">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_inventario" value="<?= (int)$p['id_inventario'] ?>">
                                            <button type="submit" class="btn-accion btn-eliminar" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fila cuando no hay datos en el inventario -->
                            <tr>
                                <td colspan="10" style="text-align:center;padding:32px;color:#888;">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No hay productos registrados en el inventario.
                                    <br>
                                    <a href="<?= BASE_URL ?>/representante/registro-producto">Registrar el primero</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /.area-contenido -->

        <!-- SCRIPTS -->
        <!-- jQuery primero (DataTables lo requiere) -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Menú lateral (colapsar/expandir sidebar) -->
        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

        <!-- Lógica del módulo: DataTables + SweetAlert confirmar eliminación -->
        <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/inventario.js"></script>

    </div><!-- /.contenido-principal -->
</body>
</html>
