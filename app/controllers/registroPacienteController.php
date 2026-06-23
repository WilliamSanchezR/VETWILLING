<?php

require_once __DIR__ . "/../helpers/alert_helpers.php";
require_once __DIR__ . "/../models/Propietario.php";
require_once __DIR__ . "/../models/Mascotas.php";
require_once __DIR__ . "/../models/PacienteProfesionalAsignacion.php";
require_once __DIR__ . "/../models/DisponibilidadUsuario.php";
require_once __DIR__ . "/../models/Veterinario.php";
require_once __DIR__ . "/../helpers/mailer_helper.php";
require_once __DIR__ . "/../helpers/email_helper.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    registrarPacienteConPropietario();
}

function resolverIdVeterinariaRegistro(): ?int
{
    $idVeterinariaSesion =
        $_SESSION["user"]["id_veterinaria"] ??
        ($_SESSION["id_veterinaria"] ?? null);

    if (!empty($idVeterinariaSesion)) {
        if (
            is_string($idVeterinariaSesion) &&
            strpos($idVeterinariaSesion, ",") !== false
        ) {
            $idVeterinariaSesion = trim(explode(",", $idVeterinariaSesion)[0]);
        }

        if (is_numeric($idVeterinariaSesion)) {
            return (int) $idVeterinariaSesion;
        }
    }

    $idUsuario = $_SESSION["user"]["id_usuario"] ?? null;
    if (empty($idUsuario)) {
        return null;
    }

    $disponibilidadModel = new DisponibilidadUsuario();
    $idVeterinariaRelacion = $disponibilidadModel->obtenerVeterinariaPorUsuario(
        (int) $idUsuario,
    );
    if (!empty($idVeterinariaRelacion)) {
        return (int) $idVeterinariaRelacion;
    }

    return null;
}

