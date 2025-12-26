<?php
class SoporteController {
    
    // public function index() {
    //     require_once 'views/soporte/index.php';
    // }
    
    public function enviarTicket() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar datos
            $asunto = trim($_POST['asunto'] ?? '');
            $mensaje = trim($_POST['mensaje'] ?? '');
            $categoria = $_POST['categoria'] ?? '';
            
            // Aquí puedes guardar en BD o enviar email
            // Por ahora solo simulo una respuesta
            
            $_SESSION['mensaje_exito'] = 'Tu solicitud ha sido enviada correctamente';
            header('Location: ' . BASE_URL . 'soporte');
            exit;
        }
    }
}