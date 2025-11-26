<?php

require_once __DIR__ . '/../../config/database.php';

class Usuario
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


            $insertar = "INSERT INTO usuario(
                    id_usuario,
                    email,
                    password,
                    estado,
                    id_rol
                )
                VALUES(
                    :id_usuario,
                    :email,
                    :password_hash,
                    :estado,
                    :id_rol
                )";

            $resultado = $this->conexion->prepare($insertar);
            $resultado->bindParam(':id_usuario', $data['id_usuario']);
            $resultado->bindParam(':email', $data['email']);
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $resultado->bindParam(':password_hash', $passwordHash);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_rol', $data['id_rol']);
         

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en el instructor::registrar " . $e->getMessage());
            return false;
        }
    }

    // Funcuion para listar usuarios
    public function listar()
    {
        try {
            $listar = "SELECT id_usuario, email, estado, rol.id_rol AS id_rol rol.nombre as rol FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id_rol";
            $resultado = $this->conexion->prepare($listar);
            $resultado->execute();
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en el usuario::listar " . $e->getMessage());
            return [];
        }
    }

    // Funcion para consultar usuario por id
    public function consultarUsuario($id)
    {

        try {

            $consultar = "SELECT usuario.*,  rol.nombre AS nombre_rol FROM usuario INNER JOIN rol  ON usuario.id_rol = rol.id_rol WHERE usuario.id_usuario = :id LIMIT 1;";

            $resultado = $this->conexion->prepare($consultar);

            $resultado->bindParam(':id', $id);

            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            error_log("Error en el instructor::registrar " . $e->getMessage());
            return false;
        }
    }

    // Funcion para actualizar los usuarios 
    public function actualizarUsuario($data)
    {
        try {
            $actualizar = "UPDATE usuario SET
                            tipo_documento = :tipo_documento,
                            numero_documento = :numero_documento,
                            nombres = :nombres,
                            apellidos = :apellidos,
                            telefono = :telefono,
                            email = :email,
                            estado = :estado,
                            id_rol = :id_rol
                            WHERE id_usuario = :id_usuario
                            LIMIT 1";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id_usuario', $data['id_usuario']);
            $resultado->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultado->bindParam(':numero_documento', $data['numero_documento']);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_rol', $data['id_rol']);

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en el usuario::actualizarUsuario " . $e->getMessage());
            return false;
        }
    }

    //    Funcion para eliminar usuarios
    public function elimimarUsuario() {}
}
