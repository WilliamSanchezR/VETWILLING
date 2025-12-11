<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Eventos.php';

class EventController
{

    protected $eventModel;

    // Constructor: Inicializa el Modelo
    public function __construct()
    {
        // Creamos una instancia del modelo de Eventos
        $this->eventModel = new EventModel();
    }

    // ----------------------------------------------------------------------
    // 1. CARGAR EVENTOS (Petición GET desde FullCalendar: events: './events')
    // ----------------------------------------------------------------------
    public function load()
    {
        // 1. Pedimos al Modelo que nos traiga todos los eventos de la DB
        $db_events = $this->eventModel->getAllEvents();

        $calendar_events = []; // Array que FullCalendar entenderá

        // 2. Convertimos el formato de la DB al formato de FullCalendar (JSON)
        foreach ($db_events as $event) {
            $calendar_events[] = [
                'id'    => $event['id'],
                'title' => $event['title'],
                // FullCalendar requiere 'start' y 'end'
                'start' => $event['start_date'],
                'end'   => $event['end_date'],
                // ... otros campos
            ];
        }

        // 3. Devolvemos la respuesta como JSON
        header('Content-Type: application/json');
        echo json_encode($calendar_events);
        // En frameworks modernos, esto sería: return response()->json($calendar_events);
    }

    // ----------------------------------------------------------------------
    // 2. CREAR EVENTO (Petición POST desde FullCalendar: CREATE_URL: './events/create')
    // ----------------------------------------------------------------------
    public function store()
    {
        // 1. Obtener y sanitizar los datos JSON enviados por el frontend
        $data = json_decode(file_get_contents("php://input"), true);

        // Aseguramos que las variables están definidas
        $title = $data['title'] ?? 'Nuevo Evento';
        $start = $data['start'] ?? null;
        $end = $data['end'] ?? null;

        if (!$start) {
            // Si no hay fecha de inicio, no podemos guardar.
            return $this->errorResponse('Falta la fecha de inicio.');
        }

        // 2. Pedimos al Modelo que inserte el nuevo evento y retorne el ID
        $newId = $this->eventModel->createEvent($title, $start, $end);

        if ($newId) {
            // 3. Devolvemos el ID generado por la DB, crucial para que el JS lo use.
            return $this->successResponse('Evento creado.', ['id' => $newId]);
        } else {
            return $this->errorResponse('Error al insertar en la base de datos.');
        }
    }

    // ----------------------------------------------------------------------
    // 3. MODIFICAR EVENTO (Petición POST desde FullCalendar: UPDATE_URL: './events/update')
    // ----------------------------------------------------------------------
    public function update()
    {
        // 1. Obtener y sanitizar los datos JSON
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data['id'] ?? null;
        $start = $data['new_start'] ?? null;
        $end = $data['new_end'] ?? null;

        if (!$id || !$start) {
            return $this->errorResponse('Datos incompletos para actualizar.');
        }

        // 2. Pedimos al Modelo que actualice el registro
        $success = $this->eventModel->updateEventDates($id, $start, $end);

        if ($success) {
            return $this->successResponse('Evento actualizado.');
        } else {
            return $this->errorResponse('Error al actualizar o el ID no existe.');
        }
    }

    // Funciones helper para las respuestas JSON
    private function successResponse($message, $extra = [])
    {
        header('Content-Type: application/json');
        // Unir el status/message con cualquier dato extra (como el 'id')
        echo json_encode(array_merge(['status' => 'success', 'message' => $message], $extra));
        return;
    }

    private function errorResponse($message)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        return;
    }
}
