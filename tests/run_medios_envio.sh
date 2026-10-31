#!/usr/bin/env bash
# Ejecuta las pruebas de Medios de envío con un servidor SMTP falso local.
# Uso: bash tests/run_medios_envio.sh
set -euo pipefail

RAIZ="$(cd "$(dirname "$0")/.." && pwd)"
SALIDA="$(mktemp -d)"
PUERTO="$(python3 -c 'import socket; s=socket.socket(); s.bind(("127.0.0.1",0)); print(s.getsockname()[1]); s.close()')"

python3 "$RAIZ/tests/fake_smtp.py" "$PUERTO" "$SALIDA" &
PID_SMTP=$!
trap 'kill $PID_SMTP 2>/dev/null; rm -rf "$SALIDA"' EXIT

# Esperar a que el servidor acepte conexiones
python3 - "$PUERTO" <<'PY'
import socket, sys, time
fin = time.time() + 5
while time.time() < fin:
    try:
        socket.create_connection(("127.0.0.1", int(sys.argv[1])), 0.2).close()
        break
    except OSError:
        time.sleep(0.1)
PY

# PHP_BIN permite probar con otro intérprete, p. ej. PHP_BIN=/opt/lampp/bin/php
"${PHP_BIN:-php}" "$RAIZ/tests/medios_envio_test.php" "$PUERTO" "$SALIDA"
echo
# Ensayo con la credencial real: solo lectura de la base real, cifrado con clave efímera, SMTP falso
"${PHP_BIN:-php}" "$RAIZ/tests/credencial_real_simulacion.php" "$PUERTO" "$SALIDA"
