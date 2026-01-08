<?php

require_once __DIR__ . '/../../config/database.php';

class Profesional
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

    // FUNCION PARA REGISTRAR UN NUEVO USUARIO

    function registrarProfesional($data)
    {
        try {
            // Validamos si el email ya existe
            $consultaEmail = $this->conexion->prepare("SELECT * FROM usuario WHERE email = :email LIMIT 1");
            $consultaEmail->bindParam(':email', $data['email']);
            $consultaEmail->execute();
            $usuarioData = $consultaEmail->fetch();

            if ($consultaEmail->rowCount() > 0) {

                // Validamos si el usuario esta relacionado con la tabla de profesionales por veterinaria
                $consultaUsuarioVet = $this->conexion->prepare("SELECT pv.* 
                    FROM profesional_veterinaria pv
                    INNER JOIN profesional p ON p.id_profesional = pv.id_profesional
                    INNER JOIN usuario u ON u.id_usuario = p.id_usuario
                    WHERE u.id_usuario  = :id_usuario AND pv.id_veterinaria = :id_veterinaria LIMIT 1");

                $consultaUsuarioVet->bindParam(':id_usuario', $usuarioData['id_usuario']);
                $consultaUsuarioVet->bindParam(':id_veterinaria', $data['id_veterinaria']);
                $consultaUsuarioVet->execute();

                if ($consultaUsuarioVet->rowCount() > 0) {
                    // El usuario ya está registrado para esta veterinaria
                    return false;
                }

                $consultaProf = $this->conexion->prepare("SELECT * FROM profesional WHERE id_usuario = :id_usuario LIMIT 1");
                $consultaProf->bindParam(':id_usuario', $usuarioData['id_usuario']);
                $consultaProf->execute();
                $profesional = $consultaProf->fetch();

                // Registramos el prosional en la veterinaria
                $idProfesional = $profesional['id_profesional'];

                $statudRegPV = $this->registrarVeterinariaProfesional($idProfesional, $data['id_veterinaria']);

                if (!$statudRegPV) {
                    return false;
                }

                // Registramos las especialidades del profesional
                if (!empty($data['especialidades'])) {
                    return $this->registrarEspecialidadProfesional($usuarioData['id_usuario'], json_decode($data['especialidades']), $data['id_veterinaria']);
                } else {
                    return $statudRegPV;
                }
            } else {
                $insertar = "INSERT INTO usuario(email, password_hash, estado, id_rol)
                VALUES(:email, :password_hash, :estado, :id_rol)";

                $resultado = $this->conexion->prepare($insertar);
                $resultado->bindParam(':email', $data['email']);
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $resultado->bindParam(':password_hash', $passwordHash);
                $resultado->bindParam(':estado', $data['estado']);
                $resultado->bindParam(':id_rol', $data['id_rol']);
                $respCreacion = $resultado->execute();
                if (!$respCreacion) {
                    return false;
                }

                // Obtenemos el usuario recién creado
                $consulta = $this->conexion->prepare("SELECT * FROM usuario WHERE email = :email LIMIT 1");
                $consulta->bindParam(':email', $data['email']);
                $consulta->execute();
                $idUser = $consulta->fetch();

                if (!$idUser) return false;

                $sql = "INSERT INTO profesional(
                        id_usuario, tipo_documento, numero_documento, registro_medico, nombres, apellidos, telefono, img_perfil, img_firma, nivel_acceso, direccion
                    ) VALUES(
                        :id_usuario, :tipo_documento, :numero_documento, :registro_medico, :nombres, :apellidos, :telefono, :img_perfil, :img_firma, :nivel_acceso, :direccion
                    )";

                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id_usuario', $idUser['id_usuario']);
                $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
                $stmt->bindParam(':numero_documento', $data['numero_documento']);
                $stmt->bindParam(':registro_medico', $data['registro_medico']);
                $stmt->bindParam(':nombres', $data['nombres']);
                $stmt->bindParam(':apellidos', $data['apellidos']);
                $stmt->bindParam(':telefono', $data['telefono']);
                $stmt->bindParam(':img_perfil', $data['img_perfil']);
                $stmt->bindParam(':img_firma', $data['img_firma']);
                $stmt->bindParam(':nivel_acceso', $data['nivel_acceso']);
                $stmt->bindParam(':direccion', $data['direccion']);
                $respCreacionPro = $stmt->execute();

                if (!$respCreacionPro) {
                    return false;
                }

                // Registramos el prosional en la veterinaria
                $idProfesional = $this->conexion->lastInsertId();

                print_r($idProfesional);

                $statudRegPV = $this->registrarVeterinariaProfesional($idProfesional, $data['id_veterinaria']);

                if (!$statudRegPV) {
                    return false;
                }

                // Registramos las especialidades del profesional
                if (!empty($data['especialidades'])) {
                    return $this->registrarEspecialidadProfesional($idUser['id_usuario'], json_decode($data['especialidades']), $data['id_veterinaria']);
                } else {
                    return $statudRegPV;
                }
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar -> " . $e->getMessage());
            return false;
        }
    }

    function registrarVeterinariaProfesional($idProfesional, $idVeterinaria)
    {
        $sqlVet = "INSERT INTO profesional_veterinaria(id_profesional, id_veterinaria)
                        VALUES(:id_profesional, :id_veterinaria)";
        $stmtVet = $this->conexion->prepare($sqlVet);
        $stmtVet->bindParam(':id_profesional', $idProfesional);
        $stmtVet->bindParam(':id_veterinaria', $idVeterinaria);
        return $stmtVet->execute();
    }

    function listar($id_vterinaria)
    {
        try {
            $sql = "SELECT 
                    p.id_usuario,
                    pv.id_prof_veterinaria,
                    p.tipo_documento,
                    p.numero_documento,
                    p.nombres,
                    p.apellidos,
                    p.telefono,
                    p.img_perfil,
                    us.email,
                    pv.estado,
                    rol.nombre AS rol, 
                    GROUP_CONCAT(esp.nombre SEPARATOR ', ') as especialidad
                FROM profesional_veterinaria pv
                INNER JOIN profesional p ON p.id_profesional = pv.id_profesional
                INNER JOIN usuario us on p.id_usuario = us.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                LEFT JOIN profesional_especialidad pe ON p.id_usuario = pe.id_usuario AND pe.estado = 'Activo' AND pe.id_veterinaria = :id_veterinaria
                LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad
                WHERE pv.id_veterinaria = :id_veterinaria AND us.estado = 'activo' AND pv.estado = 'Activo'
                GROUP BY p.id_profesional, p.nombres, us.id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_vterinaria);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }

    function consultarPorId($id)
    {
        try {
            $sql = "SELECT 
                    p.id_profesional,
                    p.tipo_documento,
                    p.numero_documento,
                    p.nombres,
                    p.apellidos,
                    p.telefono,
                    p.img_perfil,
                    p.img_firma,
                    p.nivel_acceso,
                    p.direccion,
                    p.registro_medico,
                    us.email,
                    us.estado,
                    rol.id_rol,
                    rol.nombre
                FROM profesional p
                INNER JOIN usuario us on p.id_usuario = us.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                WHERE p.id_usuario = :id_usuario
                LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Profesional::consultarPorId -> " . $e->getMessage());
            return null;
        }
    }

    function listarEspecialidadesPorProfesional($id, $idVeterinaria)
    {
        try {
            $sql = "SELECT 
                    pe.id_profesional_esp as id,
                    esp.id_especialidad,
                    esp.nombre
                FROM profesional_especialidad pe
                INNER JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad
                WHERE pe.id_usuario = :id_usuario AND pe.estado = 'Activo' AND pe.id_veterinaria = :id_veterinaria";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id);
            $stmt->bindParam(':id_veterinaria', $idVeterinaria);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Profesional::listarEspecialidadesPorProfesional -> " . $e->getMessage());
            return [];
        }
    }

    function deleteEspProfesional($id)
    {
        try {
            $estado = 'Inactivo';

            $sql = "UPDATE profesional_especialidad 
                    SET estado = :estado, fecha_modificacion = NOW() 
                    WHERE id_profesional_esp = :id_especialidad AND estado = 'Activo'";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id_especialidad', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Profesional::deleteEspProfesional -> " . $e->getMessage());
            return false;
        }
    }

    function actualizarProfesional($data)
    {
        // Actualizamos los datos del usuario
        try {
            $sql = "UPDATE usuario SET email = :email, estado = :estado
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':estado', $data['estado']);

            $ok = $stmt->execute();
            // Verificamos si la actualización fue exitosa
            if (!$ok) return false;

            // Actualizamos los datos del profesional
            $sqlProf = "UPDATE profesional SET 
                        tipo_documento = :tipo_documento,
                        numero_documento = :numero_documento,
                        registro_medico = :registro_medico,
                        nombres = :nombres,
                        apellidos = :apellidos,
                        telefono = :telefono,
                        direccion = :direccion,
                        img_perfil = :img_perfil,
                        img_firma = :img_firma, 
                        fecha_modificacion = NOW()
                        WHERE id_usuario = :id_usuario";

            $stmtProf = $this->conexion->prepare($sqlProf);
            $stmtProf->bindParam(':id_usuario', $data['id_usuario']);
            $stmtProf->bindParam(':tipo_documento', $data['tipo_documento']);
            $stmtProf->bindParam(':numero_documento', $data['numero_documento']);
            $stmtProf->bindParam(':registro_medico', $data['registro_medico']);
            $stmtProf->bindParam(':nombres', $data['nombres']);
            $stmtProf->bindParam(':apellidos', $data['apellidos']);
            $stmtProf->bindParam(':telefono', $data['telefono']);
            $stmtProf->bindParam(':direccion', $data['direccion']);
            $stmtProf->bindParam(':img_perfil', $data['img_perfil']);
            $stmtProf->bindParam(':img_firma', $data['img_firma']);
            $okProf = $stmtProf->execute();

            // Registramos las especialidades del profesional
            if (!empty($data['especialidades'])) {
                return $this->registrarEspecialidadProfesional($data['id_usuario'], json_decode($data['especialidades']), $data['id_veterinaria']);
            } else {
                return $okProf;
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    function registrarEspecialidadProfesional($idUsuario, $especialidades, $id_veterinaria)
    {
        $estado = 'Activo';

        try {
            foreach ($especialidades as $idEspecialidad) {

                $sqlEsp = "INSERT INTO profesional_especialidad(id_usuario, id_especialidad, estado, id_veterinaria)
                                        VALUES(:id_usuario, :id_especialidad, :estado, :id_veterinaria)";

                $stmtEsp = $this->conexion->prepare($sqlEsp);
                $stmtEsp->bindParam(':id_usuario', $idUsuario);
                $stmtEsp->bindParam(':id_especialidad', $idEspecialidad->id);
                $stmtEsp->bindParam(':estado', $estado);
                $stmtEsp->bindParam(':id_veterinaria', $id_veterinaria);
                $stmtEsp->execute();
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar -> " . $e->getMessage());
            return false;
        }
    }

    function eliminarProfesional($idUsuario)
    {
        try {
            // Cambiamos el estado del usuario a 'Inactivo'
            $sql = "UPDATE profesional_veterinaria SET estado = 'Inactivo', fecha_modificacion = NOW()
                WHERE id_prof_veterinaria  = :id_prof_veterinaria ";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_prof_veterinaria', $idUsuario);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Profesional::eliminarProfesional -> " . $e->getMessage());
            return false;
        }
    }
}
