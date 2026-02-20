<?php

// Importamos las dependencias

require_once __DIR__ . '/../../config/database.php';

class Veterinario
{

    private $conexion;

    public function __construct()
    {

        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function contarPacientesPorVeterinario($id_usuario)
    {
        try {
            $sql = "SELECT COUNT(DISTINCT a.id_paciente)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarPacientesPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarCitasHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) = :fecha";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasHoyPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarPacientesHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(DISTINCT a.id_paciente)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) = :fecha";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarPacientesHoyPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarCitasPendientesHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) = :fecha
                      AND UPPER(a.estado) = 'PENDIENTE'";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasPendientesHoyPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function contarCitasSemanaPorVeterinario($id_usuario, $fecha)
    {
        try {
            $sql = "SELECT COUNT(*)
                    FROM agendamiento a
                    WHERE a.id_usuario = :id_usuario
                      AND YEARWEEK(a.fecha_hora, 1) = YEARWEEK(:fecha, 1)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Veterinario::contarCitasSemanaPorVeterinario - " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerCitasHoyPorVeterinario($id_usuario, $fecha)
    {
        try {
            // Debug
            error_log("=== QUERY CITAS HOY ===");
            error_log("Buscando citas para id_usuario: " . $id_usuario . " en fecha: " . $fecha);

            // Primero consultamos TODAS las citas del usuario para ver qué fechas hay
            $sqlDebug = "SELECT id_agendamiento, fecha_hora, DATE(fecha_hora) as fecha_solo, id_usuario 
                        FROM agendamiento 
                        WHERE id_usuario = :id_usuario 
                        ORDER BY fecha_hora DESC LIMIT 10";
            $stmtDebug = $this->conexion->prepare($sqlDebug);
            $stmtDebug->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtDebug->execute();
            $todasCitas = $stmtDebug->fetchAll(PDO::FETCH_ASSOC);
            error_log("TODAS las citas del usuario (últimas 10): " . print_r($todasCitas, true));

            $sql = "SELECT 
                        a.id_agendamiento,
                        a.fecha_hora,
                        a.fecha_hora_fin,
                        a.tipo,
                        a.estado,
                        a.observaciones,
                        p.id_paciente,
                        p.nombre AS paciente_nombre,
                        p.especie,
                        p.raza,
                        p.edad_numero,
                        p.edad_unidad,
                        p.sexo,
                        p.img_mascota,
                        prop.id_propietario,
                        prop.nombres AS propietario_nombres,
                        prop.apellidos AS propietario_apellidos,
                        prop.telefono AS propietario_telefono,
                                                u.email AS propietario_email
                    FROM agendamiento a
                    LEFT JOIN paciente p ON a.id_paciente = p.id_paciente
                    LEFT JOIN propietario prop ON p.id_propietario = prop.id_propietario
                                        LEFT JOIN usuario u ON prop.id_usuario = u.id_usuario
                    WHERE a.id_usuario = :id_usuario
                      AND DATE(a.fecha_hora) = :fecha
                    ORDER BY a.fecha_hora ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Registros encontrados para fecha " . $fecha . ": " . count($resultado));
            error_log("========================");

            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en Veterinario::obtenerCitasHoyPorVeterinario - " . $e->getMessage());
            return [];
        }
    }



    public function registrar($data)
    {
        try {
            $this->conexion->beginTransaction();

            // INSERT USUARIO
            $sqlUsuario = "INSERT INTO usuario (email, password_hash, estado, id_rol)
                       VALUES (:email, :password_hash, :estado, :id_rol)";
            $stmtUsuario = $this->conexion->prepare($sqlUsuario);

            $passwordHash = password_hash($data['password_hash'], PASSWORD_DEFAULT);

            $stmtUsuario->execute([
                ':email' => $data['email'],
                ':password_hash' => $passwordHash,
                ':estado' => $data['estado'],
                ':id_rol' => 2
            ]);

            $id_usuario = $this->conexion->lastInsertId();

            // INSERT VETERINARIO
            $sqlVet = "INSERT INTO veterinario 
            (id_usuario, tipo_documento, numero_documento, nombres, apellidos,
             telefono, img_perfil, numero_licencia_profesional, id_veterinaria, fecha_contratacion)
            VALUES
            (:id_usuario, :tipo_documento, :numero_documento, :nombres, :apellidos,
             :telefono, :img_perfil, :numero_licencia_profesional, :id_veterinaria, :fecha_contratacion)";

            $stmtVet = $this->conexion->prepare($sqlVet);

            $stmtVet->execute([
                ':id_usuario' => $id_usuario,
                ':tipo_documento' => $data['tipo_documento'],
                ':numero_documento' => $data['numero_documento'],
                ':nombres' => $data['nombres'],
                ':apellidos' => $data['apellidos'],
                ':telefono' => $data['telefono'],
                ':img_perfil' => $data['img_perfil'],
                ':numero_licencia_profesional' => $data['numero_licencia_profesional'],
                ':id_veterinaria' => $data['id_veterinaria'],
                ':fecha_contratacion' => date('Y-m-d')
            ]);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("ERROR REGISTRAR VETERINARIO: " . $e->getMessage());
            return false;
        }
    }



    public function listar($id_veterinaria)
    {
        try {
            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.fecha_contratacion,  r.nombre AS nombre_rol, vet.nombre AS nombre_veterinaria FROM usuario u  INNER JOIN rol r ON u.id_rol = r.id_rol INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE v.id_veterinaria = :id_veterinaria  AND u.id_rol = 2 ORDER BY v.nombres ASC, v.apellidos ASC";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id_veterinaria', $id_veterinaria, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en veterinario::listar - " . $e->getMessage());
            return [];
        }
    }

    public function listarVeterinario($id)
    {
        try {
            $consultar = "SELECT  u.id_usuario, u.email, u.estado, u.fecha_creacion, u.ultimo_acceso, u.id_rol, v.id_veterinario, v.tipo_documento, v.numero_documento, v.nombres, v.apellidos, r.nombre AS nombre_rol, v.telefono, v.img_perfil, v.numero_licencia_profesional, v.id_veterinaria, v.fecha_contratacion, vet.nombre AS nombre_veterinaria FROM usuario u INNER JOIN rol r ON u.id_rol = r.id_rol INNER JOIN veterinario v ON u.id_usuario = v.id_usuario INNER JOIN veterinaria vet ON v.id_veterinaria = vet.id_veterinaria WHERE u.id_usuario = :id  LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id, PDO::PARAM_INT);
            $resultado->execute();

            return $resultado->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en veterinario::listarVeterinario - " . $e->getMessage());
            return false;
        }
    }


    public function actualizar($data)
    {
        try {
            // Iniciamos una transacción
            $this->conexion->beginTransaction();

            // 1. Actualizamos la tabla usuario
            $actualizarUsuario = "UPDATE usuario SET email = :email, estado = :estado WHERE id_usuario = :id_usuario";

            $resultadoUsuario = $this->conexion->prepare($actualizarUsuario);
            $resultadoUsuario->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $resultadoUsuario->bindParam(':email', $data['email']);
            $resultadoUsuario->bindParam(':estado', $data['estado']);
            $resultadoUsuario->execute();

            // 2. Actualizamos la tabla veterinario
            $actualizarVeterinario = "UPDATE veterinario  SET tipo_documento = :tipo_documento,  numero_documento = :numero_documento,  nombres = :nombres,  apellidos = :apellidos,  telefono = :telefono, numero_licencia_profesional = :numero_licencia_profesional WHERE id_usuario = :id_usuario";

            $resultadoVeterinario = $this->conexion->prepare($actualizarVeterinario);
            $resultadoVeterinario->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
            $resultadoVeterinario->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultadoVeterinario->bindParam(':numero_documento', $data['numero_documento']);
            $resultadoVeterinario->bindParam(':nombres', $data['nombres']);
            $resultadoVeterinario->bindParam(':apellidos', $data['apellidos']);
            $resultadoVeterinario->bindParam(':telefono', $data['telefono']);
            $resultadoVeterinario->bindParam(':numero_licencia_profesional', $data['numero_licencia_profesional']);
            $resultadoVeterinario->execute();

            // Confirmamos la transacción
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            // Si hay error, revertimos los cambios
            $this->conexion->rollBack();
            error_log("Error en veterinario::actualizar - " . $e->getMessage());
            return false;
        }
    }


    public function actualizarFotoPerfil($data)
    {
        // Actualizamos la foto de perfil del usuario
        try {
            $sql = "UPDATE profesional SET img_perfil = :img_perfil
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




    public function eliminar($id)
    {
        try {
            // Actualizamos el estado del usuario a 'inactivo'
            $actualizar = "UPDATE usuario SET
                        estado = :estado
                        WHERE id_usuario = :id_usuario";
            // Preparar y ejecutar la consulta
            $resultado = $this->conexion->prepare($actualizar);

            $resultado->bindValue(':estado', 'inactivo');
            $resultado->bindValue(':id_usuario', $id);

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::eliminar - " . $e->getMessage());
            return false;
        }
    }

    // Método adicional para actualizar la imagen de perfil
    public function actualizarImagen($id_usuario, $img_perfil)
    {
        try {
            $actualizar = "UPDATE veterinario 
                        SET img_perfil = :img_perfil 
                        WHERE id_usuario = :id_usuario";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $resultado->bindParam(':img_perfil', $img_perfil);
            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en veterinario::actualizarImagen - " . $e->getMessage());
            return false;
        }
    }
}
