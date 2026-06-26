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
        try {
            $this->conexion->beginTransaction();

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

            $resultado->execute();
            $idVeterinaria = $this->conexion->lastInsertId();

            $this->registrarRepresentante($data, $idVeterinaria);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error al registrar la veterinaria: " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA LISTAR LAS VETERINARIAS REGISTRADAS
    public function listar()
    {
        // Listamos las veterinarias registradas en la base de datos
        try {
            $consulta = "SELECT 
                            v.id_veterinaria, 
                            v.nit, 
                            v.nombre AS razon_social, 
                            v.ciudad, 
                            v.foto AS logo,
                            v.estado, 
                            CONCAT(rl.nombres, ' ', rl.apellidos) AS nombre,                    
                            rl.telefono,
                            u.email
                         FROM veterinaria v
                         INNER JOIN representante_legal rl ON v.id_veterinaria = rl.id_veterinaria
                         INNER JOIN usuario u ON rl.id_usuario = u.id_usuario";
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
            $consulta = "SELECT v.id_veterinaria, v.nit, v.nombre, v.direccion, v.ciudad, v.telefono, v.email, v.estado, v.foto, 
            rl.id_usuario, rl.tipo_documento, rl.numero_documento,
            rl.nombres, rl.apellidos, rl.telefono as telefono_user, us.email as email_user, us.estado as estado_user, rol.id_rol, rol.nombre as rol_name, rl.direccion as direccion_user
            FROM veterinaria v
            INNER JOIN representante_legal rl ON v.id_veterinaria = rl.id_veterinaria
            INNER JOIN usuario us ON rl.id_usuario = us.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol
            WHERE v.id_veterinaria = :id";
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
            $response = $resultado->execute();

            if (!$response) {
                return false;
            }

            return $this->actualizarUsuarioVeterinaria($data);
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

    public function actualizarUsuarioVeterinaria($data)
    {
        // Actualizamos los datos del usuario
        try {
            $sql = "UPDATE usuario SET email = :email, estado = :estado
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':email', $data['email_user']);
            $stmt->bindParam(':estado', $data['estado_user']);

            $ok = $stmt->execute();
            // Verificamos si la actualización fue exitosa
            if (!$ok) return false;

            // REPRESENTANTE LEGAL
            $sqlRep = "UPDATE representante_legal
                SET id_veterinaria = :id_veterinaria,
                tipo_documento = :tipo_documento,
                numero_documento = :numero_documento,
                nombres = :nombres,
                apellidos = :apellidos,
                telefono = :telefono,
                direccion = :direccion
                WHERE id_usuario = :id_usuario";
            // Preparar y ejecutar la consulta
            $stmt3 = $this->conexion->prepare($sqlRep);
            $stmt3->bindParam(':id_usuario', $data['id_usuario']);
            $stmt3->bindParam(':id_veterinaria', $data['id_veterinaria']);
            $stmt3->bindParam(':tipo_documento', $data['tipo_documento']);
            $stmt3->bindParam(':numero_documento', $data['numero_documento']);
            $stmt3->bindParam(':nombres', $data['nombres']);
            $stmt3->bindParam(':apellidos', $data['apellidos']);
            $stmt3->bindParam(':telefono', $data['telefono_user']);
            $stmt3->bindParam(':direccion', $data['direccion_user']);

            return $stmt3->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
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
        $insertar = "INSERT INTO usuario(email, password_hash, estado, id_rol)
            VALUES(:email, :password_hash, :estado, :id_rol)";

        $resultado = $this->conexion->prepare($insertar);
        $resultado->bindParam(':email', $data['email']);
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $resultado->bindParam(':password_hash', $passwordHash);
        $resultado->bindParam(':estado', $data['estado']);
        $resultado->bindParam(':id_rol', $data['id_rol']);
        $resultado->execute();

        $idUser = $this->conexion->lastInsertId();
        if (!$idUser) {
            throw new PDOException("No se pudo obtener el ID del usuario creado");
        }

        $sql = "INSERT INTO representante_legal(
                id_veterinaria, id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso, direccion
            ) VALUES(
                :id_veterinaria, :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso, :direccion
            )";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_veterinaria', $idVeterinaria);
        $stmt->bindParam(':id_usuario', $idUser);
        $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
        $stmt->bindParam(':numero_documento', $data['numero_documento']);
        $stmt->bindParam(':nombres', $data['nombres']);
        $stmt->bindParam(':apellidos', $data['apellidos']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':img_perfil', $data['img_perfil']);
        $stmt->bindParam(':nivel_acceso', $data['nivel_acceso']);
        $stmt->bindParam(':direccion', $data['direccion']);

        $stmt->execute();
    }

    // FUNCION PARA ACTUALIZAR LOS DATOS DE LA VETERINARIA
    public function actualizarInformacionVeterinaria($data)
    {
        // Actualizamos los datos de la veterinaria en la base de datos
        try {

            $consulta = "UPDATE veterinaria 
                         SET foto = :foto,                            
                             direccion = :direccion, 
                             telefono = :telefono,
                             ciudad = :ciudad
                         WHERE id_veterinaria = :id_veterinaria";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':foto', $data['foto']);
            $resultado->bindParam(':direccion', $data['direccion']);
            $resultado->bindParam(':ciudad', $data['ciudad']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':id_veterinaria', $data['id_veterinaria']);

            // Ejecutamos la consulta
            $response = $resultado->execute();

            if (!$response) {
                return false;
            }

            if ($response && !empty($data['horarios'])) {
                return $this->registrarHorarioVeterinaria($data);
            } else {
                return true;
            }
        } catch (PDOException $e) {
            echo "Error al actualizar la veterinaria: " . $e->getMessage();
            return false;
        }
    }

    public function registrarHorarioVeterinaria($data)
    {
        try {

            $horarios = json_decode($data['horarios'], true);

            foreach ($horarios as $horario) {
                if (isset($horario['horario_1'])) {
                    $sql = "INSERT INTO horario_veterinaria (id_veterinaria, dia_semana, hora_inicio, hora_fin) 
                    VALUES (:id_veterinaria, :dia_semana, :hora_inicio, :hora_fin)";

                    $stmt = $this->conexion->prepare($sql);
                    $stmt->bindParam(':id_veterinaria', $data['id_veterinaria']);
                    $stmt->bindParam(':dia_semana', $horario['dia']);
                    $stmt->bindParam(':hora_inicio', $horario['horario_1']['hora_inicio']);
                    $stmt->bindParam(':hora_fin', $horario['horario_1']['hora_fin']);

                    $stmt->execute();
                }
                // validmos si hay un segundo horario
                if (isset($horario['horario_2'])) {
                    $consultaHorario2 = "INSERT INTO horario_veterinaria (id_veterinaria, dia_semana, hora_inicio, hora_fin) 
                    VALUES (:id_veterinaria, :dia_semana, :hora_inicio, :hora_fin)";


                    $resultadoHorario2 = $this->conexion->prepare($consultaHorario2);
                    $resultadoHorario2->bindParam(':id_veterinaria', $data['id_veterinaria']);
                    $resultadoHorario2->bindParam(':dia_semana', $horario['dia']);
                    $resultadoHorario2->bindParam(':hora_inicio', $horario['horario_2']['hora_inicio']);
                    $resultadoHorario2->bindParam(':hora_fin', $horario['horario_2']['hora_fin']);

                    $resultadoHorario2->execute();
                }
            }

            return true;
        } catch (PDOException $e) {
            echo "Error al registrar el horario de atención: " . $e->getMessage();
            return false;
        }
    }

    public function consultarHorariosVeterinaria($idVeterinaria)
    {
        try {
            $consulta = "SELECT id_horario_veterinaria as id_horario, dia_semana, hora_inicio, hora_fin
                         FROM horario_veterinaria 
                         WHERE id_veterinaria = :id_veterinaria and estado = 'Activo'";

            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_veterinaria', $idVeterinaria);
            $resultado->execute();
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al consultar los horarios de la veterinaria: " . $e->getMessage();
            return [];
        }
    }

    public function eliminarHorariosPorVeterinaria($idHorario)
    {
        try {
            $consulta = "UPDATE horario_veterinaria SET estado = 'Inactivo' WHERE id_horario_veterinaria = :id_horario";
            $resultado = $this->conexion->prepare($consulta);
            $resultado->bindParam(':id_horario', $idHorario);
            return $resultado->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar el horario de la veterinaria: " . $e->getMessage();
            return false;
        }
    }
}
