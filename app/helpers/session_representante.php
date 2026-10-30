<?php

// Exige una sesión iniciada por el login con rol de representante. Ver acceso_helper.php.
require_once __DIR__ . '/acceso_helper.php';

acceso_exigir_usuario([4]);
