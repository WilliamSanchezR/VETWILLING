<?php

require_once __DIR__ . '/../../config/database.php';

class Usuario
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
    public function registrar($data)
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
                $consulta = $this->conexion->prepare("SELECT * FROM usuario WHERE email = :email LIMIT 1");
                $consulta->bindParam(':email', $data['email']);
                $consulta->execute();
                $idUser = $consulta->fetch();
                // Verificamos si se obtuvo el usuario
                if (!$idUser) return false;
                // Insertamos en la tabla correspondiente según el rol
                if ($data['id_rol'] == '1') {
                    $sql = "INSERT INTO administrador(
                        id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso, direccion
                    ) VALUES(
                        :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso, :direccion
                    )";
                } elseif ($data['id_rol'] == '4') {
                    $sql = "INSERT INTO representante_legal(
                        id_veterinaria, id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso
                    ) VALUES(
                        :id_veterinaria, :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso
                    )";
                }
                // Preparar y ejecutar la inserción
                $stmt = $this->conexion->prepare($sql);
                // Vincular los parámetros
                if ($data['id_rol'] == '4') {
                    $stmt->bindParam(':id_veterinaria', $data['id_veterinaria']);
                }
                // Vincular los demás parámetros
                $stmt->bindParam(':id_usuario', $idUser['id_usuario']);
                $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
                $stmt->bindParam(':numero_documento', $data['numero_documento']);
                $stmt->bindParam(':nombres', $data['nombres']);
                $stmt->bindParam(':apellidos', $data['apellidos']);
                $stmt->bindParam(':telefono', $data['telefono']);
                $stmt->bindParam(':img_perfil', $data['img_perfil']);
                $stmt->bindParam(':nivel_acceso', $data['nivel_acceso']);

                if ($data['id_rol'] == '1') {
                    $stmt->bindParam(':direccion', $data['direccion']);
                }

                return $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA LISTAR LOS USUARIOS REGISTRADOS
    public function listar()
    {
        // Listamos los usuarios registrados en la base de datos
        try {
            $sql = "SELECT 
                adm.id_usuario,
                adm.tipo_documento,
                adm.numero_documento,
                adm.nombres,
                adm.apellidos,
                adm.telefono,
                adm.img_perfil,
                us.email,
                us.estado,
                rol.nombre AS rol
            FROM usuario us
            INNER JOIN administrador adm ON us.id_usuario = adm.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol

            UNION
            -- REPRESENTANTE LEGAL
            SELECT 
                rep.id_usuario,
                rep.tipo_documento,
                rep.numero_documento,
                rep.nombres,
                rep.apellidos,
                rep.telefono,
                rep.img_perfil,
                us.email,
                us.estado,
                rol.nombre AS rol
            FROM usuario us
            INNER JOIN representante_legal rep ON us.id_usuario = rep.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol

            ORDER BY id_usuario ASC";
            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }

    public function ListarTodosUsuarios()
    {
        // Listamos los usuarios registrados en la base de datos
        try {
            $sql = "SELECT 
                adm.id_usuario,
                adm.tipo_documento,
                adm.numero_documento,
                adm.nombres,
                adm.apellidos,
                adm.telefono,
                adm.img_perfil,
                us.email,
                us.estado,
                rol.nombre AS rol,
                rol.id_rol
            FROM usuario us
            INNER JOIN administrador adm ON us.id_usuario = adm.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol

            UNION
            -- REPRESENTANTE LEGAL
            SELECT 
                rep.id_usuario,
                rep.tipo_documento,
                rep.numero_documento,
                rep.nombres,
                rep.apellidos,
                rep.telefono,
                rep.img_perfil,
                us.email,
                us.estado,
                rol.nombre AS rol,
                rol.id_rol
            FROM usuario us
            INNER JOIN representante_legal rep ON us.id_usuario = rep.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol
            
            UNION
            -- Profesionales
            SELECT 
                pro.id_usuario,
                pro.tipo_documento,
                pro.numero_documento,
                pro.nombres,
                pro.apellidos,
                pro.telefono,
                pro.img_perfil,
                us.email,
                us.estado,
                rol.nombre AS rol,
                rol.id_rol
            FROM usuario us
            INNER JOIN profesional pro ON us.id_usuario = pro.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol
            
            UNION
            -- Propietarios
            SELECT 
                pro.id_usuario,
                pro.tipo_documento,
                pro.numero_documento,
                pro.nombres,
                pro.apellidos,
                pro.telefono,
                pro.img_perfil,
                us.email,
                us.estado,
                rol.nombre AS rol,
                rol.id_rol
            FROM usuario us
            INNER JOIN propietario pro ON us.id_usuario = pro.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol

            ORDER BY id_usuario ASC";
            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }

    // FUNCION PARA CONSULTAR UN USUARIO POR ID
    public function consultarUsuario($id)
    {
        // Consultamos un usuario por su ID
        try {

            // ADMIN
            $sqlAdmin = "SELECT adm.id_usuario, adm.tipo_documento, adm.numero_documento,
                adm.nombres, adm.apellidos, adm.telefono, us.email, us.estado, rol.id_rol, rol.nombre as rol_name, adm.direccion
                FROM usuario us
                INNER JOIN administrador adm ON us.id_usuario = adm.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id LIMIT 1";
            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sqlAdmin);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch();
            // Verificamos si se obtuvo el usuario
            if ($data) return $data;

            // REPRESENTANTE LEGAL
            $sqlRep = "SELECT rep.id_usuario, rep.tipo_documento, rep.numero_documento,
                rep.nombres, rep.apellidos, rep.telefono, us.email, us.estado, rol.id_rol, rol.nombre as rol_name, rep.id_veterinaria, rep.direccion
                FROM usuario us
                INNER JOIN representante_legal rep ON us.id_usuario = rep.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id LIMIT 1";
            // Preparar y ejecutar la consulta
            $stmt2 = $this->conexion->prepare($sqlRep);
            $stmt2->bindParam(':id', $id);
            $stmt2->execute();
            return $stmt2->fetch();
        } catch (PDOException $e) {
            error_log("Error en Usuario::consultarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA ACTUALIZAR LOS DATOS DEL USUARIO
    public function actualizarUsuario($data)
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

            // SI ES ADMIN
            if ($data['id_rol'] == '1') {

                $sqlAdmin = "UPDATE administrador
                    SET tipo_documento = :tipo_documento, numero_documento = :numero_documento,
                    nombres = :nombres, apellidos = :apellidos, telefono = :telefono, direccion = :direccion
                    WHERE id_usuario = :id_usuario";
                // Preparar y ejecutar la consulta
                $stmt2 = $this->conexion->prepare($sqlAdmin);
                $stmt2->bindParam(':id_usuario', $data['id_usuario']);
                $stmt2->bindParam(':tipo_documento', $data['tipo_documento']);
                $stmt2->bindParam(':numero_documento', $data['numero_documento']);
                $stmt2->bindParam(':nombres', $data['nombres']);
                $stmt2->bindParam(':apellidos', $data['apellidos']);
                $stmt2->bindParam(':telefono', $data['telefono']);
                $stmt2->bindParam(':direccion', $data['direccion']);
                return $stmt2->execute();
            }

            // SI ES REPRESENTANTE LEGAL
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
            $stmt3->bindParam(':telefono', $data['telefono']);
            $stmt3->bindParam(':direccion', $data['direccion']);

            return $stmt3->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA ELIMINAR UN USUARIO
    public function eliminarUsuario($id)
    {
        // Actualizamos el estado del usuario a 'inactivo'
        $actualizar = "UPDATE usuario SET
                        estado = :estado
                        WHERE id_usuario = :id_usuario";
        // Preparar y ejecutar la consulta
        $resultado = $this->conexion->prepare($actualizar);

        $resultado->bindValue(':estado', 'inactivo');
        $resultado->bindValue(':id_usuario', $id);

        return $resultado->execute();
    }

    // FUNCION PARA ACTUALIZAR LA CONTRASEÑA DEL USUARIO    
    public function actualizarContrasena($data)
    {
        // Actualizamos la contraseña del usuario
        try {
            $consultar = "SELECT *
                FROM usuario us
                WHERE us.id_usuario = :id LIMIT 1";
            // Preparar y ejecutar la consulta
            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $data['id_usuario']);
            $resultado->execute();

            $user = $resultado->fetch();
            // Verificamos si se obtuvo el usuario
            if (!$user) {
                return false;
            }

            // Verificacion de la contraseña incriptada
            if (!password_verify($data['password_actual'], $user['password_hash'])) {
                return false;
            }

            $nuevoPassword = password_hash($data['nuevo_password'], PASSWORD_DEFAULT);
            // Actualizar la contraseña
            $sql = "UPDATE usuario SET password_hash = :password_hash
                    WHERE id_usuario = :id_usuario";
            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':password_hash', $nuevoPassword);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    // FUNCION PARA ACTUALIZAR LA FOTO DE PERFIL DEL USUARIO    
    public function actualizarFotoPerfil($data)
    {
        // Actualizamos la foto de perfil del usuario
        try {

            // Cosnsultamos el usurio para obteerr su rol
            $consultar = "SELECT us.id_rol
                FROM usuario us
                WHERE us.id_usuario = :id LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $data['id_usuario']);
            $resultado->execute();
            $user = $resultado->fetch();


            if (!$user) {
                return false;
            }

            if ($user['id_rol'] == '1') {

                $sql = "UPDATE administrador SET img_perfil = :img_perfil
                    WHERE id_usuario = :id_usuario";

                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id_usuario', $data['id_usuario']);
                $stmt->bindParam(':img_perfil', $data['img_perfil']);

                $ok = $stmt->execute();
                // Verificamos si la actualización fue exitosa
                if ($ok) {
                    return true;
                }
            } else if ($user['id_rol'] == '4') {

                // SI NO ES ADMIN, INTENTAMOS CON REPRESENTANTE LEGAL
                $sqlRep = "UPDATE representante_legal SET img_perfil = :img_perfil
                    WHERE id_usuario = :id_usuario";

                $stmt2 = $this->conexion->prepare($sqlRep);
                $stmt2->bindParam(':id_usuario', $data['id_usuario']);
                $stmt2->bindParam(':img_perfil', $data['img_perfil']);

                return $stmt2->execute();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarFotoPerfil -> " . $e->getMessage());
            return false;
        }
    }

    public function consultarUsuarioTicket($id)
    {
        // Consultamos un usuario por su ID
        try {

            // ADMIN
            $sqlAdmin = "SELECT adm.id_usuario, adm.tipo_documento, adm.numero_documento,
                adm.nombres, adm.apellidos, adm.telefono, us.email, us.estado, rol.id_rol, rol.nombre as rol_name, adm.direccion, '' as especialidad
                FROM usuario us
                INNER JOIN administrador adm ON us.id_usuario = adm.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id LIMIT 1";
            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sqlAdmin);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch();
            // Verificamos si se obtuvo el usuario
            if ($data) return $data;

            // REPRESENTANTE LEGAL
            $sqlRep = "SELECT rep.id_usuario, rep.tipo_documento, rep.numero_documento,
                rep.nombres, rep.apellidos, rep.telefono, us.email, us.estado, rol.id_rol, rol.nombre as rol_name, rep.id_veterinaria, rep.direccion, vet.nombre as nombre_veterinaria, esp.nombre as especialidad
                FROM usuario us
                INNER JOIN representante_legal rep ON us.id_usuario = rep.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                INNER JOIN veterinaria vet on rep.id_veterinaria = vet.id_veterinaria
                LEFT JOIN profesional_especialidad pe ON us.id_usuario = pe.id_usuario
                LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad 
                WHERE us.id_usuario = :id LIMIT 1";

            // Preparar y ejecutar la consulta
            $stmt2 = $this->conexion->prepare($sqlRep);
            $stmt2->bindParam(':id', $id);
            $stmt2->execute();
            $representante = $stmt2->fetch();
            if ($representante) return $representante;
            // SI NO ES ADMIN NI REPRESENTANTE LEGAL, INTENTAMOS CON PROFESIONAL
            $sqlProf = "SELECT pr.id_usuario, pr.tipo_documento, pr.numero_documento,
                pr.nombres, pr.apellidos, pr.telefono, us.email, us.estado, rol.id_rol, rol.nombre as rol_name, vet.id_veterinaria, pr.direccion, vet.nombre as nombre_veterinaria, esp.nombre as especialidad
                FROM usuario us
                INNER JOIN profesional pr ON us.id_usuario = pr.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                INNER JOIN profesional_veterinaria pv ON pr.id_profesional = pv.id_profesional
                INNER JOIN veterinaria vet ON pv.id_veterinaria = vet.id_veterinaria
                LEFT JOIN profesional_especialidad pe ON us.id_usuario = pe.id_usuario
                LEFT JOIN especialidad esp ON pe.id_especialidad = esp.id_especialidad 
                WHERE us.id_usuario = :id LIMIT 1";
            // Preparar y ejecutar la consulta
            $stmt3 = $this->conexion->prepare($sqlProf);
            $stmt3->bindParam(':id', $id);
            $stmt3->execute();
            $profesional = $stmt3->fetch();
            return $profesional;
        } catch (PDOException $e) {
            error_log("Error en Usuario::consultarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    public function ListaUsuariosAdmin()
    {
        // Listamos los usuarios registrados en la base de datos
        try {
            $sql = "SELECT 
                adm.id_usuario,
                adm.nombres,
                adm.apellidos,
                rol.nombre AS rol
            FROM usuario us
            INNER JOIN administrador adm ON us.id_usuario = adm.id_usuario
            INNER JOIN rol ON us.id_rol = rol.id_rol
            WHERE rol.id_rol = 1 and us.estado = 'activo'
            ORDER BY id_usuario ASC";
            // Preparar y ejecutar la consulta
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }
}
