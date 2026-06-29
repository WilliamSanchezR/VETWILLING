<?php

// Cargar preferencias e i18n
if (!isset($prefs)) {
    if (!class_exists('PreferenciasManager')) { require_once BASE_PATH . '/app/services/PreferenciasManager.php'; }
    $_pm = new PreferenciasManager((int)$_SESSION['user']['id_usuario']);
    $prefs = $_pm->obtener();
}
if (!isset($t)) {
    if (!class_exists('I18n')) { require_once BASE_PATH . '/app/lang/i18n.php'; }
    $t = I18n::cargar($prefs['idioma']);
}require_once BASE_PATH . '/app/controllers/mascotasController.php';
require_once BASE_PATH . '/app/controllers/perfilControllers.php';
require_once BASE_PATH . '/app/services/SessionManager.php';

$rol      = $_SESSION['user']['id_rol'];
$id       = $_SESSION['user']['id_usuario'];
$usuario  = mostrarPerfil($id);
$mascotas = listarMascotas();

// ── Gestor de sesiones ────────────────────────────────────────────────────
$sm = new SessionManager($id);
$sm->registrar();                    // Registra / actualiza la sesión actual
$sesiones    = $sm->listar();        // Lista para mostrar en la vista
$sesionNueva = $sm->esNueva();       // ¿Primer login desde este dispositivo?

// ── Estadísticas dinámicas del propietario ────────────────────────────────
$stats = ($rol == 3) ? obtenerEstadisticasPerfil($id) : ['total_citas' => 0, 'vacunas_aplicadas' => 0, 'anio_registro' => null];
$anioRegistro = !empty($stats['anio_registro']) ? $stats['anio_registro'] : date('Y');

// ── Ruta de imagen de perfil ──────────────────────────────────────────────
$fotoUsuario    = $usuario['img_perfil'] ?? '';
$carpetaUsuario = 'usuarios';
$nombreCompleto = trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''));

require_once BASE_PATH . '/app/helpers/avatar_helper.php';
$fallbackAvatar = generarAvatarSVG($usuario['nombres'] ?? '', $usuario['apellidos'] ?? '');
$rutaImagen     = $fallbackAvatar;

if (!empty($fotoUsuario) && $fotoUsuario !== 'default-avatar.png') {
  $rutaAbs = BASE_PATH . "/public/uploads/{$carpetaUsuario}/{$fotoUsuario}";
  if (file_exists($rutaAbs)) {
    $rutaImagen = BASE_URL . "/public/uploads/{$carpetaUsuario}/{$fotoUsuario}";
  }
} else {
  $defLocal = BASE_PATH . "/public/uploads/{$carpetaUsuario}/default-avatar.png";
  if (file_exists($defLocal)) {
    $rutaImagen = BASE_URL . "/public/uploads/{$carpetaUsuario}/default-avatar.png";
  }
}

// ── Acción AJAX: cerrar sesión específica ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  header('Content-Type: application/json');
  switch ($_POST['accion']) {
    case 'cerrar_sesion':
      $ok = $sm->cerrar($_POST['token'] ?? '');
      echo json_encode(['ok' => $ok]);
      exit;
    case 'cerrar_todas':
      $n = $sm->cerrarTodas();
      echo json_encode(['ok' => true, 'cerradas' => $n]);
      exit;
  }
}
?>
<!DOCTYPE html>
<html lang="<?= $prefs['idioma'] === 'pt' ? 'pt-BR' : $prefs['idioma'] ?>">

<head>
  <meta charset="UTF-8">
    <script>(function(){var p=<?= json_encode($prefs) ?>;document.documentElement.setAttribute('data-tema',p.tema);if(p.tema==='oscuro'&&document.body)document.body.classList.add('dark-theme');}());</script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil – VetCare</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/theme.css?v=<?= APP_VERSION ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css?v=<?= APP_VERSION ?>">
</head>

