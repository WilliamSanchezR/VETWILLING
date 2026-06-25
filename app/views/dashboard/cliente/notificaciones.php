<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones | VetWilling</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" href="<?= BASE_URL ?>/public/assets/webSite/img/FAVICON.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/cliente/css/clientes.css">

    <style>
        /* ================================================================
           NOTIFICACIONES
           Paleta: azul #0d6efd · rojo #dc3545 · verde #198754
        ================================================================ */

        :root {
            --notif-radius-card : 16px;
            --notif-radius-icon : 12px;
            --notif-radius-pill : 999px;
            --notif-radius-btn  : 10px;
            --notif-shadow-hover: 0 6px 24px rgba(0,0,0,.12);
            --notif-transition  : .22s ease;

            --c-cita-accent : #0d6efd;
            --c-cita-soft   : #e8f0fe;
            --c-cita-text   : #0a52c7;

            --c-vacuna-accent: #dc3545;
            --c-vacuna-soft  : #fdecea;
            --c-vacuna-text  : #b02a37;

            --c-trat-accent : #198754;
            --c-trat-soft   : #e6f4ed;
            --c-trat-text   : #146c43;

            --c-surface  : #ffffff;
            --c-border   : rgba(0,0,0,.08);
            --c-text-main: #1a1d23;
            --c-text-muted: #6c757d;
            --c-text-hint : #adb5bd;
        }

        .notif-page { padding: 2rem; }

        /* ── Encabezado ─────────────────────────────────────────────── */
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .notif-header-left { display: flex; align-items: center; gap: 14px; }

        .notif-header-icon {
            width: 46px; height: 46px;
            border-radius: 14px;
            background: var(--c-cita-soft);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: var(--c-cita-accent); flex-shrink: 0;
        }

        .notif-header-text h2 {
            font-size: 1.25rem; font-weight: 700;
            color: var(--c-text-main); margin: 0 0 2px; line-height: 1.2;
        }

        .notif-header-text p { font-size: .85rem; color: var(--c-text-muted); margin: 0; }

        .notif-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        .notif-badge-unread {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--notif-radius-pill);
            padding: 6px 14px; font-size: .8rem; font-weight: 600;
            color: var(--c-text-muted);
        }

        .notif-badge-unread .dot {
            width: 8px; height: 8px;
            border-radius: 50%; background: #dc3545; flex-shrink: 0;
        }

        .btn-marcar-todas {
            display: inline-flex; align-items: center; gap: 6px;
            border: 1px solid var(--c-trat-accent);
            background: transparent;
            border-radius: var(--notif-radius-pill);
            padding: 6px 14px; font-size: .8rem; font-weight: 600;
            color: var(--c-trat-accent); cursor: pointer;
            transition: background var(--notif-transition);
        }

        .btn-marcar-todas:hover { background: var(--c-trat-soft); }

        /* ── Tabs ───────────────────────────────────────────────────── */
        .notif-tabs {
            display: flex; gap: 4px;
            border-bottom: 1.5px solid var(--c-border);
            margin-bottom: 1.5rem;
        }

        .notif-tab {
            background: transparent; border: none;
            padding: 8px 18px; font-size: .85rem; font-weight: 500;
            color: var(--c-text-muted); cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1.5px;
            transition: color var(--notif-transition), border-color var(--notif-transition);
            border-radius: 4px 4px 0 0;
        }

        .notif-tab:hover { color: var(--c-text-main); }

        .notif-tab.active {
            color: var(--c-cita-accent);
            border-bottom-color: var(--c-cita-accent);
            font-weight: 600;
        }

        /* ── Lista ──────────────────────────────────────────────────── */
        .notif-list { display: flex; flex-direction: column; gap: 10px; }

        /* ── Tarjeta ────────────────────────────────────────────────── */
        .notif-card {
            display: flex; gap: 14px; align-items: flex-start;
            background: var(--c-surface);
            border-radius: var(--notif-radius-card);
            border: 1px solid var(--c-border);
            padding: 16px 18px;
            transition: box-shadow var(--notif-transition), transform var(--notif-transition), opacity var(--notif-transition);
        }

        .notif-card:hover { box-shadow: var(--notif-shadow-hover); transform: translateY(-2px); }

        .notif-card.no-leida { border-left-width: 3px; border-left-style: solid; }
        .notif-card.no-leida.tipo-cita        { border-left-color: var(--c-cita-accent);   }
        .notif-card.no-leida.tipo-vacuna       { border-left-color: var(--c-vacuna-accent); }
        .notif-card.no-leida.tipo-tratamiento  { border-left-color: var(--c-trat-accent);   }

        .notif-card.leida { opacity: .65; }
        .notif-card.leida:hover { opacity: 1; }

        /* ── Icono ──────────────────────────────────────────────────── */
        .notif-icon-wrap {
            width: 44px; height: 44px;
            border-radius: var(--notif-radius-icon);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; color: #fff; flex-shrink: 0;
        }

        .tipo-cita        .notif-icon-wrap { background: var(--c-cita-accent);   }
        .tipo-vacuna      .notif-icon-wrap { background: var(--c-vacuna-accent);  }
        .tipo-tratamiento .notif-icon-wrap { background: var(--c-trat-accent);    }

        /* ── Cuerpo ─────────────────────────────────────────────────── */
        .notif-body { flex: 1; min-width: 0; }

        .notif-meta {
            display: flex; justify-content: space-between;
            align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 5px;
        }

        .notif-title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .notif-title { font-size: .9rem; font-weight: 600; color: var(--c-text-main); margin: 0; }

        .notif-pill {
            display: inline-flex; align-items: center;
            font-size: .7rem; font-weight: 600;
            padding: 2px 9px; border-radius: var(--notif-radius-pill);
        }

        .tipo-cita        .notif-pill { background: var(--c-cita-soft);   color: var(--c-cita-text);   }
        .tipo-vacuna      .notif-pill { background: var(--c-vacuna-soft);  color: var(--c-vacuna-text); }
        .tipo-tratamiento .notif-pill { background: var(--c-trat-soft);    color: var(--c-trat-text);   }

        .notif-time { font-size: .78rem; color: var(--c-text-hint); white-space: nowrap; }

        .notif-message { font-size: .85rem; color: var(--c-text-muted); line-height: 1.6; margin: 0 0 12px; }

        /* ── Acciones ───────────────────────────────────────────────── */
        .notif-actions { display: flex; gap: 8px; flex-wrap: wrap; }

        .btn-notif {
            display: inline-flex; align-items: center; gap: 5px;
            border: 1px solid; border-radius: var(--notif-radius-btn);
            padding: 5px 13px; font-size: .78rem; font-weight: 600;
            background: transparent; cursor: pointer; line-height: 1;
            transition: background var(--notif-transition), transform var(--notif-transition);
        }

        .btn-notif:hover { transform: translateY(-1px); }

        .btn-notif-read   { border-color: var(--c-trat-accent);   color: var(--c-trat-accent);   }
        .btn-notif-read:hover   { background: var(--c-trat-soft); }

        .btn-notif-delete { border-color: var(--c-vacuna-accent); color: var(--c-vacuna-accent); }
        .btn-notif-delete:hover { background: var(--c-vacuna-soft); }

        /* ── Pip sin leer ───────────────────────────────────────────── */
        .notif-pip {
            width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px;
        }

        .tipo-cita        .notif-pip { background: var(--c-cita-accent);   }
        .tipo-vacuna      .notif-pip { background: var(--c-vacuna-accent);  }
        .tipo-tratamiento .notif-pip { background: var(--c-trat-accent);    }

        /* ── Estado vacío ───────────────────────────────────────────── */
        .notif-empty {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 64px 24px;
            background: var(--c-surface);
            border-radius: var(--notif-radius-card);
            border: 1px solid var(--c-border);
            text-align: center;
        }

        .notif-empty-icon { font-size: 2.5rem; color: var(--c-text-hint); margin-bottom: 1rem; }
        .notif-empty h4   { font-size: 1rem; font-weight: 600; color: var(--c-text-main); margin-bottom: 6px; }
        .notif-empty p    { font-size: .85rem; color: var(--c-text-muted); margin: 0; }

        @media (max-width: 576px) {
            .notif-page    { padding: 1rem; }
            .notif-meta    { flex-direction: column; align-items: flex-start; }
            .notif-header  { flex-direction: column; align-items: flex-start; }
        }
    </style>

