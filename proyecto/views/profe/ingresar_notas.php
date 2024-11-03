<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la configuración de sesión
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

// Conexión a la base de datos
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
if (!isset($_SESSION['id_usuario'])) {
    die('Acceso denegado.');
}

$idUsuario = $_SESSION['id_usuario'];

// Obtener el ID del profesor
$queryProfesor = "SELECT ID_profesor FROM profesor WHERE id_usuario = $idUsuario";
$resultProfesor = mysqli_query($conexion, $queryProfesor);

if (!$resultProfesor) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

if (mysqli_num_rows($resultProfesor) > 0) {
    $row = mysqli_fetch_assoc($resultProfesor);
    $idProfesor = $row['ID_profesor'];
} else {
    echo "<div class='alert alert-warning' role='alert'><h2>No se encontró un profesor asociado con este usuario.</h2>";
    echo "<p>Será redirigido a la página anterior en 5 segundos...</p>";
    echo "<p>Si no es redirigido automáticamente, haga clic <a href='/proyecto/views/' class='alert-link'>aquí</a>.</p></div>";
    header("refresh:5;url=/proyecto/views/");
    exit();
}

// Obtener la lista de grados asignados al profesor
$queryGrados = "
    SELECT 
        asignaciones.ID_Grado, 
        grado.nombre_grado
    FROM 
        asignaciones
    JOIN 
        grado ON asignaciones.ID_Grado = grado.ID_Grado
    JOIN 
        area ON asignaciones.ID_Area = area.ID_Area
    JOIN 
        profesor ON asignaciones.ID_Profesor = profesor.ID_Profesor
    JOIN 
        usuario ON profesor.id_usuario = usuario.id_usuario
    WHERE 
        asignaciones.ID_Profesor = (
            SELECT ID_Profesor FROM profesor WHERE id_usuario = $idUsuario
        )";
$resultGrados = mysqli_query($conexion, $queryGrados);

if (!$resultGrados) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso de Notas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Ingreso de Notas</h2>
        <button class="btn btn-secondary" onclick="location.href='../'">Home</button>
    </div>
    
    <div class="form-group">
        <label for="gradoSelect">Grado</label>
        <select id="gradoSelect" class="form-control">
            <option value="">Seleccionar Grado</option>
            <?php while ($row = mysqli_fetch_assoc($resultGrados)) { ?>
                <option value="<?php echo $row['ID_Grado']; ?>"><?php echo $row['nombre_grado']; ?></option>
            <?php } ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="tareaSelect">Tarea/Examen/Parcial</label>
        <select id="tareaSelect" class="form-control"></select>
    </div>

    <h3>Estudiantes</h3>
    <form id="formNotas">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Nombre del Estudiante</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody id="estudiantesTable"></tbody>
        </table>
        <button type="button" id="guardarNotasBtn" class="btn btn-primary">Guardar Notas</button>
    </form>

    <script>
    $(document).ready(function() {
        $('#gradoSelect').change(function() {
            var idGrado = $(this).val();
            var idUsuario = <?php echo json_encode($idUsuario); ?>; // Pasar el idUsuario a JavaScript
            if (idGrado !== "") {
                $.ajax({
                    url: 'obtener_tareas_y_estudiantes.php',
                    method: 'GET',
                    data: { id_grado: idGrado, id_usuario: idUsuario },
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#tareaSelect').html(data.tareasHtml || data.examenesHtml || data.parcialesHtml);
                        $('#estudiantesTable').html(data.estudiantesHtml);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en la solicitud AJAX: " + error);
                    }
                });
            } else {
                $('#tareaSelect').html('');
                $('#estudiantesTable').html('');
            }
        });

        $('#tareaSelect').change(function() {
            var idGrado = $('#gradoSelect').val();
            var idTarea = $(this).val();
            if (idGrado !== "" && idTarea !== "") {
                $.ajax({
                    url: 'obtener_tareas_y_estudiantes.php',
                    method: 'GET',
                    data: { id_grado: idGrado, id_tarea: idTarea },
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#estudiantesTable').html(data.estudiantesHtml);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en la solicitud AJAX: " + error);
                    }
                });
            }
        });

        $('#guardarNotasBtn').click(function() {
            var idTarea = $('#tareaSelect').val();
            var notas = [];

            $('#estudiantesTable tr').each(function() {
                var idEstudiante = $(this).data('id');
                var nota = $(this).find('input').val();
                notas.push({ idEstudiante: idEstudiante, nota: nota });
            });

            $.ajax({
                url: 'guardar_notas.php',
                method: 'POST',
                data: {
                    id_tarea: idTarea,
                    notas: notas
                },
                success: function(response) {
                    alert(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error en la solicitud AJAX: " + error);
                }
            });
        });
    });
    </script>

</body>
</html>

