<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

if (isset($_GET['id_estudiante'])) {
    $id_estudiante = $_GET['id_estudiante'];

    // Consulta para obtener las actividades del estudiante junto con el puntaje
    $sql = "
        SELECT a.nombre_actividad, p.puntaje_obtenido, p.obtenido_por 
        FROM actividades a
        JOIN punteos p ON a.id_actividad = p.id_actividad
        WHERE p.IdEstudiante = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p><strong>Actividad:</strong> " . $row['nombre_actividad'] . "</p>";
            echo "<p><strong>Puntaje Obtenido:</strong> " . $row['puntaje_obtenido'] . "</p>";
            echo "<p><strong>Obtenido Por:</strong> " . $row['obtenido_por'] . "</p>";
            echo "<hr>"; // Línea separadora entre actividades
        }
    } else {
        echo "<p>No se encontraron actividades para este estudiante.</p>";
    }
} else {
    echo "<p>Error: No se ha recibido el ID del estudiante.</p>";
}
?>

