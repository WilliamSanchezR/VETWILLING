<?php
/**
 * Vista de Catálogo de Productos – VetWilling
 *
 * CORRECCIONES:
 * - sidebar.css movido al inicio (base del layout)
 * - $datosUsuario['nombre_veterinaria'] documentado correctamente
 * - Productos movidos a array preparado para reemplazar con BD
 * - Número de WhatsApp extraído a constante configurable
 * - Botón "Avisar cuando llegue" convertido a botón real con JS
 * - data-nombre normalizado con mb_strtolower para tildes
 */

require_once BASE_PATH . '/app/helpers/session_propietario.php';
// Cargar preferencias e i18n
if (!isset($prefs)) {
    if (!class_exists('PreferenciasManager')) { require_once BASE_PATH . '/app/services/PreferenciasManager.php'; }
    $_pm = new PreferenciasManager((int)$_SESSION['user']['id_usuario']);
    $prefs = $_pm->obtener();
}
if (!isset($t)) {
    if (!class_exists('I18n')) { require_once BASE_PATH . '/app/lang/i18n.php'; }
    $t = I18n::cargar($prefs['idioma']);
}

// $datosUsuario viene de panel_superio_paciente.php (incluido en el body)
$nombreVet = 'VetWilling'; // se sobreescribe después de incluir el panel

// Número de WhatsApp de la clínica — cámbialo aquí o tráelo de la BD/config
// $whatsappNum = defined('WHATSAPP_CLINICA') ? WHATSAPP_CLINICA : '573000000000';

/* ====================================================================
   PRODUCTOS — cargados desde el inventario real de la veterinaria
   ==================================================================== */
require_once BASE_PATH . '/app/models/Inventario.php';

$id_veterinaria = (int)($_SESSION['user']['id_veterinaria'] ?? 0);

/* Mapeo de categorías DB → íconos Bootstrap Icons */
$iconosPorCategoria = [
    'alimentos'    => 'bi-box-seam',
    'medicamentos' => 'bi-capsule',
    'higiene'      => 'bi-droplet-half',
    'accesorios'   => 'bi-tag',
    'juguetes'     => 'bi-controller',
    'camas'        => 'bi-house-heart',
    'suplementos'  => 'bi-capsule',
    'antiparasitarios' => 'bi-shield-check',
];

function mapearProductoInventario(array $row, array $iconos): array {
    $cat      = strtolower($row['categoria'] ?? 'otros');
    $cantidad = (int)($row['cantidad']    ?? 0);
    $stockMin = (int)($row['stock_minimo'] ?? 0);

    if ($cantidad <= 0) {
        $stockLabel = 'Agotado temporalmente';
    } elseif ((int)($row['alerta_stock'] ?? 0)) {
        $stockLabel = 'Pocas unidades';
    } else {
        $stockLabel = 'Disponible en clínica';
    }

    $imagenUrl = null;
    if (!empty($row['imagen'])) {
        $imagenUrl = BASE_URL . '/public/uploads/productos/' . $row['imagen'];
    }

    return [
        'id'          => (int)$row['id_inventario'],
        'nombre'      => $row['nombre']      ?? '',
        'descripcion' => $row['descripcion'] ?? '',
        'categoria'   => $cat,
        'cat_label'   => ucfirst($cat),
        'precio'      => (float)($row['precio'] ?? 0),
        'precio_ant'  => null,
        'para'        => '',
        'peso'        => !empty($row['numero_lote']) ? 'Lote: ' . $row['numero_lote'] : '',
        'disponible'  => $cantidad > 0,
        'stock_label' => $stockLabel,
        'icono'       => $iconos[$cat] ?? 'bi-bag',
        'etiqueta'    => (int)($row['alerta_stock'] ?? 0) ? 'oferta' : null,
        'et_texto'    => (int)($row['alerta_stock'] ?? 0) ? 'Stock bajo' : null,
        'imagen_url'  => $imagenUrl,
    ];
}

$productosRaw = [];
if ($id_veterinaria > 0) {
    $_inventario = new Inventario();
    $productosRaw = $_inventario->listarInventario($id_veterinaria);
}

