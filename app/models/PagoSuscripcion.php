<?php

require_once __DIR__ . '/../../config/database.php';

class PagoSuscripcion
{
    private $conexion;
    private $columnasSuscripcion = null;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    public function obtenerCatalogoSuscripciones()
    {
        return [
            'basico' => [
                'slug' => 'basico',
                'titulo' => 'Plan Essential',
                'nombre' => 'Plan Essential',
                'descripcion' => 'Suscripcion mensual VetWilling - Plan Essential',
                'detalle' => 'Suscripcion mensual para clinicas en crecimiento',
                'monto' => 7900,
                'icono' => '🐾',
                'referencia' => 'SUS-BASICO-' . date('YmdHis'),
            ],
            'procare' => [
                'slug' => 'procare',
                'titulo' => 'Plan ProCare',
                'nombre' => 'Plan ProCare',
                'descripcion' => 'Suscripcion mensual VetWilling - Plan ProCare',
                'detalle' => 'Suscripcion mensual para operacion avanzada',
                'monto' => 14900,
                'icono' => '⭐',
                'referencia' => 'SUS-PROCARE-' . date('YmdHis'),
            ],
            'mastervet' => [
                'slug' => 'mastervet',
                'titulo' => 'Plan MasterVet',
                'nombre' => 'Plan MasterVet',
                'descripcion' => 'Suscripcion mensual VetWilling - Plan MasterVet',
                'detalle' => 'Suscripcion mensual completa para alta demanda',
                'monto' => 40900,
                'icono' => '👑',
                'referencia' => 'SUS-MASTERVET-' . date('YmdHis'),
            ],
        ];
    }

    public function obtenerProducto($origen, $plan = 'producto', $idSuscripcion = 0)
    {
        if ($origen === 'suscripcion') {
            if ((int) $idSuscripcion > 0) {
                $producto = $this->obtenerProductoSuscripcionPorId((int) $idSuscripcion);
                if ($producto !== null) {
                    return $producto;
                }
            }

            $planes = $this->obtenerCatalogoSuscripciones();
            return $planes[$plan] ?? reset($planes);
        }

        return [
            'slug' => 'producto',
            'titulo' => 'Producto VetWilling',
            'nombre' => 'Producto VetWilling',
            'descripcion' => 'Compra en tienda VetWilling',
            'detalle' => 'Producto general de la tienda VetWilling',
            'monto' => 150000,
            'icono' => '🐶',
            'referencia' => 'ORD-' . date('YmdHis'),
        ];
    }

