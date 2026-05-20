<?php

require_once __DIR__ . '/../../config/database.php';


class Mascota
{
    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }


    /**
     * REGISTRAR MASCOTA
     */
    /**
     * REGISTRAR MASCOTA CON EDAD MEJORADA
     */
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO paciente 
                (id_propietario, nombre, especie, raza, edad_numero, edad_unidad, sexo, img_mascota)
                VALUES 
                (:id_propietario, :nombre, :especie, :raza, :edad_numero, :edad_unidad, :sexo, :img_mascota)";

            $stmt = $this->conexion->prepare($sql);

            // Vincular parámetros
            $stmt->bindParam(':id_propietario', $data['id_propietario'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':especie', $data['especie'], PDO::PARAM_STR);
            $stmt->bindParam(':raza', $data['raza'], PDO::PARAM_STR);

            // ✅ NUEVOS CAMPOS DE EDAD
            $stmt->bindParam(':edad_numero', $data['edad_numero'], PDO::PARAM_INT);
            $stmt->bindParam(':edad_unidad', $data['edad_unidad'], PDO::PARAM_STR);

            $stmt->bindParam(':sexo', $data['sexo'], PDO::PARAM_STR);

            // Imagen (puede ser NULL)
            $img_mascota = !empty($data['img_mascota']) ? $data['img_mascota'] : null;
            $stmt->bindParam(':img_mascota', $img_mascota, PDO::PARAM_STR);

            $resultado = $stmt->execute();

            if (!$resultado) {
                error_log("Error PDO: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            return (int) $this->conexion->lastInsertId();
        } catch (PDOException $e) {
            error_log("❌ ERROR SQL: " . $e->getMessage());
            error_log("❌ CÓDIGO ERROR: " . $e->getCode());
            error_log("❌ DATOS: " . print_r($data, true));
            return false;
        }
    }


    /**
     * LISTAR MASCOTAS POR PROPIETARIO
     */
    public function listarPorPropietario($id_propietario)
    {
        try {
            $sql = "SELECT *
                    FROM paciente
                    WHERE id_propietario = :id_propietario
                    AND estado = 'Activo'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_propietario', $id_propietario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Mascota::listarPorPropietario → " . $e->getMessage());
            return [];
        }
    }

    /**
     * LISTAR TODAS LAS MASCOTAS
     */
    public function listar()
    {
        try {
            $sql = "SELECT * FROM paciente WHERE estado = 'Activo'";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Mascota::listar → " . $e->getMessage());
            return [];
        }
    }

    /**
     * CONSULTAR UNA MASCOTA POR ID
     */
    public function consultar($id)
    {
        try {
            $sql = "SELECT *
                    FROM paciente
                    WHERE id_paciente = :id
                    AND estado = 'Activo'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Mascota::consultar → " . $e->getMessage());
            return null;
        }
    }

    /**
     * ACTUALIZAR MASCOTA CON EDAD MEJORADA
     */
    public function actualizar(array $data)
    {
        try {
            // 1. Capturar valores actuales para auditoría
            $sqlAntes = "SELECT nombre, especie, raza, edad_numero, edad_unidad, sexo,
                                peso, estado_salud, fecha_ultima_desparasitacion, img_mascota
                         FROM paciente WHERE id_paciente = :id_paciente AND estado = 'Activo'";
            $stmtAntes = $this->conexion->prepare($sqlAntes);
            $stmtAntes->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);
            $stmtAntes->execute();
            $valoresAntes = $stmtAntes->fetch(PDO::FETCH_ASSOC) ?: [];

            // 2. Construir UPDATE dinámico
            $campos = "
                nombre      = :nombre,
                especie     = :especie,
                raza        = :raza,
                edad_numero = :edad_numero,
                edad_unidad = :edad_unidad,
                sexo        = :sexo,
                peso        = :peso,
                estado_salud = :estado_salud,
                fecha_ultima_desparasitacion = :fecha_ultima_desparasitacion,
                updated_by  = :updated_by
            ";

            if (!empty($data['img_mascota'])) {
                $campos .= ", img_mascota = :img_mascota";
            }

            $sql = "UPDATE paciente SET $campos WHERE id_paciente = :id_paciente AND estado = 'Activo'";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':nombre',       $data['nombre']);
            $stmt->bindParam(':especie',      $data['especie']);
            $stmt->bindParam(':raza',         $data['raza']);
            $stmt->bindParam(':edad_numero',  $data['edad_numero'], PDO::PARAM_INT);
            $stmt->bindParam(':edad_unidad',  $data['edad_unidad'], PDO::PARAM_STR);
            $stmt->bindParam(':sexo',         $data['sexo']);
            $stmt->bindParam(':peso',         $data['peso']);
            $stmt->bindParam(':estado_salud', $data['estado_salud']);
            $stmt->bindParam(':fecha_ultima_desparasitacion', $data['fecha_ultima_desparasitacion']);
            $stmt->bindParam(':updated_by',   $data['id_usuario'], PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente',  $data['id_paciente'], PDO::PARAM_INT);

            if (!empty($data['img_mascota'])) {
                $stmt->bindParam(':img_mascota', $data['img_mascota']);
            }

            $resultado = $stmt->execute();

            if (!$resultado) {
                error_log("Error PDO actualizar: " . print_r($stmt->errorInfo(), true));
                return false;
            }

            // 3. Registrar auditoría de campos cambiados
            if (!empty($data['id_usuario'])) {
                require_once __DIR__ . '/AuditoriaModificacionMascotas.php';

                $valoresDespues = [
                    'nombre'                       => $data['nombre'],
                    'especie'                      => $data['especie'],
                    'raza'                         => $data['raza'],
                    'edad_numero'                  => $data['edad_numero'],
                    'edad_unidad'                  => $data['edad_unidad'],
                    'sexo'                         => $data['sexo'],
                    'peso'                         => $data['peso'],
                    'estado_salud'                 => $data['estado_salud'],
                    'fecha_ultima_desparasitacion' => $data['fecha_ultima_desparasitacion'],
                    'img_mascota'                  => !empty($data['img_mascota']) ? $data['img_mascota'] : ($valoresAntes['img_mascota'] ?? null),
                ];

                $auditoria = new AuditoriaModificacionMascotas();
                $auditoria->registrarCambios(
                    (int)$data['id_paciente'],
                    (int)$data['id_usuario'],
                    $valoresAntes,
                    $valoresDespues
                );
            }

            return true;
        } catch (PDOException $e) {
            error_log('❌ Error Mascota::actualizar → ' . $e->getMessage());
            error_log('❌ Datos: ' . print_r($data, true));
            return false;
        }
    }

    /**
     * VERIFICAR DEPENDENCIAS ACTIVAS
     * Valida si hay citas, tratamientos o facturas pendientes
     */
    public function verificarDependencias(int $id)
    {
        try {
            $dependencias = [
                'citas_pendientes' => 0,
                'tratamientos_activos' => 0,
                'facturas_pendientes' => 0,
                'tiene_dependencias' => false,
                'mensaje' => ''
            ];

            // 1. Verificar citas pendientes
            $sqlCitas = "SELECT COUNT(*) as total FROM agendamiento 
                        WHERE id_paciente = :id_paciente 
                        AND estado IN ('Pendiente', 'Confirmada')";
            $stmtCitas = $this->conexion->prepare($sqlCitas);
            $stmtCitas->bindParam(':id_paciente', $id, PDO::PARAM_INT);
            $stmtCitas->execute();
            $resultCitas = $stmtCitas->fetch(PDO::FETCH_ASSOC);
            $dependencias['citas_pendientes'] = $resultCitas['total'] ?? 0;

            // 2. Verificar tratamientos activos
            $sqlTratamientos = "SELECT COUNT(*) as total FROM paciente_tratamiento 
                               WHERE id_paciente = :id_paciente 
                               AND estado IN ('Activo', 'En Progreso')";
            $stmtTratamientos = $this->conexion->prepare($sqlTratamientos);
            $stmtTratamientos->bindParam(':id_paciente', $id, PDO::PARAM_INT);
            $stmtTratamientos->execute();
            $resultTratamientos = $stmtTratamientos->fetch(PDO::FETCH_ASSOC);
            $dependencias['tratamientos_activos'] = $resultTratamientos['total'] ?? 0;

            // 3. Verificar facturas pendientes (si existe tabla)
            $sqlExisteFacturas = "SELECT COUNT(*) FROM information_schema.TABLES 
                                 WHERE TABLE_SCHEMA = DATABASE() 
                                 AND TABLE_NAME = 'factura'";
            $stmtExiste = $this->conexion->prepare($sqlExisteFacturas);
            $stmtExiste->execute();
            $existeFacturas = ((int) $stmtExiste->fetchColumn() > 0);

            if ($existeFacturas) {
                $sqlFacturas = "SELECT COUNT(*) as total FROM factura 
                               WHERE id_paciente = :id_paciente 
                               AND estado IN ('Pendiente', 'Parcial')";
                $stmtFacturas = $this->conexion->prepare($sqlFacturas);
                $stmtFacturas->bindParam(':id_paciente', $id, PDO::PARAM_INT);
                $stmtFacturas->execute();
                $resultFacturas = $stmtFacturas->fetch(PDO::FETCH_ASSOC);
                $dependencias['facturas_pendientes'] = $resultFacturas['total'] ?? 0;
            }

            // Determinar si hay dependencias
            $totalDependencias = $dependencias['citas_pendientes'] + 
                                 $dependencias['tratamientos_activos'] + 
                                 $dependencias['facturas_pendientes'];

            if ($totalDependencias > 0) {
                $dependencias['tiene_dependencias'] = true;
                $mensaje = "No se puede eliminar. Hay: ";
                $detalles = [];
                if ($dependencias['citas_pendientes'] > 0) {
                    $detalles[] = $dependencias['citas_pendientes'] . " cita(s) pendiente(s)";
                }
                if ($dependencias['tratamientos_activos'] > 0) {
                    $detalles[] = $dependencias['tratamientos_activos'] . " tratamiento(s) activo(s)";
                }
                if ($dependencias['facturas_pendientes'] > 0) {
                    $detalles[] = $dependencias['facturas_pendientes'] . " factura(s) pendiente(s)";
                }
                $dependencias['mensaje'] = implode(", ", $detalles) . ".";
            }

            return $dependencias;
        } catch (PDOException $e) {
            error_log("Error Mascota::verificarDependencias → " . $e->getMessage());
            return [
                'citas_pendientes' => 0,
                'tratamientos_activos' => 0,
                'facturas_pendientes' => 0,
                'tiene_dependencias' => false,
                'mensaje' => 'Error al verificar dependencias'
            ];
        }
    }

    /**
     * ELIMINAR MASCOTA (Borrado Lógico)
     * Ahora puede recibir datos de auditoría
     */
    public function eliminar(int $id, array $datosAuditoria = [])
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener información de la mascota
            $stmt = $this->conexion->prepare(
                "SELECT nombre, img_mascota FROM paciente WHERE id_paciente = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $mascota = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Cerrar asignaciones activas si existen
            $sqlExisteAsignacion = "SELECT COUNT(*)
                                    FROM information_schema.TABLES
                                    WHERE TABLE_SCHEMA = DATABASE()
                                      AND TABLE_NAME = 'paciente_profesional_asignacion'";
            $stmtExiste = $this->conexion->prepare($sqlExisteAsignacion);
            $stmtExiste->execute();
            $existeAsignacion = ((int) $stmtExiste->fetchColumn() > 0);

            if ($existeAsignacion) {
                $cerrarAsignacion = $this->conexion->prepare(
                    "UPDATE paciente_profesional_asignacion
                     SET estado = 'Finalizado', fecha_fin = CURRENT_TIMESTAMP
                     WHERE id_paciente = :id AND estado = 'Activo'"
                );
                $cerrarAsignacion->bindParam(':id', $id, PDO::PARAM_INT);
                $cerrarAsignacion->execute();
                error_log("✅ Asignación profesional finalizada");
            }

            // 3. Cancelar citas pendientes y contar
            $sqlContarCitas = "UPDATE agendamiento
                              SET estado = 'Cancelada'
                              WHERE id_paciente = :id AND estado IN ('Pendiente', 'Confirmada')";
            $stmtCitas = $this->conexion->prepare($sqlContarCitas);
            $stmtCitas->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCitas->execute();
            $citasCanceladas = $stmtCitas->rowCount();

            // 4. Contar tratamientos activos antes de marcar como inactivos
            $sqlTratamientos = "SELECT COUNT(*) as total FROM paciente_tratamiento 
                               WHERE id_paciente = :id_paciente AND estado IN ('Activo', 'En Progreso')";
            $stmtTratCount = $this->conexion->prepare($sqlTratamientos);
            $stmtTratCount->bindParam(':id_paciente', $id, PDO::PARAM_INT);
            $stmtTratCount->execute();
            $resultTrat = $stmtTratCount->fetch(PDO::FETCH_ASSOC);
            $tratamientosCancelados = $resultTrat['total'] ?? 0;

            // 5. Inactivar mascota (borrado lógico)
            $delete = $this->conexion->prepare(
                "UPDATE paciente
                 SET estado = 'Inactivo'
                 WHERE id_paciente = :id AND estado = 'Activo'"
            );
            $delete->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado = $delete->execute();

            if (!$resultado) {
                $this->conexion->rollBack();
                return false;
            }

            // 6. Registrar en auditoría si se proporcionan datos
            if (!empty($datosAuditoria)) {
                require_once __DIR__ . '/AuditoriaEliminacion.php';
                $auditoria = new AuditoriaEliminacion();
                $datosAuditoria['id_paciente'] = $id;
                $datosAuditoria['nombre_mascota'] = $mascota['nombre'] ?? 'Desconocido';
                $datosAuditoria['citas_canceladas'] = $citasCanceladas;
                $datosAuditoria['tratamientos_cancelados'] = $tratamientosCancelados;
                $auditoria->registrarEliminacion($datosAuditoria);
            }

            $this->conexion->commit();

            return $resultado;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error Mascota::eliminar → " . $e->getMessage());
            return false;
        }
    }
}
