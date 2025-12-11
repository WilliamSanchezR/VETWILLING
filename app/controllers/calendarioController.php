<?php

// Importamos las dependencias

require __DIR__ . '/../helpers/alert_helpers.php';
require __DIR__ . '/../models/Eventos.php';

class calendarioController
{

    protected $agendamientoModel;

    // Constructor: Se ejecuta al crear una instancia del controlador
    public function __construct()
    {
        // Inicializamos el modelo para poder usar sus métodos de DB
        $this->agendamientoModel = new Eventos();
    }

    // Función helper para devolver una respuesta JSON de éxito
    private function successResponse($message, $extra = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success', 'message' => $message], $extra));
        exit; // Detenemos la ejecución después de enviar la respuesta
    }

    // Función helper para devolver una respuesta JSON de error
    private function errorResponse($message)
    {
        header('Content-Type: application/json');
        // Se podría enviar un código de estado HTTP 400 o 500 aquí
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    // ----------------------------------------------------------------------
    // 1. CARGAR EVENTOS (Mapeado a la ruta: './calendario/loadEvents' con GET)
    // ----------------------------------------------------------------------
    // Este método es llamado por FullCalendar al inicio o al cambiar de mes.
    public function loadEvents()
    {
        // 1. Obtener todos los agendamientos desde el Modelo
        // Necesitas implementar este método en Eventos.php para hacer un SELECT
        $db_agendamientos = $this->agendamientoModel->getAllAgendamientos();

        $calendar_events = []; // Array que FullCalendar entenderá

        // 2. Mapear los nombres de las columnas de la DB a los nombres de FullCalendar
        foreach ($db_agendamientos as $agendamiento) {
            $calendar_events[] = [
                // FullCalendar requiere 'id'
                'id'    => $agendamiento['id_agendamiento'],
                // FullCalendar requiere 'title'. Usamos el 'tipo' de tu DB.
                'title' => $agendamiento['tipo'],
                // FullCalendar requiere 'start'. Usamos tu 'fecha_hora'.
                'start' => $agendamiento['fecha_hora'],
                // FullCalendar requiere 'end'. Usamos 'fecha_hora_fin' (si lo añadiste).
                'end'   => $agendamiento['fecha_hora_fin'] ?? null,
                // Opcional: Para colorear el evento según el estado
                'backgroundColor' => $this->getColorByEstado($agendamiento['estado']),
                'allDay' => false // Asumimos que no son de día completo, sino con hora
            ];
        }

        // 3. Devolvemos la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($calendar_events);
    }

    // ----------------------------------------------------------------------
    // 2. CREAR EVENTO (Mapeado a la ruta: './calendario/storeEvent' con POST)
    // ----------------------------------------------------------------------
    // Este método guarda un nuevo agendamiento (desde select o drop).
    public function storeEvent()
    {
        // 1. Obtener y decodificar los datos JSON enviados por el frontend
        $data = json_decode(file_get_contents("php://input"), true);

        // **Validación básica de campos críticos**
        if (empty($data['tipo']) || empty($data['fecha_hora'])) {
            return $this->errorResponse('Título o fecha de inicio faltante.');
        }

        // 2. Asignar los valores del JS a la estructura de la DB (o a un objeto de datos)
        $agendamientoData = [
            'tipo'          => $data['tipo'],
            'fecha_hora'    => $data['fecha_hora'],
            // Si el cliente no envió fin, será NULL.
            'fecha_hora_fin' => $data['fecha_hora_fin'] ?? null,
            'id_usuario'    => $_SESSION['id_usuario'] ?? 1, // Ejemplo: obtener de la sesión
            // Puedes establecer un estado inicial
            'estado'        => 'Pendiente',
        ];

        // 3. Llamar al Modelo para insertar el nuevo agendamiento
        // Implementar este método en Eventos.php, debe devolver el ID generado (id_agendamiento)
        $newId = $this->agendamientoModel->createAgendamiento($agendamientoData);

        if ($newId) {
            // 4. Respuesta de éxito, devolviendo el ID.
            return $this->successResponse('Agendamiento creado con éxito.', ['id' => $newId]);
        } else {
            return $this->errorResponse('Error al guardar el agendamiento en la DB.');
        }
    }

    // ----------------------------------------------------------------------
    // 3. MODIFICAR EVENTO (Mapeado a la ruta: './calendario/updateEvent' con POST)
    // ----------------------------------------------------------------------
    // Este método actualiza las fechas/horas (desde eventDrop o eventResize).
    public function updateEvent()
    {
        // 1. Obtener y decodificar los datos JSON
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data['id_agendamiento'] ?? null;
        $start = $data['new_fecha_hora'] ?? null;
        $end = $data['new_fecha_hora_fin'] ?? null;
        $action = $data['action'] ?? 'move';

        if (!$id || !$start) {
            return $this->errorResponse('ID o fecha de inicio faltante para actualizar.');
        }

        // 2. Preparar los datos para el Modelo
        $updateData = [
            'id_agendamiento' => $id,
            'fecha_hora'      => $start,
            'fecha_hora_fin'  => $end,
            'action_type'     => $action // Opcional, para logs o lógica compleja
        ];

        // 3. Llamar al Modelo para actualizar el registro
        // Implementar este método en Eventos.php, debe devolver true/false
        $success = $this->agendamientoModel->updateAgendamientoDates($updateData);

        if ($success) {
            return $this->successResponse('Agendamiento actualizado.');
        } else {
            return $this->errorResponse('Error al actualizar o el ID no fue encontrado.');
        }
    }

    // Opcional: Función para asignar colores según el estado
    private function getColorByEstado($estado)
    {
        switch ($estado) {
            case 'Confirmado':
                return '#28a745'; // Verde
            case 'Pendiente':
                return '#ffc107'; // Amarillo
            case 'Cancelado':
                return '#dc3545'; // Rojo
            default:
                return '#007bff'; // Azul por defecto
        }
    }
}
