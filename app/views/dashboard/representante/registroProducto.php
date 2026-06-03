<?php
// Verificamos que el representante tenga sesion activa
require_once BASE_PATH . '/app/helpers/session_representante.php';

// Obtenemos el ID de la veterinaria desde la sesion para enviarlo en el formulario
$id_veterinaria = (int) $_SESSION['user']['id_veterinaria'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Producto</title>
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/dashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/administrador/css/formularioAdminStyles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_representante.php' ?>

    <div class="contenido-principal" id="contenidoPrincipal">
        <?php include_once __DIR__ . '/../../layouts/panel_superior_representante.php' ?>

        <div class="area-contenido">

            <!-- Titulo del formulario -->
            <div class="encabezado-modulo">
                <h3><i class="bi bi-plus-circle me-2"></i>Registrar Producto en Inventario</h3>
            </div>

            <!-- Formulario de registro -->
            <!-- Al enviarse va al controlador que crea el lote y el producto -->
            <div class="wizard-container">
                <form method="POST" action="<?= BASE_URL ?>/representante/guardar-producto">

                    <!-- Campos ocultos: id de veterinaria y accion -->
                    <input type="hidden" name="id_veterinaria" value="<?= $id_veterinaria ?>">
                    <input type="hidden" name="accion" value="guardar">

                    <!-- ===== SECCION: Informacion del producto ===== -->
                    <div class="seccion-form">
                        <h5 class="titulo-seccion">
                            <i class="bi bi-box me-2"></i>Datos del Producto
                        </h5>

                        <div class="row g-3">

                            <!-- Nombre del producto -->
                            <div class="col-md-6">
                                <label class="form-label" for="nombre">
                                    Nombre del producto <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    class="form-control"
                                    placeholder="Ej: Amoxicilina 500mg"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <!-- Categoria -->
                            <div class="col-md-6">
                                <label class="form-label" for="categoria">
                                    Categoria <span class="text-danger">*</span>
                                </label>
                                <select id="categoria" name="categoria" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="medicamento">Medicamento</option>
                                    <option value="alimento">Alimento / Dieta</option>
                                    <option value="accesorio">Accesorio</option>
                                    <option value="insumo">Insumo medico</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <!-- Descripcion -->
                            <div class="col-12">
                                <label class="form-label" for="descripcion">Descripcion</label>
                                <textarea
                                    id="descripcion"
                                    name="descripcion"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Descripcion breve del producto o indicaciones..."
                                ></textarea>
                            </div>

                            <!-- Proveedor o laboratorio -->
                            <div class="col-md-6">
                                <label class="form-label" for="proveedor">
                                    Proveedor / Laboratorio <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="proveedor"
                                    name="proveedor"
                                    class="form-control"
                                    placeholder="Ej: Lab. Veterinario S.A."
                                    maxlength="150"
                                    required
                                >
                            </div>

                            <!-- Precio unitario -->
                            <div class="col-md-6">
                                <label class="form-label" for="precio">
                                    Precio unitario <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input
                                        type="number"
                                        id="precio"
                                        name="precio"
                                        class="form-control"
                                        placeholder="0.00"
                                        min="0"
                                        step="0.01"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Fecha de vencimiento del producto -->
                            <div class="col-md-6">
                                <label class="form-label" for="fecha_vencimiento">
                                    Fecha de vencimiento <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="fecha_vencimiento"
                                    name="fecha_vencimiento"
                                    class="form-control"
                                    required
                                >
                            </div>

                        </div><!-- /.row -->
                    </div><!-- /.seccion-form -->

                    <!-- ===== SECCION: Informacion del lote ===== -->
                    <div class="seccion-form mt-4">
                        <h5 class="titulo-seccion">
                            <i class="bi bi-archive me-2"></i>Datos del Lote / Stock
                        </h5>

                        <div class="row g-3">

                            <!-- Numero de lote -->
                            <div class="col-md-6">
                                <label class="form-label" for="numero_lote">Numero de lote</label>
                                <input
                                    type="text"
                                    id="numero_lote"
                                    name="numero_lote"
                                    class="form-control"
                                    placeholder="Ej: LOT-2024-001"
                                    maxlength="60"
                                >
                            </div>

                            <!-- Cantidad en stock -->
                            <div class="col-md-3">
                                <label class="form-label" for="cantidad">
                                    Cantidad inicial <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="cantidad"
                                    name="cantidad"
                                    class="form-control"
                                    placeholder="Ej: 100"
                                    min="0"
                                    required
                                >
                            </div>

                            <!-- Stock minimo para alertas -->
                            <div class="col-md-3">
                                <label class="form-label" for="stock_minimo">Stock minimo</label>
                                <input
                                    type="number"
                                    id="stock_minimo"
                                    name="stock_minimo"
                                    class="form-control"
                                    value="5"
                                    min="0"
                                >
                                <small class="text-muted">
                                    Cuando el stock baje de este valor se mostrara una alerta.
                                </small>
                            </div>

                            <!-- Detalle de almacenamiento -->
                            <div class="col-12">
                                <label class="form-label" for="detalle_almacenamiento">
                                    Detalle de almacenamiento
                                </label>
                                <input
                                    type="text"
                                    id="detalle_almacenamiento"
                                    name="detalle_almacenamiento"
                                    class="form-control"
                                    placeholder="Ej: Refrigerar entre 2-8 grados, Anaquel B"
                                    maxlength="150"
                                >
                            </div>

                        </div><!-- /.row -->
                    </div><!-- /.seccion-form -->

                    <!-- Botones de accion -->
                    <div class="botones-form mt-4">
                        <!-- Boton cancelar: vuelve a la lista sin guardar -->
                        <a href="<?= BASE_URL ?>/representante/inventario" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <!-- Boton guardar: envia el formulario -->
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Producto
                        </button>
                    </div>

                </form>
            </div><!-- /.wizard-container -->

        </div><!-- /.area-contenido -->

        <!-- SCRIPTS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

    </div><!-- /.contenido-principal -->
</body>
</html>
