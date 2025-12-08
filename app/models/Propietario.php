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
            $sql = "SELECT * FROM propietario ORDER BY id_propietario DESC";
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
            $query->bindParam(':id', $id);
            $query->execute();

            return $query->fetch();
        } catch (PDOException $e) {
            error_log("Error en Propietario::consultarPropietario → " . $e->getMessage());
            return null;
        }
    }

    /* ===============================
                ACTUALIZAR
       =============================== */
    public function actualizar($data, $actualizarImagen = false)
    {
        try {
            // 1. Construir la consulta base
            $sql = "UPDATE propietario SET
            tipo_documento   = :tipo_documento,
            numero_documento = :numero_documento,
            nombres          = :nombres,
            apellidos        = :apellidos,
            telefono         = :telefono,
            direccion        = :direccion,
            id_veterinaria   = :id_veterinaria,";

            // 2. Agregar la imagen SOLO si se envió una nueva
            if ($actualizarImagen === true) {
                $sql .= " img_perfil = :img_perfil,";
            }

            // El último campo siempre tendrá una coma al final en este punto.
            // La eliminamos (ej. de 'veterinaria,' a 'veterinaria')
            $sql = rtrim($sql, ',');

            // 3. Agregar la cláusula WHERE
            $sql .= " WHERE id_propietario = :id_propietario";


            // Preparación de la consulta
            $query = $this->conexion->prepare($sql);

            // Bind de parámetros (obligatorios)
            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $data['numero_documento']);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $data['telefono']);
            $query->bindParam(':direccion',        $data['direccion']);
            $query->bindParam(':id_veterinaria',   $data['id_veterinaria']);
            $query->bindParam(':id_propietario',   $data['id_propietario']);

            // Bind de la imagen (condicional)
            if ($actualizarImagen === true) {
                $query->bindParam(':img_perfil', $data['img_perfil']);
            }

            return $query->execute();
        } catch (PDOException $e) {
            // Aquí puedes usar un logger en lugar de die() en producción
            die("Error en Propietario::actualizar → " . $e->getMessage());
        }
    }

    /* ===============================
                ELIMINAR
       =============================== */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM propietario WHERE id_propietario = :id";
            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            return $query->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::eliminar → " . $e->getMessage());
            return false;
        }
    }
}
