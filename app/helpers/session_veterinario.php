<?php

// Exige una sesión iniciada por el login con rol de veterinario o representante (con la
// veterinaria ya seleccionada). Ver acceso_helper.php.
require_once __DIR__ . '/acceso_helper.php';

acceso_exigir_usuario([2, 4]);
