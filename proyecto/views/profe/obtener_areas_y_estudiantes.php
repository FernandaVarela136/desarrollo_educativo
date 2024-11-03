<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

// Validar sesión
session_start();
if (!isset($_SESSION['id_usuario'])) {
    die('Acceso denegado.');
}

$idUsuario = $_SESSION['id_usuario'];
$idGrado = isset($_GET['id_grado']) ? $_GET['id_grado'] : null;
$idArea = isset($_GET['id_area']) ? $_GET['id_area'] : null;

// Obtener el ID del profesor
$queryProfesor = "SELECT ID_profesor FROM profesor WHERE id_usuario = $idUsuario";
$resultProfesor = mysqli_query($conexion, $queryProfesor);
if (!$resultProfesor || mysqli_num_rows($resultProfesor) === 0) {
    die("No se encontró el profesor para este usuario.");
}
$rowProfesor = mysqli_fetch_assoc($resultProfesor);
$idProfesor = $rowProfesor['ID_profesor'];

if ($idGrado) {
    // Obtener las áreas del profesor y del grado seleccionado
    $queryAreas = "
        SELECT 
            area.ID_Area, 
            area.area 
        FROM 
            area 
        JOIN 
            asignaciones ON area.ID_Area = asignaciones.ID_Area 
        WHERE 
            asignaciones.ID_Grado = $idGrado 
            AND asignaciones.ID_Profesor = $idProfesor";

    $resultAreas = mysqli_query($conexion, $queryAreas);
    if (!$resultAreas) {
        die("Error en la consulta de áreas: " . mysqli_error($conexion));
    }

    // Construir el HTML para las opciones del select de áreas
    $areasHtml = "<option value=''>Seleccionar Área</option>";
    while ($rowArea = mysqli_fetch_assoc($resultAreas)) {
        $areasHtml .= "<option value='" . $rowArea['ID_Area'] . "'>" . $rowArea['area'] . "</option>";
    }
    $queryEstudiantes = "
    SELECT 
        usuario.ID_Usuario, 
        usuario.nombre 
    FROM 
        usuario 
    JOIN 
        estudiante ON usuario.ID_Usuario = estudiante.ID_Usuario  
    WHERE 
        estudiante.IDGrado = $idGrado"; 

$resultEstudiantes = mysqli_query($conexion, $queryEstudiantes);

// Generar HTML para los estudiantes
$estudiantesHtml = "";
while ($rowEstudiante = mysqli_fetch_assoc($resultEstudiantes)) {
    $estudiantesHtml .= "<tr>
                            <td>" . $rowEstudiante['ID_Usuario'] . "</td>
                            <td>" . $rowEstudiante['nombre'] . "</td>
                            <td><input type='text' name='examen' placeholder='Examen'></td>
                            <td><input type='text' name='parcial' placeholder='Parcial'></td>
                        </tr>";
}

// Enviar de vuelta el HTML como respuesta
echo json_encode([
    'areas' => $areasHtml,
    'estudiantes' => $estudiantesHtml
]);
}
?>


