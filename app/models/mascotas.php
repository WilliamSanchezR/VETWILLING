<?php

require_once __DIR__ . '/../../config/database.php';

class mascota
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }


    /*
       REGISTRAR MASCOTA
 */
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO paciente 
                    (nombre, especie, raza, edad, sexo)
                    VALUES 
                    (:nombre, :especie, :raza, :edad, :sexo";

            $stmt = $this->conexion->prepare($sql);

            return $stmt->execute([
                ":nombre"        => $data['nombre'],
                ":especie"       => $data['especie'],
                ":raza"          => $data['raza'],
                ":edad"          => $data['edad'],
                ":sexo"          => $data['sexo']
            ]);
        } catch (PDOException $e) {
            error_log("Error en Mascota::registrar → " . $e->getMessage());
            return false;
        }
    }

    /*
       LISTAR MASCOTAS POR PROPIETARIO
 */
    public function listarPorPropietario($id_propietario)
    {
        try {
            $sql = "SELECT *
                    FROM paciente
                    WHERE id_propietario = :id_propietario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_propietario', $id_propietario);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Mascota::listarPorPropietario → " . $e->getMessage());
            return [];
        }
    }
    public function listar()
    {
        try {
            $sql = "SELECT * FROM paciente";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Mascota::listar → " . $e->getMessage());
            return [];
        }
    }

    /*
       CONSULTAR UNA MASCOTA POR ID
 */
    public function consultar($id)
    {
        try {
            $sql = "SELECT *
                    FROM paciente
                    WHERE id_paciente = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Mascota::consultar → " . $e->getMessage());
            return null;
        }
    }

    /*
       ACTUALIZAR MASCOTA
 */
    public function actualizar($data)
    {
        try {
            $sql = "UPDATE paciente SET
                        nombre = :nombre,
                        especie = :especie,
                        raza = :raza,
                        edad = :edad,
                        sexo = :sexo,
                        img_mascota = :img_mascota
                    WHERE id_paciente = :id_paciente";

            $stmt = $this->conexion->prepare($sql);

            return $stmt->execute([
                ":nombre"      => $data['nombre'],
                ":especie"     => $data['especie'],
                ":raza"        => $data['raza'],
                ":edad"        => $data['edad'],
                ":sexo"        => $data['sexo'],
                ":img_mascota" => $data['img_mascota'],
                ":id_paciente" => $data['id_paciente']
            ]);
        } catch (PDOException $e) {
            error_log("Error en Mascota::actualizar → " . $e->getMessage());
            return false;
        }
    }

    /*
       ELIMINAR MASCOTA
 */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM paciente
                    WHERE id_paciente = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Mascota::eliminar → " . $e->getMessage());
            return false;
        }
    }
}
