<?php
require_once BASE_PATH . '/app/models/EquipoSistema.php';

// Si la tabla aún no existe, el directorio sigue funcionando sin esta sección
try {
    $equipo = (new EquipoSistema())->listar();
} catch (Throwable $error) {
    error_log('[equipoDirectorio] ' . $error->getMessage());
    $equipo = null;
}

$esAdmin = $idRol === 1;
$miTarjeta = null;
foreach ($equipo ?? [] as $miembro) {
    if ((int) $miembro['id_usuario'] === $idUsuario) {
        $miTarjeta = $miembro;
    }
}
?>
<?php if ($equipo !== null && ($equipo || $esAdmin)): ?>
<section class="dir-equipo" aria-labelledby="dirEquipoTitulo">
    <div class="dir-equipo-encabezado">
        <div>
            <span class="dir-equipo-etiqueta">Equipo</span>
            <h3 id="dirEquipoTitulo">Administradores del sistema</h3>
            <p>Las personas detrás de VetWilling.</p>
        </div>
        <?php if ($esAdmin): ?>
            <button type="button" class="dir-equipo-editar" data-bs-toggle="modal" data-bs-target="#modalEquipo">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                <?= $miTarjeta ? 'Editar mi tarjeta' : 'Crear mi tarjeta' ?>
            </button>
        <?php endif; ?>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 justify-content-center">
        <?php foreach ($equipo as $miembro):
            $nombre = (string) $miembro['nombre'];
            $foto = $miembro['foto']
                ? BASE_URL . '/' . $miembro['foto']
                : avatar_usuario_src(['nombres' => $nombre, 'apellidos' => '']);
            $habilidades = array_filter(array_map('trim', explode(',', (string) $miembro['habilidades'])), 'strlen');
        ?>
            <div class="col">
                <article class="team-card">
                    <div class="team-card-inner">
                        <div class="team-front">
                            <img src="<?= e($foto) ?>" width="480" height="640" alt="<?= e($nombre) ?>" loading="lazy">
                            <div class="team-overlay">
                                <h3><?= e($nombre) ?></h3>
                                <p><?= e($miembro['rol_equipo']) ?></p>
                            </div>
                            <button type="button" class="team-flip" aria-expanded="false" aria-label="Ver experiencia de <?= e($nombre) ?>">
                                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="team-back">
                            <h3><?= e($nombre) ?></h3>
                            <p class="team-role"><?= e($miembro['rol_equipo']) ?></p>
                            <ul class="team-skills" aria-label="Experiencia">
                                <?php foreach ($habilidades as $habilidad): ?>
                                    <li><?= e($habilidad) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="social-links">
                                <?php if ($miembro['github']): ?>
                                    <a href="<?= e($miembro['github']) ?>" target="_blank" rel="noopener" aria-label="GitHub de <?= e($nombre) ?>"><i class="bi bi-github" aria-hidden="true"></i></a>
                                <?php endif; ?>
                                <?php if ($miembro['linkedin']): ?>
                                    <a href="<?= e($miembro['linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn de <?= e($nombre) ?>"><i class="bi bi-linkedin" aria-hidden="true"></i></a>
                                <?php endif; ?>
                                <?php if ($miembro['correo']): ?>
                                    <a href="mailto:<?= e($miembro['correo']) ?>" aria-label="Correo de <?= e($nombre) ?>"><i class="bi bi-envelope" aria-hidden="true"></i></a>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="team-flip team-flip--back" aria-label="Volver a la foto de <?= e($nombre) ?>">
                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($esAdmin): ?>
        <div class="modal fade" id="modalEquipo" tabindex="-1" aria-labelledby="modalEquipoTitulo" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <form class="modal-content" action="<?= BASE_URL ?>/directorio/equipo/guardar" method="POST" enctype="multipart/form-data">
                    <?= csrf_campo() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEquipoTitulo">Mi tarjeta del equipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="equipoNombre">Nombre</label>
                            <input class="form-control" id="equipoNombre" name="nombre" maxlength="120" required value="<?= e($miTarjeta['nombre'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="equipoRol">Rol</label>
                            <input class="form-control" id="equipoRol" name="rol_equipo" maxlength="120" required value="<?= e($miTarjeta['rol_equipo'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="equipoHabilidades">Habilidades (separadas por coma, máximo 8)</label>
                            <input class="form-control" id="equipoHabilidades" name="habilidades" maxlength="500" value="<?= e($miTarjeta['habilidades'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="equipoFoto">Foto (JPG, PNG o WEBP, máximo 2 MB)</label>
                            <input class="form-control" type="file" id="equipoFoto" name="foto" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="equipoGithub">GitHub</label>
                            <input class="form-control" type="url" id="equipoGithub" name="github" placeholder="https://github.com/usuario" value="<?= e($miTarjeta['github'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="equipoLinkedin">LinkedIn</label>
                            <input class="form-control" type="url" id="equipoLinkedin" name="linkedin" placeholder="https://www.linkedin.com/in/usuario" value="<?= e($miTarjeta['linkedin'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="form-label" for="equipoCorreo">Correo visible</label>
                            <input class="form-control" type="email" id="equipoCorreo" name="correo" maxlength="150" value="<?= e($miTarjeta['correo'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>
