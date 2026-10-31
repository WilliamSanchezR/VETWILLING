<?php

// Cifrado de secretos guardados en la base de datos (contraseña SMTP, claves de proveedores).
//
// - Cifrado autenticado AES-256-GCM con OpenSSL: si el valor se altera, no se descifra.
//   (Se usa OpenSSL y no libsodium porque el PHP de XAMPP 8.2 no trae sodium; OpenSSL está en ambos.)
// - La clave vive en .env (APP_ENCRYPTION_KEY), nunca junto al dato cifrado.
// - Formato guardado: "enc:v1:" + base64(iv[12] || tag[16] || texto_cifrado).
// - Los valores antiguos en texto plano se siguen leyendo (transición) y se migran con
//   scripts/cifrar-credenciales-smtp.php.
//
// Generar una clave:  php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'

require_once __DIR__ . '/../../config/config.php';

const SECRETO_PREFIJO = 'enc:v1:';
const SECRETO_CIFRADO = 'aes-256-gcm';
const SECRETO_IV_BYTES = 12;
const SECRETO_TAG_BYTES = 16;
const SECRETO_AAD = 'vetwilling-secreto-v1'; // dato asociado: liga el cifrado a este uso

/**
 * Devuelve la clave de 32 bytes de APP_ENCRYPTION_KEY o null si no está configurada o es inválida.
 */
function secreto_clave(): ?string
{
    $base64 = (string) env_value('APP_ENCRYPTION_KEY', '');
    if ($base64 === '') return null;

    $clave = base64_decode($base64, true);
    return ($clave !== false && strlen($clave) === 32) ? $clave : null;
}

function secreto_esta_cifrado(?string $valor): bool
{
    return is_string($valor) && str_starts_with($valor, SECRETO_PREFIJO);
}

function secreto_verificar_entorno(): void
{
    if (!extension_loaded('openssl') || !in_array(SECRETO_CIFRADO, openssl_get_cipher_methods(), true)) {
        throw new RuntimeException('Este PHP no tiene OpenSSL con AES-256-GCM: no se pueden cifrar credenciales.');
    }
}

/**
 * Cifra un secreto. Sin clave configurada lanza una excepción:
 * nunca se guarda en texto plano como alternativa silenciosa.
 *
 * @throws RuntimeException
 */
function secreto_cifrar(string $plano): string
{
    secreto_verificar_entorno();
    $clave = secreto_clave();
    if ($clave === null) {
        throw new RuntimeException(
            'Falta APP_ENCRYPTION_KEY en .env (32 bytes en base64). No se guardó la credencial.'
        );
    }

    $iv = random_bytes(SECRETO_IV_BYTES);
    $tag = '';
    $cifrado = openssl_encrypt($plano, SECRETO_CIFRADO, $clave, OPENSSL_RAW_DATA, $iv, $tag, SECRETO_AAD, SECRETO_TAG_BYTES);
    if ($cifrado === false) {
        throw new RuntimeException('No se pudo cifrar la credencial.');
    }

    return SECRETO_PREFIJO . base64_encode($iv . $tag . $cifrado);
}

/**
 * Descifra un secreto guardado. Si el valor es antiguo (texto plano) lo devuelve tal cual.
 *
 * @throws RuntimeException si está cifrado y falta la clave o el valor fue alterado
 */
function secreto_descifrar(?string $valor): ?string
{
    if ($valor === null || $valor === '') return $valor;
    if (!secreto_esta_cifrado($valor)) return $valor; // valor antiguo, pendiente de migrar

    secreto_verificar_entorno();
    $clave = secreto_clave();
    if ($clave === null) {
        throw new RuntimeException('Hay una credencial cifrada pero falta APP_ENCRYPTION_KEY en .env.');
    }

    $binario = base64_decode(substr($valor, strlen(SECRETO_PREFIJO)), true);
    if ($binario === false || strlen($binario) <= SECRETO_IV_BYTES + SECRETO_TAG_BYTES) {
        throw new RuntimeException('La credencial cifrada está dañada.');
    }

    $iv = substr($binario, 0, SECRETO_IV_BYTES);
    $tag = substr($binario, SECRETO_IV_BYTES, SECRETO_TAG_BYTES);
    $cifrado = substr($binario, SECRETO_IV_BYTES + SECRETO_TAG_BYTES);
    $plano = openssl_decrypt($cifrado, SECRETO_CIFRADO, $clave, OPENSSL_RAW_DATA, $iv, $tag, SECRETO_AAD);

    if ($plano === false) {
        throw new RuntimeException('No se pudo descifrar la credencial (clave distinta o valor alterado).');
    }
    return $plano;
}

/**
 * Estado de una credencial para mostrarlo en pantalla sin revelarla:
 * 'vacia', 'texto_plano', 'cifrada' o 'ilegible' (cifrada, pero no se puede descifrar con la clave actual).
 */
function secreto_estado(?string $valor): string
{
    if ($valor === null || $valor === '') return 'vacia';
    if (!secreto_esta_cifrado($valor)) return 'texto_plano';
    try {
        secreto_descifrar($valor);
        return 'cifrada';
    } catch (Throwable $e) {
        return 'ilegible';
    }
}