function registrarPacienteConPropietario()
{
    // Establecer header JSON
    header("Content-Type: application/json");

    $id_veterinaria = resolverIdVeterinariaRegistro();

    // Verificar que el usuario esté autenticado
    if (empty($id_veterinaria)) {
        echo json_encode([
            "success" => false,
            "message" => "No se pudo identificar la veterinaria",
        ]);
        exit();
    }

    $id_usuario_profesional = isset($_SESSION["user"]["id_usuario"])
        ? (int) $_SESSION["user"]["id_usuario"]
        : null;

    // Log para debug
    error_log("ID Veterinaria obtenida: " . $id_veterinaria);
    error_log("POST recibido: " . print_r($_POST, true));

    // Capturar datos del propietario
    $nombres = $_POST["nombres"] ?? "";
    $apellidos = $_POST["apellidos"] ?? "";
    $tipo_documento = $_POST["tipo_documento"] ?? "";
    $numero_documento = $_POST["numero_documento"] ?? "";
    $telefono = $_POST["telefono"] ?? "";
    $email = $_POST["email"] ?? "";
    $direccion = $_POST["direccion"] ?? "";

    // Capturar datos de mascotas (nuevo formato) o mascota única (compatibilidad)
    $mascotas = [];

    if (!empty($_POST["mascotas"])) {
        $mascotasDecodificadas = json_decode($_POST["mascotas"], true);
        if (is_array($mascotasDecodificadas)) {
            $mascotas = $mascotasDecodificadas;
        }
    }

    if (empty($mascotas)) {
        $mascotas[] = [
            "nombre" => $_POST["nombre_mascota"] ?? "",
            "especie" => $_POST["especie"] ?? "",
            "raza" => $_POST["raza"] ?? "",
            "sexo" => $_POST["sexo"] ?? "",
            "edad_numero" => $_POST["edad_numero"] ?? "",
            "edad_unidad" => $_POST["edad_unidad"] ?? "",
        ];
    }

    // Validar campos obligatorios del propietario
    if (
        empty($nombres) ||
        empty($apellidos) ||
        empty($tipo_documento) ||
        empty($numero_documento) ||
        empty($telefono) ||
        empty($email) ||
        empty($direccion)
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Complete todos los campos del propietario",
        ]);
        exit();
    }

    // Validar campos obligatorios de cada mascota
    foreach ($mascotas as $index => $mascota) {
        $nombreMascota = trim($mascota["nombre"] ?? "");
        $especieMascota = trim($mascota["especie"] ?? "");
        $razaMascota = trim($mascota["raza"] ?? "");
        $sexoMascota = trim($mascota["sexo"] ?? "");
        $edadNumeroMascota = trim((string) ($mascota["edad_numero"] ?? ""));
        $edadUnidadMascota = trim($mascota["edad_unidad"] ?? "");

        if (
            $nombreMascota === "" ||
            $especieMascota === "" ||
            $razaMascota === "" ||
            $sexoMascota === "" ||
            $edadNumeroMascota === "" ||
            $edadUnidadMascota === ""
        ) {
            $numeroMascota = $index + 1;
            echo json_encode([
                "success" => false,
                "message" => "Complete todos los campos de la mascota #{$numeroMascota}",
            ]);
            exit();
        }
    }

    try {
        // 1. VALIDAR DUPLICIDAD - Verificar si el propietario ya existe
        $propietarioModel = new Propietario();

        if (
            $propietarioModel->existePropietario(
                $numero_documento,
                $id_veterinaria,
            )
        ) {
            $propietarioExistente = $propietarioModel->obtenerPropietarioExistente(
                $numero_documento,
                $id_veterinaria,
            );

            if ($propietarioExistente) {
                error_log(
                    "⚠️ Cliente duplicado detectado. Documento: " .
                        $numero_documento .
                        " ID: " .
                        $propietarioExistente["id_propietario"],
                );

                echo json_encode([
                    "success" => false,
                    "message" =>
                        "Este cliente ya está registrado en el sistema. Usa el registro existente.",
                    "cliente_existente" => true,
                    "id_propietario" => $propietarioExistente["id_propietario"],
                ]);
                exit();
            }
        }

        // 2. Registrar el propietario
        $dataPropietario = [
            "tipo_documento" => $tipo_documento,
            "numero_documento" => $numero_documento,
            "nombres" => $nombres,
            "apellidos" => $apellidos,
            "telefono" => $telefono,
            "direccion" => $direccion,
            "id_veterinaria" => $id_veterinaria,
            "email" => $email,
        ];

        error_log(
            "Datos propietario a insertar: " . print_r($dataPropietario, true),
        );

        $id_propietario = $propietarioModel->registrar($dataPropietario);

        error_log("ID Propietario generado: " . $id_propietario);

        if (!$id_propietario) {
                // Intentar incluir detalle de error desde el modelo para depuración
                $debug = isset($propietarioModel->lastError) ? $propietarioModel->lastError : null;
                error_log("❌ Fallo registrar propietario. Debug: " . print_r($debug, true));
                echo json_encode([
                    "success" => false,
                    "message" => "No se pudo registrar el propietario",
                    "debug" => $debug,
                ]);
            exit();
        }
        

        $emailEnviado = false;
        $emailAviso = "";
        if (validarFormatoEmail($email)) {
            $emailEnviado = enviarBienvenidaPropietario([
                "email" => $email,
                "nombres" => $nombres,
                "apellidos" => $apellidos,
                "numero_documento" => $numero_documento,
            ]);
            if (!$emailEnviado) {
                $emailAviso = "No se pudo enviar el correo de bienvenida.";
            }
        } else {
            $emailAviso =
                "El correo registrado no tiene un formato valido para enviar notificaciones.";
        }

        // 3. Registrar mascotas
        $mascotaModel = new Mascota();
        $asignacionModel = new PacienteProfesionalAsignacion();

        $registradas = 0;
        foreach ($mascotas as $mascota) {
            $dataMascota = [
                "id_propietario" => $id_propietario,
                "nombre" => trim($mascota["nombre"]),
                "especie" => trim($mascota["especie"]),
                "raza" => trim($mascota["raza"]),
                "edad_numero" => (int) $mascota["edad_numero"],
                "edad_unidad" => trim($mascota["edad_unidad"]),
                "sexo" => trim($mascota["sexo"]),
                "img_mascota" => null,
                "id_usuario_profesional" => $id_usuario_profesional,
                "id_usuario_asigno" => $id_usuario_profesional,
            ];

            error_log(
                "Datos mascota a insertar: " . print_r($dataMascota, true),
            );
            $id_mascota_registrada = $mascotaModel->registrar($dataMascota);

            if (!$id_mascota_registrada) {
                echo json_encode([
                    "success" => false,
                    "message" => "No se pudo registrar una de las mascotas",
                ]);
                exit();
            }

            if (
                $id_usuario_profesional !== null &&
                $asignacionModel->tablaExiste()
            ) {
                $okAsignacion = $asignacionModel->asegurarAsignacionActiva(
                    (int) $id_mascota_registrada,
                    $id_usuario_profesional,
                    $id_usuario_profesional,
                    "Asignación inicial desde registro de pacientes",
                );

                if (!$okAsignacion) {
                    // La mascota y propietario ya están en BD; no bloquear el registro por esto
                    error_log(
                        "Advertencia: mascota registrada pero falló asignación al profesional. id_mascota=" .
                            $id_mascota_registrada,
                    );
                    // Continuar: la asignación se puede crear manualmente desde gestión de pacientes
                }
            }

            $registradas++;
        }

        $mensaje = "Registro exitoso: propietario y {$registradas} mascota(s) guardadas correctamente";
        if ($emailAviso !== "") {
            $mensaje .= " {$emailAviso}";
        }

        echo json_encode([
            "success" => true,
            "message" => $mensaje,
            "email_enviado" => $emailEnviado,
        ]);
    } catch (Exception $e) {
        error_log("Error en registroPacienteController: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" =>
                "Ocurrió un error al procesar el registro: " . $e->getMessage(),
        ]);
    }

    exit();
}

