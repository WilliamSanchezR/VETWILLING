document.addEventListener("DOMContentLoaded", () => {

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    const contenido = document.getElementById("contenidoPrincipal");

    toggleBtn.addEventListener("click", () => {

        // Colapsar sidebar
        sidebar.classList.toggle("collapsed");

        // Mover el contenido suavemente
        contenido.classList.toggle("contenido-expandido");
    });

});
