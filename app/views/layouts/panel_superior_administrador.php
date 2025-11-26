<div class="barra-navegacion-superior">
    <div class="navegacion-izquierda">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-star text-warning"></i>
            <span class="fw-semibold">Usuario</span>
            <span class="text-muted">/</span>
            <span>Registro</span>
        </div>
    </div>
    <div class="buscador-navegacion">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search" class="form-control">
    </div>
    <div class="acciones-navegacion">
        <button class="boton-icono-navegacion">
            <i class="bi bi-brightness-high"></i>
        </button>
        <button class="boton-icono-navegacion">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button class="btn-perfil" onclick="togglePerfilMenu()" aria-label="Perfil">
            <div class="avatar-usuario">
                <img src="<?= BASE_URL ?>/public/uploads/veterinario/jorgeAndres.jpg" width="35" alt="">
            </div>
            <div class="info-usuario">
                <span class="nombre-usuario"><?= $usuario['nombres'] ?? 'Usuario' ?></span>
                <span class="rol-usuario"><?= $usuario['rol'] ?? 'Veterinario' ?></span>
            </div>
            <a href="perfil"><i class="bi bi-chevron-down flecha-perfil"></i></a>
        </button>
        <button class="boton-icono-navegacion" onclick="alternarBarraDerecha()">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
</div>