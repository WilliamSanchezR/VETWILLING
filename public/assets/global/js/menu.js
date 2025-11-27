// JavaScript para el funcionamiento del submenú desplegable de usuario
document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', e => {
        e.preventDefault();
        const parent = toggle.closest('.submenu');
        parent.classList.toggle('open');
    });
});


// Resaltar el item activo basado en la URL
document.addEventListener('DOMContentLoaded', function () {
    // Seleccionar todos los submenús
    const submenus = document.querySelectorAll('.submenu');

    submenus.forEach(submenu => {
        const toggle = submenu.querySelector('.submenu-toggle');

        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            // Cerrar otros submenús (opcional, comenta estas líneas si quieres múltiples abiertos)
            submenus.forEach(otroSubmenu => {
                if (otroSubmenu !== submenu) {
                    otroSubmenu.classList.remove('activo');
                }
            });

            // Alternar el submenú actual
            submenu.classList.toggle('activo');
        });
    });

    // Marcar el item activo según la URL actual
    const urlActual = window.location.href;
    const enlacesSubmenu = document.querySelectorAll('.submenu-items a');

    enlacesSubmenu.forEach(enlace => {
        if (urlActual.includes(enlace.getAttribute('href'))) {
            enlace.classList.add('activo');
            // Abrir el submenú padre
            enlace.closest('.submenu').classList.add('activo');
        }
    });
});
