<?php
// Iniciar sesión y conectar a la base de datos
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

// Verificar que el ID de usuario esté definido
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('No estás autorizado.');</script>";
    echo "<script>location.assign('../index.php');</script>";
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Consulta SQL para obtener calificaciones
$sql = "
    SELECT e.ID_Estudiante, 
           u.Nombre AS estudiante, 
           CONCAT(g.nombre_grado, ' ', g.nivel) AS grado_nivel,  -- Concatenando el grado y el nivel
           a.Area AS nombre_materia,
           COALESCE(SUM(CASE WHEN t.ID_Bimestre = 1 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre1,
           COALESCE(SUM(CASE WHEN t.ID_Bimestre = 2 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre2,
           COALESCE(SUM(CASE WHEN t.ID_Bimestre = 3 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre3,
           COALESCE(SUM(CASE WHEN t.ID_Bimestre = 4 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre4,
           CASE 
               WHEN COUNT(DISTINCT CASE WHEN t.ID_Bimestre IN (1, 2, 3, 4) AND (p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0)) > 0 THEN 1 END) > 0 THEN 
                   (COALESCE(SUM(CASE WHEN t.ID_Bimestre IN (1, 2, 3, 4) THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) / 
                   COUNT(DISTINCT CASE WHEN t.ID_Bimestre IN (1, 2, 3, 4) AND (p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0)) > 0 THEN t.ID_Bimestre END)) 
               ELSE 0 END AS promedio_bimestres
    FROM 
        estudiante e
    LEFT JOIN 
        usuario u ON e.Id_usuario = u.Id_usuario
    LEFT JOIN 
        grado g ON e.IDGrado = g.ID_Grado
    LEFT JOIN 
        punteos p ON e.ID_Estudiante = p.IdEstudiante
    LEFT JOIN 
        tareas t ON p.id_tarea = t.ID_Tarea
    LEFT JOIN 
        area a ON t.id_area = a.ID_Area
    LEFT JOIN 
        calificaciones c ON e.ID_Estudiante = c.IdEstudiante AND c.id_area = a.ID_Area
    WHERE 
        u.Id_usuario = ?  -- Filtrar solo por el usuario que está ingresando
    GROUP BY 
        e.ID_Estudiante, u.Nombre, a.Area, grado_nivel
    ORDER BY 
        e.ID_Estudiante, a.Area
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-home {
            background-color: gray; /* Color gris */
            color: white; /* Texto blanco */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2>Calificaciones</h2>
            <button class="btn btn-home" onclick="location.href='../'">Home</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Grado y Nivel</th>
                    <th>Bimestre 1</th>
                    <th>Bimestre 2</th>
                    <th>Bimestre 3</th>
                    <th>Bimestre 4</th>
                    <th>Promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['nombre_materia']; ?></td>
                        <td><?php echo $row['grado_nivel']; ?></td>
                        <td><?php echo $row['bimestre1']; ?></td>
                        <td><?php echo $row['bimestre2']; ?></td>
                        <td><?php echo $row['bimestre3']; ?></td>
                        <td><?php echo $row['bimestre4']; ?></td>
                        <td><?php echo $row['promedio_bimestres']; ?></td>
                        
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para Detalles -->
  
?>

