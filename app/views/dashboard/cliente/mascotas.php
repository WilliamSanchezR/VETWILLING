<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Mascotas VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">



</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: #f5f7fb;
    }

    .area-contenido {
        padding: 30px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header */
    .header-mascotas {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header-titulo {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-titulo h1 {
        font-size: 32px;
        color: #2c3e50;
        font-weight: 600;
    }

    .header-titulo .badge-count {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-agregar {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-agregar:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    /* Filtros */
    .filtros-container {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .search-box input:focus {
        outline: none;
        border-color: #667eea;
    }

    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #7f8c8d;
    }

    .filter-btn {
        padding: 12px 20px;
        border: 2px solid #e9ecef;
        background: white;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
        color: #6c757d;
    }

    .filter-btn:hover,
    .filter-btn.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
    }

    /* Grid de Mascotas */
    .mascotas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    /* Tarjeta de Mascota */
    .mascota-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
        position: relative;
    }

    .mascota-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* Header de la tarjeta con imagen de fondo */
    .mascota-card-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 30px 25px;
        position: relative;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .mascota-avatar-grande {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 45px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .mascota-info-header {
        flex: 1;
        color: white;
    }

    .mascota-info-header h3 {
        font-size: 26px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .mascota-info-header p {
        font-size: 15px;
        opacity: 0.9;
        margin-bottom: 8px;
    }

    .mascota-chip {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 12px;
        margin-right: 8px;
    }

    /* Cuerpo de la tarjeta */
    .mascota-card-body {
        padding: 25px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .info-icon {
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        font-size: 11px;
        color: #7f8c8d;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
        margin-top: 2px;
    }

    /* Sección de estado */
    .estado-section {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .estado-title {
        font-size: 13px;
        color: #7f8c8d;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .estado-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .estado-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .estado-item span:first-child {
        color: #6c757d;
    }

    .estado-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .estado-badge.al-dia {
        background: #d4edda;
        color: #155724;
    }

    .estado-badge.pendiente {
        background: #fff3cd;
        color: #856404;
    }

    .estado-badge.vencido {
        background: #f8d7da;
        color: #721c24;
    }

    /* Acciones */
    .mascota-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .action-btn {
        padding: 12px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .action-btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .action-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .action-btn-secondary {
        background: #e9ecef;
        color: #495057;
    }

    .action-btn-secondary:hover {
        background: #dee2e6;
    }

    .action-btn-success {
        background: #28a745;
        color: white;
    }

    .action-btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .action-btn-info {
        background: #17a2b8;
        color: white;
    }

    .action-btn-info:hover {
        background: #138496;
        transform: translateY(-2px);
    }

    /* Menú de opciones */
    .mascota-menu {
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .menu-btn {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .menu-btn:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Sin mascotas */
    .sin-mascotas {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .sin-mascotas-icon {
        font-size: 80px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .sin-mascotas h3 {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .sin-mascotas p {
        color: #7f8c8d;
        margin-bottom: 25px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .area-contenido {
            padding: 20px;
        }

        .header-mascotas {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .mascotas-grid {
            grid-template-columns: 1fr;
        }

        .filtros-container {
            flex-direction: column;
        }

        .search-box {
            width: 100%;
        }
    }

    /* Animaciones */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mascota-card {
        animation: fadeInUp 0.5s ease-out;
    }
</style>
<body>

    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '../../../layouts/sidebar_pasiente.php'; ?>

    <!-- PANEL DERECHO -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_notifi_pasiente.php'; ?>


    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido-principal" id="contenidoPrincipal">
        <!-- NAVBAR SUPERIOR -->
        <?php include_once __DIR__ . '../../../layouts/panel_superio_paciente.php'; ?>


        <!-- ÁREA DE CONTENIDO -->
        <div class="area-contenido">

            <!-- Header -->
            <div class="header-mascotas">
                <div class="header-titulo">
                    <h1>🐾 Mis Mascotas</h1>
                    <span class="badge-count">3 Mascotas</span>
                </div>
                <button class="btn-agregar">
                    <i class="bi bi-plus-lg"></i>
                    Agregar Mascota
                </button>
            </div>

            <!-- Filtros -->
            <div class="filtros-container">
                <div class="search-box">
                    <input type="text" placeholder="Buscar mascota por nombre...">
                    <i class="bi bi-search"></i>
                </div>
                <button class="filter-btn active">Todas</button>
                <button class="filter-btn">Perros 🐕</button>
                <button class="filter-btn">Gatos 🐈</button>
                <button class="filter-btn">Vacunas al día</button>
                <button class="filter-btn">Pendientes</button>
            </div>

            <!-- Grid de Mascotas -->
            <div class="mascotas-grid">

                <!-- Mascota 1: Max -->
                <div class="mascota-card">
                    <div class="mascota-card-header">
                        <div class="mascota-menu">
                            <button class="menu-btn"><i class="bi bi-three-dots-vertical"></i></button>
                        </div>
                        <div class="mascota-avatar-grande">🐕</div>
                        <div class="mascota-info-header">
                            <h3>Max</h3>
                            <p>Golden Retriever</p>
                            <div>
                                <span class="mascota-chip">Macho</span>
                                <span class="mascota-chip">Activo</span>
                            </div>
                        </div>
                    </div>

                    <div class="mascota-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Edad</div>
                                    <div class="info-value">3 años</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-speedometer2"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Peso</div>
                                    <div class="info-value">28 kg</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-palette"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Color</div>
                                    <div class="info-value">Dorado</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-clipboard-pulse"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Última Visita</div>
                                    <div class="info-value">15 Nov 2025</div>
                                </div>
                            </div>
                        </div>

                        <div class="estado-section">
                            <div class="estado-title">Estado de Salud</div>
                            <div class="estado-items">
                                <div class="estado-item">
                                    <span>💉 Vacunas</span>
                                    <span class="estado-badge pendiente">Pendiente</span>
                                </div>
                                <div class="estado-item">
                                    <span>💊 Desparasitación</span>
                                    <span class="estado-badge al-dia">Al día</span>
                                </div>
                                <div class="estado-item">
                                    <span>🩺 Control Anual</span>
                                    <span class="estado-badge al-dia">Al día</span>
                                </div>
                            </div>
                        </div>

                        <div class="mascota-actions">
                            <button class="action-btn action-btn-primary">
                                <i class="bi bi-calendar-plus"></i>
                                Agendar Cita
                            </button>
                            <button class="action-btn action-btn-info">
                                <i class="bi bi-file-medical"></i>
                                Ver Historial
                            </button>
                            <button class="action-btn action-btn-success">
                                <i class="bi bi-syringe"></i>
                                Vacunas
                            </button>
                            <button class="action-btn action-btn-secondary">
                                <i class="bi bi-pencil"></i>
                                Editar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mascota 2: Luna -->
                <div class="mascota-card">
                    <div class="mascota-card-header">
                        <div class="mascota-menu">
                            <button class="menu-btn"><i class="bi bi-three-dots-vertical"></i></button>
                        </div>
                        <div class="mascota-avatar-grande">🐈</div>
                        <div class="mascota-info-header">
                            <h3>Luna</h3>
                            <p>Siamés</p>
                            <div>
                                <span class="mascota-chip">Hembra</span>
                                <span class="mascota-chip">Activo</span>
                            </div>
                        </div>
                    </div>

                    <div class="mascota-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Edad</div>
                                    <div class="info-value">2 años</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-speedometer2"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Peso</div>
                                    <div class="info-value">4.5 kg</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-palette"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Color</div>
                                    <div class="info-value">Blanco/Café</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-clipboard-pulse"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Última Visita</div>
                                    <div class="info-value">10 Nov 2025</div>
                                </div>
                            </div>
                        </div>

                        <div class="estado-section">
                            <div class="estado-title">Estado de Salud</div>
                            <div class="estado-items">
                                <div class="estado-item">
                                    <span>💉 Vacunas</span>
                                    <span class="estado-badge al-dia">Al día</span>
                                </div>
                                <div class="estado-item">
                                    <span>💊 Desparasitación</span>
                                    <span class="estado-badge pendiente">Próxima: 5 días</span>
                                </div>
                                <div class="estado-item">
                                    <span>🩺 Control Anual</span>
                                    <span class="estado-badge al-dia">Al día</span>
                                </div>
                            </div>
                        </div>

                        <div class="mascota-actions">
                            <button class="action-btn action-btn-primary">
                                <i class="bi bi-calendar-plus"></i>
                                Agendar Cita
                            </button>
                            <button class="action-btn action-btn-info">
                                <i class="bi bi-file-medical"></i>
                                Ver Historial
                            </button>
                            <button class="action-btn action-btn-success">
                                <i class="bi bi-syringe"></i>
                                Vacunas
                            </button>
                            <button class="action-btn action-btn-secondary">
                                <i class="bi bi-pencil"></i>
                                Editar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mascota 3: Rocky -->
                <div class="mascota-card">
                    <div class="mascota-card-header">
                        <div class="mascota-menu">
                            <button class="menu-btn"><i class="bi bi-three-dots-vertical"></i></button>
                        </div>
                        <div class="mascota-avatar-grande">🐕</div>
                        <div class="mascota-info-header">
                            <h3>Rocky</h3>
                            <p>Pastor Alemán</p>
                            <div>
                                <span class="mascota-chip">Macho</span>
                                <span class="mascota-chip">Activo</span>
                            </div>
                        </div>
                    </div>

                    <div class="mascota-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Edad</div>
                                    <div class="info-value">5 años</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-speedometer2"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Peso</div>
                                    <div class="info-value">35 kg</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-palette"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Color</div>
                                    <div class="info-value">Negro/Café</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-clipboard-pulse"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Última Visita</div>
                                    <div class="info-value">08 Nov 2025</div>
                                </div>
                            </div>
                        </div>

                        <div class="estado-section">
                            <div class="estado-title">Estado de Salud</div>
                            <div class="estado-items">
                                <div class="estado-item">
                                    <span>💉 Vacunas</span>
                                    <span class="estado-badge al-dia">Al día</span>
                                </div>
                                <div class="estado-item">
                                    <span>💊 Desparasitación</span>
                                    <span class="estado-badge al-dia">Al día</span>
                                </div>
                                <div class="estado-item">
                                    <span>🩺 Control Anual</span>
                                    <span class="estado-badge pendiente">Programar</span>
                                </div>
                            </div>
                        </div>

                        <div class="mascota-actions">
                            <button class="action-btn action-btn-primary">
                                <i class="bi bi-calendar-plus"></i>
                                Agendar Cita
                            </button>
                            <button class="action-btn action-btn-info">
                                <i class="bi bi-file-medical"></i>
                                Ver Historial
                            </button>
                            <button class="action-btn action-btn-success">
                                <i class="bi bi-syringe"></i>
                                Vacunas
                            </button>
                            <button class="action-btn action-btn-secondary">
                                <i class="bi bi-pencil"></i>
                                Editar
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/cliente/js/clientes.js"></script>

    <script>
        // Filtros
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Búsqueda
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.mascota-card').forEach(card => {
                const nombre = card.querySelector('.mascota-info-header h3').textContent.toLowerCase();
                if (nombre.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mascota-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        console.log('✅ Vista de Mascotas cargada correctamente');
    </script>
</body>

</html>