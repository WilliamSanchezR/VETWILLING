<?php
require_once BASE_PATH . '/app/helpers/session_veterinario.php';
require_once BASE_PATH . '/app/controllers/veterinariaController.php';

$veterinarias = $_SESSION['user']['id_veterinaria'];

$veterinariasArray = explode(', ', $veterinarias);

$veterinariasDetalles = consultarVeterinariasPorArray($veterinariasArray);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Veterinaria</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/seleccionarVeterinaria.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/globalStyles.css">
</head>

<body>

    <div class="modal-overlay">
        <div class="modal-content"> 
            <h1>Seleccione la Veterinaria</h1>
            <form action="<?= BASE_URL ?>/login/ingresarVeterinaria" method="post">
                <input type="hidden" name="action" value="seleccionarVeterinaria">
                <select name="id_veterinaria" required>
                    <option value="" disabled selected>Seleccione una veterinaria</option>
                    <?php foreach ($veterinariasDetalles as $veterinaria): ?>
                        <option value="<?php echo $veterinaria['id_veterinaria']; ?>">
                            <?php echo htmlspecialchars($veterinaria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-submit" type="submit">Ingresar</button>
            </form>
        </div>
    </div>

</body>

</html>