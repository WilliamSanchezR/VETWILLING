#!/usr/bin/env bash
# Pruebas de F3 / P4 y borrados por POST contra el contenedor de pruebas de MySQL (nunca contra la base local).
# Uso: bash tests/run_f3_p4.sh
# Toma la conexión de TEST_DB_* o, si falta la contraseña, del contenedor (sin mostrarla).
set -euo pipefail

RAIZ="$(cd "$(dirname "$0")/.." && pwd)"
CONTENEDOR="${CONTENEDOR_PRUEBAS:-vetwilling-pruebas-mysql}"

if [ -z "${TEST_DB_PASS:-}" ]; then
  VARS="$(docker inspect "$CONTENEDOR" --format '{{range .Config.Env}}{{println .}}{{end}}')"
  TEST_DB_PASS="$(sed -n 's/^MYSQL_PASSWORD=//p' <<<"$VARS")"
  TEST_DB_USER="${TEST_DB_USER:-$(sed -n 's/^MYSQL_USER=//p' <<<"$VARS")}"
  TEST_DB_NAME="${TEST_DB_NAME:-$(sed -n 's/^MYSQL_DATABASE=//p' <<<"$VARS")}"
  TEST_DB_PORT="${TEST_DB_PORT:-$(docker port "$CONTENEDOR" 3306/tcp | head -1 | sed 's/.*://')}"
fi
export TEST_DB_HOST="${TEST_DB_HOST:-127.0.0.1}" TEST_DB_PORT TEST_DB_NAME TEST_DB_USER TEST_DB_PASS

"${PHP_BIN:-php}" "$RAIZ/tests/f3_p4_integracion_test.php"
