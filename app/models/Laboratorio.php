<?php

require_once __DIR__ . '/../../config/database.php';

class Laboratorio
{
    private PDO $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    public function obtenerListadoOrdenes(int $idUsuario): array
    {
        try {
            $sql = "SELECT
                        lo.id_orden_laboratorio,
                        lo.id_paciente,
                        DATE_FORMAT(lo.fecha_orden, '%Y-%m-%d') AS fecha,
                        lo.estado_orden,
                        lo.prioridad,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario,
                        p.nombre AS nombreMascota,
                        p.especie AS animal,
                        p.raza,
                        p.sexo,
                        COUNT(lop.id_orden_prueba) AS cantLaboratorios
                    FROM laboratorio_orden lo
                    INNER JOIN paciente p ON p.id_paciente = lo.id_paciente
                    INNER JOIN propietario pr ON pr.id_propietario = p.id_propietario
                    LEFT JOIN laboratorio_orden_prueba lop ON lop.id_orden_laboratorio = lo.id_orden_laboratorio
                    WHERE lo.id_usuario_solicitante = :id_usuario
                    GROUP BY
                        lo.id_orden_laboratorio,
                        lo.id_paciente,
                        lo.fecha_orden,
                        lo.estado_orden,
                        lo.prioridad,
                        pr.nombres,
                        pr.apellidos,
                        p.nombre,
                        p.especie,
                        p.raza,
                        p.sexo
                    ORDER BY lo.fecha_orden DESC, lo.id_orden_laboratorio DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return array_map(function (array $row): array {
                $idOrden = (int) ($row['id_orden_laboratorio'] ?? 0);

                return [
                    'id_orden_laboratorio' => $idOrden,
                    'id_paciente' => (int) ($row['id_paciente'] ?? 0),
                    'folio' => 'LAB-' . str_pad((string) $idOrden, 4, '0', STR_PAD_LEFT),
                    'fecha' => $row['fecha'] ?? date('Y-m-d'),
                    'propietario' => $row['propietario'] ?? '',
                    'nombreMascota' => $row['nombreMascota'] ?? '',
                    'animal' => $row['animal'] ?? '',
                    'raza' => $row['raza'] ?? '',
                    'sexo' => $row['sexo'] ?? '',
                    'cantLaboratorios' => (int) ($row['cantLaboratorios'] ?? 0),
                    'estado' => $this->mapearEstadoParaVista((string) ($row['estado_orden'] ?? 'Pendiente')),
                    'estado_texto' => $row['estado_orden'] ?? 'Pendiente',
                    'prioridad' => $row['prioridad'] ?? 'Normal',
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::obtenerListadoOrdenes - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerEstadisticas(int $idUsuario): array
    {
        try {
            $sql = "SELECT
                        COUNT(DISTINCT lo.id_orden_laboratorio) AS total_ordenes,
                        COUNT(lop.id_orden_prueba) AS total_examenes,
                        SUM(CASE WHEN lop.estado_prueba IN ('Procesada', 'Validada') THEN 1 ELSE 0 END) AS completados,
                        SUM(CASE WHEN lop.estado_prueba IN ('Pendiente', 'En proceso') THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN lop.estado_prueba = 'Cancelada' THEN 1 ELSE 0 END) AS cancelados
                    FROM laboratorio_orden lo
                    LEFT JOIN laboratorio_orden_prueba lop ON lop.id_orden_laboratorio = lo.id_orden_laboratorio
                    WHERE lo.id_usuario_solicitante = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total_ordenes' => (int) ($row['total_ordenes'] ?? 0),
                'total_examenes' => (int) ($row['total_examenes'] ?? 0),
                'completados' => (int) ($row['completados'] ?? 0),
                'pendientes' => (int) ($row['pendientes'] ?? 0),
                'cancelados' => (int) ($row['cancelados'] ?? 0),
            ];
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::obtenerEstadisticas - ' . $e->getMessage());
            return [
                'total_ordenes' => 0,
                'total_examenes' => 0,
                'completados' => 0,
                'pendientes' => 0,
                'cancelados' => 0,
            ];
        }
    }

    public function obtenerDetalleOrden(int $idUsuario, int $idOrden): ?array
    {
        try {
            $sqlOrden = "SELECT
                            lo.id_orden_laboratorio,
                            lo.id_paciente,
                            DATE_FORMAT(lo.fecha_orden, '%Y-%m-%d') AS fecha,
                            lo.estado_orden,
                            lo.prioridad,
                            lo.motivo,
                            lo.observaciones,
                            lo.fecha_toma_muestra,
                            CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario,
                            p.nombre AS nombreMascota,
                            p.especie AS animal,
                            p.raza,
                            p.sexo,
                            pr.telefono
                        FROM laboratorio_orden lo
                        INNER JOIN paciente p ON p.id_paciente = lo.id_paciente
                        INNER JOIN propietario pr ON pr.id_propietario = p.id_propietario
                        WHERE lo.id_orden_laboratorio = :id_orden
                        AND lo.id_usuario_solicitante = :id_usuario
                        LIMIT 1";

            $stmtOrden = $this->conexion->prepare($sqlOrden);
            $stmtOrden->bindParam(':id_orden', $idOrden, PDO::PARAM_INT);
            $stmtOrden->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtOrden->execute();

            $orden = $stmtOrden->fetch(PDO::FETCH_ASSOC);
            if (!$orden) {
                return null;
            }

            $sqlPruebas = "SELECT
                                lop.id_orden_prueba,
                                lop.estado_prueba,
                                lop.notas_prueba,
                                DATE_FORMAT(COALESCE(lr.fecha_resultado, lo.fecha_orden), '%Y-%m-%d') AS fecha,
                                pc.nombre_prueba,
                                pc.codigo_prueba,
                                pc.tipo_resultado,
                                pc.unidad_default,
                                pc.rango_referencia_texto,
                                pc.valor_referencia_min,
                                pc.valor_referencia_max,
                                lr.resultado_numerico,
                                lr.resultado_texto,
                                lr.resultado_booleano,
                                lr.unidad_resultado,
                                lr.interpretacion,
                                lr.observaciones AS observaciones_resultado
                            FROM laboratorio_orden_prueba lop
                            INNER JOIN laboratorio_orden lo ON lo.id_orden_laboratorio = lop.id_orden_laboratorio
                            INNER JOIN laboratorio_prueba_catalogo pc ON pc.id_prueba_catalogo = lop.id_prueba_catalogo
                            LEFT JOIN laboratorio_resultado lr ON lr.id_orden_prueba = lop.id_orden_prueba
                            WHERE lop.id_orden_laboratorio = :id_orden
                            ORDER BY lop.orden_visual ASC, lop.id_orden_prueba ASC";

            $stmtPruebas = $this->conexion->prepare($sqlPruebas);
            $stmtPruebas->bindParam(':id_orden', $idOrden, PDO::PARAM_INT);
            $stmtPruebas->execute();

            $orden['pruebas'] = $stmtPruebas->fetchAll(PDO::FETCH_ASSOC) ?: [];
            return $orden;
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::obtenerDetalleOrden - ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerResultadoPrueba(int $idUsuario, int $idOrdenPrueba): ?array
    {
        try {
            $sql = "SELECT
                        lop.id_orden_prueba,
                        lop.id_orden_laboratorio,
                        lop.estado_prueba,
                        pc.nombre_prueba,
                        pc.codigo_prueba,
                        pc.tipo_resultado,
                        pc.unidad_default,
                        pc.rango_referencia_texto,
                        pc.valor_referencia_min,
                        pc.valor_referencia_max,
                        lo.fecha_orden,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario,
                        p.nombre AS nombreMascota,
                        p.especie AS animal,
                        p.raza,
                        p.sexo,
                        lr.resultado_numerico,
                        lr.resultado_texto,
                        lr.resultado_booleano,
                        lr.unidad_resultado,
                        lr.interpretacion,
                        lr.observaciones,
                        lr.fecha_resultado
                    FROM laboratorio_orden_prueba lop
                    INNER JOIN laboratorio_orden lo ON lo.id_orden_laboratorio = lop.id_orden_laboratorio
                    INNER JOIN laboratorio_prueba_catalogo pc ON pc.id_prueba_catalogo = lop.id_prueba_catalogo
                    INNER JOIN paciente p ON p.id_paciente = lo.id_paciente
                    INNER JOIN propietario pr ON pr.id_propietario = p.id_propietario
                    LEFT JOIN laboratorio_resultado lr ON lr.id_orden_prueba = lop.id_orden_prueba
                    WHERE lop.id_orden_prueba = :id_orden_prueba
                    AND lo.id_usuario_solicitante = :id_usuario
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_orden_prueba', $idOrdenPrueba, PDO::PARAM_INT);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::obtenerResultadoPrueba - ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerPacientesDisponibles(int $idUsuario): array
    {
        try {
            $sql = "SELECT
                        p.id_paciente,
                        p.nombre AS nombre_mascota,
                        p.especie,
                        p.raza,
                        CONCAT(pr.nombres, ' ', pr.apellidos) AS propietario
                    FROM paciente_profesional_asignacion ppa
                    INNER JOIN paciente p ON p.id_paciente = ppa.id_paciente
                    INNER JOIN propietario pr ON pr.id_propietario = p.id_propietario
                    WHERE ppa.id_usuario_profesional = :id_usuario
                    AND ppa.estado = 'Activo'
                    AND ppa.fecha_fin IS NULL
                    ORDER BY p.nombre ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::obtenerPacientesDisponibles - ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerCatalogoPruebas(): array
    {
        try {
            $sql = "SELECT
                        id_prueba_catalogo,
                        codigo_prueba,
                        nombre_prueba,
                        categoria,
                        tipo_resultado,
                        unidad_default,
                        rango_referencia_texto,
                        valor_referencia_min,
                        valor_referencia_max
                    FROM laboratorio_prueba_catalogo
                    WHERE activo = 1
                    ORDER BY categoria ASC, nombre_prueba ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::obtenerCatalogoPruebas - ' . $e->getMessage());
            return [];
        }
    }

    public function crearOrden(int $idUsuario, array $data): array
    {
        $idPaciente = (int) ($data['id_paciente'] ?? 0);
        $pruebas = $data['pruebas'] ?? [];
        $prioridad = trim((string) ($data['prioridad'] ?? 'Normal'));
        $motivo = trim((string) ($data['motivo'] ?? ''));
        $observaciones = trim((string) ($data['observaciones'] ?? ''));

        if ($idPaciente <= 0) {
            return ['ok' => false, 'message' => 'Paciente inválido'];
        }

        if (!$this->pacientePerteneceProfesional($idUsuario, $idPaciente)) {
            return ['ok' => false, 'message' => 'El paciente no está asignado al profesional'];
        }

        $pruebasIds = array_values(array_unique(array_map('intval', is_array($pruebas) ? $pruebas : [])));
        $pruebasIds = array_filter($pruebasIds, fn($id) => $id > 0);
        if (empty($pruebasIds)) {
            return ['ok' => false, 'message' => 'Debes seleccionar al menos una prueba'];
        }

        try {
            $this->conexion->beginTransaction();

            $sqlOrden = "INSERT INTO laboratorio_orden (
                            id_paciente, id_usuario_solicitante, fecha_orden, estado_orden, prioridad, motivo, observaciones
                        ) VALUES (
                            :id_paciente, :id_usuario, NOW(), 'Pendiente', :prioridad, :motivo, :observaciones
                        )";

            $stmtOrden = $this->conexion->prepare($sqlOrden);
            $stmtOrden->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmtOrden->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtOrden->bindParam(':prioridad', $prioridad, PDO::PARAM_STR);
            $stmtOrden->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmtOrden->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmtOrden->execute();

            $idOrden = (int) $this->conexion->lastInsertId();

            $sqlPrueba = "INSERT INTO laboratorio_orden_prueba (
                            id_orden_laboratorio, id_prueba_catalogo, estado_prueba, orden_visual
                        ) VALUES (
                            :id_orden, :id_prueba, 'Pendiente', :orden_visual
                        )";

            $stmtPrueba = $this->conexion->prepare($sqlPrueba);
            $ordenVisual = 1;
            foreach ($pruebasIds as $idPrueba) {
                $stmtPrueba->bindParam(':id_orden', $idOrden, PDO::PARAM_INT);
                $stmtPrueba->bindParam(':id_prueba', $idPrueba, PDO::PARAM_INT);
                $stmtPrueba->bindParam(':orden_visual', $ordenVisual, PDO::PARAM_INT);
                $stmtPrueba->execute();
                $ordenVisual++;
            }

            $this->conexion->commit();

            return [
                'ok' => true,
                'message' => 'Orden de laboratorio creada correctamente',
                'id_orden_laboratorio' => $idOrden,
            ];
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log('Error en Laboratorio::crearOrden - ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No fue posible crear la orden de laboratorio'];
        }
    }

    public function guardarResultado(int $idUsuario, array $data): array
    {
        $idOrdenPrueba = (int) ($data['id_orden_prueba'] ?? 0);
        if ($idOrdenPrueba <= 0) {
            return ['ok' => false, 'message' => 'Prueba inválida'];
        }

        $prueba = $this->obtenerResultadoPrueba($idUsuario, $idOrdenPrueba);
        if (!$prueba) {
            return ['ok' => false, 'message' => 'No se encontró la prueba seleccionada'];
        }

        $tipoResultado = strtoupper((string) ($prueba['tipo_resultado'] ?? 'NUMERICO'));
        $resultadoNumerico = null;
        $resultadoTexto = null;
        $resultadoBooleano = null;

        if ($tipoResultado === 'NUMERICO') {
            $valor = trim((string) ($data['resultado_numerico'] ?? ''));
            if ($valor === '' || !is_numeric($valor)) {
                return ['ok' => false, 'message' => 'Debes ingresar un resultado numérico válido'];
            }
            $resultadoNumerico = (float) $valor;
        } elseif ($tipoResultado === 'BOOLEANO') {
            $resultadoBooleano = isset($data['resultado_booleano']) ? (int) $data['resultado_booleano'] : null;
            if ($resultadoBooleano !== 0 && $resultadoBooleano !== 1) {
                return ['ok' => false, 'message' => 'Debes seleccionar un resultado booleano válido'];
            }
        } else {
            $resultadoTexto = trim((string) ($data['resultado_texto'] ?? ''));
            if ($resultadoTexto === '') {
                return ['ok' => false, 'message' => 'Debes ingresar un resultado de texto'];
            }
        }

        $unidadResultado = trim((string) ($data['unidad_resultado'] ?? ($prueba['unidad_default'] ?? '')));
        $observaciones = trim((string) ($data['observaciones'] ?? ''));
        $estadoPrueba = trim((string) ($data['estado_prueba'] ?? 'Procesada'));
        if (!in_array($estadoPrueba, ['Pendiente', 'En proceso', 'Procesada', 'Validada', 'Cancelada'], true)) {
            $estadoPrueba = 'Procesada';
        }

        try {
            $this->conexion->beginTransaction();

            $sqlResultado = "INSERT INTO laboratorio_resultado (
                                id_orden_prueba,
                                id_usuario_responsable,
                                fecha_resultado,
                                resultado_numerico,
                                resultado_texto,
                                resultado_booleano,
                                unidad_resultado,
                                rango_referencia_texto,
                                valor_referencia_min,
                                valor_referencia_max,
                                interpretacion,
                                observaciones
                            ) VALUES (
                                :id_orden_prueba,
                                :id_usuario_responsable,
                                NOW(),
                                :resultado_numerico,
                                :resultado_texto,
                                :resultado_booleano,
                                :unidad_resultado,
                                :rango_referencia_texto,
                                :valor_referencia_min,
                                :valor_referencia_max,
                                :interpretacion,
                                :observaciones
                            ) ON DUPLICATE KEY UPDATE
                                id_usuario_responsable = VALUES(id_usuario_responsable),
                                fecha_resultado = NOW(),
                                resultado_numerico = VALUES(resultado_numerico),
                                resultado_texto = VALUES(resultado_texto),
                                resultado_booleano = VALUES(resultado_booleano),
                                unidad_resultado = VALUES(unidad_resultado),
                                rango_referencia_texto = VALUES(rango_referencia_texto),
                                valor_referencia_min = VALUES(valor_referencia_min),
                                valor_referencia_max = VALUES(valor_referencia_max),
                                interpretacion = VALUES(interpretacion),
                                observaciones = VALUES(observaciones)";

            $stmtResultado = $this->conexion->prepare($sqlResultado);
            $interpretacion = null;
            $rangoReferenciaTexto = $prueba['rango_referencia_texto'] ?? null;
            $valorReferenciaMin = $prueba['valor_referencia_min'] !== null ? (float) $prueba['valor_referencia_min'] : null;
            $valorReferenciaMax = $prueba['valor_referencia_max'] !== null ? (float) $prueba['valor_referencia_max'] : null;

            $stmtResultado->bindParam(':id_orden_prueba', $idOrdenPrueba, PDO::PARAM_INT);
            $stmtResultado->bindParam(':id_usuario_responsable', $idUsuario, PDO::PARAM_INT);
            $stmtResultado->bindValue(':resultado_numerico', $resultadoNumerico, $resultadoNumerico === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtResultado->bindValue(':resultado_texto', $resultadoTexto, $resultadoTexto === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtResultado->bindValue(':resultado_booleano', $resultadoBooleano, $resultadoBooleano === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtResultado->bindParam(':unidad_resultado', $unidadResultado, PDO::PARAM_STR);
            $stmtResultado->bindValue(':rango_referencia_texto', $rangoReferenciaTexto, $rangoReferenciaTexto === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtResultado->bindValue(':valor_referencia_min', $valorReferenciaMin, $valorReferenciaMin === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtResultado->bindValue(':valor_referencia_max', $valorReferenciaMax, $valorReferenciaMax === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtResultado->bindValue(':interpretacion', $interpretacion, PDO::PARAM_NULL);
            $stmtResultado->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmtResultado->execute();

            $sqlEstado = "UPDATE laboratorio_orden_prueba
                           SET estado_prueba = :estado_prueba
                           WHERE id_orden_prueba = :id_orden_prueba";
            $stmtEstado = $this->conexion->prepare($sqlEstado);
            $stmtEstado->bindParam(':estado_prueba', $estadoPrueba, PDO::PARAM_STR);
            $stmtEstado->bindParam(':id_orden_prueba', $idOrdenPrueba, PDO::PARAM_INT);
            $stmtEstado->execute();

            $this->actualizarEstadoOrden((int) $prueba['id_orden_laboratorio']);

            $this->conexion->commit();
            return ['ok' => true, 'message' => 'Resultado guardado correctamente'];
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log('Error en Laboratorio::guardarResultado - ' . $e->getMessage());
            return ['ok' => false, 'message' => 'No fue posible guardar el resultado'];
        }
    }

    private function pacientePerteneceProfesional(int $idUsuario, int $idPaciente): bool
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM paciente_profesional_asignacion
                    WHERE id_usuario_profesional = :id_usuario
                    AND id_paciente = :id_paciente
                    AND estado = 'Activo'
                    AND fecha_fin IS NULL";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->execute();

            return ((int) $stmt->fetchColumn()) > 0;
        } catch (PDOException $e) {
            error_log('Error en Laboratorio::pacientePerteneceProfesional - ' . $e->getMessage());
            return false;
        }
    }

    private function actualizarEstadoOrden(int $idOrden): void
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado_prueba = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN estado_prueba = 'En proceso' THEN 1 ELSE 0 END) AS en_proceso,
                    SUM(CASE WHEN estado_prueba = 'Procesada' THEN 1 ELSE 0 END) AS procesadas,
                    SUM(CASE WHEN estado_prueba = 'Validada' THEN 1 ELSE 0 END) AS validadas,
                    SUM(CASE WHEN estado_prueba = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas
                FROM laboratorio_orden_prueba
                WHERE id_orden_laboratorio = :id_orden";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_orden', $idOrden, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int) ($row['total'] ?? 0);
        $pendientes = (int) ($row['pendientes'] ?? 0);
        $enProceso = (int) ($row['en_proceso'] ?? 0);
        $procesadas = (int) ($row['procesadas'] ?? 0);
        $validadas = (int) ($row['validadas'] ?? 0);
        $canceladas = (int) ($row['canceladas'] ?? 0);

        $nuevoEstado = 'Pendiente';
        if ($total > 0 && $canceladas === $total) {
            $nuevoEstado = 'Cancelada';
        } elseif ($total > 0 && ($validadas + $canceladas) === $total && $validadas > 0) {
            $nuevoEstado = 'Validada';
        } elseif ($total > 0 && ($procesadas + $validadas + $canceladas) === $total && ($procesadas + $validadas) > 0) {
            $nuevoEstado = 'Procesada';
        } elseif ($enProceso > 0) {
            $nuevoEstado = 'En proceso';
        } elseif ($pendientes > 0) {
            $nuevoEstado = 'Pendiente';
        }

        $sqlUpdate = "UPDATE laboratorio_orden
                      SET estado_orden = :estado_orden,
                          fecha_procesamiento = CASE
                              WHEN :estado_orden IN ('Procesada', 'Validada') AND fecha_procesamiento IS NULL THEN NOW()
                              ELSE fecha_procesamiento
                          END,
                          fecha_entrega = CASE
                              WHEN :estado_orden = 'Validada' AND fecha_entrega IS NULL THEN NOW()
                              ELSE fecha_entrega
                          END
                      WHERE id_orden_laboratorio = :id_orden";

        $stmtUpdate = $this->conexion->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':estado_orden', $nuevoEstado, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':id_orden', $idOrden, PDO::PARAM_INT);
        $stmtUpdate->execute();
    }

    private function mapearEstadoParaVista(string $estadoDb): string
    {
        $estado = strtoupper(trim($estadoDb));

        if ($estado === '' || str_contains($estado, 'PEND') || str_contains($estado, 'PROCESO')) {
            return '1';
        }

        if (
            str_contains($estado, 'PROCESADA')
            || str_contains($estado, 'VALIDADA')
            || str_contains($estado, 'ENTREGADA')
            || str_contains($estado, 'COMPLET')
        ) {
            return '2';
        }

        if (str_contains($estado, 'CANCEL')) {
            return '3';
        }

        return '1';
    }
}