<body class="<?= $prefs['tema'] === 'oscuro' ? 'dark-theme' : '' ?>" data-tema="<?= $prefs['tema'] ?>">

  <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

  <main class="contenido-principal" id="contenidoPrincipal">

    <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

    <div class="area-contenido">
      <div class="container-dashboard">

        <!-- ══════════════════════════════════════
             HERO HEADER
        ══════════════════════════════════════ -->
        <div class="header-perfil">
          <div class="avatar-grande">
            <form class="contenedor-foto avatar-ring" id="form_cambio_imagen"
              action="<?= BASE_URL ?>/cliente/cambiar-foto"
              method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id_usuario" value="<?= $id ?>">
              <input type="hidden" name="accion" value="cambiar-foto">
              <img
                src="<?= $rutaImagen ?>"
                class="fotito"
                alt="<?= htmlspecialchars($nombreCompleto) ?>"
                onerror="this.onerror=null;this.src='<?= $fallbackAvatar ?>'">
              <div class="avatar-icon" id="btn-camera" title="Cambiar foto">
                <i class="bi bi-camera-fill"></i>
              </div>
              <input type="file" id="upload-logo" accept="image/*" name="img_perfil">
            </form>
          </div>

          <div class="info-perfil-header">
            <h1><?= htmlspecialchars($usuario['nombres']) ?> <?= htmlspecialchars($usuario['apellidos']) ?></h1>
            <p>
              <i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($usuario['email']) ?><br>
              <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($usuario['telefono']) ?>
            </p>
            <div class="badges-perfil">
              <span class="badge-item"><i class="bi bi-star-fill"></i> Cliente VIP</span>
              <span class="badge-item"><i class="bi bi-calendar-check"></i> Miembro desde <?= $anioRegistro ?></span>
              <span class="badge-item"><i class="bi bi-heart-fill"></i> <?= count($mascotas) ?> <?= count($mascotas) === 1 ? 'Mascota' : 'Mascotas' ?></span>
            </div>
          </div>
        </div>


        <!-- ══════════════════════════════════════
             STATS BAR
        ══════════════════════════════════════ -->
        <div class="stats-bar">
          <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-heart-fill"></i></div>
            <div class="stat-card-data">
              <div class="stat-card-number"><?= count($mascotas) ?></div>
              <div class="stat-card-label">Mascotas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-calendar2-check-fill"></i></div>
            <div class="stat-card-data">
              <div class="stat-card-number"><?= $stats['total_citas'] ?></div>
              <div class="stat-card-label">Citas Totales</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-shield-fill-check"></i></div>
            <div class="stat-card-data">
              <div class="stat-card-number"><?= $stats['vacunas_aplicadas'] ?></div>
              <div class="stat-card-label">Vacunas Aplicadas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-calendar3"></i></div>
            <div class="stat-card-data">
              <div class="stat-card-number"><?= $anioRegistro ?></div>
              <div class="stat-card-label">Año de Registro</div>
            </div>
          </div>
        </div>


        <!-- ══════════════════════════════════════
             TABS
        ══════════════════════════════════════ -->
        <div class="tabs-perfil">
          <button class="tab-perfil active" data-tab="personal">
            <i class="bi bi-person-fill"></i><span>Información Personal</span>
          </button>
          <button class="tab-perfil" data-tab="mascotas">
            <i class="bi bi-heart-pulse"></i><span>Mis Mascotas</span>
          </button>
          <button class="tab-perfil" data-tab="historial">
            <i class="bi bi-clock-history"></i><span>Historial</span>
          </button>
        </div>


        <!-- ══════════════════════════════════════
             TAB: PERSONAL
        ══════════════════════════════════════ -->
        <div class="perfil-grid tab-content" id="tab-personal">

          <!-- ── Datos Personales ── -->
          <div class="card-perfil">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-person-circle"></i>
                Datos Personales
              </h2>
              <button type="button" class="btn-editar" onclick="abrirModalEditar()">
                <i class="bi bi-pencil-fill"></i> Editar
              </button>
            </div>

            <div class="info-grid" id="vistaPersonal">
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-person-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Nombres</div>
                  <div class="info-valor" id="txt-nombres"><?= htmlspecialchars($usuario['nombres']) ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-person-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Apellidos</div>
                  <div class="info-valor" id="txt-apellidos"><?= htmlspecialchars($usuario['apellidos']) ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-card-text"></i></div>
                <div class="info-content">
                  <div class="info-label">Tipo Doc.</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['tipo_documento']) ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-hash"></i></div>
                <div class="info-content">
                  <div class="info-label">Número Doc.</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['numero_documento']) ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-envelope-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Correo</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['email']) ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-telephone-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Teléfono</div>
                  <div class="info-valor" id="txt-telefono">+57 <?= htmlspecialchars($usuario['telefono']) ?></div>
                </div>
              </div>
              <div class="info-item full-col">
                <div class="info-item-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Dirección</div>
                  <div class="info-valor" id="txt-direccion"><?= htmlspecialchars($usuario['direccion']) ?></div>
                </div>
              </div>
            </div>
          </div>


          <!-- ── Seguridad ── -->
          <div class="card-perfil">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-shield-lock-fill"></i>
                Seguridad
              </h2>
            </div>

            <!-- Alerta dispositivo nuevo -->
            <?php if ($sesionNueva): ?>
              <div class="alerta-nuevo-dispositivo">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Inicio de sesión desde un nuevo dispositivo detectado.</span>
              </div>
            <?php endif; ?>

            <!-- Cambiar contraseña -->
            <div class="security-section">
              <div class="security-section-title">
                <i class="bi bi-key-fill"></i> Cambiar Contraseña
              </div>

              <form method="POST"
                action="<?= BASE_URL ?>/cliente/actualizar-contrasena"
                id="passwordForm"
                novalidate>

                <input type="hidden" name="id_usuario"
                  value="<?= htmlspecialchars($_SESSION['user']['id_usuario']) ?>">
                <input type="hidden" name="accion" value="modificar-contrasena">

                <!-- Contraseña actual -->
                <div class="form-group">
                  <label for="currentPassword">Contraseña actual</label>
                  <div class="input-icon-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password"
                      id="currentPassword"
                      name="contrasena-actual"
                      placeholder="••••••••"
                      required>
                    <button type="button"
                      class="toggle-pass"
                      data-target="currentPassword"
                      aria-label="Mostrar/ocultar contraseña">
                      <i class="bi bi-eye-fill"></i>
                    </button>
                  </div>
                </div>

                <!-- Nueva contraseña -->
                <div class="form-group">
                  <label for="pass-nueva">Nueva contraseña</label>
                  <div class="input-icon-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password"
                      id="pass-nueva"
                      name="nueva-contrasena"
                      placeholder="••••••••"
                      required
                      minlength="8">
                    <button type="button"
                      class="toggle-pass"
                      data-target="pass-nueva"
                      aria-label="Mostrar/ocultar contraseña">
                      <i class="bi bi-eye-fill"></i>
                    </button>
                  </div>

                  <!-- Barra de fortaleza (misma estructura del security-section) -->
                  <div class="strength-bar mt-1">
                    <div class="strength-segment" id="seg-1"></div>
                    <div class="strength-segment" id="seg-2"></div>
                    <div class="strength-segment" id="seg-3"></div>
                    <div class="strength-segment" id="seg-4"></div>
                  </div>
                  <div class="strength-label" id="strength-label">Ingresa una contraseña</div>

                  <!-- Confirmar contraseña -->
                  <div class="form-group">
                    <label for="pass-confirmar">Confirmar contraseña</label>
                    <div class="input-icon-wrap">
                      <i class="bi bi-lock-fill"></i>
                      <input type="password"
                        id="pass-confirmar"
                        name="confi-contrasena"
                        placeholder="••••••••"
                        required
                        minlength="8">
                      <button type="button"
                        class="toggle-pass"
                        data-target="pass-confirmar"
                        aria-label="Mostrar/ocultar contraseña">
                        <i class="bi bi-eye-fill"></i>
                      </button>
                    </div>
                    <!-- Indicador de coincidencia reutiliza strength-label -->
                    <div class="strength-label" id="match-label" aria-live="polite"></div>
                  </div>

                  <button type="submit"
                    class="btn-guardar btn-full"
                    id="btn-submit-pass"
                    style="margin-top:4px;">
                    <i class="bi bi-check-circle-fill me-2"></i>Actualizar Contraseña
                  </button>

              </form>
            </div>

            <!-- Sesiones activas -->
            <div class="security-section mb-0">
              <div class="security-section-title">
                <i class="bi bi-shield-lock"></i> Sesiones Activas
              </div>

              <div class="sesiones-lista" id="sesiones-lista">
                <?php foreach ($sesiones as $s): ?>
                  <?php
                  $esCurrent = $s['is_current'] ?? false;
                  $esNuevaSes = $s['es_nueva'] ?? false;
                  $diff = time() - ($s['last_seen'] ?? time());

                  if ($diff < 60)           $tiempo = 'Ahora';
                  elseif ($diff < 3600)     $tiempo = 'Hace ' . floor($diff / 60) . ' min';
                  elseif ($diff < 86400)    $tiempo = 'Hace ' . floor($diff / 3600) . ' h';
                  elseif ($diff < 604800)   $tiempo = 'Hace ' . floor($diff / 86400) . ' días';
                  else                      $tiempo = 'Hace ' . floor($diff / 604800) . ' sem';

                  $inactiva = $diff > 86400 * 3;
                  ?>
                  <div class="session-item <?= $esCurrent ? 'current' : '' ?>"
                    data-token="<?= htmlspecialchars($s['token'] ?? '') ?>">

                    <div class="session-device-icon <?= $esCurrent ? 'active' : '' ?>">
                      <i class="bi <?= htmlspecialchars($s['tipo']['icono'] ?? 'bi-display') ?>"></i>
                    </div>

                    <div class="session-info">
                      <div class="session-name">
                        <?= htmlspecialchars(($s['navegador']['nombre'] ?? 'Navegador') . ' · ' . ($s['marca']['nombre'] ?? 'Dispositivo')) ?>
                        <?php if ($esCurrent): ?>
                          <span class="session-pill session-pill-current">Sesión actual</span>
                        <?php elseif ($esNuevaSes): ?>
                          <span class="session-pill session-pill-new">Nuevo</span>
                        <?php elseif ($inactiva): ?>
                          <span class="session-pill session-pill-warn">Inactiva</span>
                        <?php endif; ?>
                      </div>
                      <div class="session-meta">
                        <span><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($s['ciudad'] ?? 'Desconocida') ?></span>
                        <span><i class="bi bi-clock"></i> <?= $tiempo ?></span>
                        <span class="session-os-badge">
                          <i class="bi <?= htmlspecialchars($s['so']['icono'] ?? 'bi-display') ?>"></i>
                          <?= htmlspecialchars($s['so']['nombre'] ?? 'SO') ?>
                        </span>
                      </div>
                    </div>

                    <?php if (!$esCurrent): ?>
                      <button class="btn-cerrar-sesion"
                        data-token="<?= htmlspecialchars($s['token'] ?? '') ?>">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-sm-inline">Cerrar</span>
                      </button>
                    <?php endif; ?>

                  </div>
                <?php endforeach; ?>
              </div>

              <?php if (count($sesiones) > 1): ?>
                <button class="btn-cerrar-todas" id="btn-cerrar-todas">
                  <i class="bi bi-shield-x"></i> Cerrar todas las otras sesiones
                </button>
              <?php endif; ?>

            </div>
          </div>

        </div><!-- /tab-personal -->


        <!-- ══════════════════════════════════════
             TAB: MASCOTAS
        ══════════════════════════════════════ -->
        <div class="perfil-grid tab-content d-none" id="tab-mascotas">
          <div class="card-perfil full">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-heart-fill"></i>
                Mis Mascotas (<?= count($mascotas) ?>)
              </h2>
              <a href="<?= BASE_URL ?>/cliente/registrar-mascota" class="btn-editar">
                <i class="bi bi-plus-circle-fill"></i> Agregar
              </a>
            </div>

            <div class="mascotas-lista">
              <?php foreach ($mascotas as $m): ?>
                <div class="mascota-mini-item">
                  <div class="mascota-mini-avatar">
                    <img
                      src="<?= BASE_URL ?>/public/uploads/mascotas/<?= htmlspecialchars($m['img_mascota'] ?? '') ?>"
                      alt="<?= htmlspecialchars($m['nombre']) ?>"
                      onerror="this.onerror=null;this.src='<?= BASE_URL ?>/public/assets/webSite/img/perrito.png'">
                  </div>
                  <div class="mascota-mini-info">
                    <div class="mascota-mini-nombre"><?= htmlspecialchars($m['nombre']) ?></div>
                    <div class="mascota-mini-raza">
                      <?= htmlspecialchars($m['raza']) ?> &bull;
                      <?= $m['edad_numero'] ?> <?= $m['edad_unidad'] ?>
                    </div>
                  </div>
                  <a href="<?= BASE_URL ?>/cliente/mascotas" class="btn-editar">
                    <i class="bi bi-eye-fill"></i> <span class="d-none d-sm-inline">Ver</span>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>


        <!-- ══════════════════════════════════════
             TAB: HISTORIAL
        ══════════════════════════════════════ -->
        <div class="perfil-grid tab-content d-none" id="tab-historial">
          <div class="card-perfil full">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-clock-history"></i>
                Historial Reciente
              </h2>
              <button class="btn-editar">
                <i class="bi bi-list-ul"></i> <span class="d-none d-sm-inline">Ver Todo</span>
              </button>
            </div>

            <div class="historial-lista">
              <?php
              $historial = [
                ['dia' => '15', 'mes' => 'Nov', 'titulo' => 'Consulta General – Max',   'doctor' => 'Dr. Juan Martínez',     'lugar' => 'Consultorio 2',    'precio' => '$45.000'],
                ['dia' => '10', 'mes' => 'Nov', 'titulo' => 'Vacunación – Luna',         'doctor' => 'Dra. Ana García',       'lugar' => 'Consultorio 1',    'precio' => '$35.000'],
                ['dia' => '08', 'mes' => 'Nov', 'titulo' => 'Control Postoperatorio – Rocky', 'doctor' => 'Dr. Carlos Rodríguez', 'lugar' => 'Consultorio 3', 'precio' => '$30.000'],
                ['dia' => '05', 'mes' => 'Nov', 'titulo' => 'Baño y Peluquería – Luna',  'doctor' => 'María López',           'lugar' => 'Sala de Estética', 'precio' => '$40.000'],
              ];
              foreach ($historial as $h): ?>
                <div class="historial-item">
                  <div class="historial-fecha">
                    <div class="historial-dia"><?= $h['dia'] ?></div>
                    <div class="historial-mes"><?= $h['mes'] ?></div>
                  </div>
                  <div class="historial-info">
                    <div class="historial-titulo"><?= htmlspecialchars($h['titulo']) ?></div>
                    <div class="historial-detalle">
                      <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($h['doctor']) ?></span>
                      <span><i class="bi bi-door-closed-fill"></i> <?= htmlspecialchars($h['lugar']) ?></span>
                    </div>
                  </div>
                  <div class="historial-precio"><?= $h['precio'] ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

      </div><!-- /container-dashboard -->
    </div><!-- /area-contenido -->

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js?v=<?= APP_VERSION ?>"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/perfil.js?v=<?= APP_VERSION ?>"></script>

  <!-- URL base para el JS -->
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';

    (function() {
      'use strict';

      /* ── helpers ─────────────────────────────────────────── */
      const $ = id => document.getElementById(id);

      /* ── toggle show/hide password ───────────────────────── */
      // Reutiliza el mismo listener de perfil.js (data-target)
      // pero también cubre el botón de contraseña actual que tiene
      // data-target="currentPassword" en lugar de los ids genéricos.
      document.querySelectorAll('#passwordForm .toggle-pass').forEach(btn => {
        btn.addEventListener('click', function() {
          const input = document.getElementById(this.dataset.target);
          if (!input) return;
          const show = input.type === 'password';
          input.type = show ? 'text' : 'password';
          this.querySelector('i').className = show ?
            'bi bi-eye-slash-fill' :
            'bi bi-eye-fill';
        });
      });

      /* ── strength meter ──────────────────────────────────── */
      const segs = [1, 2, 3, 4].map(n => $(`seg-${n}`));
      const lbl = $('strength-label');
      const nueva = $('pass-nueva');

      const reqItems = {
        length: {
          el: $('req-length'),
          ico: $('ico-length'),
          test: v => v.length >= 8
        },
        uppercase: {
          el: $('req-uppercase'),
          ico: $('ico-uppercase'),
          test: v => /[A-Z]/.test(v)
        },
        number: {
          el: $('req-number'),
          ico: $('ico-number'),
          test: v => /[0-9]/.test(v)
        },
      };

      function actualizarRequisito(key, val) {
        const ok = reqItems[key].test(val);
        const ico = reqItems[key].ico;
        if (!ico) return;
        ico.className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
        // color inline para no agregar clases nuevas
        ico.style.color = ok ? '#0FA832' : '';
      }

      nueva?.addEventListener('input', function() {
        const val = this.value;
        let score = 0;

        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const cls = ['', 'active-weak', 'active-medium', 'active-medium', 'active-strong'];
        const labels = [
          'Ingresa una contraseña',
          'Muy débil', 'Débil', 'Aceptable', '¡Contraseña segura!',
        ];

        segs.forEach((s, i) => {
          s.className = 'strength-segment';
          if (i < score) s.classList.add(cls[score]);
        });

        if (lbl) lbl.textContent = val.length === 0 ? labels[0] : labels[score];

        Object.keys(reqItems).forEach(k => actualizarRequisito(k, val));
        verificarCoincidencia();
      });

      /* ── match indicator ─────────────────────────────────── */
      const confirmar = $('pass-confirmar');
      const matchLbl = $('match-label');

      function verificarCoincidencia() {
        if (!confirmar || !matchLbl || !confirmar.value) {
          if (matchLbl) matchLbl.textContent = '';
          return;
        }
        const ok = nueva?.value === confirmar.value;
        matchLbl.textContent = ok ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden';
        matchLbl.style.color = ok ? '#0FA832' : '#A32D2D';
      }

      confirmar?.addEventListener('input', verificarCoincidencia);

      /* ── validación antes de enviar ──────────────────────── */
      $('passwordForm')?.addEventListener('submit', function(e) {
        const actual = $('currentPassword')?.value.trim();
        const nueVal = nueva?.value ?? '';
        const confVal = confirmar?.value ?? '';

        if (!actual) {
          e.preventDefault();
          $('currentPassword')?.focus();
          return;
        }

        if (nueVal.length < 8 || !/[A-Z]/.test(nueVal) || !/[0-9]/.test(nueVal)) {
          e.preventDefault();
          nueva?.focus();
          return;
        }

        if (nueVal !== confVal) {
          e.preventDefault();
          confirmar?.focus();
        }
      });

    })();
  </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/theme.js?v=<?= APP_VERSION ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/i18n.js?v=<?= APP_VERSION ?>"></script>

