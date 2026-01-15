
class MenuAdministrador {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.cacheDom();
        this.bindEvents();
        this.restoreSidebarState();
    }

    cacheDom() {
        this.sidebar = document.getElementById("sidebar");
        this.sidebarToggle = document.getElementById("sidebarToggle");
        this.subMenu = document.querySelectorAll('.submenu-toggle');
        this.subMenuSeccond = document.querySelectorAll('.submenu-seccond-toggle');
    }




    bindEvents() {

        // Toggle sidebar
        if (this.sidebarToggle)
            this.sidebarToggle.addEventListener("click", () => this.toggleSidebar());

        if (this.subMenu) {
            this.subMenu.forEach(toggle => {
                toggle.addEventListener('click', e => {
                    e.preventDefault();
                    if (document.getElementById("sidebar").classList.contains("collapsed")) {
                        document.querySelectorAll('.view-modal-menu').forEach(menu => {
                            menu.classList.remove('view-modal-menu');
                        });

                         const parent = toggle.closest('.submenu');
                        parent.classList.toggle('view-modal-menu');
                    } else {
                        const parent = toggle.closest('.submenu');
                        parent.classList.toggle('open');
                    }
                });
            });
        }

         if (this.subMenuSeccond) {
            this.subMenuSeccond.forEach(toggle => {
                toggle.addEventListener('click', e => {
                    e.preventDefault();
                    if (document.getElementById("sidebar").classList.contains("collapsed")) {
                        document.querySelectorAll('.view-modal-menu').forEach(menu => {
                            menu.classList.remove('view-modal-menu');
                        });

                         const parent = toggle.closest('.submenu-seccond');
                        parent.classList.toggle('view-modal-menu');
                    } else {
                        const parent = toggle.closest('.submenu-seccond');
                        parent.classList.toggle('open');
                    }
                });
            });
        }
    }

    toggleSidebar() {
        this.sidebar.classList.toggle("collapsed");
        this.content?.classList.toggle("contenido-expandido");

        // guardar en localStorage
        localStorage.setItem("sidebarCollapsed", this.sidebar.classList.contains("collapsed"));
    }

    restoreSidebarState() {
        const collapsed = localStorage.getItem("sidebarCollapsed") === "true";
        if (collapsed) {
            this.sidebar.classList.add("collapsed");
            this.content?.classList.add("contenido-expandido");
        }
    }
}

new MenuAdministrador();
