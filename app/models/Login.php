<?php

require_once __DIR__ . '/../../config/database.php';

class Login
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function autenticar($email, $password)
    {

        try {
            $consultar = "SELECT * FROM usuario WHERE email = :email AND estado = 'activo' LIMIT 1";
            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':email', $email);
            $resultado->execute();

            $user = $resultado->fetch();

            if (!$user) {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }

            // Verificacion de la contraseña incriptada

            if (!password_verify($password, $user['password_hash'])) {
                return ['error' => 'Contraseña incorrecta'];
            }

            // Obtenemos datos adicionales según cada rol, mediante switch case 


            switch ($user['id_rol']) {
                case 1: // Representante
                    $consultar = "SELECT  id_veterinaria, nombres, apellidos, telefono, img_perfil, nivel_acceso FROM representante_legal WHERE id_usuario = :id";
                    break;

                case 2: // Veterinario
                    $consultar = "SELECT id_veterinaria, nombres, apellidos, telefono, numero_licencia_profesional, img_perfil FROM veterinario WHERE id_usuario = :id";
                    break;

                case 3: // Propietario
                    $consultar = "SELECT id_veterinaria, nombres, apellidos, telefono, direccion, img_perfil FROM propietario WHERE id_usuario = :id";
                    break;

                default:
                    return ['error' => 'Rol de usuario no válido'];
            }

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $user['id_usuario']);
            $resultado->execute();
            $perfil = $resultado->fetch();

            return [
                'id_usuario' => $user['id_usuario'],
                'id_rol' => $user['id_rol'],
                'email' => $user['email'],
                'password_hash' => $user['password_hash'],
                'estado' => $user['estado'],
                'id_veterinaria' => $perfil['id_veterinaria'],
                'nombres' => $perfil['nombres'],
                'apellidos' => $perfil['apellidos']
            ];
        } catch (PDOException $e) {
            error_log("Error en el modelo login: " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }
}
