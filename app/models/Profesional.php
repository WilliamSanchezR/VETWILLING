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
                $consultaUsuarioVet = $this->conexion->prepare("SELECT v.* 
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
            }


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
                $stmt->execute();

                if ($respCreacion) {
                    // Registramos el prosional en la veterinaria
                    $idProfesional = $this->conexion->lastInsertId();
                    $statudRegPV = $this->registrarVeterinariaProfesional($idProfesional, $data['id_veterinaria']);

                    if (!$statudRegPV) {
                        return false;
                    }

                    // Registramos las especialidades del profesional
                    if (!empty($data['especialidades'])) {
                        return $this->registrarEspecialidadProfesional($idProfesional, json_decode($data['especialidades']));
                    } else {
                        return $statudRegPV;
                    }
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

    function registrarEspecialidadProfesional($idProfesional, $especialidades)
    {
        $estado = 'Activo';

        try {
            foreach ($especialidades as $idEspecialidad) {

                $sqlEsp = "INSERT INTO profesional_especialidad(id_profesional, id_especialidad, estado)
                                        VALUES(:id_profesional, :id_especialidad, :estado)";

                $stmtEsp = $this->conexion->prepare($sqlEsp);
                $stmtEsp->bindParam(':id_profesional', $idProfesional);
                $stmtEsp->bindParam(':id_especialidad', $idEspecialidad->id);
                $stmtEsp->bindParam(':estado', $estado);
                $stmtEsp->execute();
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar -> " . $e->getMessage());
            return false;
        }
    }

    function listar($id_vterinaria)
    {
        try {
            $sql = "SELECT 
                    p.id_usuario,
                    p.tipo_documento,
                    p.numero_documento,
                    p.nombres,
                    p.apellidos,
                    p.telefono,
                    p.img_perfil,
                    us.email,
                    us.estado,
                    rol.nombre AS rol, 
                    GROUP_CONCAT(esp.nombre SEPARATOR ', ') as especialidad
                FROM profesional_veterinaria pv
                INNER JOIN profesional p ON p.id_profesional = pv.id_profesional
                INNER JOIN usuario us on p.id_usuario = us.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                LEFT JOIN profesional_especialidad pe ON p.id_profesional = pe.id_profesional
                LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad
                WHERE pv.id_veterinaria = :id_veterinaria
                GROUP BY p.id_profesional, p.nombres";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_veterinaria', $id_vterinaria);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }
}