<!-- ══ MODAL EDITAR DATOS PERSONALES ══ -->
<div id="modalEditar" style="display:none;position:fixed;inset:0;z-index:1055;
     background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;padding:1rem">
  <div style="background:var(--color-bg-card,#fff);border-radius:1rem;
              width:100%;max-width:520px;box-shadow:0 8px 32px rgba(0,0,0,.2);overflow:hidden">

    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--color-border,#e9ecef);
                display:flex;align-items:center;justify-content:space-between">
      <h5 style="margin:0;font-weight:600"><i class="bi bi-pencil-fill me-2"></i>Editar datos personales</h5>
      <button type="button" onclick="cerrarModalEditar()"
              style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:inherit;line-height:1">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="formEditarDatos" style="padding:1.5rem" onsubmit="guardarDatos(event)">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">

        <div>
          <label style="font-size:.85rem;font-weight:500;display:block;margin-bottom:.4rem">
            Nombres <span style="color:#dc3545">*</span>
          </label>
          <input type="text" id="edit-nombres" name="nombres" required
                 value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>"
                 style="width:100%;padding:.5rem .75rem;border:1px solid var(--color-border,#ced4da);
                        border-radius:.5rem;font-size:.95rem;background:var(--color-input-bg,#fff);
                        color:inherit;box-sizing:border-box">
        </div>

        <div>
          <label style="font-size:.85rem;font-weight:500;display:block;margin-bottom:.4rem">
            Apellidos <span style="color:#dc3545">*</span>
          </label>
          <input type="text" id="edit-apellidos" name="apellidos" required
                 value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>"
                 style="width:100%;padding:.5rem .75rem;border:1px solid var(--color-border,#ced4da);
                        border-radius:.5rem;font-size:.95rem;background:var(--color-input-bg,#fff);
                        color:inherit;box-sizing:border-box">
        </div>

        <div>
          <label style="font-size:.85rem;font-weight:500;display:block;margin-bottom:.4rem">Teléfono</label>
          <input type="tel" id="edit-telefono" name="telefono"
                 value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                 placeholder="Ej: 3001234567"
                 style="width:100%;padding:.5rem .75rem;border:1px solid var(--color-border,#ced4da);
                        border-radius:.5rem;font-size:.95rem;background:var(--color-input-bg,#fff);
                        color:inherit;box-sizing:border-box">
        </div>

        <div style="grid-column:1/-1">
          <label style="font-size:.85rem;font-weight:500;display:block;margin-bottom:.4rem">Dirección</label>
          <input type="text" id="edit-direccion" name="direccion"
                 value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>"
                 style="width:100%;padding:.5rem .75rem;border:1px solid var(--color-border,#ced4da);
                        border-radius:.5rem;font-size:.95rem;background:var(--color-input-bg,#fff);
                        color:inherit;box-sizing:border-box">
        </div>
      </div>

      <div id="edit-error" style="display:none;margin-top:.75rem;padding:.6rem 1rem;
           background:#fff3cd;border:1px solid #ffc107;border-radius:.5rem;font-size:.9rem;color:#664d03"></div>

      <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.25rem">
        <button type="button" onclick="cerrarModalEditar()"
                style="padding:.5rem 1.25rem;border:1px solid var(--color-border,#ced4da);
                       border-radius:.5rem;background:transparent;cursor:pointer;color:inherit">
          Cancelar
        </button>
        <button type="submit" id="btn-guardar-datos"
                style="padding:.5rem 1.5rem;border:none;border-radius:.5rem;
                       background:#4e9af1;color:#fff;font-weight:600;cursor:pointer">
          <i class="bi bi-check-lg me-1"></i>Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  const _API_DATOS = '<?= BASE_URL ?>/cliente/actualizar-datos';

  function abrirModalEditar() {
    document.getElementById('modalEditar').style.display = 'flex';
    document.getElementById('edit-error').style.display  = 'none';
    document.body.style.overflow = 'hidden';
  }

  function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
    document.body.style.overflow = '';
  }

  /* Cerrar al hacer clic fuera */
  document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalEditar();
  });

  async function guardarDatos(e) {
    e.preventDefault();
    const btn     = document.getElementById('btn-guardar-datos');
    const errDiv  = document.getElementById('edit-error');
    const payload = {
      nombres   : document.getElementById('edit-nombres').value.trim(),
      apellidos : document.getElementById('edit-apellidos').value.trim(),
      telefono  : document.getElementById('edit-telefono').value.trim(),
      direccion : document.getElementById('edit-direccion').value.trim(),
    };

    btn.disabled    = true;
    btn.textContent = 'Guardando…';
    errDiv.style.display = 'none';

    try {
      const res  = await fetch(_API_DATOS, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json' },
        body    : JSON.stringify(payload),
      });
      const data = await res.json();

      if (data.status === 'ok') {
        /* Actualizar vista sin recargar */
        document.getElementById('txt-nombres').textContent    = payload.nombres;
        document.getElementById('txt-apellidos').textContent  = payload.apellidos;
        document.getElementById('txt-telefono').textContent   = '+57 ' + payload.telefono;
        document.getElementById('txt-direccion').textContent  = payload.direccion;

        /* Actualizar cabecera del hero */
        const h1 = document.querySelector('.info-perfil-header h1');
        if (h1) h1.textContent = payload.nombres + ' ' + payload.apellidos;

        cerrarModalEditar();

        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon:'success', title:'¡Listo!', text: data.message,
                      timer:2000, showConfirmButton:false });
        }
      } else {
        errDiv.textContent     = data.message || 'No se pudo guardar.';
        errDiv.style.display   = 'block';
      }
    } catch (_) {
      errDiv.textContent   = 'Error de conexión. Intenta de nuevo.';
      errDiv.style.display = 'block';
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Guardar';
    }
  }
</script>
</body>

</html>