<?php

require_once __DIR__ . '/../../config/database.php';

class VeterinariaRegistrarse
{

    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }
   
   
    // FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
    public function registrarse($data)
    {
        // Insertamos los datos en la base de datos
        try {
            $this->conexion->beginTransaction();

            $consulta = "INSERT INTO veterinaria (nombre, direccion, ciudad, telefono, email, fecha_creacion, nit, estado, foto) 
                         VALUES (:nombre, :direccion, :ciudad, :telefono, :email, NOW(), :nit, 'pendiente', :foto)";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':foto', $data['fotoVeterinaria']);
            $resultado->bindParam(':nit', $data['nit']);
            $resultado->bindParam(':nombre', $data['nombreVeterinaria']);
            $resultado->bindParam(':direccion', $data['direccionVeterinaria']);
            $resultado->bindParam(':ciudad', $data['ciudad']);
            $resultado->bindParam(':telefono', $data['telefonoVeterinaria']);
            $resultado->bindParam(':email', $data['emailVeterinaria']);

            // Ejecutamos la consulta
            $estado = $resultado->execute();

            if ($estado) {
                // Retornamos el ID de la veterinaria recién creada
                $idVeterinaria = $this->conexion->lastInsertId();
                $resultadoRepresentante = $this->registrarRepresentante($data, $idVeterinaria);

                if ($resultadoRepresentante) {
                    $this->conexion->commit();
                    return true;
                }

                $this->conexion->rollBack();
                return false;
            } else {
                // Si hubo un error al incertar realizamos el rollback
                $this->conexion->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            echo "Error al registrar la veterinaria: " . $e->getMessage();
            return false;
        }
    }




    // FUNCION PARA REGISTRAR EL REPRESENTANTE DE LA VETERINARIA DESDE EL FORMULARIO DE REGISTRO DESDE LA VISTA REGISTRARSE.PHP
    private function registrarRepresentante($data, $idVeterinaria)
    {
        try {
            $insertarUsuario = "INSERT INTO usuario(email, password_hash, estado, id_rol)
                                VALUES(:email, :password_hash, :estado, :id_rol)";

            $resultadoUsuario = $this->conexion->prepare($insertarUsuario);
            $resultadoUsuario->bindParam(':email', $data['email']);

            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $resultadoUsuario->bindParam(':password_hash', $passwordHash);
            $resultadoUsuario->bindParam(':estado', $data['estado']);
            $resultadoUsuario->bindParam(':id_rol', $data['id_rol']);

            if (!$resultadoUsuario->execute()) {
                return false;
            }

            $idUsuario = $this->conexion->lastInsertId();

            if (!$idUsuario) {
                return false;
            }

            $consulta = "INSERT INTO representante_legal(
                            id_veterinaria, id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso, direccion
                        ) VALUES(
                            :id_veterinaria, :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso, :direccion
                        )";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $idVeterinaria);
            $resultado->bindParam(':id_usuario', $idUsuario);
            $resultado->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultado->bindParam(':numero_documento', $data['numero_documento']);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':img_perfil', $data['img_perfil']);
            $resultado->bindParam(':nivel_acceso', $data['nivel_acceso']);
            $resultado->bindParam(':direccion', $data['direccion']);

            return $resultado->execute();

        } catch (PDOException $e) {
            echo "Error al registrar el representante: " . $e->getMessage();
            return false;
        }
    }

}

