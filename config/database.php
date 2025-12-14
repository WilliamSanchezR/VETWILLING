<?php

// Usamos una clase con propiedades privadas para guardar las credenciales de la base de datos (host, usuario, contraseña, y nombre de la BD).

// Lo hacemos asi para que nadie fuera de la clase pueda acceder o modificar esos datos directamente.

class Conexion
{
    private $host = "localhost";
    private $db = "vetwilling";
    private $user = "root";
    private $pass = "";
    private $conexion;

    // El constructor (_construct se ejecuta automaticamente cuando creamos un objeto de la clase, y se encarga de abrir la conexion con la base de datos usando PDO.

    public function __construct()
    {

        // La palabra $this significa literalmente 'esta clase'. la usamos para acceder a las variables interna de la misma clase. por ejemplo, $this->conexion hace referencia a la conexion que pertenece a esta instancia de la clase.

        try {
            $this->conexion = new PDO("mysql:host={$this->host};dbname={$this->db};charset=utf8", $this->user, $this->pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexion: " . $e->getMessage());
        }
    }

    // finalmente, el metodo getConexion() sirve para obtener la conexion ya creada. En vez de abrir una nueva conexion cada vez, simplemente pedimos la que ya existe dentro del objeto.

    public function getConexion()
    {
        return $this->conexion;
    }
}

// En resumen: la clase guarda las credenciales de forma segura. El constructor abre la conexion automaticamnete. $this permite acceder a las variables internas de la clase. getConexino() nos devuelve la conexion para poder ejecutar consultas. De esta forma el codigo queda mas limpio, reutilizable y facil de mantener.
