/**
 * tienda.js – VetWilling Catálogo de Productos
 * Version: 1.0
 *
 * Funcionalidades:
 * - Filtro por categoría (botones cat-btn)
 * - Búsqueda en tiempo real normalizada con tildes
 * - Ordenar por precio asc/desc y nombre A-Z
 * - Modal "Consultar producto" — rellena nombre y URL de WhatsApp
 * - Botón "Avisar cuando llegue" — feedback visual + endpoint opcional
 * - Estado vacío cuando no hay resultados
 * - resetFiltros() global para el botón del estado vacío
 */

(function () {
    'use strict';

    /* ================================================================
       REFERENCIAS AL DOM
    ================================================================ */
    var grid        = document.getElementById('catalogoGrid');
    var vacio       = document.getElementById('catalogoVacio');
    var inputBuscar = document.getElementById('inputBuscar');
    var selectOrden = document.getElementById('selectOrden');
    var contador    = document.getElementById('contadorResultados');
    var filtroCats  = document.getElementById('filtroCats');

    /* Estado actual de los filtros */
    var estado = {
        cat:    'todos',
        buscar: '',
        orden:  'default',
    };

    /* ================================================================
       OBTENER TODAS LAS TARJETAS
       Se obtienen en tiempo de ejecución para que funcione si el
       grid se actualiza dinámicamente (futura integración con AJAX).
    ================================================================ */
    function tarjetas() {
        return Array.from(grid ? grid.querySelectorAll('.prod-card') : []);
    }

    /* ================================================================
       NORMALIZAR TEXTO
       Elimina tildes y convierte a minúsculas para que la búsqueda
       funcione sin importar si el usuario escribe "alimento" o "álimento".
    ================================================================ */
    function normalizar(str) {
        return (str || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, ''); /* elimina diacríticos */
    }

    /* ================================================================
       APLICAR FILTROS Y ORDENAR
    ================================================================ */
    function aplicar() {
        var lista    = tarjetas();
        var termino  = normalizar(estado.buscar);
        var cat      = estado.cat;
        var visibles = [];

        lista.forEach(function (card) {
            var nombre    = normalizar(card.dataset.nombre || '');
            var categoria = (card.dataset.cat || '').toLowerCase();

            var matchCat    = cat === 'todos' || categoria === cat;
            var matchBuscar = termino === '' || nombre.includes(termino);

            if (matchCat && matchBuscar) {
                card.style.display = '';
                visibles.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        /* Ordenar los visibles */
        ordenar(visibles);

        /* Actualizar contador */
        if (contador) {
            var n = visibles.length;
            contador.textContent = n + ' producto' + (n !== 1 ? 's' : '');
        }

        /* Mostrar/ocultar estado vacío */
        if (vacio) {
            vacio.classList.toggle('d-none', visibles.length > 0);
        }
    }

    function ordenar(lista) {
        if (!grid || lista.length < 2) return;

        var orden = estado.orden;
        if (orden === 'default') return;

        lista.sort(function (a, b) {
            if (orden === 'precio-asc') {
                return (+a.dataset.precio || 0) - (+b.dataset.precio || 0);
            }
            if (orden === 'precio-desc') {
                return (+b.dataset.precio || 0) - (+a.dataset.precio || 0);
            }
            if (orden === 'nombre') {
                return normalizar(a.dataset.nombre || '').localeCompare(
                    normalizar(b.dataset.nombre || ''), 'es'
                );
            }
            return 0;
        });

        /* Re-insertar en el DOM en el nuevo orden */
        lista.forEach(function (card) { grid.appendChild(card); });
    }

    /* ================================================================
       EVENTOS DE FILTROS
    ================================================================ */

    /* Categorías */
    if (filtroCats) {
        filtroCats.addEventListener('click', function (e) {
            var btn = e.target.closest('.cat-btn');
            if (!btn) return;

            filtroCats.querySelectorAll('.cat-btn').forEach(function (b) {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });

            btn.classList.add('active');
            btn.setAttribute('aria-pressed', 'true');
            estado.cat = btn.dataset.cat || 'todos';
            aplicar();
        });
    }

    /* Búsqueda en tiempo real con debounce de 200ms
       para no recalcular en cada tecla */
    if (inputBuscar) {
        var debounceTimer;
        inputBuscar.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var val = this.value;
            debounceTimer = setTimeout(function () {
                estado.buscar = val;
                aplicar();
            }, 200);
        });

        /* Limpiar al presionar Escape */
        inputBuscar.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = '';
                estado.buscar = '';
                aplicar();
            }
        });
    }

    /* Ordenar */
    if (selectOrden) {
        selectOrden.addEventListener('change', function () {
            estado.orden = this.value;
            aplicar();
        });
    }

    /* ================================================================
       MODAL CONSULTAR PRODUCTO
       Al hacer click en "Consultar", el modal de Bootstrap se abre
       y se rellena con el nombre del producto y la URL de WhatsApp.
    ================================================================ */
    var modalEl = document.getElementById('modalConsultar');

    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function (event) {
            var btn     = event.relatedTarget; /* botón que disparó el modal */
            if (!btn) return;

            var nombre  = btn.dataset.nombre || 'este producto';
            var spanNom = document.getElementById('modalNombreProducto');
            var linkWa  = document.getElementById('modalWhatsapp');

            if (spanNom) spanNom.textContent = nombre;

            /* Actualizar URL de WhatsApp con el nombre del producto */
            if (linkWa) {
                var baseUrl = linkWa.href.split('?text=')[0];
                linkWa.href = baseUrl + '?text=' +
                    encodeURIComponent('Hola, quiero consultar disponibilidad del producto: ' + nombre);
            }
        });
    }

    /* ================================================================
       BOTÓN "AVISAR CUANDO LLEGUE"
       CORRECCIÓN: antes era un <span> sin funcionalidad.
       Ahora envía una solicitud y muestra feedback visual.

       Para conectar con tu backend:
       1. Crea un endpoint POST /cliente/api/productos/avisar
       2. Recibe { id_producto: int } en JSON
       3. Guarda el id_usuario + id_producto en una tabla "avisos_stock"
       4. Cuando el stock cambie, envía un email a los usuarios avisados
    ================================================================ */
    if (grid) {
        grid.addEventListener('click', function (e) {
            var btn = e.target.closest('.prod-btn-avisar');
            if (!btn || btn.classList.contains('solicitado')) return;

            var idProducto = btn.dataset.id;
            var nombre     = btn.dataset.nombre || 'el producto';

            /* Feedback inmediato */
            btn.disabled = true;
            var textoOriginal = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…';

            /* Llamada al backend (descomentar cuando exista el endpoint) */
            /*
            fetch(BASE_URL + '/cliente/api/productos/avisar', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ id_producto: idProducto })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'ok') {
                    marcarAvisado(btn, nombre);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = textoOriginal;
                    mostrarToast('No se pudo registrar. Intenta de nuevo.', 'error');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
                mostrarToast('Error de conexión.', 'error');
            });
            */

            /* Por ahora: simulación del éxito (quitar cuando conectes el backend) */
            setTimeout(function () {
                marcarAvisado(btn, nombre);
            }, 600);
        });
    }

    function marcarAvisado(btn, nombre) {
        btn.innerHTML = '<i class="bi bi-bell-fill"></i> Te avisaremos';
        btn.classList.add('solicitado');
        btn.disabled  = true;
        btn.title     = 'Recibirás una notificación cuando ' + nombre + ' esté disponible';
        mostrarToast('¡Listo! Te avisaremos cuando llegue "' + nombre + '"', 'success');
    }

    /* ================================================================
       TOAST HELPER
       Reutiliza el contenedor de toasts si existe (de configuración),
       o crea uno propio si no hay.
    ================================================================ */
    function mostrarToast(mensaje, tipo) {
        var container = document.getElementById('toastContainer');

        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.cssText =
                'position:fixed;top:18px;right:18px;z-index:9999;' +
                'display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(container);
        }

        var iconos = {
            success: 'bi-check-circle-fill',
            error:   'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info:    'bi-info-circle-fill',
        };

        var colores = {
            success: '#10b981',
            error:   '#ef4444',
            warning: '#f59e0b',
            info:    '#3b82f6',
        };

        var toast = document.createElement('div');
        toast.style.cssText =
            'min-width:260px;max-width:340px;padding:13px 16px;background:#fff;' +
            'border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);' +
            'display:flex;align-items:center;gap:10px;' +
            'border-left:4px solid ' + (colores[tipo] || colores.info) + ';' +
            'opacity:0;transform:translateX(360px);' +
            'transition:opacity .25s ease,transform .25s ease;pointer-events:auto;';

        toast.innerHTML =
            '<i class="bi ' + (iconos[tipo] || iconos.info) + '" ' +
                'style="font-size:18px;color:' + (colores[tipo] || colores.info) + ';flex-shrink:0;"></i>' +
            '<span style="font-size:13px;color:#111827;">' + mensaje + '</span>';

        container.appendChild(toast);

        requestAnimationFrame(function () {
            toast.style.opacity   = '1';
            toast.style.transform = 'translateX(0)';
        });

        setTimeout(function () {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateX(360px)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3500);
    }

    /* ================================================================
       RESET DE FILTROS (llamado desde el botón del estado vacío)
    ================================================================ */
    window.resetFiltros = function () {
        estado.cat    = 'todos';
        estado.buscar = '';
        estado.orden  = 'default';

        if (inputBuscar)  inputBuscar.value   = '';
        if (selectOrden)  selectOrden.value   = 'default';
        if (filtroCats) {
            filtroCats.querySelectorAll('.cat-btn').forEach(function (b) {
                b.classList.toggle('active', b.dataset.cat === 'todos');
            });
        }

        aplicar();
    };

    /* ================================================================
       INIT — aplicar filtros al cargar (por si hay parámetros en URL)
    ================================================================ */
    aplicar();

})();