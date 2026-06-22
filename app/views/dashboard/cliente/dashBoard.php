<?php
require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/models/CitasCliente.php';

$usuario = $_SESSION['user'] ?? [];
$id_propietario = $_SESSION['user']['id_propietario'] ?? null;

if (!$id_propietario && isset($_SESSION['user']['id_usuario'])) {
    $modeloCitas = new CitasCliente();
    $id_propietario = $modeloCitas->obtenerIdPropietarioPorUsuario((int)$_SESSION['user']['id_usuario']);
    if ($id_propietario) {
        $_SESSION['user']['id_propietario'] = $id_propietario;
    }
}

$proximasCitas = [];

if ($id_propietario) {
    if (!isset($modeloCitas)) {
        $modeloCitas = new CitasCliente();
    }

    $filtros = ['fecha_inicio' => date('Y-m-d')];
    $citas = $modeloCitas->listarCitasPropietario($id_propietario, $filtros);

    $proximasCitas = array_filter($citas, function ($cita) {
        return !in_array($cita['estado'], ['Cancelada', 'Realizada']) &&
               strtotime($cita['fecha_hora']) >= strtotime(date('Y-m-d') . ' 00:00:00');
    });

    usort($proximasCitas, function ($a, $b) {
        return strtotime($a['fecha_hora']) - strtotime($b['fecha_hora']);
    });

    $proximasCitas = array_slice($proximasCitas, 0, 3);
}

function formatCitaFechaDia($fechaHora)
{
    $dt = new DateTime($fechaHora);
    return $dt->format('d');
}

function formatCitaFechaMes($fechaHora)
{
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $dt = new DateTime($fechaHora);
    return $meses[(int)$dt->format('n') - 1];
}

function formatCitaHora($fechaHora)
{
    $dt = new DateTime($fechaHora);
    return $dt->format('h:i A');
}

function cleanText($texto)
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">



    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">

            <!-- DASHBOARD CONTENT -->
            <div class="container-dashboard">

                <!-- BIENVENIDA -->
                <div class="bienvenida-card">
                    <h2>¡Bienvenido, <?= $usuario['nombres'] ?>! <i class="fas fa-paw" style="color: #ffffff; font-size: 0.9em;"></i></h2>
                    <p>Nos alegra verte nuevamente. En VetWilling cuidamos de tus mascotas con amor, profesionalismo y dedicación.</p>
                    <p class="frase">Tu familia está en buenas patas.</p>
                </div>

                <!-- ALERTA -->
                <div class="alert-box">
                    <div class="alert-icon"><i class="fas fa-syringe"></i></div>
                    <div class="alert-content">
                        <h3>Recordatorio Importante</h3>
                        <p>Max tiene vacuna antirrábica pendiente para el 25 de noviembre</p>
                    </div>
                </div>

                <!-- CITAS Y RECORDATORIOS -->
                <div class="content-grid">

                    <!-- PRÓXIMAS CITAS -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-calendar2-week"></i> Próximas Citas</h2>
                            <a href="#" class="card-action">Ver todas</a>
                        </div>

                        <?php if (!empty($proximasCitas)): ?>
                            <?php foreach ($proximasCitas as $cita): ?>
                                <div class="cita-item<?= strtolower($cita['tipo'] ?? '') === 'urgente' ? ' urgente' : '' ?>">
                                    <div class="cita-fecha">
                                        <div class="cita-dia"><?= formatCitaFechaDia($cita['fecha_hora']) ?></div>
                                        <div class="cita-mes"><?= formatCitaFechaMes($cita['fecha_hora']) ?></div>
                                    </div>
                                    <div class="cita-info">
                                        <div class="cita-mascota"><?= cleanText($cita['mascota_nombre'] . ' - ' . ($cita['tipo'] ?: 'Cita')) ?></div>
                                        <div class="cita-detalles"><?= cleanText($cita['veterinario_nombre'] . ' - ' . ($cita['subservicio_nombre'] ?: $cita['servicio_nombre'] ?: 'Sin servicio')) ?></div>
                                    </div>
                                    <div class="cita-hora"><?= formatCitaHora($cita['fecha_hora']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="cita-item">
                                <div class="cita-info">
                                    <div class="cita-mascota">No hay citas próximas</div>
                                    <div class="cita-detalles">Agrega una nueva cita para empezar</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- RECORDATORIOS -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-bell-fill"></i> Recordatorios</h2>
                        </div>

                        <div class="recordatorio-item">
                            <div class="recordatorio-icon"><span><i class="fas fa-syringe"></i></span></div>
                            <div class="recordatorio-texto">
                                <h4>Vacuna Antirrábica - Max</h4>
                                <p>Programada para el 25 de noviembre</p>
                            </div>
                        </div>

                        <div class="recordatorio-item">
                            <div class="recordatorio-icon"><span><i class="bi bi-capsule"></i></span></div>
                            <div class="recordatorio-texto">
                                <h4>Desparasitación - Luna</h4>
                                <p>Próxima dosis en 5 días</p>
                            </div>
                        </div>

                        <div class="recordatorio-item">
                            <div class="recordatorio-icon"><span><i class="bi bi-heart-pulse-fill"></i></span></div>
                            <div class="recordatorio-texto">
                                <h4>Control anual - Rocky</h4>
                                <p>Programar chequeo general para diciembre</p>
                            </div>
                        </div>

                        <div class="recordatorio-item">
                            <div class="recordatorio-icon"><span><i class="bi bi-droplet-fill"></i></span></div>
                            <div class="recordatorio-texto">
                                <h4>Baño programado - Luna</h4>
                                <p>28 de noviembre a las 11:00 AM</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <!-- JavaScript -->
    <script>
        // Toggle Sidebar
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');

            // Guardar estado
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Restaurar estado del sidebar
        window.addEventListener('load', function() {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
            }
        });

        // Menú móvil
        if (window.innerWidth <= 768) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            // Cerrar sidebar al hacer click fuera
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.mascota-card, .card, .cita-item, .recordatorio-item');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            elements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'all 0.5s ease';
                observer.observe(el);
            });

            // Efecto ripple en botones
            document.querySelectorAll('.btn-mascota').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.6);
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                        animation: ripple 0.6s ease-out;
                    `;

                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);

                    setTimeout(() => ripple.remove(), 600);
                });
            });

            console.log('✅ Dashboard VetWilling cargado correctamente');
        });

        // Keyframes para ripple
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>