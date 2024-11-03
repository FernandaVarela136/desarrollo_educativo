<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


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

// Leer los datos JSON enviados
$inputData = json_decode(file_get_contents('php://input'), true);
if (!$inputData) {
    die('No se recibieron datos.');
}

// Obtener los datos del JSON
$idArea = $inputData['id_area'];
$idBimestre = $inputData['id_bimestre']; // Asegúrate de que el ID_Bimestre se envía en la entrada JSON
$examen = $inputData['examen'];
$parcial = $inputData['parcial'];

// Preparar el array para insertar
$calificaciones = [];

foreach ($examen as $ex) {
    $idEstudiante = $ex['idEstudiante'];
    $notaExamen = $ex['nota']; // Obtener la nota del examen

    // Inicializar la nota parcial como NULL
    $notaParcial = NULL;

    // Buscar la nota parcial correspondiente
    foreach ($parcial as $pa) {
        if ($pa['idEstudiante'] === $idEstudiante) {
            $notaParcial = $pa['nota']; // Asignar la nota parcial correspondiente
            break;
        }
    }

    // Combinar las calificaciones en el mismo array
    $calificaciones[] = "($idEstudiante, $idArea, $idBimestre, $notaExamen, $notaParcial)";
}

// Crear la consulta de inserción
if (!empty($calificaciones)) {
    $values = implode(", ", $calificaciones);
    $query = "INSERT INTO calificaciones (IdEstudiante, id_area, ID_Bimestre, calificacion_examen, calificacion_parcial) 
              VALUES $values";

    if (!mysqli_query($conexion, $query)) {
        die('Error al guardar calificaciones: ' . mysqli_error($conexion));
    }
    
echo 'Calificaciones guardadas exitosamente';
}

?>





