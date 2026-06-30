<?php
/**
 * Genera un SVG inline con las iniciales del usuario.
 * Devuelve un data URI base64 listo para usar en <img src="...">.
 * Sin dependencia de servicios externos.
 */
function generarAvatarSVG(string $nombres, string $apellidos, int $size = 128): string
{
    $i1 = mb_strtoupper(mb_substr(trim($nombres),  0, 1, 'UTF-8'), 'UTF-8');
    $i2 = mb_strtoupper(mb_substr(trim($apellidos), 0, 1, 'UTF-8'), 'UTF-8');
    $iniciales = ($i1 ?: '?') . $i2;

    $paleta = ['#4e9af1','#e67e22','#2ecc71','#9b59b6','#e74c3c',
               '#1abc9c','#3498db','#f39c12','#16a085','#8e44ad'];
    $color  = $paleta[abs(crc32($nombres . $apellidos)) % count($paleta)];
    $fs     = (int)($size * 0.42);
    $cx     = $size / 2;

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '"'
         . ' viewBox="0 0 ' . $size . ' ' . $size . '">'
         . '<rect width="' . $size . '" height="' . $size . '"'
         . ' rx="' . ($size / 2) . '" fill="' . $color . '"/>'
         . '<text x="' . $cx . '" y="' . $cx . '"'
         . ' text-anchor="middle" dominant-baseline="central"'
         . ' fill="#fff" font-size="' . $fs . '"'
         . ' font-family="\'Segoe UI\',Arial,sans-serif" font-weight="700">'
         . htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8')
         . '</text></svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