/* Mapear y filtrar solo productos con precio > 0 */
$productos = array_values(array_filter(
    array_map(fn($r) => mapearProductoInventario($r, $iconosPorCategoria), $productosRaw),
    fn($p) => $p['precio'] > 0
));

/* Fallback si el inventario está vacío */
if (empty($productos)) {
    $productos = [
    [
        'id'          => 1,
        'nombre'      => 'Alimento Premium Adultos',
        'descripcion' => 'Fórmula balanceada para perros adultos de razas medianas y grandes. Alta proteína, sin conservantes artificiales.',
        'categoria'   => 'alimentos',
        'cat_label'   => 'Alimentos',
        'precio'      => 89990,
        'precio_ant'  => 112490,
        'para'        => 'Perros adultos',
        'peso'        => '15 kg',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-box-seam',
        'etiqueta'    => 'oferta',
        'et_texto'    => '−20%',
    ],
    [
        'id'          => 2,
        'nombre'      => 'Alimento Premium Gatos',
        'descripcion' => 'Nutrición completa para gatos adultos. Enriquecido con taurina y omega-3 para salud ocular y cardíaca.',
        'categoria'   => 'alimentos',
        'cat_label'   => 'Alimentos',
        'precio'      => 75990,
        'precio_ant'  => null,
        'para'        => 'Gatos adultos',
        'peso'        => '10 kg',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-box-seam',
        'etiqueta'    => 'nuevo',
        'et_texto'    => 'Nuevo',
    ],
    [
        'id'          => 3,
        'nombre'      => 'Antiparasitario Completo',
        'descripcion' => 'Tratamiento de amplio espectro contra parásitos internos y externos. Aplicación trimestral.',
        'categoria'   => 'medicamentos',
        'cat_label'   => 'Medicamentos',
        'precio'      => 32990,
        'precio_ant'  => null,
        'para'        => 'Perros y gatos',
        'peso'        => '1 dosis',
        'disponible'  => true,
        'stock_label' => 'Pocas unidades',
        'icono'       => 'bi-capsule',
        'etiqueta'    => null,
        'et_texto'    => null,
    ],
    [
        'id'          => 4,
        'nombre'      => 'Shampoo Antipulgas',
        'descripcion' => 'Fórmula dermatológica con extracto de neem. Protege y da brillo al pelaje. Uso mensual.',
        'categoria'   => 'higiene',
        'cat_label'   => 'Higiene',
        'precio'      => 24990,
        'precio_ant'  => 29490,
        'para'        => 'Perros y gatos',
        'peso'        => '500 ml',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-droplet-half',
        'etiqueta'    => 'oferta',
        'et_texto'    => '−15%',
    ],
    [
        'id'          => 5,
        'nombre'      => 'Collar Ajustable Premium',
        'descripcion' => 'Collar de nylon reforzado con hebilla de seguridad de liberación rápida. Tallas S, M y L.',
        'categoria'   => 'accesorios',
        'cat_label'   => 'Accesorios',
        'precio'      => 15990,
        'precio_ant'  => null,
        'para'        => 'Perros',
        'peso'        => 'Talla M',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-tag',
        'etiqueta'    => null,
        'et_texto'    => null,
    ],
    [
        'id'          => 6,
        'nombre'      => 'Set de Pelotas Resistentes',
        'descripcion' => 'Pack de 3 pelotas de caucho natural de distintos tamaños. Resistentes a mordidas fuertes.',
        'categoria'   => 'juguetes',
        'cat_label'   => 'Juguetes',
        'precio'      => 14990,
        'precio_ant'  => 19990,
        'para'        => 'Perros',
        'peso'        => 'Pack x3',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-controller',
        'etiqueta'    => 'oferta',
        'et_texto'    => '−25%',
    ],
    [
        'id'          => 7,
        'nombre'      => 'Cama Ortopédica Grande',
        'descripcion' => 'Base de espuma viscoelástica con funda lavable antiácaros. Ideal para razas grandes con problemas articulares.',
        'categoria'   => 'camas',
        'cat_label'   => 'Camas',
        'precio'      => 129990,
        'precio_ant'  => null,
        'para'        => 'Perros grandes',
        'peso'        => '90×60 cm',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-house-heart',
        'etiqueta'    => 'nuevo',
        'et_texto'    => 'Nuevo',
    ],
    [
        'id'          => 8,
        'nombre'      => 'Vitaminas y Suplementos',
        'descripcion' => 'Complejo vitamínico con calcio, fósforo y biotina. Fortalece huesos, pelo y sistema inmune.',
        'categoria'   => 'medicamentos',
        'cat_label'   => 'Medicamentos',
        'precio'      => 28990,
        'precio_ant'  => null,
        'para'        => 'Perros y gatos',
        'peso'        => '60 tabletas',
        'disponible'  => false,
        'stock_label' => 'Agotado temporalmente',
        'icono'       => 'bi-capsule',
        'etiqueta'    => null,
        'et_texto'    => null,
    ],
    [
        'id'          => 9,
        'nombre'      => 'Correa Retráctil 5m',
        'descripcion' => 'Correa retráctil con freno de seguridad y mango ergonómico antideslizante. Para perros hasta 25 kg.',
        'categoria'   => 'accesorios',
        'cat_label'   => 'Accesorios',
        'precio'      => 42990,
        'precio_ant'  => null,
        'para'        => 'Perros medianos',
        'peso'        => '5 metros',
        'disponible'  => true,
        'stock_label' => 'Disponible en clínica',
        'icono'       => 'bi-tag',
        'etiqueta'    => null,
        'et_texto'    => null,
    ],
]; /* fin del array de fallback */
} /* fin del if (empty($productos)) */

