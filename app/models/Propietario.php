<?php

require_once __DIR__ . '/../../config/database.php';

class Propietario
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /* ===============================
                    LISTAR
    =============================== */
    public function listar()
    {
        try {
            $sql = "SELECT * FROM propietario ORDER BY id_propietario DESC";
            $query = $this->conexion->prepare($sql);
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Propietario::listar → " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
            CONSULTAR POR ID
    =============================== */
    public function consultarPropietario($id)
    {
        try {
            $sql = "SELECT * FROM propietario WHERE id_propietario = :id LIMIT 1";
            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Propietario::consultarPropietario → " . $e->getMessage());
            return null;
        }
    }

    /* ===============================
                REGISTRAR
    =============================== */
    public function registrar($data)
    {
        try {
            // Iniciar transacción
            $this->conexion->beginTransaction();

            // 1. Crear el usuario primero
            $sqlUsuario = "INSERT INTO usuario (email, password_hash, estado, id_rol)
                          VALUES (:email, :password_hash, 'Activo', 3)";

            $queryUsuario = $this->conexion->prepare($sqlUsuario);

            // Generar password temporal basado en el número de documento
            $passwordHash = password_hash($data['numero_documento'], PASSWORD_DEFAULT);

            $queryUsuario->bindParam(':email', $data['email']);
            $queryUsuario->bindParam(':password_hash', $passwordHash);

            if (!$queryUsuario->execute()) {
                $this->conexion->rollBack();
                error_log("❌ Error al crear usuario");
                error_log("Error Info: " . print_r($queryUsuario->errorInfo(), true));
                return false;
            }

            // Obtener el id_usuario generado
            $id_usuario = $this->conexion->lastInsertId();
            error_log("✅ Usuario creado con ID: " . $id_usuario);

            // 2. Crear el propietario asociado al usuario
            $sql = "INSERT INTO propietario 
                (id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, direccion, id_veterinaria)
                VALUES 
                (:id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :direccion, :id_veterinaria)";

            $query = $this->conexion->prepare($sql);

            // Bind de parámetros
            $query->bindParam(':id_usuario',       $id_usuario);
            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $data['numero_documento']);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $data['telefono']);
            $query->bindParam(':direccion',        $data['direccion']);
            $query->bindParam(':id_veterinaria',   $data['id_veterinaria']);

            $success = $query->execute();

            if ($success) {
                $lastId = $this->conexion->lastInsertId();
                $this->conexion->commit();
                error_log("✅ Propietario registrado exitosamente. ID: " . $lastId);
                error_log("✅ Credenciales - Email: " . $data['email'] . " / Password: " . $data['numero_documento']);
                return $lastId;
            } else {
                $this->conexion->rollBack();
                error_log("❌ Error al ejecutar INSERT propietario");
                error_log("Error Info: " . print_r($query->errorInfo(), true));
            }

            return false;
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("❌ Error en Propietario::registrar → " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }

    /* ===============================
                ACTUALIZAR
    =============================== */
    public function actualizar($data, $actualizarImagen)
    {
        try {
            // 1. Construir base sin WHERE
            $sql = "UPDATE propietario SET
            tipo_documento   = :tipo_documento,
            numero_documento = :numero_documento,
            nombres          = :nombres,
            apellidos        = :apellidos,
            telefono         = :telefono,
            direccion        = :direccion,
            id_veterinaria   = :id_veterinaria";

            // 2. Si hay imagen, agregamos campo
            if ($actualizarImagen) {
                $sql .= ", img_perfil = :img_perfil";
            }

            // 3. Ahora sí agregamos el WHERE
            $sql .= " WHERE id_propietario = :id_propietario";

            $query = $this->conexion->prepare($sql);

            // Bind de parámetros
            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $data['numero_documento']);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $data['telefono']);
            $query->bindParam(':direccion',        $data['direccion']);
            $query->bindParam(':id_veterinaria',   $data['id_veterinaria']);
            $query->bindParam(':id_propietario',   $data['id_propietario']);

            if ($actualizarImagen) {
                $query->bindParam(':img_perfil', $data['img_perfil']);
            }
            $success = $query->execute();
            if (!$success) {
                print_r($query->errorInfo());
            }
            return $success;


            // return $query->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::actualizar → " . $e->getMessage());
            return;
        }
    }

    /* ===============================
                ELIMINAR (INHABILITAR)
    =============================== */
    public function eliminar($id)
    {
        try {
            // Cambiar estado en vez de borrar
            $sql = "UPDATE propietario SET estado = 0 WHERE id_propietario = :id";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            return $query->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::eliminar → " . $e->getMessage());
            return false;
        }
    }
}
