<?php
require_once BASE_PATH . '/app/helpers/session_propietario.php';
require_once BASE_PATH . '/app/models/CitasCliente.php';

$usuario = $_SESSION['user'] ?? [];
$id_propietario = $_SESSION['user']['id_propietario'] ?? null;

$modeloCitas = new CitasCliente();

if (!$id_propietario && isset($_SESSION['user']['id_usuario'])) {
    $id_propietario = $modeloCitas->obtenerIdPropietarioPorUsuario((int)$_SESSION['user']['id_usuario']);
    if ($id_propietario) {
        $_SESSION['user']['id_propietario'] = $id_propietario;
    }
}

$proximasCitas = [];

if ($id_propietario) {
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

if (!function_exists('formatCitaFechaDia')) {
    function formatCitaFechaDia($fechaHora)
    {
        $dt = new DateTime($fechaHora);
        return $dt->format('d');
    }
}

if (!function_exists('formatCitaFechaMes')) {
    function formatCitaFechaMes($fechaHora)
    {
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $dt = new DateTime($fechaHora);
        return $meses[(int)$dt->format('n') - 1];
    }
}

if (!function_exists('formatCitaHora')) {
    function formatCitaHora($fechaHora)
    {
        $dt = new DateTime($fechaHora);
        return $dt->format('h:i A');
    }
}

if (!function_exists('cleanText')) {
    function cleanText($texto)
    {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatReminderDate')) {
    function formatReminderDate($fecha)
    {
        if (empty($fecha)) {
            return 'Fecha no disponible';
        }

        try {
            $dt = new DateTime($fecha);
            return $dt->format('d/m/Y');
        } catch (Exception $e) {
            return 'Fecha no válida';
        }
    }
}

if (!function_exists('obtenerRecordatorios')) {
    function obtenerRecordatorios($id_propietario, CitasCliente $modeloCitas)
    {
        $recordatorios = [];
        $mascotas = $modeloCitas->obtenerMascotasPropietario($id_propietario);
        $hoy = new DateTime('today');

        foreach ($mascotas as $mascota) {
            $id_paciente = (int)$mascota['id_paciente'];
            $vacunas = $modeloCitas->obtenerVacunasPorPaciente($id_propietario, $id_paciente);

            foreach ($vacunas as $vacuna) {
                try {
                    $fechaAplicacion = new DateTime($vacuna['fecha_aplicacion']);
                } catch (Exception $e) {
                    continue;
                }

                if ($fechaAplicacion >= $hoy) {
                    $recordatorios[] = [
                        'tipo' => 'vacuna',
                        'mascota' => $mascota['nombre'] ?? 'Mascota',
                        'titulo' => $vacuna['tipo_vacuna'] ?: 'Vacuna próxima',
                        'detalle' => trim(($vacuna['dosis'] ? $vacuna['dosis'] . ' - ' : '') . 'Profesional: ' . ($vacuna['profesional_nombre'] ?? 'No especificado')),
                        'fecha' => $vacuna['fecha_aplicacion'],
                    ];
                }
            }

            $tratamientos = $modeloCitas->obtenerTratamientosPorPaciente($id_propietario, $id_paciente);

            foreach ($tratamientos as $tratamiento) {
                $estado = strtolower(trim($tratamiento['estado'] ?? ''));
                $fechaFin = $tratamiento['fecha_fin'] ?? null;
                $fechaInicio = $tratamiento['fecha_inicio'] ?? null;
                $vencimientoOK = false;

                try {
                    $fechaFinObj = $fechaFin ? new DateTime($fechaFin) : null;
                    if ($fechaFinObj) {
                        $vencimientoOK = $fechaFinObj >= $hoy;
                    } else {
                        $vencimientoOK = true;
                    }
                } catch (Exception $e) {
                    $vencimientoOK = true;
                }

                if (($estado === 'activo' || $estado === 'en curso' || $estado === 'en proceso') && $vencimientoOK) {
                    $recordatorios[] = [
                        'tipo' => 'tratamiento',
                        'mascota' => $mascota['nombre'] ?? 'Mascota',
                        'titulo' => ($tratamiento['medicamento'] ?: 'Tratamiento en proceso') . ($tratamiento['dosis'] ? ' - ' . $tratamiento['dosis'] : ''),
                        'detalle' => trim('Desde: ' . ($fechaInicio ?? 'N/A') . ($fechaFin ? ' Hasta: ' . $fechaFin : '')),
                        'fecha' => $fechaFin ?: $fechaInicio,
                    ];
                }
            }
        }

        usort($recordatorios, function ($a, $b) {
            $fechaA = strtotime($a['fecha'] ?? '9999-12-31');
            $fechaB = strtotime($b['fecha'] ?? '9999-12-31');
            return $fechaA - $fechaB;
        });

        return array_slice($recordatorios, 0, 4);
    }
}

$recordatorios = [];
if ($id_propietario) {
    $recordatorios = obtenerRecordatorios($id_propietario, $modeloCitas);
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

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include_once _DIR_ . '/../../layouts/sidebar_pasiente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-principal" id="contenidoPrincipal">

        <!-- NAVBAR SUPERIOR -->
        <?php include_once _DIR_ . '/../../layouts/panel_superio_paciente.php'; ?>

        <div class="area-contenido">

            <!-- DASHBOARD CONTENT -->
            <div class="container-dashboard">

                <!-- BIENVENIDA -->
                <div class="bienvenida-card">
                    <h2>¡Bienvenido, <?= cleanText($usuario['nombres'] ?? '') ?>! <i class="bi bi-person" style="color: #ffffff; font-size: 0.9em;"></i></h2>
                    <p>Nos alegra verte nuevamente. En VetWilling cuidamos de tus mascotas con amor, profesionalismo y dedicación.</p>
                    <p class="frase">Tu familia está en buenas patas.</p>
                </div>

                <!-- ALERTA -->
                <div class="alert-box">
                    <div class="alert-icon"><i class="bi bi-exclamation-circle" style="color: #dc3545;"></i></div>
                    <div class="alert-content">
                        <?php if (!empty($recordatorios)): ?>
                            <h3>Recordatorio Importante</h3>
                            <p><?= cleanText($recordatorios[0]['titulo'] . ' de ' . $recordatorios[0]['mascota'] . ' para el ' . formatReminderDate($recordatorios[0]['fecha'])) ?></p>
                        <?php else: ?>
                            <h3>Recordatorios al día</h3>
                            <p>No hay vacunas próximas ni tratamientos activos pendientes.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CITAS Y RECORDATORIOS -->
                <div class="content-grid">

                    <!-- PRÓXIMAS CITAS -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="bi bi-calendar2-week"></i> Próximas Citas</h2>
                            <a href="<?= BASE_URL ?>/cliente/citas" class="card-action">Ver todas</a>
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

                        <?php if (!empty($recordatorios)): ?>
                            <?php foreach ($recordatorios as $item): ?>
                                <div class="recordatorio-item">
                                    <div class="recordatorio-icon"><span><i class="<?= $item['tipo'] === 'vacuna' ? 'bi bi-heart-pulse-fill' : 'bi bi-capsule' ?>"></i></span></div>
                                    <div class="recordatorio-texto">
                                        <h4><?= cleanText($item['titulo'] . ' - ' . $item['mascota']) ?></h4>
                                        <p><?= cleanText($item['detalle'] . ' · ' . formatReminderDate($item['fecha'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="recordatorio-item">
                                <div class="recordatorio-icon"><span><i class="bi bi-bell"></i></span></div>
                                <div class="recordatorio-texto">
                                    <h4>No hay recordatorios</h4>
                                    <p>No se encontraron vacunas próximas ni tratamientos activos.</p>
                                </div>
                            </div>
                        <?php endif; ?>
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

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');

                // Guardar estado
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
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
        }

        // Restaurar estado del sidebar
        window.addEventListener('load', function() {
            if (!sidebar) return;
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
            }
        });

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