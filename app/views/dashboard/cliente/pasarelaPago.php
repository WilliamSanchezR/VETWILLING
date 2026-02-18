<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pasarela de pago segura para servicios veterinarios VetWilling">
    <meta name="robots" content="noindex, nofollow">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        crossorigin="anonymous">

    <!-- Iconos -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png">

    <title>Pasarela de Pago Segura | VetWilling</title>

    <!-- Estilos propios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/pasarelaPago.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="container">

            <div class="payment-card">

                <!-- =========================
                     FORMULARIO DE PAGO
                ========================== -->
                <section class="payment-card-right" aria-label="Formulario de pago">

                    <form
                        id="payment-form"
                        method="POST"
                        action="<?= BASE_URL ?>/api/procesar-pago"
                        novalidate>

                        <!-- Seguridad -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="order_id" value="<?= $orderId ?? '' ?>">

                        <!-- DATOS PERSONALES -->
                        <fieldset class="form-section">
                            <legend class="h5">
                                <i class="bi bi-person-fill"></i>
                                Datos personales
                            </legend>

                            <div class="form-group">
                                <label for="full-name">
                                    Nombre completo
                                    <abbr title="Campo obligatorio" class="text-danger">*</abbr>
                                </label>
                                <input
                                    type="text"
                                    id="full-name"
                                    name="full_name"
                                    class="form-control"
                                    placeholder="Ej: Juan Pérez García"
                                    required
                                    minlength="3"
                                    maxlength="100"
                                    autocomplete="name"
                                    aria-describedby="full-name-error">
                                <span id="full-name-error" class="error-message" role="alert"></span>
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    Correo electrónico
                                    <abbr title="Campo obligatorio" class="text-danger">*</abbr>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="correo@ejemplo.com"
                                    required
                                    autocomplete="email"
                                    aria-describedby="email-error">
                                <span id="email-error" class="error-message" role="alert"></span>
                            </div>
                        </fieldset>

                        <!-- DATOS DE TARJETA -->
                        <fieldset class="form-section">
                            <legend class="h5">
                                <i class="bi bi-credit-card-fill"></i>
                                Datos de la tarjeta
                            </legend>

                            <div class="form-group position-relative">
                                <label for="card-number">
                                    Número de tarjeta
                                    <abbr title="Campo obligatorio" class="text-danger">*</abbr>
                                </label>
                                <input
                                    type="tel"
                                    id="card-number"
                                    name="card_number"
                                    class="form-control"
                                    placeholder="1234 5678 9012 3456"
                                    maxlength="19"
                                    required
                                    inputmode="numeric"
                                    autocomplete="cc-number"
                                    aria-describedby="card-number-error card-type">
                                <span id="card-type" class="card-type" aria-live="polite"></span>
                                <span id="card-number-error" class="error-message" role="alert"></span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="expiry-date">
                                            Fecha de expiración
                                            <abbr title="Campo obligatorio" class="text-danger">*</abbr>
                                        </label>
                                        <input
                                            type="tel"
                                            id="expiry-date"
                                            name="expiry_date"
                                            class="form-control"
                                            placeholder="MM/AA"
                                            maxlength="5"
                                            required
                                            inputmode="numeric"
                                            autocomplete="cc-exp"
                                            aria-describedby="expiry-date-error">
                                        <span id="expiry-date-error" class="error-message" role="alert"></span>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="cvv">
                                            CVV
                                            <abbr title="Campo obligatorio" class="text-danger">*</abbr>
                                            <button
                                                type="button"
                                                class="btn btn-link btn-sm p-0 ms-1"
                                                data-bs-toggle="tooltip"
                                                title="3 dígitos en el reverso de la tarjeta">
                                                <i class="bi bi-question-circle"></i>
                                            </button>
                                        </label>
                                        <input
                                            type="tel"
                                            id="cvv"
                                            name="cvv"
                                            class="form-control"
                                            placeholder="123"
                                            maxlength="4"
                                            required
                                            inputmode="numeric"
                                            autocomplete="cc-csc"
                                            aria-describedby="cvv-error">
                                        <span id="cvv-error" class="error-message" role="alert"></span>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- BOTÓN DE PAGO -->
                        <button
                            type="submit"
                            id="btn-pay"
                            class="btn btn-pay w-100"
                            aria-busy="false">

                            <span class="btn-text">
                                <i class="bi bi-credit-card-2-front"></i>
                                Pagar <span id="pay-amount">$117.81</span>
                            </span>

                            <span class="btn-loader d-none" role="status">
                                <span class="spinner-border spinner-border-sm"></span>
                                Procesando pago...
                            </span>
                        </button>

                        <!-- TÉRMINOS -->
                        <p class="terms-note text-center small mt-3">
                            Al pagar aceptas nuestros
                            <a href="<?= BASE_URL ?>/terminos" target="_blank" rel="noopener noreferrer">Términos</a>
                            y
                            <a href="<?= BASE_URL ?>/privacidad" target="_blank" rel="noopener noreferrer">Privacidad</a>
                        </p>

                        <!-- RESULTADO -->
                        <div
                            id="payment-result"
                            class="payment-result d-none"
                            role="alert"
                            aria-live="assertive">
                        </div>

                    </form>
                </section>

                <!-- =========================
                     RESUMEN DEL PEDIDO
                ========================== -->
                <aside class="payment-card-left" aria-label="Resumen del pedido">

                    <header class="header">
                        <h1 class="h4">Pasarela de pago</h1>
                        <p>Revisa los detalles antes de continuar</p>
                        <span class="secure-badge">
                            <i class="bi bi-shield-lock-fill"></i>
                            Pago seguro
                        </span>
                    </header>

                    <section class="order-summary">
                        <h2 class="h5">Resumen del pedido</h2>

                        <div class="summary-item">
                            <span>Producto ejemplo</span>
                            <span>$99.00</span>
                        </div>

                        <div class="summary-item">
                            <span>IVA (19%)</span>
                            <span>$18.81</span>
                        </div>

               

                        <div class="summary-total">
                            <strong>Total</strong>
                            <strong id="total-amount">$117.81</strong>
                        </div>
                    </section>

                    <section class="security-info">
                        <h3 class="h6">
                            <i class="bi bi-shield-check"></i>
                            Tu pago está protegido
                        </h3>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success"></i> Encriptación SSL</li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> Certificación PCI-DSS</li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> Datos protegidos</li>
                        </ul>
                    </section>

                </aside>

            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/pasarelaPago.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document
                .querySelectorAll('[data-bs-toggle="tooltip"]')
                .forEach(el => new bootstrap.Tooltip(el));
        });
    </script>

</body>
</html>