$totalProductos = count($productos);
?>
<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">
<head>
    <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo – <?= $nombreVet ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css?v=<?= APP_VERSION ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css?v=<?= APP_VERSION ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/tienda.css?v=<?= APP_VERSION ?>">
</head>
<body class="<?= $prefs['tema'] === 'oscuro' ? 'dark-theme' : '' ?>" data-tema="<?= $prefs['tema'] ?>">

    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <main class="contenido-principal" id="contenidoPrincipal">

        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">
            <div class="catalogo-wrap">

                <!-- ── HERO ── -->
                <div class="catalogo-hero">
                    <div class="hero-texto">
                        <span class="hero-eyebrow">
                            <i class="bi bi-bag-heart-fill" aria-hidden="true"></i>
                            Catálogo
                            <!-- CORRECCIÓN: $nombreVet definido arriba desde $datosUsuario -->
                            <strong><?= $nombreVet ?></strong>
                        </span>
                        <h1>Productos para el cuidado<br>de tu mascota</h1>
                        <p>Consulta disponibilidad y precios. Para adquirir cualquier producto,
                           solicítalo en tu próxima cita o comunícate con nosotros.</p>
                        <a href="<?= BASE_URL ?>/cliente/citas" class="hero-cta">
                            <i class="bi bi-calendar-plus" aria-hidden="true"></i>
                            Agendar cita
                        </a>
                    </div>
                    <div class="hero-deco" aria-hidden="true">
                        <div class="deco-circle c1"></div>
                        <div class="deco-circle c2"></div>
                        <i class="bi bi-bag-heart deco-icon"></i>
                    </div>
                </div>

                <!-- ── BARRA DE FILTROS ── -->
                <div class="catalogo-filtros">

                    <!-- Búsqueda -->
                    <div class="filtro-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search"
                               id="inputBuscar"
                               placeholder="Buscar producto, marca o categoría…"
                               autocomplete="off"
                               aria-label="Buscar productos">
                    </div>

                    <!-- Categorías (scroll horizontal en móvil) -->
                    <div class="filtro-cats" id="filtroCats" role="group" aria-label="Filtrar por categoría">
                        <button class="cat-btn active" data-cat="todos" type="button">
                            <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>
                            <span>Todos</span>
                        </button>
                        <button class="cat-btn" data-cat="alimentos" type="button">
                            <i class="bi bi-egg-fried" aria-hidden="true"></i>
                            <span>Alimentos</span>
                        </button>
                        <button class="cat-btn" data-cat="medicamentos" type="button">
                            <i class="bi bi-capsule" aria-hidden="true"></i>
                            <span>Medicamentos</span>
                        </button>
                        <button class="cat-btn" data-cat="higiene" type="button">
                            <i class="bi bi-droplet-half" aria-hidden="true"></i>
                            <span>Higiene</span>
                        </button>
                        <button class="cat-btn" data-cat="accesorios" type="button">
                            <i class="bi bi-tag" aria-hidden="true"></i>
                            <span>Accesorios</span>
                        </button>
                        <button class="cat-btn" data-cat="juguetes" type="button">
                            <i class="bi bi-controller" aria-hidden="true"></i>
                            <span>Juguetes</span>
                        </button>
                        <button class="cat-btn" data-cat="camas" type="button">
                            <i class="bi bi-house-heart" aria-hidden="true"></i>
                            <span>Camas</span>
                        </button>
                    </div>

                    <!-- Ordenar -->
                    <div class="filtro-orden">
                        <i class="bi bi-sort-down" aria-hidden="true"></i>
                        <select id="selectOrden" aria-label="Ordenar productos">
                            <option value="default">Ordenar por…</option>
                            <option value="precio-asc">Menor precio</option>
                            <option value="precio-desc">Mayor precio</option>
                            <option value="nombre">Nombre A–Z</option>
                        </select>
                    </div>

                </div>

                <!-- Contador + nota informativa -->
                <div class="catalogo-meta">
                    <span id="contadorResultados" aria-live="polite">
                        <?= $totalProductos ?> producto<?= $totalProductos !== 1 ? 's' : '' ?>
                    </span>
                    <span class="meta-sep" aria-hidden="true">·</span>
                    <span class="meta-nota">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Los productos se adquieren únicamente en clínica o por solicitud en cita
                    </span>
                </div>

                <!-- ── GRID DE PRODUCTOS ── -->
                <div class="catalogo-grid" id="catalogoGrid">

                    <?php foreach ($productos as $i => $p):
                        /*
                         * CORRECCIÓN: mb_strtolower() para normalizar tildes en la búsqueda.
                         * "Alimento" y "alimento" o "Álimento" buscan igual.
                         * Se usa mb_strtolower con UTF-8 en lugar de strtolower que no maneja tildes.
                         */
                        $nombreBusqueda = mb_strtolower($p['nombre'] . ' ' . $p['cat_label'] . ' ' . $p['para'], 'UTF-8');
                    ?>
                    <article class="prod-card"
                             data-nombre="<?= htmlspecialchars($nombreBusqueda, ENT_QUOTES, 'UTF-8') ?>"
                             data-cat="<?= htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8') ?>"
                             data-precio="<?= (int)$p['precio'] ?>"
                             data-disponible="<?= $p['disponible'] ? '1' : '0' ?>"
                             style="animation-delay: <?= $i * 0.04 ?>s">

                        <!-- Etiqueta (oferta / nuevo) -->
                        <?php if ($p['etiqueta']): ?>
                        <span class="prod-etiqueta prod-etiqueta--<?= htmlspecialchars($p['etiqueta'], ENT_QUOTES, 'UTF-8') ?>"
                              aria-label="<?= htmlspecialchars($p['et_texto'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($p['et_texto'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php endif; ?>

                        <!-- Thumbnail / ícono -->
                        <div class="prod-thumb <?= !$p['disponible'] ? 'prod-thumb--agotado' : '' ?>"
                             aria-hidden="true">
                            <i class="bi <?= htmlspecialchars($p['icono'], ENT_QUOTES, 'UTF-8') ?>"></i>
                            <?php if (!$p['disponible']): ?>
                            <div class="prod-agotado-overlay">
                                <span>Agotado</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Cuerpo de la tarjeta -->
                        <div class="prod-body">

                            <div class="prod-meta">
                                <span class="prod-cat">
                                    <?= htmlspecialchars($p['cat_label'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="prod-para">
                                    <i class="bi bi-heart-pulse" aria-hidden="true"></i>
                                    <?= htmlspecialchars($p['para'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <h3 class="prod-nombre">
                                <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <p class="prod-desc">
                                <?= htmlspecialchars($p['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <div class="prod-detalle">
                                <span>
                                    <i class="bi bi-box" aria-hidden="true"></i>
                                    <?= htmlspecialchars($p['peso'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <!-- Precio -->
                            <div class="prod-precio-wrap">
                                <span class="prod-precio">
                                    $<?= number_format($p['precio'], 0, ',', '.') ?>
                                </span>
                                <?php if ($p['precio_ant']): ?>
                                <span class="prod-precio-ant">
                                    $<?= number_format($p['precio_ant'], 0, ',', '.') ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Footer: stock + acción -->
                            <div class="prod-footer">
                                <span class="prod-stock <?= !$p['disponible'] ? 'prod-stock--agotado' : '' ?>">
                                    <i class="bi bi-<?= $p['disponible'] ? 'check-circle-fill' : 'x-circle-fill' ?>"
                                       aria-hidden="true"></i>
                                    <?= htmlspecialchars($p['stock_label'], ENT_QUOTES, 'UTF-8') ?>
                                </span>

                                <?php if ($p['disponible']): ?>
                                <!-- Botón consultar: abre el modal -->
                                <button class="prod-btn-consultar"
                                        type="button"
                                        data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-id="<?= (int)$p['id'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalConsultar"
                                        aria-label="Consultar <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-chat-dots" aria-hidden="true"></i>
                                    Consultar
                                </button>

                                <?php else: ?>
                                <!--
                                    CORRECCIÓN: "Avisar cuando llegue" ahora es un <button>
                                    real con data-id para que el JS pueda manejarlo.
                                    Antes era un <span> sin ninguna funcionalidad.
                                -->
                                <button class="prod-btn-avisar"
                                        type="button"
                                        data-id="<?= (int)$p['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                        aria-label="Avisarme cuando llegue <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="bi bi-bell" aria-hidden="true"></i>
                                    Avisar cuando llegue
                                </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </article>
                    <?php endforeach; ?>

                </div><!-- /catalogo-grid -->

                <!-- Estado vacío (oculto por defecto, JS lo muestra) -->
                <div class="catalogo-vacio d-none" id="catalogoVacio" aria-live="polite">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <h3>Sin resultados</h3>
                    <p>No encontramos productos con ese criterio.<br>
                       Intenta con otra categoría o término de búsqueda.</p>
                    <button type="button" class="hero-cta" onclick="resetFiltros()" style="margin-top:16px;">
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        Ver todos los productos
                    </button>
                </div>

            </div><!-- /catalogo-wrap -->
        </div><!-- /area-contenido -->

    </main>

    <!-- ── MODAL CONSULTAR PRODUCTO ── -->
    <div class="modal fade" id="modalConsultar" tabindex="-1"
         aria-labelledby="modalConsultarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 modal-consultar-content">

                <div class="modal-header border-0 modal-consultar-header">
                    <h5 class="modal-title" id="modalConsultarTitulo">
                        <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
                        Consultar producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body modal-consultar-body">
                    <p class="modal-consultar-intro">Estás consultando disponibilidad de:</p>

                    <div class="modal-consultar-nombre-wrap">
                        <strong id="modalNombreProducto"></strong>
                    </div>

                    <p class="modal-consultar-intro">Puedes solicitar este producto de dos formas:</p>

                    <div class="modal-consultar-opciones">
                        <!-- Opción 1: Cita -->
                        <a href="<?= BASE_URL ?>/cliente/citas"
                           class="modal-opcion">
                            <i class="bi bi-calendar-plus modal-opcion-icono modal-opcion-icono--green"
                               aria-hidden="true"></i>
                            <div>
                                <div class="modal-opcion-titulo">Solicitar en cita</div>
                                <div class="modal-opcion-desc">
                                    Agrega este producto a tu próxima cita veterinaria
                                </div>
                            </div>
                        </a>

                        <!-- Opción 2: WhatsApp
                             CORRECCIÓN: número extraído a $whatsappNum definido arriba.
                             El texto del mensaje incluye el nombre del producto dinámicamente. -->
                        <!-- <a href="https://wa.me/<?= htmlspecialchars($whatsappNum, ENT_QUOTES, 'UTF-8') ?>?text=Hola,%20quiero%20consultar%20disponibilidad%20del%20producto:%20" -->
                           id="modalWhatsapp"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="modal-opcion">
                            <i class="bi bi-whatsapp modal-opcion-icono modal-opcion-icono--wa"
                               aria-hidden="true"></i>
                            <div>
                                <div class="modal-opcion-titulo">Preguntar por WhatsApp</div>
                                <div class="modal-opcion-desc">Chatea directo con la clínica</div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/tienda.js?v=<?= APP_VERSION ?>"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>