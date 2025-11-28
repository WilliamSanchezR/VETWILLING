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
        // Inicia transacción para asegurar que ambas inserciones se hagan correctamente
        $this->conexion->beginTransaction();

        // 1. INSERTAR EN TABLA USUARIO
        $insertar = "INSERT INTO usuario (
            email, password_hash, estado, id_rol, fecha_creacion
        ) VALUES (
            :email, :password_hash, :estado, :id_rol, NOW()
        )";

        $resultado = $this->conexion->prepare($insertar);

        $resultado->bindParam(':email', $data['email']);
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $resultado->bindParam(':password_hash', $passwordHash);
        $resultado->bindParam(':estado', $data['estado']);
        $resultado->bindParam(':id_rol', $data['id_rol']);

        $resultado->execute();

        // 2. OBTENER EL ID DEL USUARIO RECIÉN INSERTADO
        $idUsuario = $this->conexion->lastInsertId();

        // 3. INSERTAR EN TABLA VETERINARIO (corregido: agregado id_usuario en columnas)
        $sqlVet = "INSERT INTO veterinario (
            id_usuario, id_veterinaria, tipo_documento, numero_documento, 
            nombres, apellidos, telefono, img_perfil, numero_licencia_profesional, 
            fecha_creacion
        ) VALUES (
            :id_usuario, :id_veterinaria, :tipo_documento, :numero_documento, 
            :nombres, :apellidos, :telefono, :img_perfil, :numero_licencia_profesional, 
            NOW()
        )";

        $stmtVet = $this->conexion->prepare($sqlVet);

        $stmtVet->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmtVet->bindParam(':id_veterinaria', $data['id_veterinaria'], PDO::PARAM_INT);
        $stmtVet->bindParam(':tipo_documento', $data['tipo_documento']);
        $stmtVet->bindParam(':numero_documento', $data['numero_documento']);
        $stmtVet->bindParam(':nombres', $data['nombres']);
        $stmtVet->bindParam(':apellidos', $data['apellidos']);
        $stmtVet->bindParam(':telefono', $data['telefono']);
        $stmtVet->bindParam(':img_perfil', $data['img_perfil']);
        $stmtVet->bindParam(':numero_licencia_profesional', $data['numero_licencia_profesional']);

        $stmtVet->execute();

        // Si todo sale bien, confirmamos la transacción
        $this->conexion->commit();
        return true;

    } catch (PDOException $e) {
        // Si hay error, revertimos todo
        $this->conexion->rollBack();
        error_log("Error en registrar Veterinario: " . $e->getMessage());
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
