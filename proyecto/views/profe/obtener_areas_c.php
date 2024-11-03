<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

$idGrado = $_GET['id_grado'];

if (empty($idGrado)) {
    die("ID de grado no proporcionado.");
}

// Obtener las áreas asignadas para el grado
$queryAreas = "SELECT a.ID_Area, a.nombre_area FROM areas a 
               JOIN asignaciones asg ON a.ID_Area = asg.ID_Area
               WHERE asg.ID_Grado = $idGrado";
$resultAreas = mysqli_query($conexion, $queryAreas);

if (!$resultAreas) {
    die("Error en la consulta de áreas: " . mysqli_error($conexion));
}

$areasHtml = "";
while ($row = mysqli_fetch_assoc($resultAreas)) {
    $areasHtml .= "<option value='{$row['ID_Area']}'>{$row['nombre_area']}</option>";
}

$response = array('areasHtml' => $areasHtml);
echo json_encode($response);
?>


