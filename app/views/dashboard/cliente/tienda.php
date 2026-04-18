<?php
require_once BASE_PATH . '/app/helpers/session_propietario.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - Dashboard VetCare</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/tienda.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
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

                <!-- Header Tienda -->
                <div class="header-tienda">
                    <div class="header-tienda-info">
                        <h1><i class="bi bi-cart4"></i> Tienda Veterinaria</h1>
                        <p>Encuentra los mejores productos para el cuidado de tus mascotas</p>
                    </div>
                    <div class="carrito-float">
                        <button class="cart" onclick="toggleCarrito()"><i class="bi bi-cart4"></i></button>
                        <span class="carrito-badge">3</span>
                    </div>
                </div>

                <!-- Búsqueda y Filtros -->
                <div class="barra-busqueda">
                    <div class="search-container">
                        <div class="search-box">
                            <input type="text" placeholder="Buscar productos, marcas...">
                            <i class="bi bi-search"></i>
                        </div>
                        <button class="btn-buscar">Buscar</button>
                    </div>

                    <!-- Categorías -->
                    <div class="categorias-scroll">
                        <button class="categoria-btn active">
                            <i class="bi bi-grid"></i>
                            Todos
                        </button>
                        <button class="categoria-btn">
                            🍖 Alimentos
                        </button>
                        <button class="categoria-btn">
                            💊 Medicamentos
                        </button>
                        <button class="categoria-btn">
                            🧸 Juguetes
                        </button>
                        <button class="categoria-btn">
                            🛁 Higiene
                        </button>
                        <button class="categoria-btn">
                            🦴 Accesorios
                        </button>
                        <button class="categoria-btn">
                            🏠 Camas
                        </button>
                        <button class="categoria-btn">
                            👕 Ropa
                        </button>
                    </div>
                </div>

                <!-- Grid de Productos -->
                <div class="productos-grid">

                    <!-- Producto 1 -->
                    <div class="producto-card">
                        <span class="producto-badge oferta">-20%</span>
                        <div class="producto-imagen">
                            🍖
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Alimentos</div>
                            <h3 class="producto-nombre">Alimento Premium para Perros</h3>
                            <p class="producto-descripcion">Alimento balanceado de alta calidad para perros adultos. 15kg</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(124)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$89.990</span>
                                    <span class="precio-anterior">$112.490</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (15 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 2 -->
                    <div class="producto-card">
                        <span class="producto-badge nuevo">Nuevo</span>
                        <div class="producto-imagen">
                            🐱
                        </div>
                        <div class="producto-favorito active">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Alimentos</div>
                            <h3 class="producto-nombre">Alimento Premium para Gatos</h3>
                            <p class="producto-descripcion">Nutrición completa para gatos adultos. 10kg</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(89)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$75.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (8 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 3 -->
                    <div class="producto-card">
                        <div class="producto-imagen">
                            💊
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Medicamentos</div>
                            <h3 class="producto-nombre">Antiparasitario Completo</h3>
                            <p class="producto-descripcion">Protección total contra parásitos internos y externos</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(56)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$32.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock bajo">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                Pocas unidades (3)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 4 -->
                    <div class="producto-card">
                        <div class="producto-imagen">
                            🧸
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Juguetes</div>
                            <h3 class="producto-nombre">Peluche Interactivo</h3>
                            <p class="producto-descripcion">Juguete resistente con sonido para perros</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(203)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$18.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (25 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 5 -->
                    <div class="producto-card">
                        <span class="producto-badge oferta">-15%</span>
                        <div class="producto-imagen">
                            🛁
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Higiene</div>
                            <h3 class="producto-nombre">Shampoo Antipulgas</h3>
                            <p class="producto-descripcion">Shampoo especial con protección contra pulgas y garrapatas</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(78)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$24.990</span>
                                    <span class="precio-anterior">$29.490</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (12 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 6 -->
                    <div class="producto-card">
                        <div class="producto-imagen">
                            🦴
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Accesorios</div>
                            <h3 class="producto-nombre">Collar Ajustable Premium</h3>
                            <p class="producto-descripcion">Collar resistente con hebilla de seguridad</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(145)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$15.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (30 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 7 -->
                    <div class="producto-card">
                        <span class="producto-badge nuevo">Nuevo</span>
                        <div class="producto-imagen">
                            🏠
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Camas</div>
                            <h3 class="producto-nombre">Cama Ortopédica Grande</h3>
                            <p class="producto-descripcion">Cama con espuma de memoria para perros grandes</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(92)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$129.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (5 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 8 -->
                    <div class="producto-card">
                        <div class="producto-imagen">
                            👕
                        </div>
                        <div class="producto-favorito">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Ropa</div>
                            <h3 class="producto-nombre">Suéter para Clima Frío</h3>
                            <p class="producto-descripcion">Suéter abrigado para perros pequeños y medianos</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(67)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$22.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (18 unidades)
                            </div>
                        </div>
                    </div>

                    <!-- Producto 9 -->
                    <div class="producto-card">
                        <span class="producto-badge oferta">-25%</span>
                        <div class="producto-imagen">
                            🎾
                        </div>
                        <div class="producto-favorito active">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div class="producto-info">
                            <div class="producto-categoria">Juguetes</div>
                            <h3 class="producto-nombre">Set de Pelotas Resistentes</h3>
                            <p class="producto-descripcion">Pack de 3 pelotas de distintos tamaños</p>

                            <div class="producto-rating">
                                <div class="estrellas">
                                    ⭐⭐⭐⭐⭐
                                </div>
                                <span class="rating-numero">(189)</span>
                            </div>

                            <div class="producto-footer">
                                <div class="producto-precio">
                                    <span class="precio-actual">$14.990</span>
                                    <span class="precio-anterior">$19.990</span>
                                </div>
                                <button class="btn-agregar">
                                    <i class="bi bi-cart-plus"></i>
                                    Agregar
                                </button>
                            </div>
                            <div class="producto-stock">
                                <i class="bi bi-check-circle-fill"></i>
                                En Stock (40 unidades)
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Paginación -->
                <div class="paginacion">
                    <button class="pagina-btn"><i class="bi bi-chevron-left"></i></button>
                    <button class="pagina-btn active">1</button>
                    <button class="pagina-btn">2</button>
                    <button class="pagina-btn">3</button>
                    <button class="pagina-btn">4</button>
                    <button class="pagina-btn"><i class="bi bi-chevron-right"></i></button>
                </div>

            </div>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/cliente/js/clientes.js"></script>
    <!-- JavaScript -->
    <script>
        // Búsqueda
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.producto-card').forEach(card => {
                const nombre = card.querySelector('.producto-nombre').textContent.toLowerCase();
                const categoria = card.querySelector('.producto-categoria').textContent.toLowerCase();

                if (nombre.includes(searchTerm) || categoria.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.producto-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.05}s`;
            });
        });

        // Paginación
        document.querySelectorAll('.pagina-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.querySelector('i')) {
                    document.querySelectorAll('.pagina-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });

        console.log('✅ Tienda de Productos cargada correctamente');
        Categorías
        document.querySelectorAll('.categoria-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.categoria-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Favoritos
        document.querySelectorAll('.producto-favorito').forEach(btn => {
            btn.addEventListener('click', function() {
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-heart');
                icon.classList.toggle('bi-heart-fill');
            });
        });

        // Agregar al carrito
        document.querySelectorAll('.btn-agregar').forEach(btn => {
            btn.addEventListener('click', function() {
                const badge = document.querySelector('.carrito-badge');
                let count = parseInt(badge.textContent);
                badge.textContent = count + 1;

                // Animación del badge
                badge.style.animation = 'none';
                setTimeout(() => {
                    badge.style.animation = 'pulse 0.3s ease';
                }, 10);

                // Animación del botón
                this.innerHTML = '<i class="bi bi-check-lg"></i> Agregado';
                this.style.background = '#4caf50';

                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                    this.style.background = '';
                }, 2000);
            });
        });
    </script>
</body>

</html>