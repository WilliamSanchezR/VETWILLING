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

    public function registrar($data)
    {


        try {


            $insertar = "INSERT INTO usuario(
                    email,
                    password_hash,
                    estado,
                    id_rol
                )
                VALUES(
                    :email,
                    :password_hash,
                    :estado,
                    :id_rol
                )";

            $resultado = $this->conexion->prepare($insertar);
            $resultado->bindParam(':email', $data['email']);
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $resultado->bindParam(':password_hash', $passwordHash);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_rol', $data['id_rol']);


            $respCreacion = $resultado->execute();

            // return  $respCreacion;

            if ($respCreacion === true) {

                $consultaUsuario = "SELECT * FROM usuario WHERE email = :emailUser";
                $respuestaConsulta = $this->conexion->prepare($consultaUsuario);
                $respuestaConsulta->bindParam(':emailUser', $data['email']);

                $respuestaConsulta->execute();
                $idUser = $respuestaConsulta->fetch();

                echo($idUser['id_usuario']);
                echo($data['id_rol']);

                if ($data['id_rol'] == '1') {
                    $insertAdm = "INSERT INTO administrador(id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso) 
                    VALUES (
                            :id_usuario, 
                            :tipo_documento, 
                            :numero_documento, 
                            :nombres, 
                            :apellidos, 
                            :telefono, 
                            :img_perfil, 
                            :nivel_acceso
                    )";

                    $resultadoCreate = $this->conexion->prepare($insertAdm);

                    $resultadoCreate->bindParam(':id_usuario', $idUser['id_usuario']);
                    $resultadoCreate->bindParam(':tipo_documento', $data['tipo_documento']);
                    $resultadoCreate->bindParam(':numero_documento', $data['numero_documento']);
                    $resultadoCreate->bindParam(':nombres', $data['nombres']);
                    $resultadoCreate->bindParam(':apellidos', $data['apellidos']);
                    $resultadoCreate->bindParam(':telefono', $data['telefono']);
                    $resultadoCreate->bindParam(':img_perfil', $data['img_perfil']);
                    $resultadoCreate->bindParam(':nivel_acceso', $data['nivel_acceso']);

                    return $resultadoCreate->execute();
                } else if ($data['id_rol'] == '3') {
                    echo('Insert de Representante legal');
                    $insertRepre = "INSERT INTO representante_legal(id_veterinaria, id_usuario, tipo_documento, numero_documento, nombres, apellidos, telefono, img_perfil, nivel_acceso) 
                        VALUES (
                                :id_veterinaria,
                                :id_usuario, 
                                :tipo_documento, 
                                :numero_documento, 
                                :nombres, 
                                :apellidos, 
                                :telefono, 
                                :img_perfil, 
                                :nivel_acceso
                        )";

                    $respCreaReppre = $this->conexion->prepare($insertRepre);

                    $respCreaReppre->bindParam(':id_veterinaria', $data['id_veterinaria']);
                    $respCreaReppre->bindParam(':id_usuario', $idUser['id_usuario']);
                    $respCreaReppre->bindParam(':tipo_documento', $data['tipo_documento']);
                    $respCreaReppre->bindParam(':numero_documento', $data['numero_documento']);
                    $respCreaReppre->bindParam(':nombres', $data['nombres']);
                    $respCreaReppre->bindParam(':apellidos', $data['apellidos']);
                    $respCreaReppre->bindParam(':telefono', $data['telefono']);
                    $respCreaReppre->bindParam(':img_perfil', $data['img_perfil']);
                    $respCreaReppre->bindParam(':nivel_acceso', $data['nivel_acceso']);

                    return $respCreaReppre->execute();
                }
            }
        } catch (PDOException $e) {
            echo($e);
            error_log("Error en el instructor::registrar " . $e->getMessage());
            return false;
        }
    }

    // Funcuion para listar usuarios
    public function listar()
    {
        try {
            $listar = "SELECT adm.id_usuario as id_usuario, adm.tipo_documento as tipo_documento, adm.numero_documento as numero_documento, adm.nombres as nombres, adm.apellidos as apellidos, adm.telefono as telefono, us.email as email, us.estado as estado, rol.nombre as rol
                        FROM usuario us
                        INNER JOIN administrador adm on us.id_usuario = adm.id_usuario
                        INNER JOIN rol on us.id_rol = rol.id_rol
                    -- UNION
                    -- (
                    --     SELECT vet.id_usuario as id_usuario, vet.tipo_documento as tipo_documento, vet.numero_documento as numero_documento, vet.nombres as nombres, vet.apellidos as apellidos, vet.telefono as telefono, us.email as email, us.estado as estado, rol.nombre as rol
                    --     FROM usuario us
                    --     INNER JOIN veterinario vet on us.id_usuario = vet.id_usuario
                    --     INNER JOIN rol on us.id_rol = rol.id_rol
                    -- )
                     UNION
                    (
                        SELECT rep.id_usuario as id_usuario, rep.tipo_documento as tipo_documento, rep.numero_documento as numero_documento, rep.nombres as nombres, rep.apellidos as apellidos, rep.telefono as telefono, us.email as email, us.estado as estado, rol.nombre as rol
                        FROM usuario us
                        INNER JOIN representante_legal rep on us.id_usuario = rep.id_usuario
                        INNER JOIN rol on us.id_rol = rol.id_rol
                    )  ORDER by id_usuario ASC";
            $resultado = $this->conexion->prepare($listar);
            $resultado->execute();
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en el usuario::listar " . $e->getMessage());
            return [];
        }
    }

    // Funcion para consultar usuario por id
    public function consultarUsuario($id)
    {

        try {

            $consultar = "SELECT adm.id_usuario as id_usuario, adm.tipo_documento as tipo_documento, adm.numero_documento as numero_documento, adm.nombres as nombres, adm.apellidos as apellidos, adm.telefono as telefono, us.email as email, us.estado as estado, rol.nombre as rol
                        FROM usuario us
                        INNER JOIN administrador adm on us.id_usuario = adm.id_usuario
                        INNER JOIN rol on us.id_rol = rol.id_rol WHERE usuario.id_usuario = :id LIMIT 1;";

            $resultado = $this->conexion->prepare($consultar);

            $resultado->bindParam(':id', $id);

            $resultado->execute();

            $datosAdmin = $resultado->fetch();
            if ($datosAdmin != null) {
                return$datosAdmin;
            }

            $consultaRep = "SELECT rep.id_usuario as id_usuario, rep.tipo_documento as tipo_documento, rep.numero_documento as numero_documento, rep.nombres as nombres, rep.apellidos as apellidos, rep.telefono as telefono, us.email as email, us.estado as estado, rol.nombre as rol
                        FROM usuario us
                        INNER JOIN representante_legal rep on us.id_usuario = rep.id_usuario
                        INNER JOIN rol on us.id_rol = rol.id_rol
                        WHERE usuario.id_usuario = :id LIMIT 1;";

            $resultadoRep = $this->conexion->prepare($consultaRep);

            $resultadoRep->bindParam(':id', $id);

            $resultadoRep->execute();

            return  $resultadoRep->fetch();

        } catch (PDOException $e) {
            error_log("Error en el instructor::registrar " . $e->getMessage());
            return false;
        }
    }

    // Funcion para actualizar los usuarios 
    public function actualizarUsuario($data)
    {
        try {
            $actualizar = "UPDATE usuario SET
                            tipo_documento = :tipo_documento,
                            numero_documento = :numero_documento,
                            nombres = :nombres,
                            apellidos = :apellidos,
                            telefono = :telefono,
                            email = :email,
                            estado = :estado,
                            id_rol = :id_rol
                            WHERE id_usuario = :id_usuario
                            LIMIT 1";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id_usuario', $data['id_usuario']);
            $resultado->bindParam(':tipo_documento', $data['tipo_documento']);
            $resultado->bindParam(':numero_documento', $data['numero_documento']);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':estado', $data['estado']);
            $resultado->bindParam(':id_rol', $data['id_rol']);

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en el usuario::actualizarUsuario " . $e->getMessage());
            return false;
        }
    }

    //    Funcion para eliminar usuarios
    public function elimimarUsuario() {}
}
