<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes de Suscripción</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/suscripcion.css">

</head>

<body>
    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php';

    // <!-- PANEL DERECHO -->
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php';
        ?>
        <div class="area-contenido">

            <!-- Header -->
            <div class="planes-header">
                <div class="planes-eyebrow">Suscripción</div>
                <h1 class="planes-titulo">Elige tu plan<br>ideal</h1>
                <p class="planes-subtitulo">Sin contratos, sin sorpresas. Cancela cuando quieras. Comienza gratis hoy mismo.</p>

                <!-- Billing toggle -->
                <div class="billing-toggle">
                    <span class="billing-label active" onclick="toggleBilling('mensual', this)">Mensual</span>
                    <span class="billing-label" onclick="toggleBilling('anual', this)">
                        Anual <span class="billing-badge">−20%</span>
                    </span>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="planes-grid">

                <!-- Plan Essential -->
                <div class="plan-card">
                    <div class="plan-icono">🌱</div>
                    <div class="plan-nombre">Essential</div>
                    <div class="plan-descripcion">
                        Ideal para pequeñas y medianas clínicas que están comenzando su crecimiento.
                    </div>

                    <div class="plan-precio-wrap">
                        <div class="plan-precio">
                            <span class="precio-moneda">$</span>
                            <span class="precio-numero" id="precio-essential">7.9</span>
                            <span class="precio-periodo">/mes</span>
                        </div>
                        <div class="precio-anual" id="ahorro-essential"></div>
                    </div>

                    <ul class="plan-features">
                        <li><span class="feature-icon check">✓</span> Cuentas limitadas</li>
                        <li><span class="feature-icon check">✓</span> Registros limitados</li>
                        <li><span class="feature-icon check">✓</span> Funciones básicas</li>
                        <li><span class="feature-icon check">✓</span> Gestión básica de citas</li>
                        <li><span class="feature-icon check">✓</span> Historial clínico básico</li>
                        <li class="disabled"><span class="feature-icon cross">✕</span> Soporte 24/7</li>
                        <li class="disabled"><span class="feature-icon cross">✕</span> API Access</li>
                    </ul>

                    <button class="plan-btn outline">Solicitar prueba</button>
                </div>


                <!-- Plan ProCare (Popular) -->
                <div class="plan-card popular">
                    <div class="plan-badge">⭐ Popular</div>
                    <div class="plan-icono">🚀</div>
                    <div class="plan-nombre">ProCare</div>
                    <div class="plan-descripcion">
                        Diseñado para clínicas medianas que necesitan mayor control y funcionalidades avanzadas.
                    </div>

                    <div class="plan-precio-wrap">
                        <div class="plan-precio">
                            <span class="precio-moneda">$</span>
                            <span class="precio-numero" id="precio-procare">14.9</span>
                            <span class="precio-periodo">/mes</span>
                        </div>
                        <div class="precio-anual" id="ahorro-procare"></div>
                    </div>

                    <ul class="plan-features">
                        <li><span class="feature-icon check">✓</span> Cuentas limitadas</li>
                        <li><span class="feature-icon check">✓</span> Registros ilimitados</li>
                        <li><span class="feature-icon check">✓</span> Funciones avanzadas</li>
                        <li><span class="feature-icon check">✓</span> Gestión completa de citas</li>
                        <li><span class="feature-icon check">✓</span> Reportes avanzados</li>
                        <li><span class="feature-icon check">✓</span> Panel administrativo mejorado</li>
                        <li class="disabled"><span class="feature-icon cross">✕</span> API Access</li>
                    </ul>

                    <button class="plan-btn primary">Solicitar prueba</button>
                </div>


                <!-- Plan MasterVet -->
                <div class="plan-card">
                    <div class="plan-icono">🏢</div>
                    <div class="plan-nombre">MasterVet</div>
                    <div class="plan-descripcion">
                        Solución completa para clínicas grandes con alto volumen de pacientes.
                    </div>

                    <div class="plan-precio-wrap">
                        <div class="plan-precio">
                            <span class="precio-moneda">$</span>
                            <span class="precio-numero" id="precio-mastervet">40.9</span>
                            <span class="precio-periodo">/mes</span>
                        </div>
                        <div class="precio-anual" id="ahorro-mastervet"></div>
                    </div>

                    <ul class="plan-features">
                        <li><span class="feature-icon check">✓</span> Cuentas ilimitadas</li>
                        <li><span class="feature-icon check">✓</span> Registros ilimitados</li>
                        <li><span class="feature-icon check">✓</span> Soporte 24/7</li>
                        <li><span class="feature-icon check">✓</span> Gestión multi-sucursal</li>
                        <li><span class="feature-icon check">✓</span> Reportes personalizados</li>
                        <li><span class="feature-icon check">✓</span> Acceso completo al sistema</li>
                        <li><span class="feature-icon check">✓</span> API Access completo</li>
                    </ul>

                    <button class="plan-btn outline">Solicitar prueba</button>
                </div>

            </div>


            <!-- Garantía -->
            <div class="garantia">
                <div class="garantia-icon">🛡️</div>
                <div class="garantia-texto">
                    <h4>Garantía de devolución 30 días</h4>
                    <p>Si no estás satisfecho, te reembolsamos sin preguntas.</p>
                </div>
            </div>

            <!-- FAQ -->
            <div class="faq-section">
                <h2 class="faq-titulo">Preguntas frecuentes</h2>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-pregunta">¿Puedo cambiar de plan en cualquier momento? <span>↓</span></div>
                        <div class="faq-respuesta">Sí, puedes actualizar o bajar tu plan cuando quieras. Los cambios se aplican de inmediato.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-pregunta">¿Cómo funciona el período de prueba? <span>↓</span></div>
                        <div class="faq-respuesta">El plan Básico es gratuito para siempre. Los planes de pago tienen 14 días de prueba gratis.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-pregunta">¿Qué métodos de pago aceptan? <span>↓</span></div>
                        <div class="faq-respuesta">Aceptamos tarjetas de crédito/débito, PayPal y transferencias bancarias para Enterprise.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-pregunta">¿Puedo cancelar en cualquier momento? <span>↓</span></div>
                        <div class="faq-respuesta">Sí, sin penalizaciones. Tu cuenta seguirá activa hasta el final del período pagado.</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/suscripcion.js"></script>
</body>

</html>