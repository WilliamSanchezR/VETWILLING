<?php require_once 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="bi bi-question-circle"></i> Centro de Soporte</h2>
            <p class="text-muted">¿Necesitas ayuda? Estamos aquí para ti</p>
        </div>
    </div>

    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje_exito'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>

    <div class="row mt-4">
        <!-- Formulario de contacto -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Enviar solicitud de soporte</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>soporte/enviarTicket" method="POST">
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="">Selecciona una categoría</option>
                                <option value="tecnico">Problema técnico</option>
                                <option value="cuenta">Mi cuenta</option>
                                <option value="pedido">Pedidos</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="asunto" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="asunto" name="asunto" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mensaje" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Enviar solicitud
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Preguntas frecuentes</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-chevron-right"></i> ¿Cómo realizar un pedido?
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Métodos de pago
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Tiempos de entrega
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Contacto directo</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-envelope"></i> soporte@tutienda.com</p>
                    <p><i class="bi bi-telephone"></i> +57 300 123 4567</p>
                    <p><i class="bi bi-clock"></i> Lun - Vie: 9:00 - 18:00</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>