<?php

// Verificar que el usuario tiene sesión activa y es representante
require_once __DIR__ . '/../helpers/session_representante.php';

// Importar modelos necesarios
require_once __DIR__ . '/../models/MovimientoStock.php';
require_once __DIR__ . '/../models/Inventario.php';

class MovimientosStockController
{
    private $movimientoStock;
    private $inventario;
    private $id_veterinaria;
    private $id_usuario;

    public function __construct()
    {
        $this->movimientoStock = new MovimientoStock();
        $this->inventario = new Inventario();
        $this->id_veterinaria = $_SESSION['id_veterinaria'] ?? null;
        $this->id_usuario = $_SESSION['id_usuario'] ?? null;
    }

    // Mostrar formulario y historial de movimientos
    public function index()
    {
        // Obtener historial de movimientos de la veterinaria
        $historial = $this->movimientoStock->obtenerHistorialPorVeterinaria($this->id_veterinaria);

        require_once __DIR__ . '/../views/dashboard/representante/registroMovimientos.php';
    }

    // Procesar registro de nuevo movimiento
    public function registrar()
    {
        // Validar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }

        // Obtener datos del formulario
        $id_inventario = (int) ($_POST['id_inventario'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $cantidad = (int) ($_POST['cantidad'] ?? 0);
        $motivo = $_POST['motivo'] ?? '';

        // Validaciones básicas
        if (!$id_inventario || !$tipo || $cantidad <= 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos o inválidos']);
            return;
        }

        // Validar que el tipo sea permitido
        $tiposValidos = ['entrada', 'salida', 'ajuste'];
        if (!in_array($tipo, $tiposValidos)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Tipo de movimiento no válido']);
            return;
        }

        // Si es salida, validar que haya stock suficiente
        if ($tipo === 'salida') {
            if (!$this->inventario->validarDisponibilidad($id_inventario, $cantidad)) {
                echo json_encode(['exito' => false, 'mensaje' => 'Stock insuficiente para esta operación']);
                return;
            }
        }

        // Registrar el movimiento
        $resultado = $this->movimientoStock->registrarMovimiento(
            $id_inventario,
            $tipo,
            $cantidad,
            $motivo,
            $this->id_usuario
        );

        if ($resultado) {
            // Si es entrada, incrementar stock
            if ($tipo === 'entrada') {
                $this->inventario->incrementarStock($id_inventario, $cantidad, 'entrada', $this->id_usuario);
            }
            // Si es salida, decrementar stock
            elseif ($tipo === 'salida') {
                $this->inventario->decrementarStock($id_inventario, $cantidad, 'salida', $this->id_usuario);
            }
            // Si es ajuste, actualizar la cantidad directamente (no se usa inc/dec)

            echo json_encode(['exito' => true, 'mensaje' => 'Movimiento registrado correctamente']);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al registrar el movimiento']);
        }
    }

    // Obtener historial paginado con filtros
    public function obtenerHistorial()
    {
        // Parámetros opcionales
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;

        // Obtener historial
        $historial = $this->movimientoStock->obtenerHistorialPorVeterinaria(
            $this->id_veterinaria,
            $fecha_inicio,
            $fecha_fin
        );

        echo json_encode([
            'exito' => true,
            'historial' => $historial,
            'total' => count($historial)
        ]);
    }

    // Obtener resumen de movimientos en un período
    public function obtenerResumen()
    {
        // Parámetros requeridos
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');       // Hoy

        // Obtener resumen agrupado por tipo
        $resumen = $this->movimientoStock->obtenerResumenPeriodo(
            $this->id_veterinaria,
            $fecha_inicio,
            $fecha_fin
        );

        echo json_encode([
            'exito' => true,
            'resumen' => $resumen,
            'periodo' => [
                'inicio' => $fecha_inicio,
                'fin' => $fecha_fin
            ]
        ]);
    }
}

// Procesar la acción solicitada
$accion = $_GET['accion'] ?? 'index';
$controlador = new MovimientosStockController();

switch ($accion) {
    case 'registrar':
        $controlador->registrar();
        break;
    case 'historial':
        $controlador->obtenerHistorial();
        break;
    case 'resumen':
        $controlador->obtenerResumen();
        break;
    default:
        $controlador->index();
        break;
}
?>
