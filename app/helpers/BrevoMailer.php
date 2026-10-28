<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * PHPMailer que envía por la API HTTP de Brevo (puerto 443) en lugar de SMTP.
 *
 * Se usa igual que PHPMailer (addAddress, Subject, Body, AltBody,
 * addStringAttachment, send...), así que el resto del sistema no cambia.
 * Sirve en redes que bloquean los puertos SMTP (465/587).
 *
 * Docs: https://developers.brevo.com/reference/sendtransacemail
 */
class BrevoMailer extends PHPMailer
{
    const API_URL = 'https://api.brevo.com/v3/smtp/email';

    private $apiKey;

    public function __construct(string $apiKey, $exceptions = null)
    {
        parent::__construct($exceptions);
        $this->apiKey = $apiKey;
    }

    public function send()
    {
        try {
            $payload = $this->construirPayload();
            $this->enviarApi($payload);
            return true;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }

    private function construirPayload(): array
    {
        $destinatarios = $this->mapearDirecciones($this->getToAddresses());
        if (empty($destinatarios)) {
            throw new Exception('Debe indicar al menos un destinatario.');
        }

        $payload = [
            'sender'  => array_filter(['email' => $this->From, 'name' => $this->FromName]),
            'to'      => $destinatarios,
            'subject' => $this->Subject,
        ];

        if ($this->ContentType === static::CONTENT_TYPE_TEXT_HTML) {
            $payload['htmlContent'] = $this->Body;
            if ($this->AltBody !== '') {
                $payload['textContent'] = $this->AltBody;
            }
        } else {
            $payload['textContent'] = $this->Body;
        }

        if ($cc = $this->mapearDirecciones($this->getCcAddresses())) {
            $payload['cc'] = $cc;
        }
        if ($bcc = $this->mapearDirecciones($this->getBccAddresses())) {
            $payload['bcc'] = $bcc;
        }
        if ($replyTo = $this->mapearDirecciones(array_values($this->getReplyToAddresses()))) {
            $payload['replyTo'] = $replyTo[0];
        }

        $adjuntos = [];
        foreach ($this->getAttachments() as $adjunto) {
            // [0] ruta o contenido, [2] nombre, [5] true si es addStringAttachment
            $contenido = $adjunto[5] ? $adjunto[0] : @file_get_contents($adjunto[0]);
            if ($contenido === false) {
                throw new Exception('No se pudo leer el adjunto: ' . $adjunto[2]);
            }
            $adjuntos[] = ['name' => $adjunto[2], 'content' => base64_encode($contenido)];
        }
        if ($adjuntos) {
            $payload['attachment'] = $adjuntos;
        }

        return $payload;
    }

    private function mapearDirecciones(array $direcciones): array
    {
        $resultado = [];
        foreach ($direcciones as $direccion) {
            $resultado[] = array_filter(['email' => $direccion[0], 'name' => $direccion[1] ?? '']);
        }
        return $resultado;
    }

    private function enviarApi(array $payload): void
    {
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $respuesta = curl_exec($ch);
        $codigo    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            throw new Exception('Brevo: no se pudo conectar (' . $errorCurl . ')');
        }
        if ($codigo < 200 || $codigo >= 300) {
            $json = json_decode($respuesta, true);
            throw new Exception('Brevo HTTP ' . $codigo . ': ' . ($json['message'] ?? $respuesta));
        }
    }
}
