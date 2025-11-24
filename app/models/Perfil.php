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
                        $consultar = "SELECT id_usuario, email, estado, nombres, apellidos, nivel_acceso, img_perfil FROM usuario INNER JOIN administrador ON id_usuario = id_usuario WHERE id_usuario = :id";
                        break;

                    case 2:
                        $consultar = "SELECT id_usuario, email, estado, nombres, apellidos, telefono, numero_licencia_profesional, fecha_contratacion FROM usuario INNER JOIN veterinario ON id_usuario = id_usuario WHERE id_usuario = :id";
                        break;

                    case 3:
                        $consultar = "SELECT id_usuario, email, estado, nombres, apellidos, telefono, direccion, img_perfil FROM usuario INNER JOIN propietario ON id_usuario = id_usuario WHERE id_usuario = :id";
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
