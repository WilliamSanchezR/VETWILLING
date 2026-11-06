<?php

require_once __DIR__ . '/../../config/database.php';

class EquipoSistema
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new conexion())->getConexion();
    }

    public function listar(): array
    {
        return $this->conexion
            ->query('SELECT * FROM equipo_sistema ORDER BY orden, id_equipo')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorUsuario(int $idUsuario): ?array
    {
        $stmt = $this->conexion->prepare('SELECT * FROM equipo_sistema WHERE id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function guardar(int $idUsuario, array $datos): bool
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO equipo_sistema (id_usuario, nombre, rol_equipo, habilidades, foto, github, linkedin, correo)
             VALUES (:id, :nombre, :rol, :habilidades, :foto, :github, :linkedin, :correo)
             ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), rol_equipo = VALUES(rol_equipo),
                 habilidades = VALUES(habilidades), foto = VALUES(foto), github = VALUES(github),
                 linkedin = VALUES(linkedin), correo = VALUES(correo)'
        );
        return $stmt->execute([
            ':id'          => $idUsuario,
            ':nombre'      => $datos['nombre'],
            ':rol'         => $datos['rol_equipo'],
            ':habilidades' => $datos['habilidades'],
            ':foto'        => $datos['foto'],
            ':github'      => $datos['github'],
            ':linkedin'    => $datos['linkedin'],
            ':correo'      => $datos['correo'],
        ]);
    }
}
