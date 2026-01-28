<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';


// enlazamos la dependencia, en este caso el controlador que tiene la funcion de consultar los datos

require_once BASE_PATH . '/app/controllers/veterinarioController.php';




// Llamamos la funcion especifica que existe en dicho controlador

$datos = mostrarVeterinarios();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title>Dashboard Veterinario - Citas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Open+Sans:wght@300..800&display=swap"
        rel="stylesheet">

    <!-- Tus CSS -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleCitas.css">


</head>

<body>

    <!-- BARRA LATERAL IZQUIERDA -->
    <!-- Aqui va el include -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_veterinario.php'
    ?>


    <!-- PANEL DERECHO -->
    <!-- aqui va el inclunde notifi -->
    <?php
    // include_once __DIR__ . '/../../layouts/sidebar_notifi_veterinario.php'
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->

        <!-- Aqui va el include de navbar superior -->
        <?php
        include_once __DIR__ . '/../../layouts/panel_superior_veterinario.php'
        ?>

        <!-- HEADER -->
        <div class="header-citas">
            <h2>
                <i class="bi bi-calendar-check"></i>
                Gestión de Citas
            </h2>
            <button class="btn-nueva-cita" onclick="nuevaCita()">
                <i class="bi bi-plus-circle"></i>
                Nueva Cita
            </button>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="stats-container">
            <div class="stat-card pendientes">
                <div class="stat-icon pendientes">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-content">
                    <h3>24</h3>
                    <p>Pendientes</p>
                </div>
            </div>

            <div class="stat-card confirmadas">
                <div class="stat-icon confirmadas">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>18</h3>
                    <p>Confirmadas</p>
                </div>
            </div>

            <div class="stat-card completadas">
                <div class="stat-icon completadas">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-content">
                    <h3>142</h3>
                    <p>Completadas</p>
                </div>
            </div>

            <div class="stat-card canceladas">
                <div class="stat-icon canceladas">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>8</h3>
                    <p>Canceladas</p>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="filtros-container">
            <div class="filtros-row">
                <div class="campo-busqueda">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Buscar por paciente, propietario o mascota..." id="buscarCita">
                </div>

                <select class="filtro-select" id="filtroVeterinario">
                    <option value="">Todos los veterinarios</option>
                    <option value="1">Dr. Juan Pérez</option>
                    <option value="2">Dra. María García</option>
                    <option value="3">Dr. Carlos López</option>
                </select>

                <select class="filtro-select" id="filtroFecha">
                    <option value="hoy">Hoy</option>
                    <option value="manana">Mañana</option>
                    <option value="semana">Esta Semana</option>
                    <option value="mes">Este Mes</option>
                </select>
            </div>
        </div>

        <!-- TABS -->
        <div class="tabs-container">
            <button class="tab-btn active" data-tab="todas">
                <i class="bi bi-grid-3x3-gap"></i>
                Todas
            </button>
            <button class="tab-btn" data-tab="pendientes">
                <i class="bi bi-clock-history"></i>
                Pendientes
            </button>
            <button class="tab-btn" data-tab="confirmadas">
                <i class="bi bi-check-circle"></i>
                Confirmadas
            </button>
            <button class="tab-btn" data-tab="completadas">
                <i class="bi bi-check-circle-fill"></i>
                Completadas
            </button>
        </div>

        <!-- LISTA DE CITAS -->
        <div class="citas-lista" id="citasLista">
            <!-- Cita 1 - Urgente -->
            <div class="cita-card urgente" data-estado="pendiente">
                <div class="cita-hora">
                    <div class="hora">09:00</div>
                    <div class="fecha">14 Dic 2025</div>
                </div>

                <div class="cita-info">
                    <div class="cita-header">
                        <img src="https://api.dicebear.com/7.x/bottts/svg?seed=luna" alt="Luna" class="paciente-avatar">
                        <div>
                            <h4 class="paciente-nombre">Luna - Golden Retriever</h4>
                            <span class="tipo-mascota">
                                <i class="bi bi-heart-fill"></i>
                                Perro
                            </span>
                            <span class="estado-badge pendiente">Pendiente</span>
                        </div>
                    </div>

                    <div class="cita-detalles">
                        <div class="detalle-item">
                            <i class="bi bi-person"></i>
                            <span>María González</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-telephone"></i>
                            <span>+57 300 123 4567</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-person-badge"></i>
                            <span>Dr. Juan Pérez</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-clipboard-pulse"></i>
                            <span>Control Post-Operatorio</span>
                        </div>
                    </div>
                </div>

                <div class="cita-acciones">
                    <button class="btn-accion btn-confirmar">
                        <i class="bi bi-check"></i>
                        Confirmar
                    </button>
                    <button class="btn-accion btn-editar">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </button>
                    <button class="btn-accion btn-cancelar">
                        <i class="bi bi-x"></i>
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Cita 2 - Confirmada -->
            <div class="cita-card" data-estado="confirmada">
                <div class="cita-hora">
                    <div class="hora">10:30</div>
                    <div class="fecha">14 Dic 2025</div>
                </div>

                <div class="cita-info">
                    <div class="cita-header">
                        <img src="https://api.dicebear.com/7.x/bottts/svg?seed=max" alt="Max" class="paciente-avatar">
                        <div>
                            <h4 class="paciente-nombre">Max - Labrador</h4>
                            <span class="tipo-mascota">
                                <i class="bi bi-heart-fill"></i>
                                Perro
                            </span>
                            <span class="estado-badge confirmada">Confirmada</span>
                        </div>
                    </div>

                    <div class="cita-detalles">
                        <div class="detalle-item">
                            <i class="bi bi-person"></i>
                            <span>Carlos Pérez</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-telephone"></i>
                            <span>+57 310 456 7890</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-person-badge"></i>
                            <span>Dra. María García</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-clipboard-pulse"></i>
                            <span>Vacunación Antirrábica</span>
                        </div>
                    </div>
                </div>

                <div class="cita-acciones">
                    <button class="btn-accion btn-completar">
                        <i class="bi bi-check-circle"></i>
                        Completar
                    </button>
                    <button class="btn-accion btn-editar">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </button>
                    <button class="btn-accion btn-cancelar">
                        <i class="bi bi-x"></i>
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Cita 3 - Pendiente -->
            <div class="cita-card" data-estado="pendiente">
                <div class="cita-hora">
                    <div class="hora">11:45</div>
                    <div class="fecha">14 Dic 2025</div>
                </div>

                <div class="cita-info">
                    <div class="cita-header">
                        <img src="https://api.dicebear.com/7.x/bottts/svg?seed=miau" alt="Miau" class="paciente-avatar">
                        <div>
                            <h4 class="paciente-nombre">Miau - Persa</h4>
                            <span class="tipo-mascota">
                                <i class="bi bi-heart-fill"></i>
                                Gato
                            </span>
                            <span class="estado-badge pendiente">Pendiente</span>
                        </div>
                    </div>

                    <div class="cita-detalles">
                        <div class="detalle-item">
                            <i class="bi bi-person"></i>
                            <span>Ana Martínez</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-telephone"></i>
                            <span>+57 320 789 1234</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-person-badge"></i>
                            <span>Dr. Carlos López</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-clipboard-pulse"></i>
                            <span>Consulta General</span>
                        </div>
                    </div>
                </div>

                <div class="cita-acciones">
                    <button class="btn-accion btn-confirmar">
                        <i class="bi bi-check"></i>
                        Confirmar
                    </button>
                    <button class="btn-accion btn-editar">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </button>
                    <button class="btn-accion btn-cancelar">
                        <i class="bi bi-x"></i>
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Cita 4 - Completada -->
            <div class="cita-card" data-estado="completada">
                <div class="cita-hora">
                    <div class="hora">14:00</div>
                    <div class="fecha">13 Dic 2025</div>
                </div>

                <div class="cita-info">
                    <div class="cita-header">
                        <img src="https://api.dicebear.com/7.x/bottts/svg?seed=rocky" alt="Rocky" class="paciente-avatar">
                        <div>
                            <h4 class="paciente-nombre">Rocky - Bulldog</h4>
                            <span class="tipo-mascota">
                                <i class="bi bi-heart-fill"></i>
                                Perro
                            </span>
                            <span class="estado-badge completada">Completada</span>
                        </div>
                    </div>

                    <div class="cita-detalles">
                        <div class="detalle-item">
                            <i class="bi bi-person"></i>
                            <span>Luis Rodríguez</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-telephone"></i>
                            <span>+57 315 234 5678</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-person-badge"></i>
                            <span>Dr. Juan Pérez</span>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-clipboard-pulse"></i>
                            <span>Limpieza Dental</span>
                        </div>
                    </div>
                </div>

                <div class="cita-acciones">
                    <button class="btn-accion btn-editar" style="background: #6c757d;">
                        <i class="bi bi-eye"></i>
                        Ver Detalles
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- SCRIPTS -->
    <!-- 1. jQuery PRIMERO -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- 2. Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables y extensiones -->
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>

    <!-- 4. Script de dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>

    <!-- 5. Tu script de tabla AL FINAL -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/citas.js"></script>


</body>

</html>