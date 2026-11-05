<?php

// Roles permitidos para cada acción de los controladores que comparten varias rutas.
// rutas_acceso.php valida la ruta; este mapa valida lo que se ejecuta dentro del controlador.
//
//   POST  se niega cualquier acción que no esté en la lista.
//   GET   solo se limitan las acciones listadas: las vistas también incluyen estos controladores
//         por GET, sin acción, para usar sus funciones de consulta.
//
// La clave '' es el formulario de registro, que no envía el campo accion.
// Roles: 1 admin, 2 veterinario, 3 propietario, 4 representante.

return [
    'usuario' => [
        'POST' => [
            ''                       => [1],
            'actualizar'             => [1],
            'actualizar-constrasena' => [1, 4],
            'modificar-contrasena'   => [3],
            'vet-constrasena'        => [2, 4],
            'cambiar-foto'           => [1, 4],
        ],
        'GET' => [
            'eliminar'    => [1],
            'listarAdmin' => [1],
        ],
    ],
    'veterinaria' => [
        'POST' => [
            ''               => [1],
            'actualizar'     => [1],
            'actualizarInfo' => [4],
        ],
        'GET' => [
            'eliminar'          => [1],
            'obtener_horarios'  => [4],
            'eliminar_horarios' => [4],
        ],
    ],
];
