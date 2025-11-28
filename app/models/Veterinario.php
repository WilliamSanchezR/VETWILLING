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

            $sql = "SELECT 
                    v.*,
                    u.email,
                    u.estado,
                    u.id_rol,
                    r.nombre AS nombre_rol,
                    vet.nombre AS nombre_veterinaria
                FROM veterinario v
                INNER JOIN usuario u 
                    ON v.id_usuario = u.id_usuario
                INNER JOIN rol r
                    ON u.id_rol = r.id_rol
                INNER JOIN veterinaria vet
                    ON v.id_veterinaria = vet.id_veterinaria
                WHERE v.id_veterinaria = :id_veterinaria
                ORDER BY v.id_veterinario ASC";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $query->execute();

            return $query->fetchAll();
        } catch (PDOException $e) {
            die("Error en veterinario::listar " . $e->getMessage());
        }
    }

    public function listarVeterinario($id)
    {
        try {

            $consultar = "
            SELECT 
                v.id_veterinario,
                v.nombres,
                v.apellidos,
                v.tipo_documento,
                v.numero_documento,
                v.telefono,
                v.id_veterinaria,
                
                u.id_usuario,
                u.email,
                u.estado,
                u.id_rol,

                r.nombre AS nombre_rol,
                vet.nombre AS nombre_veterinaria

            FROM veterinario v
            INNER JOIN usuario u 
                ON v.id_usuario = u.id_usuario
            INNER JOIN rol r
                ON u.id_rol = r.id_rol
            INNER JOIN veterinaria vet
                ON v.id_veterinaria = vet.id_veterinaria

            WHERE u.id_usuario = :id
            LIMIT 1
        ";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en veterinario::listarVeterinario -> " . $e->getMessage());
        }
    }


    public function actualizar($data)
    {
        try {

            // 1. Actualizar tabla usuario
            $sqlUsuario = "
            UPDATE usuario 
            SET 
                email = :email,
                estado = :estado,
                id_rol = :id_rol
            WHERE id_usuario = :id_usuario
        ";

            $queryUsuario = $this->conexion->prepare($sqlUsuario);
            $queryUsuario->bindParam(':email', $data['email']);
            $queryUsuario->bindParam(':estado', $data['estado']);
            $queryUsuario->bindParam(':id_rol', $data['id_rol']);
            $queryUsuario->bindParam(':id_usuario', $data['id_usuario']);
            $queryUsuario->execute();

            // 2. Actualizar tabla veterinario
            $sqlVet = "
            UPDATE veterinario 
            SET 
                nombres = :nombres,
                apellidos = :apellidos,
                tipo_documento = :tipo_documento,
                numero_documento = :numero_documento,
                telefono = :telefono
            WHERE id_usuario = :id_usuario
        ";

            $queryVet = $this->conexion->prepare($sqlVet);
            $queryVet->bindParam(':nombres', $data['nombres']);
            $queryVet->bindParam(':apellidos', $data['apellidos']);
            $queryVet->bindParam(':tipo_documento', $data['tipo_documento']);
            $queryVet->bindParam(':numero_documento', $data['numero_documento']);
            $queryVet->bindParam(':telefono', $data['telefono']);
            $queryVet->bindParam(':id_usuario', $data['id_usuario']);

            return $queryVet->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::actualizar -> " . $e->getMessage());
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
