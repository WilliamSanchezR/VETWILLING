<?php

// app/Models/EventModel.php (Ejemplo simplificado)

class EventModel
{

    private $db; // Objeto de conexión a la base de datos

    public function __construct()
    {
        // Inicializar la conexión a la base de datos aquí (Ej: $this->db = new PDO(...))
        // Por simplicidad, asumimos que $this->db ya está configurado.
    }

    // --- Lógica para cargar todos los eventos (usada por EventController::load) ---
    public function getAllEvents()
    {
        // CONSULTA: SELECT id, title, start_date, end_date FROM events
        // Ejecutar la consulta...
        // Devolver un array de eventos
        // return $result; 
        return []; // Retorna un array vacío o el resultado de la DB
    }

    // --- Lógica para crear un nuevo evento (usada por EventController::store) ---
    public function createEvent($title, $start, $end)
    {
        // Preparamos la consulta INSERT (¡Usar Prepared Statements!)
        // QUERY: INSERT INTO events (title, start_date, end_date) VALUES (?, ?, ?)

        // Ejecutar la inserción...

        // Devolvemos el ID que generó la base de datos (Ej: $this->db->lastInsertId())
        $fake_id = rand(100, 999); // Simulación de un ID generado
        return $fake_id;
    }

    // --- Lógica para actualizar las fechas (usada por EventController::update) ---
    public function updateEventDates($id, $start, $end)
    {
        // Preparamos la consulta UPDATE (¡Usar Prepared Statements!)
        // QUERY: UPDATE events SET start_date = ?, end_date = ? WHERE id = ?

        // Ejecutar la actualización...

        // Devolvemos TRUE si se actualizó una fila, FALSE si hubo error
        return true;
    }
}
