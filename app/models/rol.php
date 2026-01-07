<?php

require_once __DIR__ . '/../../config/database.php';

class Rol
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function listarRolAdmin()
    {
        try {

            //Variable que al,macena la sentencia de SQL a ejecutar
            $consultar = "SELECT * FROM rol WHERE id_rol = 1 OR id_rol = 4 ORDER BY id_rol ASC";

            //preparar lo necesario para ejecutar la funcion
            $resultado = $this->conexion->prepare($consultar);

            $resultado->execute();

            return $resultado->fetchAll();
        } catch (PDOException $e) {
            die("Error en Rol::listar " . $e->getMessage());
            return [];
        }
    }

    public function listarRolRepresentante()
    {
        try {

            //Variable que al,macena la sentencia de SQL a ejecutar
            $consultar = "SELECT * FROM rol WHERE id_rol != 1 AND id_rol != 3 AND id_rol != 4 ORDER BY id_rol ASC";

            //preparar lo necesario para ejecutar la funcion
            $resultado = $this->conexion->prepare($consultar);

            $resultado->execute();

            return $resultado->fetchAll();
        } catch (PDOException $e) {
            die("Error en Rol::listar " . $e->getMessage());
            return [];
        }
    }   
}
