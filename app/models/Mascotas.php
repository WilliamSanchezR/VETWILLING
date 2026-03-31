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
     * ACTUALIZAR MASCOTA
     */
    /**
     * ACTUALIZAR MASCOTA CON EDAD MEJORADA
     */
    public function actualizar(array $data)
    {
        try {
            // Campos base que siempre se actualizan
            $campos = "
            nombre = :nombre,
            especie = :especie,
            raza = :raza,
            edad_numero = :edad_numero,
            edad_unidad = :edad_unidad,
            sexo = :sexo
        ";

            // Agregar imagen solo si se subió una nueva
            if (!empty($data['img_mascota'])) {
                $campos .= ", img_mascota = :img_mascota";
            }

            $sql = "UPDATE paciente SET $campos WHERE id_paciente = :id_paciente AND estado = 'Activo'";

            $stmt = $this->conexion->prepare($sql);

            // Vincular parámetros base
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':especie', $data['especie']);
            $stmt->bindParam(':raza', $data['raza']);

            // ✅ VINCULAR EDAD CON UNIDAD
            $stmt->bindParam(':edad_numero', $data['edad_numero'], PDO::PARAM_INT);
            $stmt->bindParam(':edad_unidad', $data['edad_unidad'], PDO::PARAM_STR);

            $stmt->bindParam(':sexo', $data['sexo']);
            $stmt->bindParam(':id_paciente', $data['id_paciente'], PDO::PARAM_INT);

            // Vincular imagen solo si existe
            if (!empty($data['img_mascota'])) {
                $stmt->bindParam(':img_mascota', $data['img_mascota']);
            }

            $resultado = $stmt->execute();

            if (!$resultado) {
                error_log("Error PDO actualizar: " . print_r($stmt->errorInfo(), true));
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log('❌ Error Mascota::actualizar → ' . $e->getMessage());
            error_log('❌ Datos: ' . print_r($data, true));
            return false;
        }
    }


    /**
     * ELIMINAR MASCOTA
     */
    public function eliminar(int $id)
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener imagen antes de inactivar
            $stmt = $this->conexion->prepare(
                "SELECT img_mascota FROM paciente WHERE id_paciente = :id"
            );
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $mascota = $stmt->fetch(PDO::FETCH_ASSOC);

            $sqlExisteAsignacion = "SELECT COUNT(*)
                                    FROM information_schema.TABLES
                                    WHERE TABLE_SCHEMA = DATABASE()
                                      AND TABLE_NAME = 'paciente_profesional_asignacion'";
            $stmtExiste = $this->conexion->prepare($sqlExisteAsignacion);
            $stmtExiste->execute();
            $existeAsignacion = ((int) $stmtExiste->fetchColumn() > 0);

            if ($existeAsignacion) {
                // Cerrar asignaciones activas asociadas al paciente.
                $cerrarAsignacion = $this->conexion->prepare(
                    "UPDATE paciente_profesional_asignacion
                     SET estado = 'Inactivo', fecha_fin = CURRENT_TIMESTAMP
                     WHERE id_paciente = :id AND estado = 'Activo'"
                );
                $cerrarAsignacion->bindParam(':id', $id, PDO::PARAM_INT);
                $cerrarAsignacion->execute();
            }

            // 2. Inactivar mascota (borrado lógico)
            $delete = $this->conexion->prepare(
                "UPDATE paciente
                 SET estado = 'Inactivo'
                 WHERE id_paciente = :id
                 AND estado = 'Activo'"
            );
            $delete->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado = $delete->execute();

            if (!$resultado) {
                $this->conexion->rollBack();
                return false;
            }

            $this->conexion->commit();

            // 3. Eliminar imagen del servidor
            // if ($resultado && !empty($mascota['img_mascota'])) {
            //     $ruta = BASE_PATH . "/public/uploads/mascotas/" . $mascota['img_mascota'];
            //     if (file_exists($ruta)) {
            //         unlink($ruta);
            //     }
            // }

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
