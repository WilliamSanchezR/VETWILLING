<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard Veterinario</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Iconos -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Propio -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoard.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPerfil.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardPacientes.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleDashBoardSeguimientos.css"> -->
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/nodoNoche.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/css/styleGestionClinica.css">

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

        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- PESTAÑAS DE GESTIÓN -->
            <div class="pestanas-gestion">
                <button class="pestana-gestion active" data-seccion="personal">
                    <i class="bi bi-people-fill"></i>
                    <span id="unico">Personal</span>
                </button>
                <button class="pestana-gestion" data-seccion="salas">
                    <i class="bi bi-door-open"></i>
                    <span>Salas</span>
                </button>
                <button class="pestana-gestion" data-seccion="inventario">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventario</span>
                </button>
                <button class="pestana-gestion" data-seccion="servicios">
                    <i class="bi bi-clipboard-pulse"></i>
                    <span>Servicios</span>
                </button>
                <button class="pestana-gestion" data-seccion="horarios">
                    <i class="bi bi-calendar-week"></i>
                    <span>Horarios</span>
                </button>
            </div>

            <!-- SECCIÓN PERSONAL -->
            <div class="seccion-gestion active" id="seccionPersonal">
                <div class="encabezado-seccion">
                    <h4>Gestión de Personal</h4>
                    <button class="boton-agregar" onclick="nuevoPersonal()">
                        <i class="bi bi-plus-circle"></i> Agregar Personal
                    </button>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="stat-personal">
                            <div class="icono-stat-personal bg-primary-soft">
                                <i class="bi bi-person-badge text-primary"></i>
                            </div>
                            <div>
                                <h3>8</h3>
                                <p>Veterinarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-personal">
                            <div class="icono-stat-personal bg-success-soft">
                                <i class="bi bi-heart-pulse-fill text-success"></i>
                            </div>
                            <div>
                                <h3>12</h3>
                                <p>Enfermeros</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-personal">
                            <div class="icono-stat-personal bg-info-soft">
                                <i class="bi bi-person-fill text-info"></i>
                            </div>
                            <div>
                                <h3>6</h3>
                                <p>Administrativos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-personal">
                    <!-- Card Personal 1 -->
                    <div class="card-personal">
                        <div class="header-personal">
                            <img src="https://i.pravatar.cc/150?img=10" alt="Personal" class="avatar-personal">
                            <span class="badge-cargo veterinario">Veterinario</span>
                        </div>
                        <div class="body-personal">
                            <h5>Dra. María García</h5>
                            <p class="especialidad">Cirugía General</p>
                            <div class="info-personal">
                                <div class="item-info">
                                    <i class="bi bi-envelope"></i>
                                    <span>maria.garcia@vet.com</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-telephone"></i>
                                    <span>+57 300 123 4567</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Lun - Vie: 8:00 - 16:00</span>
                                </div>
                            </div>
                            <div class="estadisticas-personal">
                                <div class="stat-mini">
                                    <strong>156</strong>
                                    <span>Pacientes</span>
                                </div>
                                <div class="stat-mini">
                                    <strong>4.9</strong>
                                    <span>Rating</span>
                                </div>
                            </div>
                        </div>
                        <div class="footer-personal">
                            <button class="btn-personal-accion">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Card Personal 2 -->
                    <div class="card-personal">
                        <div class="header-personal">
                            <img src="https://i.pravatar.cc/150?img=12" alt="Personal" class="avatar-personal">
                            <span class="badge-cargo veterinario">Veterinario</span>
                        </div>
                        <div class="body-personal">
                            <h5>Dr. Juan Pérez</h5>
                            <p class="especialidad">Medicina Interna</p>
                            <div class="info-personal">
                                <div class="item-info">
                                    <i class="bi bi-envelope"></i>
                                    <span>juan.perez@vet.com</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-telephone"></i>
                                    <span>+57 301 234 5678</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Lun - Sáb: 10:00 - 18:00</span>
                                </div>
                            </div>
                            <div class="estadisticas-personal">
                                <div class="stat-mini">
                                    <strong>203</strong>
                                    <span>Pacientes</span>
                                </div>
                                <div class="stat-mini">
                                    <strong>4.8</strong>
                                    <span>Rating</span>
                                </div>
                            </div>
                        </div>
                        <div class="footer-personal">
                            <button class="btn-personal-accion">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Card Personal 3 -->
                    <div class="card-personal">
                        <div class="header-personal">
                            <img src="https://i.pravatar.cc/150?img=5" alt="Personal" class="avatar-personal">
                            <span class="badge-cargo enfermero">Enfermero</span>
                        </div>
                        <div class="body-personal">
                            <h5>Ana Martínez</h5>
                            <p class="especialidad">Enfermería General</p>
                            <div class="info-personal">
                                <div class="item-info">
                                    <i class="bi bi-envelope"></i>
                                    <span>ana.martinez@vet.com</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-telephone"></i>
                                    <span>+57 302 345 6789</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Lun - Vie: 7:00 - 15:00</span>
                                </div>
                            </div>
                            <div class="estadisticas-personal">
                                <div class="stat-mini">
                                    <strong>342</strong>
                                    <span>Asistencias</span>
                                </div>
                                <div class="stat-mini">
                                    <strong>5.0</strong>
                                    <span>Rating</span>
                                </div>
                            </div>
                        </div>
                        <div class="footer-personal">
                            <button class="btn-personal-accion">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Card Personal 4 -->
                    <div class="card-personal">
                        <div class="header-personal">
                            <img src="https://i.pravatar.cc/150?img=20" alt="Personal" class="avatar-personal">
                            <span class="badge-cargo admin">Administrativo</span>
                        </div>
                        <div class="body-personal">
                            <h5>Carlos López</h5>
                            <p class="especialidad">Recepción</p>
                            <div class="info-personal">
                                <div class="item-info">
                                    <i class="bi bi-envelope"></i>
                                    <span>carlos.lopez@vet.com</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-telephone"></i>
                                    <span>+57 303 456 7890</span>
                                </div>
                                <div class="item-info">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Lun - Sáb: 8:00 - 17:00</span>
                                </div>
                            </div>
                            <div class="estadisticas-personal">
                                <div class="stat-mini">
                                    <strong>1,245</strong>
                                    <span>Atenciones</span>
                                </div>
                                <div class="stat-mini">
                                    <strong>4.7</strong>
                                    <span>Rating</span>
                                </div>
                            </div>
                        </div>
                        <div class="footer-personal">
                            <button class="btn-personal-accion">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-personal-accion">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN SALAS -->
            <div class="seccion-gestion" id="seccionSalas" style="display: none;">
                <div class="encabezado-seccion">
                    <h4>Gestión de Salas</h4>
                    <button class="boton-agregar" onclick="nuevaSala()">
                        <i class="bi bi-plus-circle"></i> Agregar Sala
                    </button>
                </div>

                <div class="grid-salas">
                    <!-- Sala 1 -->
                    <div class="card-sala ocupada">
                        <div class="header-sala">
                            <div class="numero-sala-grande">01</div>
                            <span class="badge-estado-sala ocupada">Ocupada</span>
                        </div>
                        <div class="body-sala">
                            <h5>Sala de Consulta General</h5>
                            <div class="info-uso-sala">
                                <div class="item-uso">
                                    <i class="bi bi-person-fill"></i>
                                    <span>Dr. Juan Pérez</span>
                                </div>
                                <div class="item-uso">
                                    <i class="bi bi-heart-fill"></i>
                                    <span>Paciente: Luna</span>
                                </div>
                                <div class="item-uso">
                                    <i class="bi bi-clock-fill"></i>
                                    <span>Tiempo: 45 min</span>
                                </div>
                            </div>
                            <div class="equipamiento-sala">
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Estetoscopio</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Termómetro</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Balanza</span>
                            </div>
                        </div>
                        <div class="footer-sala">
                            <button class="btn-sala-accion">
                                <i class="bi bi-door-open"></i> Liberar
                            </button>
                            <button class="btn-sala-accion">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                    </div>

                    <!-- Sala 2 -->
                    <div class="card-sala disponible">
                        <div class="header-sala">
                            <div class="numero-sala-grande">02</div>
                            <span class="badge-estado-sala disponible">Disponible</span>
                        </div>
                        <div class="body-sala">
                            <h5>Sala de Cirugía</h5>
                            <div class="capacidad-sala">
                                <i class="bi bi-people"></i>
                                <span>Capacidad: 4 personas</span>
                            </div>
                            <div class="equipamiento-sala">
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Mesa quirúrgica</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Lámpara</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Anestesia</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Monitor</span>
                            </div>
                            <div class="ultima-limpieza">
                                <i class="bi bi-check-circle"></i>
                                Última limpieza: Hace 1 hora
                            </div>
                        </div>
                        <div class="footer-sala">
                            <button class="btn-sala-accion primary">
                                <i class="bi bi-check-circle"></i> Usar Sala
                            </button>
                            <button class="btn-sala-accion">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                    </div>

                    <!-- Sala 3 -->
                    <div class="card-sala limpieza">
                        <div class="header-sala">
                            <div class="numero-sala-grande">03</div>
                            <span class="badge-estado-sala limpieza">En Limpieza</span>
                        </div>
                        <div class="body-sala">
                            <h5>Sala de Radiología</h5>
                            <div class="info-limpieza">
                                <i class="bi bi-hourglass-split"></i>
                                <span>Tiempo estimado: 15 minutos</span>
                            </div>
                            <div class="equipamiento-sala">
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Rayos X</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Ecógrafo</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Protección</span>
                            </div>
                            <div class="progreso-limpieza">
                                <div class="barra-limpieza">
                                    <div class="fill-limpieza" style="width: 60%;"></div>
                                </div>
                                <span>60%</span>
                            </div>
                        </div>
                        <div class="footer-sala">
                            <button class="btn-sala-accion" disabled>
                                <i class="bi bi-clock"></i> En Proceso
                            </button>
                            <button class="btn-sala-accion">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                    </div>

                    <!-- Sala 4 -->
                    <div class="card-sala mantenimiento">
                        <div class="header-sala">
                            <div class="numero-sala-grande">04</div>
                            <span class="badge-estado-sala mantenimiento">Mantenimiento</span>
                        </div>
                        <div class="body-sala">
                            <h5>Sala de Hospitalización</h5>
                            <div class="info-mantenimiento">
                                <i class="bi bi-tools"></i>
                                <span>Reparación de equipo</span>
                            </div>
                            <div class="equipamiento-sala">
                                <span class="equipo-item"><i class="bi bi-x-circle-fill text-danger"></i> Jaulas
                                    (3)</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Monitores</span>
                                <span class="equipo-item"><i class="bi bi-check-circle-fill"></i> Suero</span>
                            </div>
                            <div class="fecha-disponible">
                                <i class="bi bi-calendar"></i>
                                Disponible: Mañana 10:00 AM
                            </div>
                        </div>
                        <div class="footer-sala">
                            <button class="btn-sala-accion" disabled>
                                <i class="bi bi-tools"></i> Mantenimiento
                            </button>
                            <button class="btn-sala-accion">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN INVENTARIO -->
            <div class="seccion-gestion" id="seccionInventario" style="display: none;">
                <div class="encabezado-seccion">
                    <h4>Gestión de Inventario</h4>
                    <button class="boton-agregar" onclick="nuevoItem()">
                        <i class="bi bi-plus-circle"></i> Agregar Item
                    </button>
                </div>

                <div class="filtros-inventario">
                    <button class="filtro-inv active" data-categoria="todos">Todos</button>
                    <button class="filtro-inv" data-categoria="medicamentos">Medicamentos</button>
                    <button class="filtro-inv" data-categoria="insumos">Insumos</button>
                    <button class="filtro-inv" data-categoria="equipos">Equipos</button>
                    <button class="filtro-inv" data-categoria="alimentos">Alimentos</button>
                </div>

                <div class="tabla-inventario">
                    <table class="tabla-inv">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Precio</th>
                                <th>Vencimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="codigo-inv">MED-001</span></td>
                                <td>
                                    <div class="producto-info">
                                        <strong>Amoxicilina 500mg</strong>
                                        <small>Antibiótico</small>
                                    </div>
                                </td>
                                <td><span class="categoria-badge medicamentos">Medicamentos</span></td>
                                <td>
                                    <div class="stock-info">
                                        <strong>45</strong>
                                        <small>unidades</small>
                                    </div>
                                </td>
                                <td><span class="estado-stock disponible">Disponible</span></td>
                                <td><strong>$15,000</strong></td>
                                <td>12/2025</td>
                                <td>
                                    <div class="acciones-inv">
                                        <button class="btn-inv-accion" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn-inv-accion" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn-inv-accion" title="Reabastecer">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="codigo-inv">INS-023</span></td>
                                <td>
                                    <div class="producto-info">
                                        <strong>Jerin
                                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Propio -->

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoard.js"></script>
    <script
        src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/dashBoardSeguimientos.js"></script>
    <script
        src="<?= BASE_URL ?>/public/assets/dashBoard/veterinarias/js/theme-switcher.js"></script>

</body>

</html>