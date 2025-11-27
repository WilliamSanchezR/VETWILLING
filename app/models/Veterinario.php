<?php

// Importamos las dependencias

require_once __DIR__ . '/../../config/database.php';

class Veterinario
{

    private $conexion;

    public function __construct()
    {

        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function registrar($data)
    {

        try {
            $insertar = "INSERT INTO usuario (tipo_documento, numero_documento, nombres, apellidos, telefono, email, password_hash, estado, tipo_usuario, img_perfil, id_rol, id_veterinaria) VALUES (:tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :email, :password_hash, :estado, :tipo_usuario, :img_perfil, :id_rol, :id_veterinaria)";

            // Preparamos la acciona a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($insertar);
            $resultado->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultado->bindParam(':numero_documento', $data['numero_documento']);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);
            $passwordHash = password_hash($data['password_hash'], PASSWORD_DEFAULT);
            $resultado->bindParam(':password_hash', $passwordHash);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':tipo_usuario', $data['tipo_usuario']);
            $resultado->bindParam(':img_perfil', $data['img_perfil']);
            $resultado->bindParam(':id_rol', $data['id_rol']);
            $resultado->bindParam(':id_veterinaria', $data['id_veterinaria'], '2');

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::registrar" . $e->getMessage());
            return false;
        }
    }

    public function listar($id_veterinaria)
    {

        try {

            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.fecha_contratacion, vet.nombre AS nombre_veterinaria FROM usuario u INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE v.id_veterinaria = :id_veterinaria  AND u.id_rol = 2 ORDER BY u.id_usuario ASC";

            // preparamos la accion a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetchALL();
        } catch (PDOException $e) {
            die("Error en veterinario::listar" . $e->getMessage());
            return [];
        }
    }

    public function listarVeterinario($id)
    {

        try {

            $consultar = "SELECT * FROM usuario WHERE id_usuario = :id LIMIT 1";

            // Preparamos la accion a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            die("Error en veterinario::listar" . $e->getMessage());
        }
    }

    public function actualizar($data)
    {
        try {
            $actualizar = "UPDATE usuario SET tipo_documento = :tipo_documento, numero_documento = :numero_documento, nombres = :nombres, apellidos = :apellidos, telefono = :telefono, email = :email, id_rol = :id_rol, estado = :estado WHERE id_usuario = :id_usuario";

            // Preparamos la acciona a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id_usuario', $data['id_usuario']);
            $resultado->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultado->bindParam(':numero_documento', $data['numero_documento']);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':id_rol', $data['id_rol']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':email', $data['email']);

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::actualizar" . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id)
    {
        try {

            $eliminar = "DELETE FROM usuario WHERE id_usuario=:id";

            // preparamos la accion a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($eliminar);
            $resultado->bindParam(':id', $id);
            return $resultado->execute();
        } catch (PDOException $e) {
            die("Error en veterinario::eliminar" . $e->getMessage());
            return [];
        }
    }
}
