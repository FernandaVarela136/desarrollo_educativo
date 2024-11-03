<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die('Acceso denegado.');
}

$idUsuario = $_SESSION['id_usuario'];

$queryEstudiante = "SELECT ID_Estudiante FROM estudiante WHERE Id_usuario = $idUsuario";
$resultEstudiante = mysqli_query($conexion, $queryEstudiante);

if ($resultEstudiante && mysqli_num_rows($resultEstudiante) > 0) {
    $row = mysqli_fetch_assoc($resultEstudiante);
    $idEstudiante = $row['ID_Estudiante'];

    $queryProgreso = "
        SELECT a.Area AS nombre_materia, 
               COALESCE(ROUND(puntajes_totales.zona), 0) AS zona, 
               COALESCE(ROUND(c.calificacion_parcial), 0) AS calificacion_parcial, 
               COALESCE(ROUND(c.calificacion_examen), 0) AS calificacion_examen
        FROM estudiante e
        LEFT JOIN calificaciones c ON e.ID_Estudiante = c.IdEstudiante
        LEFT JOIN area a ON c.id_area = a.ID_Area
        LEFT JOIN (SELECT p.IdEstudiante, 
                          ROUND(SUM(p.Puntaje)) AS zona
                   FROM punteos p 
                   GROUP BY p.IdEstudiante
                  ) puntajes_totales ON e.ID_Estudiante = puntajes_totales.IdEstudiante
        WHERE e.ID_Estudiante = $idEstudiante";

    $resultProgreso = mysqli_query($conexion, $queryProgreso);

    $progreso = [];
    while ($row = mysqli_fetch_assoc($resultProgreso)) {
        $progreso[] = $row;
    }

    // Aquí devolvemos los datos de progreso
    echo json_encode($progreso);
} else {
    echo json_encode([]);
}
?>
