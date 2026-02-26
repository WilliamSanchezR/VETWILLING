<!-- =====================================================
     MODAL — AGREGAR DISPONIBILIDAD DE AGENDA
     ===================================================== -->
<div class="modal fade" id="modalAgregarAgenda" tabindex="-1"
     aria-labelledby="modalAgregarAgendaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAgenda" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_usuario"     value="<?= htmlspecialchars($_GET['id']) ?>">
            <input type="hidden" name="id_veterinaria" value="<?= htmlspecialchars($_SESSION['user']['id_veterinaria']) ?>">

            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header">
                    <h2 class="modal-title" id="modalAgregarAgendaLabel">
                        <i class="bi bi-calendar-plus"></i>
                        Agregar disponibilidad
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <!-- Especialidad -->
                    <div class="mb-3">
                        <label for="especialidad" class="am-label">
                            <i class="bi bi-award"></i>
                            Especialidad
                            <span class="text-danger">*</span>
                        </label>
                        <select class="am-select" id="especialidad" name="id_especialidad" required>
                            <option value="" disabled selected>Seleccione una especialidad...</option>
                            <?php foreach ($listaEspecialidadesProfesional as $especialidad): ?>
                                <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>">
                                    <?= htmlspecialchars($especialidad['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Especialidad del veterinario a agendar</small>
                    </div>

                    <!-- Día de la semana -->
                    <div class="mb-3">
                        <label for="dia_semana" class="am-label">
                            <i class="bi bi-calendar-week"></i>
                            Día de la semana
                            <span class="text-danger">*</span>
                        </label>
                        <select class="am-select" id="dia_semana" name="dia_semana" required>
                            <option value="" disabled selected>Seleccione un día...</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select>
                    </div>

                    <!-- Primera Jornada -->
                    <div class="mb-3">
                        <label class="am-label">
                            <i class="bi bi-sunrise"></i>
                            Primera jornada
                            <span class="text-danger">*</span>
                        </label>
                        <div class="am-time-row">
                            <div class="am-time-group">
                                <label for="hora_inicio" class="am-label">Inicio</label>
                                <input type="time" class="am-input" id="hora_inicio" name="hora_inicio">
                            </div>
                            <div class="am-time-group">
                                <label for="hora_fin" class="am-label">Fin</label>
                                <input type="time" class="am-input" id="hora_fin" name="hora_fin">
                            </div>
                        </div>
                    </div>

                    <!-- Segunda Jornada -->
                    <div class="mb-3">
                        <label class="am-label">
                            <i class="bi bi-sunset"></i>
                            Segunda jornada
                            <span style="font-size:11px; font-weight:400; color:#94a3b8; margin-left:4px;">(opcional)</span>
                        </label>
                        <div class="am-time-row">
                            <div class="am-time-group">
                                <label for="hora_inicio_seccond" class="am-label">Inicio</label>
                                <input type="time" class="am-input" id="hora_inicio_seccond" name="hora_inicio_seccond">
                            </div>
                            <div class="am-time-group">
                                <label for="hora_fin_seccond" class="am-label">Fin</label>
                                <input type="time" class="am-input" id="hora_fin_seccond" name="hora_fin_seccond">
                            </div>
                        </div>
                        <small class="text-muted">Solo si trabaja en dos jornadas el mismo día</small>
                    </div>

                    <!-- Duración por cita -->
                    <div class="mb-3">
                        <label for="duracion_minutos" class="am-label">
                            <i class="bi bi-stopwatch"></i>
                            Duración por cita
                            <span class="text-danger">*</span>
                        </label>
                        <select class="am-select" id="duracion_minutos" name="duracion_minutos" required>
                            <option value="15">15 minutos</option>
                            <option value="20">20 minutos</option>
                            <option value="30" selected>30 minutos</option>
                            <option value="45">45 minutos</option>
                            <option value="60">60 minutos</option>
                        </select>
                        <small class="text-muted">Tiempo reservado por cada cita agendada</small>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <button type="button" class="am-btn am-btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="am-btn am-btn-primary">
                        <i class="bi bi-check-lg"></i> Agregar
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>


<!-- =====================================================
     MODAL — EDITAR DISPONIBILIDAD DE AGENDA
     ===================================================== -->
<div class="modal fade" id="modalEditarAgenda" tabindex="-1"
     aria-labelledby="modalEditarAgendaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditAgenda" method="POST"
              action="<?= BASE_URL ?>/representante/editar-disponibilidad-agenda"
              enctype="multipart/form-data">
            <input type="hidden" name="id_usuario"        value="<?= htmlspecialchars($_GET['id']) ?>">
            <input type="hidden" name="id_veterinaria"    value="<?= htmlspecialchars($_SESSION['user']['id_veterinaria']) ?>">
            <input type="hidden" name="id_disponibilidad" id="edit_id_disponibilidad" value="">
            <input type="hidden" name="action"            value="editar">

            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header">
                    <h2 class="modal-title" id="modalEditarAgendaLabel">
                        <i class="bi bi-calendar-check"></i>
                        Editar disponibilidad
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <!-- Especialidad -->
                    <div class="mb-3">
                        <label for="edit_especialidad" class="am-label">
                            <i class="bi bi-award"></i>
                            Especialidad
                            <span class="text-danger">*</span>
                        </label>
                        <select class="am-select" id="edit_especialidad" name="id_especialidad" required>
                            <option value="" disabled selected>Seleccione una especialidad</option>
                            <?php foreach ($listaEspecialidadesProfesional as $especialidad): ?>
                                <option value="<?= htmlspecialchars($especialidad['id_especialidad']) ?>">
                                    <?= htmlspecialchars($especialidad['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Día -->
                    <div class="mb-3">
                        <label for="edit_dia" class="am-label">
                            <i class="bi bi-calendar-week"></i>
                            Día de la semana
                            <span class="text-danger">*</span>
                        </label>
                        <select class="am-select" id="edit_dia" name="dia_semana" required>
                            <option value="" disabled selected>Seleccione un día...</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select>
                    </div>

                    <!-- Jornada -->
                    <div class="mb-3">
                        <label class="am-label">
                            <i class="bi bi-clock-history"></i>
                            Horario
                            <span class="text-danger">*</span>
                        </label>
                        <div class="am-time-row">
                            <div class="am-time-group">
                                <label for="edit_hora_inicio" class="am-label">Inicio</label>
                                <input type="time" class="am-input" id="edit_hora_inicio" name="hora_inicio" required>
                            </div>
                            <div class="am-time-group">
                                <label for="edit_hora_fin" class="am-label">Fin</label>
                                <input type="time" class="am-input" id="edit_hora_fin" name="hora_fin" required>
                            </div>
                        </div>
                    </div>

                    <!-- Duración -->
                    <div class="mb-3">
                        <label for="edit_duracion" class="am-label">
                            <i class="bi bi-stopwatch"></i>
                            Duración por cita
                            <span class="text-danger">*</span>
                        </label>
                        <select class="am-select" id="edit_duracion" name="duracion_minutos" required>
                            <option value="15">15 minutos</option>
                            <option value="20">20 minutos</option>
                            <option value="30">30 minutos</option>
                            <option value="45">45 minutos</option>
                            <option value="60">60 minutos</option>
                        </select>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <button type="button" class="am-btn am-btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="am-btn am-btn-primary">
                        <i class="bi bi-check-lg"></i> Actualizar
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>