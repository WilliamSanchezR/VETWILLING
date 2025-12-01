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

        // Primero obtenemos el rol
        $consultar = "SELECT id_rol FROM usuario WHERE id_usuario = :id LIMIT 1";

        $resultado = $this->conexion->prepare($consultar);
        $resultado->bindParam(':id', $id);
        $resultado->execute();
        $user = $resultado->fetch();

        if (!$user['id_rol']) {
            return null;
        }

        switch ($user['id_rol']) {

            case 1: // ADMINISTRADOR
                $consultar = "SELECT 
                                u.id_usuario, u.email, u.estado,
                                a.nombres, a.apellidos, a.nivel_acceso, a.img_perfil, a.telefono,
                                r.nombre AS rol
                              FROM usuario u
                              INNER JOIN administrador a ON u.id_usuario = a.id_usuario
                              INNER JOIN rol r ON u.id_rol = r.id_rol
                              WHERE u.id_usuario = :id";
                break;

            case 2: // VETERINARIO
                $consultar = "SELECT 
                                u.id_usuario, u.email, u.estado,
                                v.nombres, v.apellidos, v.telefono, v.img_perfil, v.telefono,
                                r.nombre AS rol
                              FROM usuario u
                              INNER JOIN veterinario v ON u.id_usuario = v.id_usuario
                              INNER JOIN rol r ON u.id_rol = r.id_rol
                              WHERE u.id_usuario = :id";
                break;

            case 3: // PROPIETARIO
                $consultar = "SELECT 
                                u.id_usuario, u.email, u.estado,
                                p.nombres, p.apellidos, p.telefono, p.direccion, p.img_perfil, p.telefono,
                                r.nombre AS rol
                              FROM usuario u
                              INNER JOIN propietario p ON u.id_usuario = p.id_usuario
                              INNER JOIN rol r ON u.id_rol = r.id_rol
                              WHERE u.id_usuario = :id";
                break;

            default:
                return null;
        }

        $resultado = $this->conexion->prepare($consultar);
        $resultado->bindParam(':id', $id);
        $resultado->execute();

        return $resultado->fetch();

    } catch (PDOException $e) {
        error_log("Error en perfil::mostrarPerfil " . $e->getMessage());
        return null;
    }
    }
}
