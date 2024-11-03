<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTarea = $_POST['id_tarea'];
    $notas = $_POST['notas'];

    foreach ($notas as $nota) {
        $idEstudiante = $nota['idEstudiante'];
        $notaExamen = $nota['notaExamen'];
        $notaParcial = $nota['notaParcial'];

        // Actualizar o insertar las notas en la tabla de calificaciones
        $query = "
            INSERT INTO calificaciones (id_usuario, id_tarea, nota_examen, nota_parcial)
            VALUES ('$idEstudiante', '$idTarea', '$notaExamen', '$notaParcial')
            ON DUPLICATE KEY UPDATE 
            nota_examen = '$notaExamen', 
            nota_parcial = '$notaParcial'";
        
        mysqli_query($conexion, $query);
    }

    echo 'Notas guardadas correctamente.';
}
?>

