
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
        this.contentMenu = document.querySelector(".submenu-items");
        this.content = document.querySelector(".contenido");
        this.menuSeleccionado = null;
        this.SubmenuSeleccionado = null;
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

                        document.querySelectorAll('.submenu-items-float').forEach(menu => {
                            menu.classList.remove('submenu-items-float');
                            document.querySelectorAll('.submenu-seccond-items-float').forEach(openSubMenu => {
                                openSubMenu.classList.remove('submenu-seccond-items-float');
                            });
                            const parent = menu.closest('div');
                            parent.classList.remove('open');
                            this.menuSeleccionado = menu;
                        });

                        document.querySelectorAll('.submenu-seccond-items-float').forEach(menu => {
                            menu.classList.remove('submenu-seccond-items-float');
                        });


                        const parent = toggle.closest('.submenu');

                        if (this.menuSeleccionado && this.menuSeleccionado === parent.children[1]) {
                            this.menuSeleccionado = null;
                            return;
                        }

                        parent.classList.toggle('view-modal-menu');

                        parent.children[1].classList.add('submenu-items-float');

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

                        document.querySelectorAll('.submenu-seccond-items-float').forEach(menu => {
                            menu.classList.remove('submenu-seccond-items-float');
                            const parent = menu.closest('div');
                            parent.classList.remove('open');
                            this.SubmenuSeleccionado = menu;
                        });

                        const parent = toggle.closest('.submenu-seccond');

                         if (this.SubmenuSeleccionado && this.SubmenuSeleccionado === parent.children[1]) {
                            this.SubmenuSeleccionado = null;
                            return;
                        }

                        
                        parent.classList.toggle('view-modal-menu');

                        const submenuItems = parent.querySelector('.submenu-seccond-items');
                        submenuItems.classList.add('submenu-seccond-items-float');




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
        if (document.getElementById("sidebar").classList.contains("collapsed")) {
            this.contentMenu.classList.remove('submenu-items-float');
        }

        this.visualizarSumbMenufloat();
        this.visualizarSumbMenuItemfloat();
    }

    restoreSidebarState() {
        const collapsed = localStorage.getItem("sidebarCollapsed") === "true";
        if (collapsed) {
            this.sidebar.classList.add("collapsed");
            this.content?.classList.add("contenido-expandido");
            this.visualizarSumbMenufloat();
            this.visualizarSumbMenuItemfloat();
        }
    }

    visualizarSumbMenufloat() {
        document.querySelectorAll('.submenu').forEach(menu => {
            if (this.sidebar.classList.contains("collapsed") && menu.classList.contains('open')) {
                menu.children[1].classList.add('submenu-items-float');

                document.querySelectorAll('.nav-text').forEach(texto => {
                    const parentLi = texto.closest('li');
                    const parentUl = texto.closest('ul');
                    if (parentLi && parentUl && parentUl.classList.contains('submenu-items-float')) {
                        texto.style.display = 'block';
                        texto.style.width = 'auto';
                    }
                });

                document.querySelectorAll('.submenu-seccond-toggle').forEach(toggle => {
                    const parentLi = toggle.closest('li');
                    const parentUl = toggle.closest('ul');

                    if (parentLi && parentUl && parentUl.classList.contains('submenu-items-float')) {
                        const navText = toggle.querySelector('.texto-item-sidebar');
                        if (navText) {
                            navText.style.display = 'block';
                            navText.style.width = 'auto';
                        }
                    }
                });
            } else {
                menu.children[1].classList.remove('submenu-items-float');
            }
        });
    }

    visualizarSumbMenuItemfloat() {
        document.querySelectorAll('.submenu-seccond').forEach(menu => {
            if (this.sidebar.classList.contains("collapsed") && menu.classList.contains('open')) {
         
                menu.children[1].classList.add('submenu-seccond-items-float');

                document.querySelectorAll('.nav-text').forEach(texto => {
                    const parentLi = texto.closest('li');
                    const parentUl = texto.closest('ul');
                    if (parentLi && parentUl && parentUl.classList.contains('submenu-seccond-items-float')) {
                        texto.style.display = 'block';
                        texto.style.width = 'auto';
                    }
                });

                document.querySelectorAll('.submenu-seccond-toggle').forEach(toggle => {
                    const parentLi = toggle.closest('li');
                    const parentUl = toggle.closest('ul');

                    if (parentLi && parentUl && parentUl.classList.contains('submenu-seccond-items-float')) {
                        const navText = toggle.querySelector('.texto-item-sidebar');
                        if (navText) {
                            navText.style.display = 'block';
                            navText.style.width = 'auto';
                        }
                    }
                });
            } else {
                menu.children[1].classList.remove('submenu-seccond-items-float');
            }
        });
    }
}

new MenuAdministrador();
