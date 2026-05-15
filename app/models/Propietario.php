<?php

require_once __DIR__ . '/../../config/database.php';

class Propietario
{
    private $conexion;
    private $encryption_key;
    private $cipher = 'AES-256-CBC';

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
        
        // Obtener clave de encriptación de variables de entorno o usar default segura
        $this->encryption_key = getenv('ENCRYPTION_KEY') ?: (defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : md5('VetWilling_Default_Key_2025'));
    }

    /* ===============================
         MÉTODOS DE ENCRIPTACIÓN
    =============================== */
    
    private function encryptData($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        try {
            $key = openssl_digest($this->encryption_key, 'sha256', true);
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
            $encrypted = openssl_encrypt($data, $this->cipher, $key, 0, $iv);
            return base64_encode($iv . $encrypted);
        } catch (Exception $e) {
            error_log("Error encriptando datos: " . $e->getMessage());
            return $data;
        }
    }
    
    private function decryptData($encrypted_data)
    {
        if (empty($encrypted_data)) {
            return $encrypted_data;
        }
        
        try {
            $key = openssl_digest($this->encryption_key, 'sha256', true);
            $data = base64_decode($encrypted_data, true);
            
            if ($data === false) {
                return $encrypted_data;
            }
            
            $iv_length = openssl_cipher_iv_length($this->cipher);
            $iv = substr($data, 0, $iv_length);
            $encrypted = substr($data, $iv_length);
            
            $decrypted = openssl_decrypt($encrypted, $this->cipher, $key, 0, $iv);
            return $decrypted !== false ? $decrypted : $encrypted_data;
        } catch (Exception $e) {
            error_log("Error desencriptando datos: " . $e->getMessage());
            return $encrypted_data;
        }
    }

    /* ===============================
       VALIDAR DUPLICIDAD DE PROPIETARIO
    =============================== */
    
    public function existePropietario($numero_documento, $id_veterinaria = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM propietario 
                    WHERE numero_documento = :numero_documento";
            
            if ($id_veterinaria) {
                $sql .= " AND id_veterinaria = :id_veterinaria";
            }
            
            $query = $this->conexion->prepare($sql);
            $query->bindParam(':numero_documento', $numero_documento);
            
            if ($id_veterinaria) {
                $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }
            
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en Propietario::existePropietario → " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerPropietarioExistente($numero_documento, $id_veterinaria = null)
    {
        try {
            $sql = "SELECT * FROM propietario WHERE numero_documento = :numero_documento";
            
            if ($id_veterinaria) {
                $sql .= " AND id_veterinaria = :id_veterinaria";
            }
            
            $sql .= " LIMIT 1";
            
            $query = $this->conexion->prepare($sql);
            $query->bindParam(':numero_documento', $numero_documento);
            
            if ($id_veterinaria) {
                $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            }
            
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Propietario::obtenerPropietarioExistente → " . $e->getMessage());
            return null;
        }
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

            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['numero_documento'] = $this->decryptData($result['numero_documento']);
                $result['telefono'] = $this->decryptData($result['telefono']);
            }
            
            return $result;
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

            // 2. Encriptar datos sensibles antes de insertar
            $numero_documento_encriptado = $this->encryptData($data['numero_documento']);
            $telefono_encriptado = $this->encryptData($data['telefono']);

            // 3. Crear el propietario asociado al usuario
            $sql = "INSERT INTO propietario 
                (id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, direccion, id_veterinaria)
                VALUES 
                (:id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos, :telefono, :direccion, :id_veterinaria)";

            $query = $this->conexion->prepare($sql);

            // Bind de parámetros (con datos encriptados)
            $query->bindParam(':id_usuario',       $id_usuario);
            $query->bindParam(':tipo_documento',   $data['tipo_documento']);
            $query->bindParam(':numero_documento', $numero_documento_encriptado);
            $query->bindParam(':nombres',          $data['nombres']);
            $query->bindParam(':apellidos',        $data['apellidos']);
            $query->bindParam(':telefono',         $telefono_encriptado);
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
            // consultamos el id del usuario asociado al propietario
            $sqlUsuario = "SELECT id_usuario FROM propietario WHERE id_propietario = :id LIMIT 1";
            $queryUsuario = $this->conexion->prepare($sqlUsuario);
            $queryUsuario->bindParam(':id', $id, PDO::PARAM_INT);
            $queryUsuario->execute();

            $resultUsuario = $queryUsuario->fetch(PDO::FETCH_ASSOC);

            if (!$resultUsuario) {
                error_log("Error: No se encontró el propietario con ID: " . $id);
                return false;
            }

            $id_usuario = $resultUsuario['id_usuario'];

            // Primero inhabilitamos el usuario
            $sqlInhabilitarUsuario = "UPDATE usuario SET estado = 'Inactivo' WHERE id_usuario = :id_usuario";
            $queryInhabilitarUsuario = $this->conexion->prepare($sqlInhabilitarUsuario);
            $queryInhabilitarUsuario->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            return $queryInhabilitarUsuario->execute();
        } catch (PDOException $e) {
            error_log("Error en Propietario::eliminar → " . $e->getMessage());
            return false;
        }
    }

    public function fotoPerfil($data)
    {
        // Actualizamos la foto de perfil del usuario
        try {
            $sql = "UPDATE propietario SET img_perfil = :img_perfil
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $data['id_usuario']);
            $stmt->bindParam(':img_perfil', $data['img_perfil']);

            $ok = $stmt->execute();
            // Verificamos si la actualización fue exitosa
            if ($ok) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarFotoPerfil -> " . $e->getMessage());
            return false;
        }
    }

    public function listarPropietariosVeterinaria($id_veterinaria)
    {
        try {
            $sql = "SELECT p.id_propietario, p.img_perfil, p.tipo_documento, p.numero_documento, CONCAT( p.nombres, ' ', p.apellidos) AS nombre, p.telefono,  u.email, u.estado AS estado
                    FROM propietario p
                    JOIN usuario u ON p.id_usuario = u.id_usuario
                    WHERE p.id_veterinaria = :id_veterinaria
                    ORDER BY p.id_propietario DESC";

            $query = $this->conexion->prepare($sql);
            $query->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $query->execute();

            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as &$resultado) {
                $resultado['numero_documento'] = $this->decryptData($resultado['numero_documento']);
                $resultado['telefono'] = $this->decryptData($resultado['telefono']);
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error en Propietario::listarPropietariosVeterinaria → " . $e->getMessage());
            return [];
        }
    }
}
