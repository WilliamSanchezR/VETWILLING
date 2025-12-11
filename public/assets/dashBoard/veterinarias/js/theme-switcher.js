
// FUNCIONES DE NAVEGACIÓN
function cambiarTab(tab) {
    // Remover clase activa de todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Ocultar todos los contenidos
    document.querySelectorAll('.contenido-tab').forEach(content => {
        content.classList.remove('activo');
    });

    // Activar el tab seleccionado
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById('tab-' + tab).classList.add('activo');
}

// FUNCIONES DE BÚSQUEDA Y FILTRADO
function buscarUsuarios() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const filas = document.querySelectorAll('#tablaUsuarios tr');

    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(input) ? '' : 'none';
    });
}

function filtrarPorRol() {
    const filtro = document.getElementById('filtroRol').value;
    const filas = document.querySelectorAll('#tablaUsuarios tr');

    filas.forEach(fila => {
        if (!filtro) {
            fila.style.display = '';
            return;
        }

        const badges = fila.querySelectorAll('.badge-tabla');
        let mostrar = false;

        badges.forEach(badge => {
            if (badge.classList.contains('badge-' + filtro)) {
                mostrar = true;
            }
        });

        fila.style.display = mostrar ? '' : 'none';
    });
}

// FUNCIONES DE SELECCIÓN
function seleccionarTodos() {
    const checkboxPrincipal = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.checkbox-usuario');

    checkboxes.forEach(checkbox => {
        checkbox.checked = checkboxPrincipal.checked;
    });
}

// FUNCIONES DE ACCIONES
function exportarReporte() {
    alert('Exportando reporte del sistema...');
}

function abrirConfiguracion() {
    alert('Abriendo configuración del sistema...');
}

function nuevoUsuario() {
    alert('Abriendo formulario para crear nuevo usuario...');
}

function exportarDatos() {
    alert('Exportando datos de usuarios...');
}

function abrirFiltros() {
    alert('Abriendo panel de filtros avanzados...');
}

function refrescarTabla() {
    alert('Refrescando tabla de usuarios...');
    location.reload();
}

function verDetalle(id) {
    alert('Ver detalles del usuario: ' + id);
}

function editarUsuario(id) {
    alert('Editar usuario: ' + id);
}

function eliminarUsuario(id) {
    if (confirm('¿Estás seguro de eliminar el usuario ' + id + '?')) {
        alert('Usuario eliminado: ' + id);
    }
}

// FUNCIONES DE PAGINACIÓN
function cambiarPagina(direccion) {
    alert('Cambiar a página: ' + direccion);
}

function irAPagina(numero) {
    // Remover clase activa de todos los botones
    document.querySelectorAll('.pagina-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Activar el botón seleccionado
    event.target.classList.add('active');
    alert('Ir a página: ' + numero);
}