    public function obtenerSuscripcionPorId(int $idSuscripcion)
    {
        $consulta = "SELECT s.id_suscripcion, s.id_plan, s.id_veterinaria, s.fecha_inicio, s.fecha_fin,
                            s.estado, s.ultimo_estado_pago, s.payment_id, s.external_reference, s.fecha_pago, s.auto_renovacion,
                            v.nombre AS nombre_veterinaria,
                            p.nombre AS nombre_plan,
                            p.descripcion AS descripcion_plan,
                            p.precio AS precio_plan
                     FROM suscripcion s
                     INNER JOIN veterinaria v ON v.id_veterinaria = s.id_veterinaria
                     LEFT JOIN plan p ON p.id_plan = s.id_plan
                     WHERE s.id_suscripcion = :id_suscripcion
                     LIMIT 1";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_suscripcion', $idSuscripcion, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function obtenerOCrearSuscripcionPendiente($plan, int $idVeterinaria): int
    {
        $idPlan = $this->resolverIdPlanDesdeEntrada($plan);
        if ($idVeterinaria <= 0 || $idPlan <= 0) {
            return 0;
        }

        $consulta = "SELECT id_suscripcion
                     FROM suscripcion
                     WHERE id_veterinaria = :id_veterinaria
                       AND id_plan = :id_plan
                       AND estado = 'pendiente'
                     ORDER BY id_suscripcion DESC
                     LIMIT 1";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindValue(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
        $stmt->bindValue(':id_plan', $idPlan, PDO::PARAM_INT);
        $stmt->execute();

        $idSuscripcion = (int) ($stmt->fetchColumn() ?: 0);
        if ($idSuscripcion > 0) {
            $this->asegurarReferenciaSuscripcionPorId($idSuscripcion);
            return $idSuscripcion;
        }

        $consultaInsert = "INSERT INTO suscripcion(id_veterinaria, id_plan, fecha_inicio, fecha_fin, estado, auto_renovacion)
                           VALUES(:id_veterinaria, :id_plan, NULL, NULL, 'pendiente', 0)";

        $insert = $this->conexion->prepare($consultaInsert);
        $insert->bindValue(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
        $insert->bindValue(':id_plan', $idPlan, PDO::PARAM_INT);
        $insert->execute();

        $nuevoId = (int) $this->conexion->lastInsertId();
        $this->asegurarReferenciaSuscripcionPorId($nuevoId);

        return $nuevoId;
    }

    public function asegurarReferenciaSuscripcionPorId(int $idSuscripcion): ?string
    {
        $suscripcion = $this->obtenerSuscripcionPorId($idSuscripcion);
        if (!$suscripcion) {
            return null;
        }

        $referencia = !empty($suscripcion['external_reference'])
            ? (string) $suscripcion['external_reference']
            : $this->generarReferenciaSuscripcion($suscripcion);

        $this->actualizarDatosInicialesSuscripcion($idSuscripcion, $referencia);

        return $referencia;
    }

    public function confirmarPagoSuscripcion(int $idSuscripcion, array $datosPago = [])
    {
        return $this->registrarResultadoPagoSuscripcion($idSuscripcion, 'approved', $datosPago);
    }

    public function registrarResultadoPagoSuscripcion(int $idSuscripcion, string $estadoPago, array $datosPago = [])
    {
        if ($idSuscripcion <= 0) {
            return [
                'ok' => false,
                'message' => 'Id de suscripción no válido.',
            ];
        }

        try {
            $this->conexion->beginTransaction();

            $suscripcion = $this->obtenerSuscripcionPorId($idSuscripcion);
            if (!$suscripcion) {
                throw new Exception('No se encontró la suscripción indicada.');
            }

            $estadoSuscripcion = $this->mapearEstadoSuscripcionDesdePago($estadoPago);
            $this->actualizarEstadoSuscripcion($idSuscripcion, $estadoSuscripcion, $estadoPago, $datosPago);

            $idVeterinaria = (int) $suscripcion['id_veterinaria'];
            $planNuevo = !empty($suscripcion['id_plan']) ? (int) $suscripcion['id_plan'] : null;
            $resultado = [
                'ok' => true,
                'message' => 'Estado de pago registrado correctamente.',
                'id_veterinaria' => $idVeterinaria,
                'plan_nuevo' => $planNuevo,
                'estado_suscripcion' => $estadoSuscripcion,
                'estado_pago' => $estadoPago,
                'activada' => false,
            ];

            if ($estadoSuscripcion === 'activa') {
                $planAnterior = $this->obtenerUltimoPlanHistorico($idVeterinaria);
                $tipoCambio = $this->determinarTipoCambio($planAnterior, $planNuevo);
                $motivo = $this->construirMotivoPago($datosPago);

                if (!$this->existeHistorialReciente($idVeterinaria, $planNuevo, $tipoCambio, $datosPago)) {
                    $this->insertarHistorialSuscripcion($idVeterinaria, $planAnterior, $planNuevo, $tipoCambio, $motivo);
                }

                $this->actualizarEstadoVeterinaria($idVeterinaria, 'activo');
                $this->actualizarEstadoUsuariosRepresentante($idVeterinaria, 'activo');

                $resultado['message'] = 'Pago confirmado y suscripción activada correctamente.';
                $resultado['activada'] = true;
            }

            $this->conexion->commit();
            return $resultado;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function obtenerProductoSuscripcionPorId(int $idSuscripcion)
    {
        $suscripcion = $this->obtenerSuscripcionPorId($idSuscripcion);
        if (!$suscripcion) {
            return null;
        }

        $slugPlan = $this->resolverSlugPlanDesdeSuscripcion($suscripcion);
        $planes = $this->obtenerCatalogoSuscripciones();
        $referenciaSuscripcion = $this->asegurarReferenciaSuscripcionPorId($idSuscripcion)
            ?: $this->generarReferenciaSuscripcion($suscripcion);

        $producto = $planes[$slugPlan] ?? [
            'slug' => $slugPlan ?: ('plan-' . (int) ($suscripcion['id_plan'] ?? 0)),
            'titulo' => $suscripcion['nombre_plan'] ?: 'Plan de suscripción',
            'nombre' => $suscripcion['nombre_plan'] ?: 'Plan de suscripción',
            'descripcion' => $suscripcion['descripcion_plan'] ?: 'Suscripción mensual VetWilling',
            'detalle' => $suscripcion['descripcion_plan'] ?: 'Suscripción mensual VetWilling',
            'monto' => !empty($suscripcion['precio_plan']) ? (float) $suscripcion['precio_plan'] : 0,
            'icono' => '🐾',
            'referencia' => $referenciaSuscripcion,
        ];

        if (!empty($suscripcion['nombre_plan'])) {
            $producto['titulo'] = $suscripcion['nombre_plan'];
            $producto['nombre'] = $suscripcion['nombre_plan'];
        }

        if (!empty($suscripcion['descripcion_plan'])) {
            $producto['descripcion'] = $suscripcion['descripcion_plan'];
            $producto['detalle'] = $suscripcion['descripcion_plan'];
        }

        if (!empty($suscripcion['precio_plan'])) {
            $producto['monto'] = (float) $suscripcion['precio_plan'];
        }

        $producto['referencia'] = $referenciaSuscripcion;
        $producto['id_veterinaria'] = (int) $suscripcion['id_veterinaria'];
        $producto['id_plan'] = (int) $suscripcion['id_plan'];

        if (!empty($suscripcion['nombre_veterinaria'])) {
            $producto['descripcion'] .= ' - ' . $suscripcion['nombre_veterinaria'];
            $producto['detalle'] .= ' · ' . $suscripcion['nombre_veterinaria'];
        }

        return $producto;
    }

    private function generarReferenciaSuscripcion(array $suscripcion)
    {
        return 'SUSC-' . (int) ($suscripcion['id_suscripcion'] ?? 0)
            . '-VET' . (int) ($suscripcion['id_veterinaria'] ?? 0)
            . '-PLAN' . (int) ($suscripcion['id_plan'] ?? 0);
    }

    private function resolverSlugPlanDesdeSuscripcion(array $suscripcion)
    {
        if (!empty($suscripcion['nombre_plan'])) {
            return $this->normalizarSlugPlan((string) $suscripcion['nombre_plan']);
        }

        return $this->obtenerSlugPlanPorId((int) ($suscripcion['id_plan'] ?? 0));
    }

    private function resolverIdPlanDesdeEntrada($plan): int
    {
        if (is_numeric($plan)) {
            return (int) $plan;
        }

        $slug = $this->normalizarSlugPlan((string) $plan);
        $mapa = [
            'basico' => 1,
            'procare' => 2,
            'mastervet' => 3,
        ];

        return $mapa[$slug] ?? 0;
    }

    private function obtenerSlugPlanPorId(int $idPlan)
    {
        $mapaPorId = [
            1 => 'basico',
            2 => 'procare',
            3 => 'mastervet',
        ];

        return $mapaPorId[$idPlan] ?? ('plan-' . $idPlan);
    }

    private function normalizarSlugPlan(string $nombrePlan)
    {
        $nombre = mb_strtolower(trim($nombrePlan));

        if (str_contains($nombre, 'essential') || str_contains($nombre, 'basico') || str_contains($nombre, 'básico')) {
            return 'basico';
        }

        if (str_contains($nombre, 'procare') || preg_match('/\bpro\b/u', $nombre)) {
            return 'procare';
        }

        if (str_contains($nombre, 'mastervet') || str_contains($nombre, 'master')) {
            return 'mastervet';
        }

        $slug = preg_replace('/[^a-z0-9]+/u', '-', $nombre);
        return trim((string) $slug, '-') ?: 'plan-general';
    }

    private function mapearEstadoSuscripcionDesdePago(string $estadoPago)
    {
        $estado = mb_strtolower(trim($estadoPago));

        return match ($estado) {
            'approved', 'success' => 'activa',
            'cancelled', 'canceled', 'cancelada' => 'cancelada',
            'expired', 'expirada' => 'expirada',
            default => 'pendiente',
        };
    }

    private function actualizarDatosInicialesSuscripcion(int $idSuscripcion, string $externalReference): void
    {
        $campos = [];
        $parametros = [
            ':id_suscripcion' => $idSuscripcion,
        ];

        if ($this->columnaSuscripcionExiste('ultimo_estado_pago')) {
            $campos[] = 'ultimo_estado_pago = COALESCE(NULLIF(ultimo_estado_pago, \'\'), :ultimo_estado_pago)';
            $parametros[':ultimo_estado_pago'] = 'pending';
        }

        if ($this->columnaSuscripcionExiste('external_reference')) {
            $campos[] = 'external_reference = COALESCE(NULLIF(external_reference, \'\'), :external_reference)';
            $parametros[':external_reference'] = $externalReference;
        }

        if (empty($campos)) {
            return;
        }

        $sql = 'UPDATE suscripcion SET ' . implode(', ', $campos) . ' WHERE id_suscripcion = :id_suscripcion';
        $stmt = $this->conexion->prepare($sql);

        foreach ($parametros as $clave => $valor) {
            if ($valor === null) {
                $stmt->bindValue($clave, null, PDO::PARAM_NULL);
            } elseif ($clave === ':id_suscripcion') {
                $stmt->bindValue($clave, (int) $valor, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($clave, $valor);
            }
        }

        $stmt->execute();
    }

    private function actualizarEstadoSuscripcion(int $idSuscripcion, string $estadoSuscripcion, string $estadoPago, array $datosPago = [])
    {
        $campos = ['estado = :estado'];
        $parametros = [
            ':estado' => $estadoSuscripcion,
            ':id_suscripcion' => $idSuscripcion,
        ];

        if ($this->columnaSuscripcionExiste('ultimo_estado_pago')) {
            $campos[] = 'ultimo_estado_pago = :ultimo_estado_pago';
            $parametros[':ultimo_estado_pago'] = mb_substr($estadoPago, 0, 50);
        }

        $paymentId = $datosPago['payment_id'] ?? $datosPago['id'] ?? null;
        if ($paymentId !== null && $paymentId !== '' && $this->columnaSuscripcionExiste('payment_id')) {
            $campos[] = 'payment_id = :payment_id';
            $parametros[':payment_id'] = (string) $paymentId;
        }

        $externalReference = $datosPago['external_reference'] ?? null;
        if ($externalReference !== null && $externalReference !== '' && $this->columnaSuscripcionExiste('external_reference')) {
            $campos[] = 'external_reference = :external_reference';
            $parametros[':external_reference'] = (string) $externalReference;
        }

        if ($estadoSuscripcion === 'activa') {
            $fechaActivacion = $this->normalizarFechaPago($datosPago['date_approved'] ?? null);

            if ($this->columnaSuscripcionExiste('fecha_inicio')) {
                $campos[] = 'fecha_inicio = COALESCE(fecha_inicio, :fecha_inicio)';
                $parametros[':fecha_inicio'] = $fechaActivacion;
            }

            if ($this->columnaSuscripcionExiste('fecha_fin')) {
                $campos[] = 'fecha_fin = CASE WHEN fecha_fin IS NULL OR fecha_fin < :fecha_inicio_base THEN DATE_ADD(:fecha_inicio_base, INTERVAL 30 DAY) ELSE fecha_fin END';
                $parametros[':fecha_inicio_base'] = $fechaActivacion;
            }

            if ($this->columnaSuscripcionExiste('fecha_pago')) {
                $campos[] = 'fecha_pago = :fecha_pago';
                $parametros[':fecha_pago'] = $fechaActivacion;
            }
        }

        $sql = 'UPDATE suscripcion SET ' . implode(', ', $campos) . ' WHERE id_suscripcion = :id_suscripcion';
        $stmt = $this->conexion->prepare($sql);

        foreach ($parametros as $clave => $valor) {
            if ($valor === null) {
                $stmt->bindValue($clave, null, PDO::PARAM_NULL);
            } elseif ($clave === ':id_suscripcion') {
                $stmt->bindValue($clave, (int) $valor, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($clave, $valor);
            }
        }

        $stmt->execute();
    }

    private function normalizarFechaPago($fecha): string
    {
        if (empty($fecha)) {
            return date('Y-m-d H:i:s');
        }

        try {
            return (new DateTime((string) $fecha))->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }

    private function columnaSuscripcionExiste(string $nombreColumna)
    {
        if ($this->columnasSuscripcion === null) {
            $this->columnasSuscripcion = [];
            $resultado = $this->conexion->query('SHOW COLUMNS FROM suscripcion');

            foreach ($resultado->fetchAll(PDO::FETCH_ASSOC) as $columna) {
                $this->columnasSuscripcion[] = $columna['Field'];
            }
        }

        return in_array($nombreColumna, $this->columnasSuscripcion, true);
    }

    private function obtenerUltimoPlanHistorico(int $idVeterinaria)
    {
        $consulta = "SELECT plan_nuevo
                     FROM historial_suscripcion
                     WHERE id_veterinaria = :id_veterinaria
                     ORDER BY fecha DESC, id_historial DESC
                     LIMIT 1";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
        $stmt->execute();

        $plan = $stmt->fetchColumn();

        return ($plan !== false && $plan !== null) ? (int) $plan : null;
    }

    private function determinarTipoCambio($planAnterior, $planNuevo)
    {
        if (empty($planAnterior) || empty($planNuevo)) {
            return 'admin';
        }

        if ((int) $planNuevo > (int) $planAnterior) {
            return 'upgrade';
        }

        if ((int) $planNuevo < (int) $planAnterior) {
            return 'downgrade';
        }

        return 'admin';
    }

    private function construirMotivoPago(array $datosPago)
    {
        $fragmentos = ['Activacion de suscripcion por pago aprobado en Mercado Pago'];

        $paymentId = $datosPago['payment_id'] ?? $datosPago['id'] ?? null;
        $referencia = $datosPago['external_reference'] ?? null;

        if (!empty($paymentId)) {
            $fragmentos[] = 'Pago ID: ' . $paymentId;
        }

        if (!empty($referencia)) {
            $fragmentos[] = 'Ref: ' . $referencia;
        }

        return mb_substr(implode(' | ', $fragmentos), 0, 255);
    }

    private function existeHistorialReciente(int $idVeterinaria, $planNuevo, string $tipoCambio, array $datosPago)
    {
        $paymentId = $datosPago['payment_id'] ?? $datosPago['id'] ?? null;

        if (!empty($paymentId)) {
            $consulta = "SELECT id_historial
                         FROM historial_suscripcion
                         WHERE id_veterinaria = :id_veterinaria
                           AND motivo LIKE :motivo
                         LIMIT 1";

            $stmt = $this->conexion->prepare($consulta);
            $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
            $motivoBusqueda = '%Pago ID: ' . $paymentId . '%';
            $stmt->bindParam(':motivo', $motivoBusqueda, PDO::PARAM_STR);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        }

        $consulta = "SELECT id_historial
                     FROM historial_suscripcion
                     WHERE id_veterinaria = :id_veterinaria
                       AND plan_nuevo = :plan_nuevo
                       AND tipo_cambio = :tipo_cambio
                       AND fecha >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                     LIMIT 1";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
        $stmt->bindParam(':plan_nuevo', $planNuevo, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_cambio', $tipoCambio, PDO::PARAM_STR);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    private function insertarHistorialSuscripcion(int $idVeterinaria, $planAnterior, $planNuevo, string $tipoCambio, string $motivo)
    {
        $consulta = "INSERT INTO historial_suscripcion (id_veterinaria, plan_anterior, plan_nuevo, tipo_cambio, motivo)
                     VALUES (:id_veterinaria, :plan_anterior, :plan_nuevo, :tipo_cambio, :motivo)";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);

        if ($planAnterior === null) {
            $stmt->bindValue(':plan_anterior', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':plan_anterior', (int) $planAnterior, PDO::PARAM_INT);
        }

        if ($planNuevo === null) {
            $stmt->bindValue(':plan_nuevo', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':plan_nuevo', (int) $planNuevo, PDO::PARAM_INT);
        }

        $stmt->bindParam(':tipo_cambio', $tipoCambio, PDO::PARAM_STR);
        $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
        $stmt->execute();
    }

    private function actualizarEstadoVeterinaria(int $idVeterinaria, string $estado)
    {
        $consulta = "UPDATE veterinaria
                     SET estado = :estado
                     WHERE id_veterinaria = :id_veterinaria";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function actualizarEstadoUsuariosRepresentante(int $idVeterinaria, string $estado)
    {
        $consulta = "UPDATE usuario u
                     INNER JOIN representante_legal rl ON rl.id_usuario = u.id_usuario
                     SET u.estado = :estado
                     WHERE rl.id_veterinaria = :id_veterinaria";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':id_veterinaria', $idVeterinaria, PDO::PARAM_INT);
        $stmt->execute();
    }
}
