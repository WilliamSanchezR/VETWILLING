<?php

require_once __DIR__ . '/../../config/database.php';

class Perfil
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // *Esta funcion se duplica por cada rol

    public function mostrarPerfil($id)
    {

        try {

            $consultar = "SELECT id_rol FROM usuario WHERE id_usuario = :id LIMIT 1";

            // Preparamos la accion a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            $user = $resultado->fetch();

            if (!$user['id_rol']) {
                return null;
            }

            try {
                switch ($user['id_rol']) {

                    case 1:
                        $consultar = "SELECT u.id_usuario, u.email, u.estado, a.nombres, a.apellidos, a.nivel_acceso, a.img_perfil FROM usuario AS u INNER JOIN administrador AS a ON u.id_usuario = a.id_usuario WHERE u.id_usuario = :id";
                        break;

                    case 2:
                        $consultar = "SELECT u.id_usuario, u.email, v.nombres, v.apellidos, v.telefono, v.numero_licencia_profesional FROM usuario AS u INNER JOIN veterinario AS v ON u.id_usuario = v.id_usuario WHERE u.id_usuario = :id";
                        break;

                    case 3:
                        $consultar = "SELECT  u.id_usuario, u.email, p.nombres, p.apellidos, p.telefono, p.direccion, p.img_perfil FROM usuario AS u INNER JOIN propietario AS p ON u.id_usuario = p.id_usuario WHERE u.id_usuario = :id";
                        break;

                    default:
                        return null;
                        break;
                }

                $resultado = $this->conexion->prepare($consultar);
                $resultado->bindParam(':id', $id);
                $resultado->execute();

                return $resultado->fetch();
            } catch (PDOException $e) {
                die("Error en perfil::listar" . $e->getMessage());
                return [];
            }

            return $resultado->fetch();
        } catch (PDOException $e) {
            die("Error en perfil::listar" . $e->getMessage());
            return [];
        }
    }
}
