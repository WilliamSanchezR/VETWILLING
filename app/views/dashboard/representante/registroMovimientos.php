<?php
// Proteger la vista
require_once __DIR__ . '/../../helpers/session_representante.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Registro de Movimientos de Stock</h2>

            <!-- Formulario de Registro -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Nuevo Movimiento</h5>
                </div>
                <div class="card-body">
                    <form id="formMovimiento" method="POST" action="/representante/movimientos-stock?accion=registrar">
                        <div class="row">
                            <!-- Tipo de Movimiento -->
                            <div class="col-md-3">
                                <label for="tipo" class="form-label">Tipo de Movimiento</label>
                                <select id="tipo" name="tipo" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="entrada">Entrada (Compra)</option>
                                    <option value="salida">Salida (Venta)</option>
                                    <option value="ajuste">Ajuste</option>
                                </select>
                            </div>

                            <!-- ID Inventario -->
                            <div class="col-md-3">
                                <label for="id_inventario" class="form-label">Lote</label>
                                <input type="number" id="id_inventario" name="id_inventario" class="form-control" placeholder="ID del lote" required min="1">
                            </div>

                            <!-- Cantidad -->
                            <div class="col-md-2">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" id="cantidad" name="cantidad" class="form-control" placeholder="0" required min="1">
                            </div>

                            <!-- Botón Enviar -->
                            <div class="col-md-4">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa fa-plus"></i> Registrar Movimiento
                                </button>
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label for="motivo" class="form-label">Motivo / Observaciones</label>
                                <textarea id="motivo" name="motivo" class="form-control" rows="2" placeholder="Descripción del movimiento..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Historial de Movimientos -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Historial de Movimientos</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($historial)): ?>
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Lote</th>
                                    <th>Cantidad</th>
                                    <th>Motivo</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $mov): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = $mov['tipo'] === 'entrada' ? 'success' : ($mov['tipo'] === 'salida' ? 'danger' : 'warning');
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($mov['tipo']); ?>
                                            </span>
                                        </td>
                                        <td>#<?php echo $mov['id_inventario']; ?></td>
                                        <td>
                                            <strong><?php echo $mov['cantidad']; ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo $mov['motivo'] ?? '—'; ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo $mov['usuario'] ?? 'Sistema'; ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No hay movimientos registrados aún.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enviar formulario por AJAX
document.getElementById('formMovimiento').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/representante/movimientos-stock?accion=registrar', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            alert(data.mensaje);
            location.reload(); // Recargar para ver el nuevo movimiento
        } else {
            alert('Error: ' + data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al registrar el movimiento');
    });
});
</script>
