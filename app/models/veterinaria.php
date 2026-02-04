<?php

require_once __DIR__ . '/../../config/database.php';

class Veterinaria
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    // =========================================
    //  FUNCIONES CRUD
    // =========================================

    // FUNCION PARA REGISTRAR UNA NUEVA VETERINARIA
    public function registrar($data)
    {
        // Insertamos los datos en la base de datos
        try {
            $consulta = "INSERT INTO veterinaria (nombre, direccion, ciudad, telefono, email, fecha_creacion, nit, estado, foto) 
                         VALUES (:nombre, :direccion, :ciudad, :telefono, :email, NOW(), :nit, 'Activo', :foto)";

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
                return $this->registrarRepresentante($data, $idVeterinaria);
            } else {
                // Si hubo un error al incertar realizamos el rollback
                $this->conexion->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            echo "Error al registrar la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS
    public function listar()
    {
        // Listamos las veterinarias registradas en la base de datos
        try {
            $consulta = "SELECT 
                            id_veterinaria, 
                            nit, 
                            nombre, 
                            direccion, 
                            ciudad, 
                            telefono, 
                            email, 
                            foto,
                            fecha_creacion, 
                            estado
                         FROM veterinaria";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->execute();
            // Devolvemos los resultados como un array asociativo
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Veterinaria::listar -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS POR ID Y NOMBRE
    public function consultarVeterinariasRegistradas($id)
    {
        try {
            $consulta = "SELECT id_veterinaria, nit, nombre, direccion, ciudad, telefono, email, estado, foto FROM veterinaria WHERE id_veterinaria = :id";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id', $id);
            $resultado->execute();
            return $resultado->fetch();
        } catch (PDOException $e) {
            echo "Error al listar las veterinarias: " . $e->getMessage();
            return [];
        }
    }

    // FUNCION PARA ACTUALIZAR LOS DATOS DE LA VETERINARIA
    public function actualizar($data)
    {
        // Actualizamos los datos de la veterinaria en la base de datos
        try {
            $consulta = "UPDATE veterinaria 
                         SET foto = :foto,
                             nombre = :nombre, 
                             direccion = :direccion, 
                             telefono = :telefono, 
                             email = :email, 
                             nit = :nit, 
                             ciudad = :ciudad, 
                             estado = :estado
                         WHERE id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':foto', $data['foto']);
            $resultado->bindParam(':nit', $data['nit']);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':direccion', $data['direccion']);
            $resultado->bindParam(':ciudad', $data['ciudad']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_veterinaria', $data['id_veterinaria']);

            // Ejecutamos la consulta
            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    // FUNCION PARA ELIMINAR UNA VETERINARIA
    public function eliminar($id)
    {
        // Eliminamos la veterinaria de la base de datos
        try {
            $consulta = "UPDATE veterinaria SET estado = 'Eliminado' WHERE id_veterinaria = :id";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id', $id);
            // Ejecutamos la consulta
            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    public function consultarVeterinariasPorArray($idsArray)
    {
        try {
            // Convertimos el array de IDs en una cadena separada por comas para la consulta SQL
            $placeholders = rtrim(str_repeat('?,', count($idsArray)), ',');
            $consulta = "SELECT id_veterinaria, nit, nombre, ciudad, foto
                         FROM veterinaria 
                         WHERE id_veterinaria IN ($placeholders)";

            $resultado = $this->conexion->prepare($consulta);
            // Ejecutamos la consulta con los IDs como parámetros
            $resultado->execute($idsArray);
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al consultar las veterinarias: " . $e->getMessage();
            return [];
        }
    }

    public function consultaIformacionVeterinaria($idVeterinaria)
    {
        try {
            $consulta = "SELECT id_veterinaria, nombre, foto
                         FROM veterinaria 
                         WHERE id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $idVeterinaria);
            $resultado->execute();
            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al consultar la información de la veterinaria: " . $e->getMessage();
            return null;
        }
    }

    // FUNCION PARA REGISTRAR UN NUEVO USUARIO
    public function registrarRepresentante($data, $idVeterinaria)
    {
        // Insertamos los datos en la base de datos
        try {
            $insertar = "INSERT INTO usuario(email, password_hash, estado, id_rol)
                VALUES(:email, :password_hash, :estado, :id_rol)";

            $resultado = $this->conexion->prepare($insertar);
            $resultado->bindParam(':email', $data['email']);
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $resultado->bindParam(':password_hash', $passwordHash);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_rol', $data['id_rol']);
            $respCreacion = $resultado->execute();

            // Si la creacion del usuario fue exitosa, insertamos en la tabla correspondiente
            if ($respCreacion) {
                // Obtenemos el usuario recién creado
                $idUser = $this->conexion->lastInsertId();
                // Verificamos si se obtuvo el usuario
                if (!$idUser) return false;
                // Insertamos en la tabla correspondiente según el rol

                echo "ID Veterinaria: " . $idVeterinaria . "\n";
                echo "ID Usuario: " . $idUser . "\n";

                $sql = "INSERT INTO representante_legal(
                        id_veterinaria, id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso, direccion
                    ) VALUES(
                        :id_veterinaria, :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso, :direccion
                    )";

                // Preparar y ejecutar la inserción
                $stmt = $this->conexion->prepare($sql);
                // Vincular los parámetros
                $stmt->bindParam(':id_veterinaria', $idVeterinaria);
                // Vincular los demás parámetros
                $stmt->bindParam(':id_usuario', $idUser);
                $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
                $stmt->bindParam(':numero_documento', $data['numero_documento']);
                $stmt->bindParam(':nombres', $data['nombres']);
                $stmt->bindParam(':apellidos', $data['apellidos']);
                $stmt->bindParam(':telefono', $data['telefono']);
                $stmt->bindParam(':img_perfil', $data['img_perfil']);
                $stmt->bindParam(':nivel_acceso', $data['nivel_acceso']);
                $stmt->bindParam(':direccion', $data['direccion']);

                return $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar -> " . $e->getMessage());
            $this->conexion->rollBack();
            return false;
        }
    }
}
