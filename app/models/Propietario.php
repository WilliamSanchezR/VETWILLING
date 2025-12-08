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
                REGISTRAR
       =============================== */
    public function registrar($data)
    {
        try {

            $sql = "INSERT INTO propietario 
                    (id_usuario, id_veterinaria, tipo_documento, numero_documento, nombres, apellidos, telefono, direccion, img_perfil, fecha_nacimiento)
                    VALUES 
                    (:id_usuario, :id_veterinaria, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :direccion, :img_perfil, :fecha_nacimiento)";

            $query = $this->conexion->prepare($sql);

            $query->bindParam(':id_usuario',       $data['id_usuario']);
            $query->bindParam(':id_veterinaria',   $data['id_veterinaria']);
            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $data['numero_documento']);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $data['telefono']);
            $query->bindParam(':direccion',        $data['direccion']);
            $query->bindParam(':img_perfil',       $data['img_perfil']);
            $query->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);

            return $query->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::registrar → " . $e->getMessage());
            return false;
        }
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
    public function actualizar($data, $actualizarImagen = false)
    {
        try {

            if ($actualizarImagen) {
                $sql = "UPDATE propietario SET
                tipo_documento   = :tipo_documento,
                numero_documento = :numero_documento,
                nombres          = :nombres,
                apellidos        = :apellidos,
                telefono         = :telefono,
                direccion        = :direccion,
                id_veterinaria   = :id_veterinaria,
                fecha_nacimiento = :fecha_nacimiento,
                img_perfil       = :img_perfil
                WHERE id_propietario = :id_propietario";
            } else {
                $sql = "UPDATE propietario SET
                tipo_documento   = :tipo_documento,
                numero_documento = :numero_documento,
                nombres          = :nombres,
                apellidos        = :apellidos,
                telefono         = :telefono,
                direccion        = :direccion,
                id_veterinaria   = :id_veterinaria,
                fecha_nacimiento = :fecha_nacimiento
                WHERE id_propietario = :id_propietario";
            }

            $query = $this->conexion->prepare($sql);

            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $data['numero_documento']);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $data['telefono']);
            $query->bindParam(':direccion',        $data['direccion']);
            $query->bindParam(':id_veterinaria',   $data['id_veterinaria']);
            $query->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
            $query->bindParam(':id_propietario',   $data['id_propietario']);

            if ($actualizarImagen) {
                $query->bindParam(':img_perfil', $data['img_perfil']);
            }

            return $query->execute();
        } catch (PDOException $e) {
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
