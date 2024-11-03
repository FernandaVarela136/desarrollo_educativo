<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

// Conexión a la base de datos
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
$idGrado = $_GET['id_grado'];

if (empty($idGrado)) {
    die("ID de grado no proporcionado.");
}

// Obtener las tareas del grado seleccionado
$queryTareas = "SELECT t.ID_Tarea, t.nombre_tarea
                FROM tareas t
                WHERE t.id_Grado = $idGrado";
$resultTareas = mysqli_query($conexion, $queryTareas);

if (!$resultTareas) {
    die("Error en la consulta de tareas: " . mysqli_error($conexion));
}

$tareasHtml = "<option value=''>Seleccione una tarea</option>";
while ($row = mysqli_fetch_assoc($resultTareas)) {
    $tareasHtml .= "<option value='{$row['ID_Tarea']}'>{$row['nombre_tarea']}</option>";
}

// Obtener los estudiantes del grado seleccionado
$queryEstudiantes = "SELECT e.ID_Estudiante, u.Nombre
                     FROM estudiante e
                     INNER JOIN usuario u ON e.Id_usuario = u.Id_usuario
                     WHERE e.IDGrado = $idGrado";
$resultEstudiantes = mysqli_query($conexion, $queryEstudiantes);

if (!$resultEstudiantes) {
    die("Error en la consulta de estudiantes: " . mysqli_error($conexion));
}

// Obtener los puntajes actuales para cada tarea
$puntajes = array();
if (isset($_GET['id_tarea']) && !empty($_GET['id_tarea'])) {
    $idTarea = $_GET['id_tarea'];
    $queryPuntajes = "SELECT IdEstudiante, Puntaje
                      FROM punteos
                      WHERE id_tarea = $idTarea";
    $resultPuntajes = mysqli_query($conexion, $queryPuntajes);
    while ($row = mysqli_fetch_assoc($resultPuntajes)) {
        $puntajes[$row['IdEstudiante']] = $row['Puntaje'];
    }
}

$estudiantesHtml = "";
while ($row = mysqli_fetch_assoc($resultEstudiantes)) {
    $nota = isset($puntajes[$row['ID_Estudiante']]) ? $puntajes[$row['ID_Estudiante']] : '';
    $estudiantesHtml .= "<tr data-id='{$row['ID_Estudiante']}'>
                            <td>{$row['Nombre']}</td>
                            <td><input type='number' step='0.01' name='nota[]' value='{$nota}'></td>
                         </tr>";
}

$response = array(
    'tareasHtml' => $tareasHtml,
    'estudiantesHtml' => $estudiantesHtml
);

echo json_encode($response);
?>