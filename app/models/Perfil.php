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

    public function mostrarPerfilVeteri($id)
    {

        try {

            $consultar = "SELECT * FROM usuario WHERE id_usuario = :id LIMIT 1";

            // Preparamos la accion a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            die("Error en perfil::listar" . $e->getMessage());
            return [];
        }
    }
}
