<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $fechaEnvio = $_POST['fechaEnvio'];
    $idUsuario = $_SESSION['id_usuario']; // Asumiendo que el ID de usuario está en la sesión
    $gradosSeleccionados = $_POST['grados']; // Array de grados seleccionados

    // Insertar la circular
    $queryCircular = "INSERT INTO circular (Titulo, Contenido, FechaEnvio, IdUsuario) 
                      VALUES ('$titulo', '$contenido', '$fechaEnvio', '$idUsuario')";
    
    if (mysqli_query($conexion, $queryCircular)) {
        $idCircular = mysqli_insert_id($conexion); // Obtener el ID de la circular recién insertada

        // Insertar la relación con los grados seleccionados
        foreach ($gradosSeleccionados as $idGrado) {
            $queryRelacion = "INSERT INTO circular_grado (ID_circular, ID_grado) 
                              VALUES ('$idCircular', '$idGrado')";
            mysqli_query($conexion, $queryRelacion);
        }

        echo "<div class='alert alert-success'>Circular insertada con éxito.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error al insertar la circular: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso de Circular</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Crear Nueva Circular</h2>
        <button class="btn btn-secondary" onclick="location.href='../'">Home</button>
    </div>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="contenido">Contenido:</label>
            <textarea name="contenido" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label for="fechaEnvio">Fecha de Envío:</label>
            <input type="date" name="fechaEnvio" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="grados">Selecciona Grados:</label>
            <select name="grados[]" class="form-control" multiple required>
                <?php
                // Obtener los grados disponibles
                $queryGrados = "SELECT ID_Grado, nombre_grado FROM grado";
                $resultGrados = mysqli_query($conexion, $queryGrados);
                while ($row = mysqli_fetch_assoc($resultGrados)) {
                    echo "<option value='{$row['ID_Grado']}'>{$row['nombre_grado']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Circular</button>
    </form>
</body>
</html>
