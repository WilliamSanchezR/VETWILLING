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

    // ================================================================
    // REGISTRAR
    // ================================================================
    public function registrar($data)
    {
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

            if ($respCreacion) {

                $consulta = $this->conexion->prepare("SELECT * FROM usuario WHERE email = :email LIMIT 1");
                $consulta->bindParam(':email', $data['email']);
                $consulta->execute();
                $idUser = $consulta->fetch();

                if (!$idUser) return false;

                if ($data['id_rol'] == '1') {
                    $sql = "INSERT INTO administrador(
                        id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso
                    ) VALUES(
                        :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso
                    )";
                } elseif ($data['id_rol'] == '3') {
                    $sql = "INSERT INTO representante_legal(
                        id_veterinaria, id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso
                    ) VALUES(
                        :id_veterinaria, :id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :img_perfil, :nivel_acceso
                    )";
                }

                $stmt = $this->conexion->prepare($sql);

                if ($data['id_rol'] == '3') {
                    $stmt->bindParam(':id_veterinaria', $data['id_veterinaria']);
                }

                $stmt->bindParam(':id_usuario', $idUser['id_usuario']);
                $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
                $stmt->bindParam(':numero_documento', $data['numero_documento']);
                $stmt->bindParam(':nombres', $data['nombres']);
                $stmt->bindParam(':apellidos', $data['apellidos']);
                $stmt->bindParam(':telefono', $data['telefono']);
                $stmt->bindParam(':img_perfil', $data['img_perfil']);
                $stmt->bindParam(':nivel_acceso', $data['nivel_acceso']);

                return $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar -> " . $e->getMessage());
            return false;
        }
    }

    // ================================================================
    // LISTAR
    // ================================================================
    public function listar()
    {
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

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::listar -> " . $e->getMessage());
            return [];
        }
    }

    // ================================================================
    // CONSULTAR POR ID
    // ================================================================
    public function consultarUsuario($id)
    {
        try {

            // ADMIN
            $sqlAdmin = "SELECT adm.id_usuario, adm.tipo_documento, adm.numero_documento,
                adm.nombres, adm.apellidos, adm.telefono, us.email, us.estado, rol.id_rol
                FROM usuario us
                INNER JOIN administrador adm ON us.id_usuario = adm.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id LIMIT 1";

            $stmt = $this->conexion->prepare($sqlAdmin);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch();

            if ($data) return $data;

            // REPRESENTANTE LEGAL
            $sqlRep = "SELECT rep.id_usuario, rep.tipo_documento, rep.numero_documento,
                rep.nombres, rep.apellidos, rep.telefono, us.email, us.estado, rol.id_rol, rep.id_veterinaria
                FROM usuario us
                INNER JOIN representante_legal rep ON us.id_usuario = rep.id_usuario
                INNER JOIN rol ON us.id_rol = rol.id_rol
                WHERE us.id_usuario = :id LIMIT 1";

            $stmt2 = $this->conexion->prepare($sqlRep);
            $stmt2->bindParam(':id', $id);
            $stmt2->execute();
            return $stmt2->fetch();
        } catch (PDOException $e) {
            error_log("Error en Usuario::consultarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    // ================================================================
    // ACTUALIZAR
    // ================================================================
    public function actualizarUsuario($data)
    {
        try {
            $sql = "UPDATE usuario SET email = :email, estado = :estado
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':estado', $data['estado']);

            $ok = $stmt->execute();

            if (!$ok) return false;

            // SI ES ADMIN
            if ($data['id_rol'] == '1') {

                $sqlAdmin = "UPDATE administrador
                    SET tipo_documento = :tipo_documento, numero_documento = :numero_documento,
                    nombres = :nombres, apellidos = :apellidos, telefono = :telefono
                    WHERE id_usuario = :id_usuario";

                $stmt2 = $this->conexion->prepare($sqlAdmin);
                $stmt2->bindParam(':id_usuario', $data['id_usuario']);
                $stmt2->bindParam(':tipo_documento', $data['tipo_documento']);
                $stmt2->bindParam(':numero_documento', $data['numero_documento']);
                $stmt2->bindParam(':nombres', $data['nombres']);
                $stmt2->bindParam(':apellidos', $data['apellidos']);
                $stmt2->bindParam(':telefono', $data['telefono']);
                return $stmt2->execute();
            }

            // SI ES REPRESENTANTE LEGAL
            $sqlRep = "UPDATE representante_legal
                SET id_veterinaria = :id_veterinaria,
                tipo_documento = :tipo_documento,
                numero_documento = :numero_documento,
                nombres = :nombres,
                apellidos = :apellidos,
                telefono = :telefono
                WHERE id_usuario = :id_usuario";

            $stmt3 = $this->conexion->prepare($sqlRep);
            $stmt3->bindParam(':id_usuario', $data['id_usuario']);
            $stmt3->bindParam(':id_veterinaria', $data['id_veterinaria']);
            $stmt3->bindParam(':tipo_documento', $data['tipo_documento']);
            $stmt3->bindParam(':numero_documento', $data['numero_documento']);
            $stmt3->bindParam(':nombres', $data['nombres']);
            $stmt3->bindParam(':apellidos', $data['apellidos']);
            $stmt3->bindParam(':telefono', $data['telefono']);

            return $stmt3->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    // ================================================================
    // ELIMINAR
    // ================================================================
    public function eliminarUsuario($id)
    {
        $actualizar = "UPDATE usuario SET
                        estado = :estado
                        WHERE id_usuario = :id_usuario";

        $resultado = $this->conexion->prepare($actualizar);

        $resultado->bindValue(':estado', 'inactivo');
        $resultado->bindValue(':id_usuario', $id);

        return $resultado->execute();
    }

    public function actualizarContrasena($data)
    {
        try {
            $consultar = "SELECT *
                FROM usuario us
                WHERE us.id_usuario = :id LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $data['id_usuario']);
            $resultado->execute();

            $user = $resultado->fetch();

            if (!$user) {
                return false;
            }

            // Verificacion de la contraseña incriptada

            if (!password_verify($data['password_actual'], $user['password_hash'])) {
                return false;
            }

            $nuevoPassword = password_hash($data['nuevo_password'], PASSWORD_DEFAULT);

            $sql = "UPDATE usuario SET password_hash = :password_hash
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':password_hash', $nuevoPassword);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarUsuario -> " . $e->getMessage());
            return false;
        }
    }
}
}
