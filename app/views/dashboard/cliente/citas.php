<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/citas.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/noche.css">
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

                <!-- Header -->
                <div class="header-citas">
                    <div class="header-titulo">
                        <h1>📅 Mis Citas</h1>
                        <p>Gestiona tus citas veterinarias</p>
                    </div>
                    <button class="btn-nueva-cita">
                        <i class="bi bi-plus-circle"></i>
                        Nueva Cita
                    </button>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="stats-rapidas">
                    <div class="stat-item">
                        <div class="stat-icon proximas">📆</div>
                        <div class="stat-content">
                            <h3>5</h3>
                            <p>Próximas citas</p>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon completadas">✓</div>
                        <div class="stat-content">
                            <h3>24</h3>
                            <p>Completadas</p>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon pendientes">⏱</div>
                        <div class="stat-content">
                            <h3>2</h3>
                            <p>Hoy</p>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon canceladas">✕</div>
                        <div class="stat-content">
                            <h3>3</h3>
                            <p>Canceladas</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tabs-container">
                    <button class="tab-btn active">Todas</button>
                    <button class="tab-btn">Próximas</button>
                    <button class="tab-btn">Hoy</button>
                    <button class="tab-btn">Esta Semana</button>
                    <button class="tab-btn">Completadas</button>
                    <button class="tab-btn">Canceladas</button>
                </div>

                <!-- Filtros Avanzados -->
                <div class="filtros-avanzados">
                    <div class="filtro-grupo">
                        <label>Mascota</label>
                        <select>
                            <option>Todas las mascotas</option>
                            <option>Max</option>
                            <option>Luna</option>
                            <option>Rocky</option>
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label>Tipo de Cita</label>
                        <select>
                            <option>Todos los tipos</option>
                            <option>Consulta</option>
                            <option>Vacunación</option>
                            <option>Cirugía</option>
                            <option>Control</option>
                            <option>Emergencia</option>
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label>Desde</label>
                        <input type="date">
                    </div>

                    <div class="filtro-grupo">
                        <label>Hasta</label>
                        <input type="date">
                    </div>
                </div>

                <!-- Timeline de Citas -->
                <div class="citas-timeline">

                    <!-- Hoy -->
                    <div class="timeline-dia">
                        <div class="dia-header">
                            <div class="dia-fecha">
                                <h3>Viernes, 22 de Noviembre</h3>
                                <p>Hoy</p>
                            </div>
                            <span class="dia-badge">2 citas</span>
                        </div>

                        <!-- Cita Urgente -->
                        <div class="cita-card urgente">
                            <div class="cita-hora">
                                <div class="hora-numero">09:00</div>
                                <div class="hora-periodo">AM</div>
                            </div>

                            <div class="cita-mascota-avatar">🐕</div>

                            <div class="cita-info">
                                <div class="cita-titulo">
                                    Control de Urgencia - Max
                                    <span class="tipo-badge emergencia">Emergencia</span>
                                </div>

                                <div class="cita-detalles">
                                    <div class="detalle">
                                        <i class="bi bi-person"></i>
                                        <span>Dr. Juan Martínez</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Consultorio 2</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-clock"></i>
                                        <span>30 minutos</span>
                                    </div>
                                </div>

                                <div class="cita-notas">
                                    <strong>Motivo:</strong> Revisión por vómito recurrente. Traer ayuno de 8 horas.
                                </div>
                            </div>

                            <div class="cita-acciones">
                                <button class="btn-accion btn-ver">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </button>
                                <button class="btn-accion btn-reagendar">
                                    <i class="bi bi-calendar"></i>
                                    Reagendar
                                </button>
                                <button class="btn-accion btn-cancelar">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <!-- Cita Normal -->
                        <div class="cita-card">
                            <div class="cita-hora">
                                <div class="hora-numero">03:00</div>
                                <div class="hora-periodo">PM</div>
                            </div>

                            <div class="cita-mascota-avatar">🐈</div>

                            <div class="cita-info">
                                <div class="cita-titulo">
                                    Baño y Peluquería - Luna
                                    <span class="tipo-badge bano">Estética</span>
                                </div>

                                <div class="cita-detalles">
                                    <div class="detalle">
                                        <i class="bi bi-person"></i>
                                        <span>María López (Peluquera)</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Sala de Estética</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-clock"></i>
                                        <span>1 hora</span>
                                    </div>
                                </div>

                                <div class="cita-notas">
                                    <strong>Servicio:</strong> Baño completo, corte de uñas y limpieza de oídos.
                                </div>
                            </div>

                            <div class="cita-acciones">
                                <button class="btn-accion btn-ver">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </button>
                                <button class="btn-accion btn-reagendar">
                                    <i class="bi bi-calendar"></i>
                                    Reagendar
                                </button>
                                <button class="btn-accion btn-cancelar">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lunes -->
                    <div class="timeline-dia">
                        <div class="dia-header">
                            <div class="dia-fecha">
                                <h3>Lunes, 25 de Noviembre</h3>
                                <p>En 3 días</p>
                            </div>
                            <span class="dia-badge">1 cita</span>
                        </div>

                        <div class="cita-card">
                            <div class="cita-hora">
                                <div class="hora-numero">10:30</div>
                                <div class="hora-periodo">AM</div>
                            </div>

                            <div class="cita-mascota-avatar">🐕</div>

                            <div class="cita-info">
                                <div class="cita-titulo">
                                    Vacunación Antirrábica - Max
                                    <span class="tipo-badge vacuna">Vacuna</span>
                                </div>

                                <div class="cita-detalles">
                                    <div class="detalle">
                                        <i class="bi bi-person"></i>
                                        <span>Dra. Ana García</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Consultorio 1</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-clock"></i>
                                        <span>15 minutos</span>
                                    </div>
                                </div>

                                <div class="cita-notas">
                                    <strong>Vacuna:</strong> Antirrábica anual. Recordar traer carnet de vacunación.
                                </div>
                            </div>

                            <div class="cita-acciones">
                                <button class="btn-accion btn-ver">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </button>
                                <button class="btn-accion btn-reagendar">
                                    <i class="bi bi-calendar"></i>
                                    Reagendar
                                </button>
                                <button class="btn-accion btn-cancelar">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Jueves -->
                    <div class="timeline-dia">
                        <div class="dia-header">
                            <div class="dia-fecha">
                                <h3>Jueves, 28 de Noviembre</h3>
                                <p>En 6 días</p>
                            </div>
                            <span class="dia-badge">2 citas</span>
                        </div>

                        <div class="cita-card">
                            <div class="cita-hora">
                                <div class="hora-numero">11:00</div>
                                <div class="hora-periodo">AM</div>
                            </div>

                            <div class="cita-mascota-avatar">🐕</div>

                            <div class="cita-info">
                                <div class="cita-titulo">
                                    Control Postoperatorio - Rocky
                                    <span class="tipo-badge control">Control</span>
                                </div>

                                <div class="cita-detalles">
                                    <div class="detalle">
                                        <i class="bi bi-person"></i>
                                        <span>Dr. Carlos Rodríguez</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Consultorio 3</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-clock"></i>
                                        <span>20 minutos</span>
                                    </div>
                                </div>

                                <div class="cita-notas">
                                    <strong>Revisión:</strong> Control de herida quirúrgica y retiro de puntos.
                                </div>
                            </div>

                            <div class="cita-acciones">
                                <button class="btn-accion btn-ver">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </button>
                                <button class="btn-accion btn-reagendar">
                                    <i class="bi bi-calendar"></i>
                                    Reagendar
                                </button>
                                <button class="btn-accion btn-cancelar">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
                            </div>
                        </div>

                        <div class="cita-card">
                            <div class="cita-hora">
                                <div class="hora-numero">02:30</div>
                                <div class="hora-periodo">PM</div>
                            </div>

                            <div class="cita-mascota-avatar">🐈</div>

                            <div class="cita-info">
                                <div class="cita-titulo">
                                    Consulta General - Luna
                                    <span class="tipo-badge consulta">Consulta</span>
                                </div>

                                <div class="cita-detalles">
                                    <div class="detalle">
                                        <i class="bi bi-person"></i>
                                        <span>Dra. Ana García</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Consultorio 1</span>
                                    </div>
                                    <div class="detalle">
                                        <i class="bi bi-clock"></i>
                                        <span>30 minutos</span>
                                    </div>
                                </div>

                                <div class="cita-notas">
                                    <strong>Motivo:</strong> Chequeo general y actualización de desparasitación.
                                </div>
                            </div>

                            <div class="cita-acciones">
                                <button class="btn-accion btn-ver">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </button>
                                <button class="btn-accion btn-reagendar">
                                    <i class="bi bi-calendar"></i>
                                    Reagendar
                                </button>
                                <button class="btn-accion btn-cancelar">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </button>
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
        // Animaciones
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.cita-simple').forEach((cita, index) => {
                cita.style.animationDelay = `${index * 0.1}s`;
            });
        });

        console.log('✅ Vista de Citas del Cliente cargada');
    </script>
</body>

</html>