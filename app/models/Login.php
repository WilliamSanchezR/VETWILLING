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
            $consultar = "SELECT * FROM usuario WHERE email = :email LIMIT 1";
            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':email', $email);
            $resultado->execute();

            $user = $resultado->fetch();

            if (!$user) {
                return ['error' => 'Usuario no encontrado'];
            }

            // Verificacion de la contraseña incriptada

            if (!password_verify($password, $user['password_hash'])) {
                return ['error' => 'Contraseña incorrecta'];
            }

            // Verificar el estado del usuario y mostrar mensajes de alerta correspondientes
            $estadoUsuario = mb_strtolower(trim((string) $user['estado']));
            if ($estadoUsuario === 'pendiente') {
                return [
                    'error' => 'Tu cuenta esta pendiente de aprobacion. Debes esperar la activacion por parte del administrador.',
                    'tipo' => 'warning',
                    'titulo' => 'Cuenta pendiente de aprobacion'
                ];
            }

            // Si el estado es inactivo, se muestra un mensaje de error indicando que la cuenta está inactiva
            if ($estadoUsuario !== 'activo') {
                return ['error' => 'Tu cuenta se encuentra inactiva. Comunicate con el administrador.'];
            }

            // Obtenemos datos adicionales según cada rol, mediante switch case 


            switch ($user['id_rol']) {
                case 1: // Administrador
                    $consultar = "SELECT nombres, apellidos, telefono, img_perfil, nivel_acceso FROM administrador WHERE id_usuario = :id";
                    break;

                case 2: // Veterinario
                    $consultar = "SELECT nombres, apellidos, telefono, registro_medico, img_perfil,     GROUP_CONCAT(DISTINCT  pv.id_veterinaria SEPARATOR ', ') as id_veterinaria
                        FROM profesional p
                        INNER JOIN profesional_veterinaria pv On p.id_profesional = pv.id_profesional
                        WHERE p.id_usuario = :id AND pv.estado = 'Activo'";
                    break;

                case 3: // Propietario
                    $consultar = "SELECT id_veterinaria, nombres, apellidos, telefono, direccion, img_perfil FROM propietario WHERE id_usuario = :id";
                    break;

                case 4: // Representante
                    $consultar = "SELECT  id_veterinaria, nombres, apellidos, telefono, img_perfil, nivel_acceso FROM representante_legal WHERE id_usuario = :id";
                    break;

                default:
                    return ['error' => 'Rol de usuario no válido'];
            }

            // Registrar la fecha de ingreso al usuario
            $updateLoginTime = "UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = :id";
            $stmtUpdate = $this->conexion->prepare($updateLoginTime);
            $stmtUpdate->bindParam(':id', $user['id_usuario']);
            $stmtUpdate->execute();                    
            

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
                'id_veterinaria' => $user['id_rol'] == 1 ? null : $perfil['id_veterinaria'],
                'nombres' => $perfil['nombres'],
                'apellidos' => $perfil['apellidos']
            ];
        } catch (PDOException $e) {
            error_log("Error en el modelo login: " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }


    
}
