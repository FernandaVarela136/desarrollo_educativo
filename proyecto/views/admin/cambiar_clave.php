<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

// Verifica que se haya proporcionado un ID de usuario
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de usuario no especificado o inválido.');
}

$id_usuario = intval($_GET['id']);

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_clave = $_POST['nueva_clave'];
    $confirmar_clave = $_POST['confirmar_clave'];

    if ($nueva_clave === $confirmar_clave) {
        $nueva_clave_hash = password_hash($nueva_clave, PASSWORD_BCRYPT);
        
        // Depuración: Imprime el hash generado
        echo "Hash generado: " . htmlspecialchars($nueva_clave_hash) . "<br>";

        $SQL = "UPDATE usuario SET clave = '$nueva_clave_hash' WHERE Id_usuario = $id_usuario";
        if (mysqli_query($conexion, $SQL)) {
            echo "Clave cambiada exitosamente.";
        } else {
            echo "Error al cambiar la clave: " . mysqli_error($conexion);
        }
    } else {
        echo "Las claves no coinciden.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-gray {
            background-color: #6c757d; /* Color gris */
            color: white;
        }
        .btn-gray:hover {
            background-color: #5a6268; /* Color gris oscuro en hover */
        }
        .btn-container {
            text-align: right; /* Alinear a la derecha */
            margin-bottom: 20px; /* Espaciado inferior */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="btn-container">
        <button class="btn btn-gray" onclick="location.href='../'">Home</button>
    </div>
    <h2>Cambiar Contraseña</h2>
    <form method="POST">
        <div class="form-group">
            <label for="nueva_clave">Nueva Contraseña</label>
            <input type="password" class="form-control" id="nueva_clave" name="nueva_clave" required>
        </div>
        <div class="form-group">
            <label for="confirmar_clave">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" required>
        </div>
        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
    </form>
</div>
</body>
</html>
