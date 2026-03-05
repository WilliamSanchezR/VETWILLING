<?php
require_once BASE_PATH . '/app/controllers/mascotasController.php';
require_once BASE_PATH . '/app/controllers/perfilControllers.php';

$rol     = $_SESSION['user']['id_rol'];
$id      = $_SESSION['user']['id_usuario'];
$usuario = mostrarPerfil($id);
$mascotas = listarMascotas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil – VetCare</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <!-- Favicon -->
  <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

  <!-- CSS existentes -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">

  <!-- NUEVO CSS DE PERFIL -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/perfil.css">
</head>

<body>

  <!-- SIDEBAR -->
  <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido-principal" id="contenidoPrincipal">

    <!-- NAVBAR SUPERIOR -->
    <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

    <div class="area-contenido">
      <div class="container-dashboard">

        <!-- ═══════════════════════════════════
             HERO HEADER
        ═══════════════════════════════════ -->
        <div class="header-perfil">

          <!-- Avatar con cambio de foto -->
          <div class="avatar-grande">
            <form class="contenedor-foto avatar-ring" id="form_cambio_imagen"
                  action="<?= BASE_URL ?>/cliente/cambiar-foto"
                  method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id_usuario" value="<?= $id ?>">
              <input type="hidden" name="accion"     value="cambiar-foto">
              <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['img_perfil'] ?>"
                   class="fotito" alt="Foto de perfil">
              <div class="avatar-icon" id="btn-camera" title="Cambiar foto">
                <i class="bi bi-camera-fill"></i>
              </div>
              <input type="file" id="upload-logo" accept="image/*" name="img_perfil">
            </form>
          </div>

          <!-- Info header -->
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

        </div><!-- /header-perfil -->


        <!-- ═══════════════════════════════════
             STATS BAR
        ═══════════════════════════════════ -->
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


        <!-- ═══════════════════════════════════
             TABS
        ═══════════════════════════════════ -->
        <div class="tabs-perfil">
          <button class="tab-perfil active" data-tab="personal">
            <i class="bi bi-person-fill"></i>Información Personal
          </button>
          <button class="tab-perfil" data-tab="mascotas">
            <i class="bi bi-heart-pulse"></i>Mis Mascotas
          </button>
          <button class="tab-perfil" data-tab="historial">
            <i class="bi bi-clock-history"></i>Historial
          </button>
        </div>


        <!-- ═══════════════════════════════════
             TAB: PERSONAL
        ═══════════════════════════════════ -->
        <div class="perfil-grid tab-content" id="tab-personal">

          <!-- Datos Personales -->
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
              <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-item-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="info-content">
                  <div class="info-label">Dirección</div>
                  <div class="info-valor"><?= htmlspecialchars($usuario['direccion']) ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Seguridad de la Cuenta -->
          <div class="card-perfil">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-shield-lock-fill"></i>
                Seguridad
              </h2>
            </div>

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

              <button class="btn-guardar" style="width:100%; margin-top:4px;">
                <i class="bi bi-check-circle-fill me-2"></i>Actualizar Contraseña
              </button>
            </div>

            <!-- Sesiones activas -->
            <div class="security-section" style="margin-bottom:0;">
              <div class="security-section-title">
                <i class="bi bi-display"></i> Sesiones Activas
              </div>

              <div class="session-item current">
                <div class="session-device-icon"><i class="bi bi-laptop-fill"></i></div>
                <div class="session-info">
                  <div class="session-name">Chrome – Windows 11</div>
                  <div class="session-meta">
                    <i class="bi bi-geo-alt-fill"></i> Bogotá, Colombia
                    <span class="session-badge-current">Sesión actual</span>
                  </div>
                </div>
              </div>

              <div class="session-item">
                <div class="session-device-icon"><i class="bi bi-phone-fill"></i></div>
                <div class="session-info">
                  <div class="session-name">App móvil – Android</div>
                  <div class="session-meta"><i class="bi bi-clock"></i> Hace 2 días</div>
                </div>
                <button class="btn-cerrar-sesion">
                  <i class="bi bi-box-arrow-right"></i> Cerrar
                </button>
              </div>

            </div>
          </div>

        </div><!-- /tab-personal -->


        <!-- ═══════════════════════════════════
             TAB: MASCOTAS
        ═══════════════════════════════════ -->
        <div class="perfil-grid tab-content d-none" id="tab-mascotas">
          <div class="card-perfil full">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-heart-fill"></i>
                Mis Mascotas (<?= count($mascotas) ?>)
              </h2>
              <a href="<?= BASE_URL ?>/cliente/registrar-mascota" class="btn-editar">
                <i class="bi bi-plus-circle-fill"></i> Agregar Mascota
              </a>
            </div>

            <div class="mascotas-lista">
              <?php foreach ($mascotas as $m) : ?>
                <div class="mascota-mini-item">
                  <div class="mascota-mini-avatar">
                    <img src="<?= BASE_URL ?>/public/uploads/mascotas/<?= $m['img_mascota'] ?>" alt="<?= htmlspecialchars($m['nombre']) ?>">
                  </div>
                  <div class="mascota-mini-info">
                    <div class="mascota-mini-nombre"><?= htmlspecialchars($m['nombre']) ?></div>
                    <div class="mascota-mini-raza"><?= htmlspecialchars($m['raza']) ?> &bull; <?= $m['edad_numero'] ?> <?= $m['edad_unidad'] ?></div>
                  </div>
                  <a href="<?= BASE_URL ?>/cliente/mascotas" class="btn-editar">
                    <i class="bi bi-eye-fill"></i> Ver
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div><!-- /tab-mascotas -->


        <!-- ═══════════════════════════════════
             TAB: HISTORIAL
        ═══════════════════════════════════ -->
        <div class="perfil-grid tab-content d-none" id="tab-historial">
          <div class="card-perfil full">
            <div class="card-header-perfil">
              <h2 class="card-titulo">
                <i class="bi bi-clock-history"></i>
                Historial Reciente
              </h2>
              <button class="btn-editar">
                <i class="bi bi-list-ul"></i> Ver Todo
              </button>
            </div>

            <div class="historial-lista">

              <div class="historial-item">
                <div class="historial-fecha">
                  <div class="historial-dia">15</div>
                  <div class="historial-mes">Nov</div>
                </div>
                <div class="historial-info">
                  <div class="historial-titulo">Consulta General – Max</div>
                  <div class="historial-detalle">
                    <span><i class="bi bi-person-fill"></i> Dr. Juan Martínez</span>
                    <span><i class="bi bi-door-closed-fill"></i> Consultorio 2</span>
                  </div>
                </div>
                <div class="historial-precio">$45.000</div>
              </div>

              <div class="historial-item">
                <div class="historial-fecha">
                  <div class="historial-dia">10</div>
                  <div class="historial-mes">Nov</div>
                </div>
                <div class="historial-info">
                  <div class="historial-titulo">Vacunación – Luna</div>
                  <div class="historial-detalle">
                    <span><i class="bi bi-person-fill"></i> Dra. Ana García</span>
                    <span><i class="bi bi-door-closed-fill"></i> Consultorio 1</span>
                  </div>
                </div>
                <div class="historial-precio">$35.000</div>
              </div>

              <div class="historial-item">
                <div class="historial-fecha">
                  <div class="historial-dia">08</div>
                  <div class="historial-mes">Nov</div>
                </div>
                <div class="historial-info">
                  <div class="historial-titulo">Control Postoperatorio – Rocky</div>
                  <div class="historial-detalle">
                    <span><i class="bi bi-person-fill"></i> Dr. Carlos Rodríguez</span>
                    <span><i class="bi bi-door-closed-fill"></i> Consultorio 3</span>
                  </div>
                </div>
                <div class="historial-precio">$30.000</div>
              </div>

              <div class="historial-item">
                <div class="historial-fecha">
                  <div class="historial-dia">05</div>
                  <div class="historial-mes">Nov</div>
                </div>
                <div class="historial-info">
                  <div class="historial-titulo">Baño y Peluquería – Luna</div>
                  <div class="historial-detalle">
                    <span><i class="bi bi-person-fill"></i> María López</span>
                    <span><i class="bi bi-stars"></i> Sala de Estética</span>
                  </div>
                </div>
                <div class="historial-precio">$40.000</div>
              </div>

            </div>
          </div>
        </div><!-- /tab-historial -->

      </div><!-- /container-dashboard -->
    </div><!-- /area-contenido -->

  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
  <!-- NUEVO JS DE PERFIL -->
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/perfil.js"></script>

</body>
</html>