</head>
<body>

<?php include_once __DIR__ . '/../../layouts/sidebar_pasiente.php'; ?>

<main class="contenido-principal" id="contenidoPrincipal">

    <?php include_once __DIR__ . '/../../layouts/panel_superio_paciente.php'; ?>

    <div class="area-contenido">
        <div class="container-dashboard">
            <div class="notif-page">

                <!-- Encabezado ─────────────────────────────────────── -->
                <div class="notif-header">

                    <div class="notif-header-left">
                        <div class="notif-header-icon">
                            <i class="bi bi-bell-fill" aria-hidden="true"></i>
                        </div>
                        <div class="notif-header-text">
                            <h2>Centro de notificaciones</h2>
                            <p>Citas, vacunas y tratamientos de tus mascotas</p>
                        </div>
                    </div>

                    <div class="notif-header-actions">
                        <?php if ($totalNoLeidas > 0): ?>
                        <button class="btn-marcar-todas" onclick="marcarTodasLeidas()">
                            <i class="bi bi-check2-all" aria-hidden="true"></i>
                            Marcar todas como leídas
                        </button>
                        <?php endif; ?>

                        <div class="notif-badge-unread" id="badge-sin-leer">
                            <span class="dot"></span>
                            <span id="badge-texto"><?= $totalNoLeidas ?> sin leer</span>
                        </div>
                    </div>

                </div>

                <!-- Tabs ───────────────────────────────────────────── -->
                <?php
                    $total  = count($notificaciones);
                    $leidas = $total - $totalNoLeidas;
                ?>
                <div class="notif-tabs" role="tablist">
                    <button class="notif-tab active" onclick="filtrar('todas',    this)">Todas (<?= $total ?>)</button>
                    <button class="notif-tab"         onclick="filtrar('sin-leer', this)">Sin leer (<?= $totalNoLeidas ?>)</button>
                    <button class="notif-tab"         onclick="filtrar('leidas',   this)">Leídas (<?= $leidas ?>)</button>
                </div>

                <!-- Lista ──────────────────────────────────────────── -->
                <?php if (empty($notificaciones)): ?>

                    <div class="notif-empty">
                        <div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>
                        <h4>Sin notificaciones</h4>
                        <p>Cuando haya actividad relacionada con tus mascotas aparecerá aquí.</p>
                    </div>

                <?php else: ?>

                    <div class="notif-list" id="notif-list">

                        <?php foreach ($notificaciones as $notif):
                            $tipo   = htmlspecialchars($notif['tipo']);
                            $leida  = (bool)$notif['leida'];
                            $clases = 'notif-card tipo-' . $tipo . ($leida ? ' leida' : ' no-leida');
                        ?>

                        <div
                            class="<?= $clases ?>"
                            id="notif-<?= $notif['id_notificacion'] ?>"
                            data-leida="<?= $leida ? '1' : '0' ?>"
                        >
                            <div class="notif-icon-wrap" aria-hidden="true">
                                <i class="bi <?= iconoNotificacion($tipo) ?>"></i>
                            </div>

                            <div class="notif-body">
                                <div class="notif-meta">
                                    <div class="notif-title-row">
                                        <p class="notif-title"><?= htmlspecialchars($notif['titulo']) ?></p>
                                        <span class="notif-pill"><?= etiquetaNotificacion($tipo) ?></span>
                                    </div>
                                    <span class="notif-time"><?= tiempoRelativo($notif['fecha_creacion']) ?></span>
                                </div>

                                <p class="notif-message"><?= htmlspecialchars($notif['mensaje']) ?></p>

                                <div class="notif-actions">
                                    <?php if (!$leida): ?>
                                    <button
                                        class="btn-notif btn-notif-read"
                                        onclick="marcarLeida(<?= $notif['id_notificacion'] ?>)"
                                        aria-label="Marcar como leída"
                                    >
                                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                                        Marcar como leída
                                    </button>
                                    <?php endif; ?>

                                    <button
                                        class="btn-notif btn-notif-delete"
                                        onclick="eliminarNotif(<?= $notif['id_notificacion'] ?>)"
                                        aria-label="Eliminar notificación"
                                    >
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                        Eliminar
                                    </button>
                                </div>
                            </div>

                            <?php if (!$leida): ?>
                            <span class="notif-pip" title="Sin leer"></span>
                            <?php endif; ?>
                        </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

