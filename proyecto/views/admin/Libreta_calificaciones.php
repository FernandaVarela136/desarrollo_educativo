<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Calificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Seleccione un Grado para Ver el Informe</h2>

        <?php
        // Incluye el archivo de conexión a la base de datos
        include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

        // Manejar el envío del formulario
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Obtener el grado seleccionado
            $gradoId = $_POST['grado'];
            // Redirigir a la página de descarga de PDF
            header("Location: descarga_pdf.php?grado=" . $gradoId);
            exit();
        }
        ?>

        <form method='POST' action=''>
            <div class='text-center mb-4'>
                <label for='grado' class='form-label'>Seleccione el Grado:</label>
                <select name='grado' id='grado' class='form-select' required>
                    <option value=''>Seleccione...</option>
                    
                    <?php
                    // Obtener grados
                    $gradosQuery = "SELECT * FROM grado"; // Ajusta esta consulta según tu estructura
                    $gradosResult = mysqli_query($conexion, $gradosQuery);

                    while ($grado = mysqli_fetch_assoc($gradosResult)) {
                        echo "<option value='{$grado['ID_Grado']}'>{$grado['nombre_grado']} {$grado['nivel']}</option>";
                    }
                    ?>
                </select>
                <button type='submit' class='btn btn-primary mt-3'>Descargar</button>
            </div>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
