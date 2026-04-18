<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';
require_once BASE_PATH . '/app/controllers/perfilControllers.php';
require_once BASE_PATH . '/app/services/SessionManager.php';

$rol      = $_SESSION['user']['id_rol'];
$id       = $_SESSION['user']['id_usuario'];
$usuario  = mostrarPerfil($id);
$mascotas = listarMascotas();

// ── Gestor de sesiones ────────────────────────────────────────────────────
$sm = new SessionManager($id);
$sm->registrar();                    // Registra / actualiza la sesión actual
$sesiones  = $sm->listar();          // Lista para mostrar en la vista
$sesionNueva = $sm->esNueva();       // ¿Primer login desde este dispositivo?

// ── Ruta de imagen de perfil ──────────────────────────────────────────────
$fotoUsuario    = $usuario['img_perfil'] ?? '';
$carpetaUsuario = 'usuarios';
$nombreCompleto = trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''));
$fallbackAvatar = "https://ui-avatars.com/api/?name=" . urlencode($nombreCompleto) . "&background=4e9af1&color=fff&size=128";
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
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil – VetCare</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css">
</head>

<body>

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
              <span class="badge-item"><i class="bi bi-calendar-check"></i> Miembro desde 2023</span>
              <span class="badge-item"><i class="bi bi-heart-fill"></i> <?= count($mascotas) ?> Mascotas</span>
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
              <div class="stat-card-number">12</div>
              <div class="stat-card-label">Citas Totales</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-shield-fill-check"></i></div>
            <div class="stat-card-data">
              <div class="stat-card-number">8</div>
              <div class="stat-card-label">Vacunas Aplicadas</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-card-icon"><i class="bi bi-star-fill"></i></div>
            <div class="stat-card-data">
              <div class="stat-card-number">4.9</div>
              <div class="stat-card-label">Calificación</div>
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
              <a href="<?= BASE_URL ?>/cliente/editar-perfil" class="btn-editar">
                <i class="bi bi-pencil-fill"></i> Editar
              </a>
            </div>

            <div class="info-grid">
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-person-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Nombres</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['nombres']) ?></div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-item-icon"><i class="bi bi-person-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Apellidos</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['apellidos']) ?></div>
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
                  <div class="info-valor">+57 <?= htmlspecialchars($usuario['telefono']) ?></div>
                </div>
              </div>
              <div class="info-item full-col">
                <div class="info-item-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Dirección</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['direccion']) ?></div>
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

              <div class="form-group">
                <label>Contraseña actual</label>
                <div class="input-icon-wrap">
                  <i class="bi bi-lock-fill"></i>
                  <input type="password" id="pass-actual" placeholder="••••••••">
                  <button type="button" class="toggle-pass" data-target="pass-actual">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label>Nueva contraseña</label>
                <div class="input-icon-wrap">
                  <i class="bi bi-lock-fill"></i>
                  <input type="password" id="pass-nueva" placeholder="••••••••">
                  <button type="button" class="toggle-pass" data-target="pass-nueva">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                </div>
                <div class="strength-bar mt-1">
                  <div class="strength-segment" id="seg-1"></div>
                  <div class="strength-segment" id="seg-2"></div>
                  <div class="strength-segment" id="seg-3"></div>
                  <div class="strength-segment" id="seg-4"></div>
                </div>
                <div class="strength-label" id="strength-label">Ingresa una contraseña</div>
              </div>

              <div class="form-group">
                <label>Confirmar contraseña</label>
                <div class="input-icon-wrap">
                  <i class="bi bi-lock-fill"></i>
                  <input type="password" id="pass-confirmar" placeholder="••••••••">
                  <button type="button" class="toggle-pass" data-target="pass-confirmar">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                </div>
              </div>

              <button class="btn-guardar btn-full">
                <i class="bi bi-check-circle-fill me-2"></i>Actualizar Contraseña
              </button>
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
                    elseif ($diff < 3600)     $tiempo = 'Hace ' . floor($diff/60) . ' min';
                    elseif ($diff < 86400)    $tiempo = 'Hace ' . floor($diff/3600) . ' h';
                    elseif ($diff < 604800)   $tiempo = 'Hace ' . floor($diff/86400) . ' días';
                    else                      $tiempo = 'Hace ' . floor($diff/604800) . ' sem';

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
                      src="<?= BASE_URL ?>/public/uploads/mascotas/<?= htmlspecialchars($m['img_mascota']) ?>"
                      alt="<?= htmlspecialchars($m['nombre']) ?>"
                      onerror="this.onerror=null;this.src='<?= BASE_URL ?>/public/assets/webSite/img/default-pet.png'">
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
                ['dia'=>'15','mes'=>'Nov','titulo'=>'Consulta General – Max',   'doctor'=>'Dr. Juan Martínez',     'lugar'=>'Consultorio 2',    'precio'=>'$45.000'],
                ['dia'=>'10','mes'=>'Nov','titulo'=>'Vacunación – Luna',         'doctor'=>'Dra. Ana García',       'lugar'=>'Consultorio 1',    'precio'=>'$35.000'],
                ['dia'=>'08','mes'=>'Nov','titulo'=>'Control Postoperatorio – Rocky','doctor'=>'Dr. Carlos Rodríguez','lugar'=>'Consultorio 3', 'precio'=>'$30.000'],
                ['dia'=>'05','mes'=>'Nov','titulo'=>'Baño y Peluquería – Luna',  'doctor'=>'María López',           'lugar'=>'Sala de Estética', 'precio'=>'$40.000'],
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
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/perfil.js"></script>

  <!-- URL base para el JS -->
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</body>
</html>