<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la configuración de sesión

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
    WHERE 
        asignaciones.ID_Profesor = $idProfesor";

$resultGrados = mysqli_query($conexion, $queryGrados);

// Verificar errores
if (!$resultGrados) {
    die("Error en la consulta de grados: " . mysqli_error($conexion));
}
$queryBimestres = "SELECT ID_Bimestre, nombre_bimestre FROM bimestres";
$resultBimestres = mysqli_query($conexion, $queryBimestres);

// Verificar errores
if (!$resultBimestres) {
    die("Error en la consulta de bimestres: " . mysqli_error($conexion));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso de Examen y Parcial</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h2>Ingreso de Notas</h2>
        <button class="btn btn-secondary" onclick="location.href='../'">Home</button>
    </div>
    <form id="formNotas" method="POST" action="">
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
        <label for="areaSelect">Área</label>
        <select id="areaSelect" class="form-control">
            <option value="">Seleccionar Área</option>
        </select>
    </div>
    <div class="form-group">
    <label for="bimestreSelect">Bimestre</label>
    <select id="bimestreSelect" class="form-control">
        <option value="">Seleccionar Bimestre</option>
        <?php while ($rowBimestre = mysqli_fetch_assoc($resultBimestres)) { ?>
            <option value="<?php echo $rowBimestre['ID_Bimestre']; ?>">
                <?php echo $rowBimestre['nombre_bimestre']; ?>
            </option>
        <?php } ?>
    </select>
</div>
    <h3>Estudiantes</h3>
    
    <select id="gradoSelect" style="display: none;">
    <option value="">Seleccione un grado</option>
    <!-- Opciones de grado se llenarán aquí -->
</select>

<select id="areaSelect" style="display: none;">
    <option value="">Seleccione un área</option>
    <!-- Opciones de área se llenarán aquí -->
</select>

<table id="estudiantesTable">
    <thead>
        <tr>
            <th>ID Estudiante</th>
            <th>Nombre</th>
            <th>Examen</th>
            <th>Parcial</th>
        </tr>
    </thead>
    <tbody>
        <!-- Filas de estudiantes se llenarán aquí -->
    </tbody>
</table>

<button type="submit" id="guardarCalificaciones" class="btn btn-primary">Guardar Notas</button>

    </form>

    
    <script>
      $('#gradoSelect').change(function () {
    var idGrado = $(this).val();

    if (idGrado) {
        $.ajax({
            url: 'obtener_areas_y_estudiantes.php', // Asegúrate de que apunte al archivo correcto
            method: 'GET',
            data: { id_grado: idGrado },
            dataType: 'json', // Especificar que esperamos una respuesta JSON
            success: function (response) {
                $('#areaSelect').html(response.areas); // Llena el select de áreas
                $('#estudiantesTable tbody').html(response.estudiantes); // Llena la tabla de estudiantes
            },
            error: function () {
                alert('Error al cargar áreas y estudiantes.');
            }
        });
    } else {
        $('#areaSelect').html(''); // Limpia el select si no hay grado seleccionado
        $('#estudiantesTable tbody').html(''); // Limpia la tabla de estudiantes
    }
});

$(document).ready(function () {
    $('#guardarCalificaciones').click(function (e) {
        e.preventDefault(); // Evita que el formulario se recargue.

        var idArea = $('#areaSelect').val(); 
        var bimestreSelect = $('#bimestreSelect').val(); // Obtener el ID del bimestre
        var examen = [];
        var parcial = [];

        $('#estudiantesTable tbody tr').each(function () {
            var idEstudiante = $(this).data('id'); 
            var notaExamen = $(this).find('input[name="examen"]').val();
            var notaParcial = $(this).find('input[name="parcial"]').val();

            examen.push({ idEstudiante: idEstudiante, nota: notaExamen });
            parcial.push({ idEstudiante: idEstudiante, nota: notaParcial });
        });

        console.log({ idArea, examen, parcial, id_bimestre: bimestreSelect }); // Verificar datos enviados

        $.ajax({
            url: 'guardar_calificaciones.php',
            method: 'POST',
            contentType: 'application/json', // Especificar que se enviará JSON
            data: JSON.stringify({
                id_area: idArea,
                examen: examen,
                parcial: parcial,
                id_bimestre: bimestreSelect // Asegúrate de que `bimestreSelect` tiene el valor correcto
            }),
            success: function (response) {
                alert('Calificaciones guardadas: ' + response);
            },
            error: function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', error);
            }
        });
    });
    if (!idArea || !bimestreSelect) {
    alert('Por favor selecciona un área y un bimestre.');
    return; // Salir de la función si no están seleccionados
}


});

    </script>
</body>
</html>


