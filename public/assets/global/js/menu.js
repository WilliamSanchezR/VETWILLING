
class MenuAdministrador {
    constructor() {
        document.addEventListener('DOMContentLoaded', () => this.init());
    }

    init() {
        this.cacheDom();
        this.bindEvents();
        this.getMenuSeleccionado();
        this.restoreSidebarState();
    }

    cacheDom() {
        this.sidebar = document.getElementById("sidebar");
        this.sidebarToggle = document.getElementById("sidebarToggle");
        this.subMenu = document.querySelectorAll('.submenu-toggle');
        this.subMenuSeccond = document.querySelectorAll('.submenu-seccond-toggle');
        this.contentMenu = document.querySelector(".submenu-items");
        this.content = document.querySelector(".contenido");
        this.iconFlechaCont = document.querySelectorAll('.icon-flecha-cont');
    }

    bindEvents() {
        this.subMenu.forEach((element) => {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                this.openMenu(e);
            });
        });

        this.subMenuSeccond.forEach((element) => {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSubMenu(e);
            });
        });

        this.sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleSidebar();
        });

    }

    openMenu(element) {
        const parent = element.currentTarget.parentElement;
        // Cerrar otros submenús abiertos
        document.querySelectorAll('.submenu').forEach((submenu) => {
            if (submenu !== parent) {
                submenu.classList.remove('open', 'active');
            }
        });
        parent.classList.toggle('open');

        // Si el menú se abre, lo marcamos como activo
        if (parent.classList.contains('open')) {
            parent.classList.add('active');
        } else {
            parent.classList.remove('active');
        }

        // Si el sidebar está colapsado, agregamos el icono de flecha
        if (this.sidebar.classList.contains("collapsed")) {
            this.mostrarSubmenuColapsado(parent);
        }
    }

    openSubMenu(element) {
        const parent = element.currentTarget.parentElement;

        // Cerrar otros submenús secundarios abiertos
        document.querySelectorAll('.submenu-seccond').forEach((submenu) => {
            if (submenu !== parent) {
                submenu.classList.remove('open', 'active');
            }
        });

        parent.classList.toggle('open');
        parent.classList.toggle('active');
    }

    getMenuSeleccionado() {
        const urlActual = window.location.href;
        const partesUrl = urlActual.split('/');
        const paginaActual = partesUrl[partesUrl.length - 1];

        document.querySelectorAll('.submenu-items li a').forEach((element) => {
            const href = element.getAttribute('href');
            if (href.includes(paginaActual)) {
                const parentSubMenu = element.closest('.submenu');
                if (parentSubMenu) {
                    parentSubMenu.classList.add('open', 'active');
                }
            }

        });

         document.querySelectorAll('.submenu-items a').forEach((element) => {
            const href = element.getAttribute('href');
            if (href.includes(paginaActual)) {
                const parentSubMenu = element.closest('.submenu');
                if (parentSubMenu) {
                    parentSubMenu.classList.add('open', 'active');
                }
            }

        });
    }

    toggleSidebar() {
        this.sidebar.classList.toggle("collapsed");
        localStorage.setItem("sidebarCollapsed", this.sidebar.classList.contains("collapsed"));

        // Validamos si esta colapsado para ocultar los submenus abiertos
        if (this.sidebar.classList.contains("collapsed")) {
            this.collapseMenu();

        } else {
            this.getMenuSeleccionado();
            // Removemos los iconos de flecha al expandir el sidebar
            document.querySelectorAll('.icon-flecha-cont').forEach((icon) => {
                icon.remove();
            });

            // Removemos las clases de los submenus colapsados
            document.querySelectorAll('.submenu-items-float').forEach((submenu) => {
                submenu.classList.remove('submenu-items-float');
            });
        }

    }

    restoreSidebarState() {
        const collapsed = localStorage.getItem("sidebarCollapsed") === "true";
        if (collapsed) {
            this.sidebar.classList.add("collapsed");
            this.content?.classList.add("contenido-expandido");
            this.collapseMenu();
        }
    }

    collapseMenu() {
        // oculatmos los Submenus abiertos al colapsar el sidebar
        document.querySelectorAll('.submenu').forEach((submenu) => {
            submenu.classList.remove('open');
        });

        // oculatmos los Submenus secundarios abiertos al colapsar el sidebar
        document.querySelectorAll('.submenu-seccond').forEach((submenu) => {
            submenu.classList.remove('open');
        });

        // Buscamos el menu activo para agregar el icono de flecha
        document.querySelectorAll('.submenu.active').forEach((menuActivo) => {
            const iconFlecha = document.createElement('i');
            iconFlecha.classList.add('bi', 'bi-caret-right-fill', 'icon-flecha-cont');
            menuActivo.querySelector('.texto-item-sidebar').after(iconFlecha);
        });
    }

    mostrarSubmenuColapsado(element) {
        // Al contenedor de los items del submenu le agregamos una clase para mostrarlo
        const submenuItems = element.querySelector('.submenu-items');
        element.classList.toggle('view-modal-menu');
        // Si esta activo le agregamos la clase para mostrar el submenu flotante
        if (element.classList.contains('open')) {
            submenuItems.classList.add('submenu-items-float');
            //Obtenemo el submenu seleccionado
            const urlActual = window.location.href;
            const partesUrl = urlActual.split('/');
            const paginaActual = partesUrl[partesUrl.length - 1];
            // Elimianmos el active de los submenus secundarios exepto el que se esta mostrando
            document.querySelectorAll('.submenu-seccond').forEach((submenuSeccond) => {
                if (submenuSeccond !== element) {
                    submenuSeccond.classList.remove('active');
                    submenuSeccond.classList.remove('open');
                }
                const enlaces = submenuSeccond.querySelectorAll('li a');
                enlaces.forEach((enlace) => {
                    const href = enlace.getAttribute('href');
                    if (href.includes(paginaActual)) {
                        submenuSeccond.classList.add('active', 'open');
                    }
                });
                
            });

        } else {
            submenuItems.classList.remove('submenu-items-float');
        }
    }

}

new MenuAdministrador();
