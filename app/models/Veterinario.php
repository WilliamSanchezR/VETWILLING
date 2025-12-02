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

            // 1. Primero insertamos en la tabla usuario
            $registrar = "INSERT INTO usuario (email, password_hash, estado, id_rol) VALUES (:email, :password_hash, :estado, :id_rol)";

            $resultado = $this->conexion->prepare($registrar);
            $resultado->bindParam(':email', $data['email']);
            $passwordHash = password_hash($data['password_hash'], PASSWORD_DEFAULT);
            $resultado->bindParam(':password_hash', $passwordHash);
            $estado = $data['estado'] ?? 'activo';
            $resultado->bindParam(':estado', $estado);
            $id_rol = 2; // Rol de veterinario
            $resultado->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            $resultado->execute();

            // Obtenemos el ID del usuario recién creado
            $id_usuario = $this->conexion->lastInsertId();

            // 2. Luego insertamos en la tabla veterinario
            $insertarVeterinario = "INSERT INTO veterinario (id_usuario, tipo_documento, numero_documento, nombres, apellidos,  telefono, img_perfil, numero_licencia_profesional, id_veterinaria, fecha_contratacion)  VALUES  (:id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos,  :telefono, :img_perfil, :numero_licencia_profesional, :id_veterinaria, :fecha_contratacion)";

            $resultadoVeterinario = $this->conexion->prepare($insertarVeterinario);
            $resultadoVeterinario->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $resultadoVeterinario->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultadoVeterinario->bindParam(':numero_documento', $data['numero_documento']);
            $resultadoVeterinario->bindParam(':nombres', $data['nombres']);
            $resultadoVeterinario->bindParam(':apellidos', $data['apellidos']);
            $resultadoVeterinario->bindParam(':telefono', $data['telefono']);
            $img_perfil = $data['img_perfil'] ?? 'foto_default.png';
            $resultadoVeterinario->bindParam(':img_perfil', $img_perfil);
            $resultadoVeterinario->bindParam(':numero_licencia_profesional', $data['numero_licencia_profesional']);
            $resultadoVeterinario->bindParam(':id_veterinaria', $data['id_veterinaria'], PDO::PARAM_INT);
            $fecha_contratacion = $data['fecha_contratacion'] ?? date('Y-m-d');
            $resultadoVeterinario->bindParam(':fecha_contratacion', $fecha_contratacion);
            $resultadoVeterinario->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Error en veterinario::registrar - " . $e->getMessage());
            return false;
        }
    }

    public function listar($id_veterinaria)
    {
        try {
            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.fecha_contratacion,  r.nombre AS nombre_rol, vet.nombre AS nombre_veterinaria FROM usuario u  INNER JOIN rol r ON u.id_rol = r.id_rol INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE v.id_veterinaria = :id_veterinaria  AND u.id_rol = 2 ORDER BY v.nombres ASC, v.apellidos ASC";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en veterinario::listar - " . $e->getMessage());
            return [];
        }
    }

    public function listarVeterinario($id)
    {
        try {
            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, u.id_rol, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, r.nombre AS nombre_rol, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.id_veterinaria, v.fecha_contratacion, vet.nombre AS nombre_veterinaria FROM usuario u INNER JOIN rol r ON u.id_rol = r.id_rol INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE u.id_usuario = :id  LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en veterinario::listarVeterinario - " . $e->getMessage());
            return false;
        }
    }


    public function actualizar($data)
    {
        try {
            // Iniciamos una transacción
            $this->conexion->beginTransaction();

            // 1. Actualizamos la tabla usuario
            $actualizarUsuario = "UPDATE usuario SET email = :email, estado = :estado WHERE id_usuario = :id_usuario";

            $resultadoUsuario = $this->conexion->prepare($actualizarUsuario);
            $resultadoUsuario->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $resultadoUsuario->bindParam(':email', $data['email']);
            $resultadoUsuario->bindParam(':estado', $data['estado']);
            $resultadoUsuario->execute();

            // 2. Actualizamos la tabla veterinario
            $actualizarVeterinario = "UPDATE veterinario  SET tipo_documento = :tipo_documento,  numero_documento = :numero_documento,  nombres = :nombres,  apellidos = :apellidos,  telefono = :telefono, numero_licencia_profesional = :numero_licencia_profesional WHERE id_usuario = :id_usuario";

            $resultadoVeterinario = $this->conexion->prepare($actualizarVeterinario);
            $resultadoVeterinario->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $resultadoVeterinario->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultadoVeterinario->bindParam(':numero_documento', $data['numero_documento']);
            $resultadoVeterinario->bindParam(':nombres', $data['nombres']);
            $resultadoVeterinario->bindParam(':apellidos', $data['apellidos']);
            $resultadoVeterinario->bindParam(':telefono', $data['telefono']);
            $resultadoVeterinario->bindParam(':numero_licencia_profesional', $data['numero_licencia_profesional']);
            $resultadoVeterinario->execute();

            // Confirmamos la transacción
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            // Si hay error, revertimos los cambios
            $this->conexion->rollBack();
            error_log("Error en veterinario::actualizar - " . $e->getMessage());
            return false;
        }
    }



    public function eliminar($id)
    {
        try {
            // Al tener CASCADE en las foreign keys, solo eliminamos el usuario
            // y automáticamente se eliminará el registro en veterinario
            $eliminar = "DELETE FROM usuario WHERE id_usuario = :id";

            $resultado = $this->conexion->prepare($eliminar);
            $resultado->bindParam(':id', $id, PDO::PARAM_INT);
            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::eliminar - " . $e->getMessage());
            return false;
        }
    }

    // Método adicional para actualizar la imagen de perfil
    public function actualizarImagen($id_usuario, $img_perfil)
    {
        try {
            $actualizar = "UPDATE veterinario 
                        SET img_perfil = :img_perfil 
                        WHERE id_usuario = :id_usuario";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $resultado->bindParam(':img_perfil', $img_perfil);
            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::actualizarImagen - " . $e->getMessage());
            return false;
        }
    }
}