</main>

<script>
const API = '<?= BASE_URL ?>/paciente/api/notificaciones';

/* ── Filtro tabs ───────────────────────────────────────────────────────── */
function filtrar(tipo, tabEl) {
    document.querySelectorAll('.notif-tab').forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');

    document.querySelectorAll('.notif-card').forEach(card => {
        if      (tipo === 'todas')    card.style.display = '';
        else if (tipo === 'sin-leer') card.style.display = card.dataset.leida === '0' ? '' : 'none';
        else if (tipo === 'leidas')   card.style.display = card.dataset.leida === '1' ? '' : 'none';
    });
}

/* ── Marcar una como leída ─────────────────────────────────────────────── */
function marcarLeida(id) {
    fetch(API + '?action=leer', {
        method: 'POST',
        body  : new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.ok) return;

        const card = document.getElementById('notif-' + id);
        if (!card) return;

        card.classList.remove('no-leida');
        card.classList.add('leida');
        card.dataset.leida = '1';
        card.querySelector('.notif-pip')?.remove();
        card.querySelector('.btn-notif-read')?.remove();

        actualizarContadores(res.sin_leer);
    })
    .catch(err => console.error('[marcarLeida]', err));
}

/* ── Marcar todas como leídas ──────────────────────────────────────────── */
function marcarTodasLeidas() {
    fetch(API + '?action=leer_todas', { method: 'POST' })
    .then(r => r.json())
    .then(res => {
        if (!res.ok) return;

        document.querySelectorAll('.notif-card.no-leida').forEach(card => {
            card.classList.remove('no-leida');
            card.classList.add('leida');
            card.dataset.leida = '1';
            card.querySelector('.notif-pip')?.remove();
            card.querySelector('.btn-notif-read')?.remove();
        });

        actualizarContadores(0);

        // Ocultar botón "marcar todas"
        document.querySelector('.btn-marcar-todas')?.remove();
    })
    .catch(err => console.error('[marcarTodasLeidas]', err));
}