function enviarBienvenidaPropietario(array $datos): bool
{
    try {
        $mail = mailer_init();

        $nombreCompleto = trim(
            ($datos["nombres"] ?? "") . " " . ($datos["apellidos"] ?? ""),
        );
        $email = $datos["email"] ?? "";
        $numeroDocumento = (string) ($datos["numero_documento"] ?? "");

        $mail->setFrom(
            SMTP_FROM_EMAIL,
            "VetWilling - Sistema de Gestion Veterinaria",
        );
        $mail->addAddress($email, $nombreCompleto);
        $mail->isHTML(true);
        $mail->Subject = "Bienvenido a VetWilling";

        $nombreSeguro = htmlspecialchars($nombreCompleto, ENT_QUOTES, "UTF-8");
        $emailSeguro = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
        $documentoSeguro = htmlspecialchars(
            $numeroDocumento,
            ENT_QUOTES,
            "UTF-8",
        );
        $baseUrl = defined("BASE_URL") ? BASE_URL : "";

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007832; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .credenciales { background-color: #ffffff; padding: 16px; margin: 20px 0; border-left: 4px solid #007832; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .btn { background-color: #007832; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>VetWilling</h1>
                    <p>Bienvenido al sistema</p>
                </div>
                <div class='content'>
                    <h2>Hola {$nombreSeguro}!</h2>
                    <p>Tu cuenta ha sido registrada correctamente en VetWilling.</p>

                    <div class='credenciales'>
                        <h3>Credenciales de acceso</h3>
                        <p><strong>Correo:</strong> {$emailSeguro}</p>
                        <p><strong>Contrasena:</strong> {$documentoSeguro}</p>
                        <p>Recuerda que tu contrasena inicial es el numero de tu documento.</p>
                    </div>

                    <p>Puedes iniciar sesion desde el siguiente enlace:</p>
                    <a class='btn' href='{$baseUrl}'>Ir al sistema</a>
                </div>
                <div class='footer'>
                    <p>Este es un correo automatico, por favor no respondas a este mensaje.</p>
                    <p>&copy; 2025 VetWilling</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody =
            "Hola {$nombreCompleto},\n\n" .
            "Tu cuenta fue registrada en VetWilling.\n" .
            "Correo: {$email}\n" .
            "Contrasena: {$numeroDocumento}\n" .
            "Recuerda que tu contrasena inicial es el numero de tu documento.\n\n" .
            "Accede desde: {$baseUrl}\n\n" .
            "VetWilling";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(
            "Error al enviar bienvenida propietario: " . $e->getMessage(),
        );
        return false;
    }
}
