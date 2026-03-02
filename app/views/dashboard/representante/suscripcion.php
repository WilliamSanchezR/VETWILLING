<?php
require_once BASE_PATH . '/app/helpers/session_representante.php';


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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/representante.styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/representante/css/suscripcion.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">


</head>

<body>
    <?php
    // <!-- BARRA LATERAL IZQUIERDA -->
    include_once __DIR__ . '/../../layouts/sidebar_representante.php'

    // <!-- PANEL DERECHO -->
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php';
    ?>
    <div class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_representante.php'
        ?>

        <div class="wrap">

            <!--  HEADER  -->
            <div class="header">
                <div class="eyebrow">Suscripción</div>
                <h1>Elige tu plan <em>ideal</em></h1>
                <p class="subtitle">Sin contratos, sin sorpresas. Cancela cuando quieras. Comienza gratis hoy mismo.</p>

                <!-- Toggle Mensual / Anual -->
                <div class="toggle-wrap">
                    <button class="toggle-btn active" onclick="setMode('mensual', this)">Mensual</button>
                    <button class="toggle-btn" onclick="setMode('anual', this)">
                        Anual <span class="badge-save">−20%</span>
                    </button>
                </div>
            </div>


            <!--  GRID DE PLANES  -->
            <div class="grid">

                <!-- Plan Essential -->
                <div class="plan">
                    <div class="plan-name">Essential</div>
                    <p class="plan-desc">Ideal para pequeñas y medianas clínicas que están comenzando su crecimiento.</p>

                    <div class="price-block">
                        <div class="price">
                            <span class="price-symbol">$</span>
                            <span class="price-num" id="p-essential">7.9</span>
                            <span class="price-period">/mes</span>
                        </div>
                        <div class="price-annual-note" id="n-essential"></div>
                    </div>

                    <ul class="features">
                        <li><span class="fi yes">✓</span> Cuentas limitadas</li>
                        <li><span class="fi yes">✓</span> Registros limitados</li>
                        <li><span class="fi yes">✓</span> Funciones básicas</li>
                        <li><span class="fi yes">✓</span> Gestión básica de citas</li>
                        <li><span class="fi yes">✓</span> Historial clínico básico</li>
                        <li class="off"><span class="fi no">✕</span> Soporte 24/7</li>
                        <li class="off"><span class="fi no">✕</span> API Access</li>
                    </ul>

                    <div class="plan-divider"></div>
                    <a class="btn btn-outline" href="<?= BASE_URL ?>/pasarela-pago?origen=suscripcion&plan=basico">
                        Solicitar prueba
                    </a>
                </div>


                <!-- Plan ProCare (POPULAR) -->
                <div class="plan popular">
                    <div class="pop-badge">⭐ Más popular</div>
                    <div class="plan-name">ProCare</div>
                    <p class="plan-desc">Diseñado para clínicas medianas que necesitan mayor control y funcionalidades avanzadas.</p>

                    <div class="price-block">
                        <div class="price">
                            <span class="price-symbol">$</span>
                            <span class="price-num" id="p-procare">14.9</span>
                            <span class="price-period">/mes</span>
                        </div>
                        <div class="price-annual-note" id="n-procare"></div>
                    </div>

                    <ul class="features">
                        <li><span class="fi yes">✓</span> Cuentas limitadas</li>
                        <li><span class="fi yes">✓</span> Registros ilimitados</li>
                        <li><span class="fi yes">✓</span> Funciones avanzadas</li>
                        <li><span class="fi yes">✓</span> Gestión completa de citas</li>
                        <li><span class="fi yes">✓</span> Reportes avanzados</li>
                        <li><span class="fi yes">✓</span> Panel administrativo mejorado</li>
                        <li class="off"><span class="fi no">✕</span> API Access</li>
                    </ul>

                    <div class="plan-divider"></div>
                    <a class="btn btn-outline Prueba" href="<?= BASE_URL ?>/pasarela-pago?origen=suscripcion&plan=procare">
                        Solicitar prueba
                    </a>
                </div>


                <!-- Plan MasterVet -->
                <div class="plan">
                    <div class="plan-name">MasterVet</div>
                    <p class="plan-desc">Solución completa para clínicas grandes con alto volumen de pacientes.</p>

                    <div class="price-block">
                        <div class="price">
                            <span class="price-symbol">$</span>
                            <span class="price-num" id="p-mastervet">40.9</span>
                            <span class="price-period">/mes</span>
                        </div>
                        <div class="price-annual-note" id="n-mastervet"></div>
                    </div>

                    <ul class="features">
                        <li><span class="fi yes">✓</span> Cuentas ilimitadas</li>
                        <li><span class="fi yes">✓</span> Registros ilimitados</li>
                        <li><span class="fi yes">✓</span> Soporte 24/7</li>
                        <li><span class="fi yes">✓</span> Gestión multi-sucursal</li>
                        <li><span class="fi yes">✓</span> Reportes personalizados</li>
                        <li><span class="fi yes">✓</span> Acceso completo al sistema</li>
                        <li><span class="fi yes">✓</span> API Access completo</li>
                    </ul>

                    <div class="plan-divider"></div>
                    <a class="btn btn-outline" href="<?= BASE_URL ?>/pasarela-pago?origen=suscripcion&plan=mastervet">
                        Solicitar prueba
                    </a>
                </div>

            </div>


            <!--  GARANTÍA  -->
            <div class="guarantee">
                <div class="guarantee-icon">🛡️</div>
                <div>
                    <h4>Garantía de devolución 30 días</h4>
                    <p>Si no estás satisfecho, te reembolsamos sin preguntas.</p>
                </div>
            </div>


            <!--  FAQ ═ -->
            <h2 class="faq-title">Preguntas frecuentes</h2>
            <div class="faq-list">

                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        ¿Puedo cambiar de plan en cualquier momento?
                        <span class="faq-arrow">↓</span>
                    </div>
                    <div class="faq-a">Sí, puedes actualizar o bajar tu plan cuando quieras. Los cambios se aplican de inmediato.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        ¿Cómo funciona el período de prueba?
                        <span class="faq-arrow">↓</span>
                    </div>
                    <div class="faq-a">El plan Básico es gratuito para siempre. Los planes de pago tienen 14 días de prueba gratis.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        ¿Qué métodos de pago aceptan?
                        <span class="faq-arrow">↓</span>
                    </div>
                    <div class="faq-a">Aceptamos tarjetas de crédito/débito, PayPal y transferencias bancarias para Enterprise.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-q" onclick="toggleFaq(this)">
                        ¿Puedo cancelar en cualquier momento?
                        <span class="faq-arrow">↓</span>
                    </div>
                    <div class="faq-a">Sí, sin penalizaciones. Tu cuenta seguirá activa hasta el final del período pagado.</div>
                </div>

            </div>

        </div>

    </div>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/representante/js/suscripcion.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/global/js/menu.js"></script>

</body>

</html>