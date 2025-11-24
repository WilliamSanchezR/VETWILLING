<?php

require_once __DIR__ . '/../../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';

class RecoveryPass
{

    private $conexion;

    public function __construct()
    {
        $db = new conexion();
        $this->conexion = $db->getConexion();
    }

    public function recuperarClave($email)
    {

        try {
            $consultar = "SELECT * FROM usuario WHERE email = :email AND estado = 'activo' LIMIT 1";

            // Preparamos la accion a ejecutar y la ejecutamos

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':email', $email);
            $resultado->execute();

            $user = $resultado->fetch();

            if ($user) {

                // Creamos la nueva contraseña apartir de una base de caracteres y un random
                $base = "ABCDEFGHIJKLMNOPQRSTUVWWYZabcdefghijklmnopqrstuvwyz0123456789*";

                // Mezclamos la cadena de caracteres 
                $random = str_shuffle($base);

                // Substraemos una cantidad de finida de este random

                //* PHP tiene la funcion para substraer un numero determinado de caracteres de uan variable, primero va la variable despues desde que posicion (en este caso es 0) empieza a substraer hasta el ultimo numero (en este caso es 8) 

                $nuevaClave = substr($random, 0, 8);

                $claveHash = password_hash($nuevaClave, PASSWORD_DEFAULT);

                $cactualizar = "UPDATE usuario SET password_hash = :nuevaClave WHERE id_usuario = :id";

                // Preparamos la accion a ejecutar y la ejecutamos

                $resultado = $this->conexion->prepare($cactualizar);
                $resultado->bindParam(':nuevaClave', $claveHash);
                $resultado->bindParam(':id', $user['id_usuario']);

                $resultado->execute();

                //* Despues de actualizar la contraseña arreglamos el enviador de correos


                //Create an instance; passing `true` enables exceptions
                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->SMTPDebug = 0;                      //Enable verbose debug output
                    $mail->isSMTP();                                            //Send using SMTP
                    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                    $mail->Username   = 'vetwillingsoporte@gmail.com';                     //SMTP username
                    $mail->Password   = 'zbfpnwrnuwykjedn';                               //SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS

                    //Recipients
                    // EMISOR Y NOMBRE DE LA PERSONA O ROL
                    $mail->setFrom('vetwillingsoporte@gmail.com', 'Personal De Soporte VetWilling');
                    // RECEPTOR, A QUIEN QUIERO QUE LE LLEGUE EL CORREO
                    $mail->addAddress($user['email']);     //Add a recipient
                    // $mail->addAddress('ellen@example.com');               //Name is optional
                    // $mail->addReplyTo('info@example.com', 'Information');
                    // $mail->addCC('cc@example.com');
                    // $mail->addBCC('bcc@example.com');

                    //Attachments
                    // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
                    // EMBEBER IMAGEN (LOGO)
                    // -------------------------------------------
                    // La imagen se adjunta internamente y se puede llamar con "cid:logoCorreo"
                    $mail->isHTML(true);
                    $mail->CharSet = "UTF-8";                                  //Set email format to HTML
                    $mail->Subject = "VetWilling - NUEVA CLAVE GENERADA";
                    $mail->Body    = '
            
            <div style="margin:0; padding:0; background:#f8f9fa; font-family:´Open Sans´, sans-serif;">
    
    <div style="max-width:600px; margin:40px auto; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(10,147,44,0.1);">
        
        <!-- ENCABEZADO -->
        <div style="background:linear-gradient(135deg, #0a932c 0%, #9de795 100%); padding:40px 20px; text-align:center; color:#fff;">
            <div style="width:240px; height:190px; background:#ffffff; border-radius:12px; margin:0 auto 20px; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 16px rgba(0,0,0,0.1);">
                <img src="https://raw.githubusercontent.com/MaicBernal11/VetWilling-Imagenes-Correo/refs/heads/main/VETWILLING/LOGO-VERTICAL.png" style="margin:0 10px 0 5px; width:220px; height:170px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="#17331eff"/>
                </img>
            </div>
            <h1 style="margin:0; font-size:28px; font-weight:700; font-family:´Fredoka´, sans-serif;">mfms</h1>
            <p style="margin:10px 0 0; font-size:16px; opacity:0.95;">Sistema de Gestión Veterinaria</p>
        </div>

        <!-- CONTENIDO -->
        <div style="padding:40px 30px; color:#424b54;">
            
            <h2 style="text-align:center; color:#0a932c; margin-bottom:10px; font-family:´Fredoka´, sans-serif; font-size:24px;">¡GRACIAS POR<br>CONFIAR EN NOSOTROS!</h2>
            
            <p style="text-align:center; font-size:18px; color:#424b54; margin-bottom:30px; font-weight:600;">
                Buen día, estimado usuario
            </p>

            <p style="font-size:15px; line-height:1.7; color:#424b54; margin-bottom:20px;">
                Hemos recibido tu solicitud para restablecer la contraseña de tu cuenta en VetWilling. 
                Por tu seguridad, hemos generado una contraseña temporal que podrás usar para acceder nuevamente.
            </p>

            <!-- INFORMACIÓN DE ACCESO -->
            <div style="background:#f8f9fa; border:2px solid #9de795; border-radius:12px; padding:25px; margin:25px 0; text-align:center;">
                <p style="margin:0 0 20px 0; font-size:14px; color:#424b54;">
                    <strong>Correo registrado:</strong><br>
                    <span style="color:#0a932c; font-size:16px;">' . htmlspecialchars($email) . '</span>
                </p>
                <p style="margin:0 0 25px 0; font-size:14px; color:#424b54;">
                    <strong>Nueva contraseña temporal:</strong><br>
                </p>
                
                <!-- BOTÓN DENTRO DEL CONTENEDOR -->
                <a href="#" 
                    style="
                        display:inline-block;
                        background:#0a932c;
                        color:white;
                        padding:16px 40px;
                        font-size:16px;
                        text-decoration:none;
                        border-radius:30px;
                        font-weight:600;
                        box-shadow:0 4px 15px rgba(10,147,44,0.3);
                        transition:all 0.3s ease;">
                    🔐 ' . htmlspecialchars($nuevaClave) . '
                </a>
            </div>

            <div style="background:#fff8e1; border-left:4px solid #93bedf; padding:15px; margin:25px 0; border-radius:6px;">
                <p style="margin:0; font-size:14px; color:#424b54; line-height:1.6;">
                    ⚠️ <strong>Importante:</strong> Por tu seguridad, te recomendamos cambiar esta contraseña 
                    inmediatamente después de iniciar sesión desde tu perfil de usuario.
                </p>
            </div>

            <p style="font-size:15px; color:#424b54; margin-bottom:30px;">
                Utiliza esta contraseña para ingresar al sistema y luego cámbiala desde tu perfil.
            </p>

            <!-- LISTA DE CARACTERÍSTICAS -->
            <div style="margin:25px 0;">
                <p style="font-size:14px; color:#424b54; margin-bottom:15px;">
                    <strong>Con tu cuenta de VetWilling podrás:</strong>
                </p>
                <ul style="list-style:none; padding:0; margin:0;">
                    <li style="padding:8px 0; font-size:14px; color:#424b54;">
                        ✓ Gestionar las citas y consultas de tus mascotas de forma sencilla
                    </li>
                    <li style="padding:8px 0; font-size:14px; color:#424b54;">
                        ✓ Acceder al historial médico completo de tus compañeros peludos
                    </li>
                    <li style="padding:8px 0; font-size:14px; color:#424b54;">
                        ✓ Recibir recordatorios de vacunas y tratamientos importantes
                    </li>
                </ul>
            </div>

            <p style="font-size:13px; color:#424b54; line-height:1.6; text-align:center; margin-top:30px;">
                ¿Necesitas ayuda? Visita nuestra <a href="#" style="color:#93bedf; text-decoration:none;">página de soporte</a> o contáctanos directamente.
            </p>

            <p style="font-size:13px; color:#888; line-height:1.6; margin-top:20px;">
                Si tú no solicitaste este cambio, por favor ignora este mensaje o contacta con nuestro 
                equipo de soporte inmediatamente. Tu cuenta permanecerá segura.
            </p>

        </div>

        <!-- PIE -->
        <div style="background:#f8f9fa; padding:20px; text-align:center; font-size:12px; color:#424b54; border-top:1px solid #e0e0e0;">
            <p style="margin:0 0 10px 0; font-weight:600;">
                🐾 Veterinaria VetWilling – Siempre aquí para ti y tus mascotas
            </p>
            <p style="margin:0; color:#888;">
                Con cariño, el equipo de VetWilling<br>
                © 2024 VetWilling. Todos los derechos reservados<br>
                Bogotá, Colombia • Calle 123 #45-67 • Tel: (601) 234-5678
            </p>
            <p style="margin:10px 0 0 0; font-size:11px; color:#999;">
                Este correo fue enviado porque solicitaste restablecer tu contraseña
            </p>
        </div>

    </div>


</div>';
                    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                    $mail->send();

                    return true;
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                return ['error' => 'Usaurio no encontrado o inactivo'];
            };
        } catch (Exception $e) {
            mostrarSweetAlert('error', 'Email no coincide', 'Registrese', '/vetwilling/veterinario/iniciar-sesion');
        }
    }
}
