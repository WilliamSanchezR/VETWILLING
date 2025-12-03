<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';


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

                $actualizar = "UPDATE usuario SET password_hash = :nuevaClave WHERE id_usuario = :id";

                // Preparamos la accion a ejecutar y la ejecutamos

                $resultado = $this->conexion->prepare($actualizar);
                $resultado->bindParam(':nuevaClave', $claveHash);
                $resultado->bindParam(':id', $user['id_usuario']);

                $resultado->execute();

                //* Despues de actualizar la contraseña arreglamos el enviador de correos

                $mail = mailer_init();

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
                //Set email format to HTML
                $mail->Subject = "VetWilling - NUEVA CLAVE GENERADA";
                $mail->Body    = '
            
           style="margin:0; padding:0; background-color:#f8f9fa; font-family:Arial, sans-serif;">
    
    <!-- Contenedor principal -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8f9fa;">
        <tr>
            <td align="center" style="padding:40px 20px;">
                
                <!-- Tarjeta del correo -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; box-shadow:0 4px 20px rgba(10,147,44,0.1);">
                    
                    <!-- ENCABEZADO -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #0a932c 0%, #9de795 100%); padding:40px 20px; text-align:center; border-radius:16px 16px 0 0;">
                            
                            <!-- Logo -->
                            <table role="presentation" width="240" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom:20px;">
                                <tr>
                                    <td align="center" style="background-color:#ffffff; border-radius:12px; padding:10px; box-shadow:0 8px 16px rgba(0,0,0,0.1);">
                                        <img src="https://raw.githubusercontent.com/MaicBernal11/VetWilling-Imagenes-Correo/refs/heads/main/VETWILLING/LOGO-VERTICAL.png" alt="VetWilling" width="220" height="170" style="display:block; max-width:100%; height:auto;">
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Título principal -->
                            <h1 style="margin:0; font-size:28px; font-weight:700; color:#ffffff; font-family:Arial, sans-serif;">
                                VetWilling
                            </h1>
                            <p style="margin:10px 0 0; font-size:16px; color:#ffffff; opacity:0.95;">
                                Sistema de Gestión Veterinaria
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- CONTENIDO PRINCIPAL -->
                    <tr>
                        <td style="padding:40px 30px; color:#424b54;">
                            
                            <!-- Saludo -->
                            <h2 style="text-align:center; color:#0a932c; margin:0 0 10px 0; font-size:24px; font-weight:700;">
                                ¡GRACIAS POR<br>CONFIAR EN NOSOTROS!
                            </h2>
                            
                            <p style="text-align:center; font-size:18px; color:#424b54; margin:0 0 30px 0; font-weight:600;">
                                Buen día, estimado usuario
                            </p>
                            
                            <!-- Texto explicativo -->
                            <p style="font-size:15px; line-height:1.7; color:#424b54; margin:0 0 20px 0;">
                                Hemos recibido tu solicitud para restablecer la contraseña de tu cuenta en VetWilling. 
                                Por tu seguridad, hemos generado una contraseña temporal que podrás usar para acceder nuevamente.
                            </p>
                            
                            <!-- Caja de información de acceso -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:25px 0;">
                                <tr>
                                    <td style="background-color:#f8f9fa; border:2px solid #9de795; border-radius:12px; padding:25px;">
                                        
                                        <!-- Email -->
                                        <p style="margin:0 0 20px 0; font-size:14px; color:#424b54; text-align:center;">
                                            <strong>Correo registrado:</strong><br>
                                            <span style="color:#0a932c; font-size:16px; font-weight:600;">' . htmlspecialchars($email) . '</span>
                                        </p>
                                        
                                        <p style="margin:0 0 20px 0; font-size:14px; color:#424b54; text-align:center;">
                                            <strong>Nueva contraseña temporal:</strong>
                                        </p>
                                        
                                        <!-- Botón con contraseña -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                            <tr>
                                                <td align="center" style="background-color:#0a932c; border-radius:30px; box-shadow:0 4px 15px rgba(10,147,44,0.3);">
                                                    <span style="display:inline-block; padding:16px 40px; font-size:16px; color:#ffffff; font-weight:600; text-decoration:none; letter-spacing:1px;">
                                                        🔐 ' . htmlspecialchars($nuevaClave) . '
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Advertencia de seguridad -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:25px 0;">
                                <tr>
                                    <td style="background-color:#fff8e1; border-left:4px solid #93bedf; border-radius:6px; padding:15px;">
                                        <p style="margin:0; font-size:14px; color:#424b54; line-height:1.6;">
                                            ⚠️ <strong>Importante:</strong> Por tu seguridad, te recomendamos cambiar esta contraseña 
                                            inmediatamente después de iniciar sesión desde tu perfil de usuario.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size:15px; color:#424b54; margin:0 0 30px 0;">
                                Utiliza esta contraseña para ingresar al sistema y luego cámbiala desde tu perfil.
                            </p>
                            
                            <!-- Características -->
                            <p style="font-size:14px; color:#424b54; margin:0 0 15px 0; font-weight:600;">
                                Con tu cuenta de VetWilling podrás:
                            </p>
                            
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:8px 0;">
                                        <p style="margin:0; font-size:14px; color:#424b54;">
                                            ✓ Gestionar las citas y consultas de tus mascotas de forma sencilla
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;">
                                        <p style="margin:0; font-size:14px; color:#424b54;">
                                            ✓ Acceder al historial médico completo de tus compañeros peludos
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;">
                                        <p style="margin:0; font-size:14px; color:#424b54;">
                                            ✓ Recibir recordatorios de vacunas y tratamientos importantes
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Soporte -->
                            <p style="font-size:13px; color:#424b54; line-height:1.6; text-align:center; margin:30px 0 0 0;">
                                ¿Necesitas ayuda? Contáctanos directamente para asistencia.
                            </p>
                            
                            <!-- Nota de seguridad -->
                            <p style="font-size:13px; color:#888; line-height:1.6; margin:20px 0 0 0;">
                                Si tú no solicitaste este cambio, por favor ignora este mensaje o contacta con nuestro 
                                equipo de soporte inmediatamente. Tu cuenta permanecerá segura.
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- PIE DE PÁGINA -->
                    <tr>
                        <td style="background-color:#f8f9fa; padding:20px; text-align:center; border-top:1px solid #e0e0e0; border-radius:0 0 16px 16px;">
                            
                            <p style="margin:0 0 10px 0; font-size:12px; color:#424b54; font-weight:600;">
                                🐾 Veterinaria VetWilling – Siempre aquí para ti y tus mascotas
                            </p>
                            
                            <p style="margin:0 0 10px 0; font-size:12px; color:#888;">
                                Con cariño, el equipo de VetWilling
                            </p>
                            
                            <p style="margin:0 0 10px 0; font-size:12px; color:#888;">
                                © 2024 VetWilling. Todos los derechos reservados
                            </p>
                            
                            <p style="margin:0 0 10px 0; font-size:12px; color:#888;">
                                Bogotá, Colombia • Calle 123 #45-67 • Tel: (601) 234-5678
                            </p>
                            
                            <p style="margin:10px 0 0 0; font-size:11px; color:#999;">
                                Este correo fue enviado porque solicitaste restablecer tu contraseña
                            </p>
                            
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>';
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                $mail->send();

                return true;
            } else {
                return ['error' => 'Usaurio no encontrado o inactivo'];
            };
        } catch (Exception $e) {
            mostrarSweetAlert('error', 'Email no coincide', 'Registrese', '/vetwilling/veterinario/iniciar-sesion');
        }
    }
}
