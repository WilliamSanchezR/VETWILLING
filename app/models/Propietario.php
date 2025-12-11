<?php

require_once __DIR__ . '/../../config/database.php';

class Propietario
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /* ===============================
                    LISTAR
    =============================== */
    public function listar()
    {
        try {
            $sql = "SELECT * FROM propietario WHERE estado = 1 ORDER BY id_propietario DESC";
            $query = $this->conexion->prepare($sql);
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Propietario::listar → " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
            CONSULTAR POR ID
    =============================== */
    public function consultarPropietario($id)
    {
        try {
            $sql = "SELECT * FROM propietario WHERE id_propietario = :id LIMIT 1";
            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Propietario::consultarPropietario → " . $e->getMessage());
            return null;
        }
    }

    /* ===============================
                ACTUALIZAR
    =============================== */
    public function actualizar($data, $actualizarImagen)
    {
        try {
            // 1. Construir base sin WHERE
            $sql = "UPDATE propietario SET
            tipo_documento   = :tipo_documento,
            numero_documento = :numero_documento,
            nombres          = :nombres,
            apellidos        = :apellidos,
            telefono         = :telefono,
            direccion        = :direccion,
            id_veterinaria   = :id_veterinaria";

            // 2. Si hay imagen, agregamos campo
            if ($actualizarImagen) {
                $sql .= ", img_perfil = :img_perfil";
            }

            // 3. Ahora sí agregamos el WHERE
            $sql .= " WHERE id_propietario = :id_propietario";

            $query = $this->conexion->prepare($sql);

            // Bind de parámetros
            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $data['numero_documento']);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $data['telefono']);
            $query->bindParam(':direccion',        $data['direccion']);
            $query->bindParam(':id_veterinaria',   $data['id_veterinaria']);
            $query->bindParam(':id_propietario',   $data['id_propietario']);

            if ($actualizarImagen) {
                $query->bindParam(':img_perfil', $data['img_perfil']);
            }
            $success = $query->execute();
            if (!$success) {
                print_r($query->errorInfo());
            }
            return $success;


            // return $query->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::actualizar → " . $e->getMessage());
            return;
        }
    }

    /* ===============================
                ELIMINAR (INHABILITAR)
    =============================== */
    public function eliminar($id)
    {
        try {
            // Cambiar estado en vez de borrar
            $sql = "UPDATE propietario SET estado = 0 WHERE id_propietario = :id";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            return $query->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::eliminar → " . $e->getMessage());
            return false;
        }
    }
}
