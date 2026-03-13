<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$veterinarias = $_SESSION['user']['id_veterinaria'];
$veterinariasArray = explode(', ', $veterinarias);
$veterinariasDetalles = consultarVeterinariasPorArray($veterinariasArray);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Veterinaria — VetCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,200;0,9..144,700;1,9..144,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
    <style>
        /* ══ RESET ══ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            color: #fff;
            overflow: hidden;
        }

        /* ══ FONDO ══ */
        body {
            background: linear-gradient(135deg, #060f09 0%, #0d2016 50%, #071409 100%);
            position: relative;
        }

        body::before {
            content: '';
            position: fixed; top: -20%; left: -10%;
            width: 60vw; height: 60vw;
            background: radial-gradient(circle, rgba(15,143,58,.13) 0%, transparent 65%);
            pointer-events: none;
            animation: blobDrift 12s ease-in-out infinite alternate;
        }
        body::after {
            content: '';
            position: fixed; bottom: -20%; right: -10%;
            width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(12,122,50,.10) 0%, transparent 65%);
            pointer-events: none;
            animation: blobDrift 15s ease-in-out infinite alternate-reverse;
        }
        @keyframes blobDrift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(4%,6%) scale(1.12); }
        }

        .grain {
            position: fixed; inset: 0; z-index: 0;
            pointer-events: none; opacity: .028;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.82' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 180px;
        }

        /* ══ CENTRADO ══ */
        .scene {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem;
        }

        /* ══ TARJETA ══ */
        .card {
            width: min(480px, 92vw);
            background: rgba(6,18,10,.60);
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 28px;
            backdrop-filter: blur(44px) saturate(170%) brightness(1.08);
            -webkit-backdrop-filter: blur(44px) saturate(170%) brightness(1.08);
            box-shadow:
                0 0 0 1px rgba(255,255,255,.06) inset,
                0 2px 0 rgba(255,255,255,.09) inset,
                0 40px 100px rgba(0,0,0,.55),
                0 0 80px rgba(15,143,58,.08);
            padding: 3rem 2.8rem 2.8rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: cardIn .7s cubic-bezier(.34,1.56,.64,1) both;
        }

        .card::before {
            content: '';
            position: absolute; top: 0; left: 15%; right: 15%; height: 1.5px;
            background: linear-gradient(90deg,
                transparent,
                rgba(15,143,58,.85),
                rgba(255,255,255,.28),
                rgba(15,143,58,.85),
                transparent);
            border-radius: 99px;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: scale(.88) translateY(28px); }
            to   { opacity: 1; transform: scale(1)   translateY(0); }
        }

        /* ══ ICONO ══ */
        .icon-wrap {
            position: relative; width: 86px; height: 86px;
            margin: 0 auto 1.8rem;
            animation: fadeUp .5s ease .15s both;
        }

        .icon-aura {
            position: absolute; inset: -14px; border-radius: 50%;
            background: radial-gradient(circle, rgba(15,143,58,.18) 0%, transparent 70%);
            animation: aura 3s ease-in-out infinite;
        }
        .icon-ring {
            position: absolute; inset: 0; border-radius: 50%;
            border: 1.5px solid rgba(15,143,58,.35);
            animation: ringAnim 2.6s ease-in-out infinite .5s;
        }
        @keyframes aura    { 0%,100%{transform:scale(.88);opacity:.7} 50%{transform:scale(1.3);opacity:.12} }
        @keyframes ringAnim{ 0%,100%{transform:scale(1);opacity:.8}  50%{transform:scale(1.2);opacity:.08} }

        .icon-circle {
            width: 86px; height: 86px; border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, rgba(15,143,58,.22), rgba(6,18,10,.92));
            border: 1.5px solid rgba(15,143,58,.48);
            display: flex; align-items: center; justify-content: center;
            box-shadow:
                0 0 0 7px rgba(15,143,58,.07),
                0 0 40px rgba(15,143,58,.22),
                0 12px 40px rgba(0,0,0,.4),
                inset 0 2px 0 rgba(255,255,255,.09);
        }

        .icon-circle svg {
            width: 36px; height: 36px;
            stroke: #0f8f3a; stroke-width: 2;
            fill: none; stroke-linecap: round; stroke-linejoin: round;
            filter: drop-shadow(0 0 10px #0f8f3acc);
            animation: iconPop .5s cubic-bezier(.34,1.56,.64,1) .55s both;
        }
        @keyframes iconPop { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }

        /* ══ TEXTO ══ */
        .paws {
            display: flex; justify-content: center; gap: 7px;
            font-size: .95rem; margin-bottom: 1rem; opacity: .38;
            animation: fadeUp .4s ease .6s both;
        }

        .card h1 {
            font-family: 'Fraunces', serif;
            font-size: clamp(1.5rem, 3.5vw, 1.85rem);
            font-weight: 700; color: #fff;
            line-height: 1.15; margin-bottom: .5rem;
            letter-spacing: -.015em;
            animation: fadeUp .4s ease .7s both;
        }

        .sep {
            width: 48px; height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(15,143,58,.6), transparent);
            border-radius: 99px; margin: 0 auto 1.8rem;
            animation: sepIn .4s ease .8s both; transform-origin: center;
        }
        @keyframes sepIn { from{transform:scaleX(0)} to{transform:scaleX(1)} }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ══ FORMULARIO ══ */
        .card form {
            display: flex; flex-direction: column;
            gap: 1.2rem; align-items: center;
            animation: fadeUp .4s ease .9s both;
        }

        /* ── Select wrapper ── */
        .select-wrap {
            position: relative; width: 100%;
        }

        .select-wrap::after {
            content: '';
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%230f8f3a' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-size: contain;
            pointer-events: none;
            transition: transform .25s;
        }

        .select-wrap:focus-within::after {
            transform: translateY(-50%) rotate(180deg);
        }

        .card select {
            width: 100%;
            padding: 13px 44px 13px 16px;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.13);
            border-radius: 14px;
            font-size: .95rem;
            font-family: 'DM Sans', sans-serif;
            color: rgba(255,255,255,.82);
            transition: border-color .25s, box-shadow .25s, background .25s;
            appearance: none; -webkit-appearance: none;
            cursor: pointer;
        }

        .card select option {
            background: #0d1f12; color: #fff;
        }

        .card select:focus {
            outline: none;
            border-color: #0f8f3a;
            background: rgba(15,143,58,.08);
            box-shadow: 0 0 0 4px rgba(15,143,58,.15);
        }

        .card select:hover:not(:focus) {
            border-color: rgba(15,143,58,.40);
        }

        .card select.error {
            border-color: #f87171;
            box-shadow: 0 0 0 4px rgba(248,113,113,.15);
            animation: shake .5s;
        }

        @keyframes shake {
            0%,100%{transform:translateX(0)}
            20%{transform:translateX(-6px)}
            40%{transform:translateX(6px)}
            60%{transform:translateX(-4px)}
            80%{transform:translateX(4px)}
        }

        /* ── Botón ── */
        .btn-submit {
            width: 100%;
            padding: 13px 38px;
            border: none; border-radius: 99px;
            font-size: .93rem; font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: .04em;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            color: #fff;
            background: linear-gradient(135deg, #0f8f3a 0%, #0c7a32 100%);
            box-shadow: 0 6px 24px rgba(15,143,58,.40), inset 0 1px 0 rgba(255,255,255,.18);
            transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s;
            position: relative; overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute; inset: 0;
            background: rgba(255,255,255,.12);
            opacity: 0; border-radius: inherit;
            transition: opacity .2s;
        }

        .btn-submit:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 36px rgba(15,143,58,.50), inset 0 1px 0 rgba(255,255,255,.18);
        }
        .btn-submit:hover::after { opacity: 1; }
        .btn-submit:active       { transform: scale(.97); }
        .btn-submit:disabled     { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── Marca ── */
        .brand {
            position: fixed; bottom: 1.4rem; right: 1.8rem; z-index: 10;
            font-size: .62rem; letter-spacing: .16em; text-transform: uppercase;
            color: rgba(255,255,255,.18);
            display: flex; align-items: center; gap: 6px;
        }
    </style>
</head>
<body>

    <div class="modal-overlay">
        <div class="modal-content"> 
            <h1>Seleccione la Veterinaria</h1>
            <form action="<?= BASE_URL ?>/login/ingresarVeterinaria" method="post">
                <input type="hidden" name="action" value="seleccionarVeterinaria">
                <select name="id_veterinaria" required>
                    <option value="" disabled selected>¿En cuál clínica estás hoy?</option>
                    <?php foreach ($veterinariasDetalles as $veterinaria): ?>
                        <option value="<?= $veterinaria['id_veterinaria'] ?>">
                            <?= htmlspecialchars($veterinaria['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn-submit" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Ingresar
            </button>
        </form>

    </div>
</div>

<div class="brand">🐾 VetCare · Sistema veterinario</div>

</body>
</html>