/* ── Eliminar (descartar) ──────────────────────────────────────────────── */
function eliminarNotif(id) {
    if (!confirm('¿Deseas eliminar esta notificación?')) return;

    fetch(API + '?action=descartar', {
        method: 'POST',
        body  : new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.ok) return;

        const card = document.getElementById('notif-' + id);
        if (!card) return;

        card.style.transition = 'opacity .2s, transform .2s';
        card.style.opacity    = '0';
        card.style.transform  = 'translateY(-4px)';

        setTimeout(() => {
            card.remove();
            actualizarContadores(res.sin_leer);
            mostrarVacioSiNecesario();
        }, 220);
    })
    .catch(err => console.error('[eliminarNotif]', err));
}

/* ── Actualizar badge y tabs ───────────────────────────────────────────── */
function actualizarContadores(sinLeer) {
    const cards  = [...document.querySelectorAll('.notif-card')];
    const total  = cards.length;
    const leidas = total - sinLeer;

    // Badge
    const texto = document.getElementById('badge-texto');
    if (texto) texto.textContent = sinLeer + ' sin leer';

    // Tabs
    const tabs = document.querySelectorAll('.notif-tab');
    if (tabs[0]) tabs[0].textContent = 'Todas ('    + total   + ')';
    if (tabs[1]) tabs[1].textContent = 'Sin leer (' + sinLeer + ')';
    if (tabs[2]) tabs[2].textContent = 'Leídas ('   + leidas  + ')';
}

/* ── Estado vacío dinámico ─────────────────────────────────────────────── */
function mostrarVacioSiNecesario() {
    const lista = document.getElementById('notif-list');
    if (!lista) return;

    if (lista.querySelectorAll('.notif-card').length === 0) {
        lista.innerHTML = `
            <div class="notif-empty">
                <div class="notif-empty-icon"><i class="bi bi-bell-slash"></i></div>
                <h4>Sin notificaciones</h4>
                <p>Cuando haya actividad relacionada con tus mascotas aparecerá aquí.</p>
            </div>`;
    }
}
</script>

</body>
</html>