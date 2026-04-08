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

            // Consultamos si existe el plan
            $consultaPlan = "SELECT * FROM plan WHERE id_plan = :id";
            $resultadoPlan = $this->conexion->prepare($consultaPlan);
            $resultadoPlan->bindParam(':id', $data['plan']);
            $resultadoPlan->execute();
            $plan = $resultadoPlan->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                throw new Exception("El plan seleccionado no existe.");
            }

            // Validamos que la compañía no exista 
            $consultaCompania = "SELECT * FROM veterinaria WHERE nit = :nit";
            $resultadoCompania = $this->conexion->prepare($consultaCompania);
            $resultadoCompania->bindParam(':nit', $data['nit']);
            $resultadoCompania->execute();
            $compania = $resultadoCompania->fetch(PDO::FETCH_ASSOC);

            if ($compania) {
                throw new Exception("La compañía ya existe.");
            }


            // Validamos que el representante legal no exista
            $consultaRepresentante = "SELECT * FROM usuario WHERE email = :email";
            $resultadoRepresentante = $this->conexion->prepare($consultaRepresentante); 

            $resultadoRepresentante->bindParam(':email', $data['email']);
            $resultadoRepresentante->execute();
            $representante = $resultadoRepresentante->fetch(PDO::FETCH_ASSOC);

            if ($representante) {
                throw new Exception("El representante legal ya existe.");
            }

            // Creamos la veterinaria

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
                    $resultadoSuscripcion = $this->registrarSuscripcion($data['plan'], $idVeterinaria);
                    if ($resultadoSuscripcion) {
                        $this->conexion->commit();
                        return $resultadoSuscripcion; // Retornamos el ID de la suscripción para redirigir al usuario a la página de pago
                    } else {
                        $this->conexion->rollBack();
                        return 0;
                    }
                }

                $this->conexion->rollBack();
                return 0;
            } else {
                // Si hubo un error al incertar realizamos el rollback
                $this->conexion->rollBack();
                return 0;
            }
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log('Error en VeterinariaRegistrarse::registrarse -> ' . $e->getMessage());

            if ($e instanceof PDOException) {
                throw new Exception('Ocurrió un error al procesar el registro. Intenta nuevamente.');
            }

            throw $e;
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
            error_log('Error al registrar el representante legal: ' . $e->getMessage());
            throw new Exception('No se pudo registrar el representante legal.');
        }
    }

    private function registrarSuscripcion($idPlan, $idVeterinaria) {
            try {
                $consulta = "INSERT INTO suscripcion(id_veterinaria, id_plan, fecha_inicio, fecha_fin, estado, auto_renovacion) 
                            VALUES(:id_veterinaria, :id_plan, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'pendiente', 0)";
    
                $resultado = $this->conexion->prepare($consulta);
                $resultado->bindParam(':id_veterinaria', $idVeterinaria);
                $resultado->bindParam(':id_plan', $idPlan);
    
                $resultado->execute();

                // Retornamos el ID de la suscripción recién creada
                return $this->conexion->lastInsertId();
    
            } catch (PDOException $e) {
                error_log('Error al registrar la suscripción: ' . $e->getMessage());
                throw new Exception('No se pudo crear la suscripción inicial.');
            }
    }

}

