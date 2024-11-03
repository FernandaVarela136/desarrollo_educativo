<?php 
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

$idUsuario = $_SESSION['id_usuario'];

// Activamos la captura de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Validación del rol de profesor
    $rolEsperado = 2; // Rol de profesor
    $query = "SELECT r.Nombre 
              FROM usuario u
              JOIN roles r ON u.id_rol = r.IDRol
              WHERE u.Id_usuario = $idUsuario AND r.IDRol = $rolEsperado";
    $result = mysqli_query($conexion, $query);

    if (mysqli_num_rows($result) == 0) {
        echo "<div class='alert alert-warning' role='alert'>
                <h2>No se encontró un profesor asociado con este usuario.</h2>
                <p>Será redirigido en 5 segundos...</p>
                <a href='/proyecto/views/' class='alert-link'>Volver</a>
              </div>";
        header("refresh:5;url=/proyecto/views/");
        exit();
    }

    // Consulta SQL para mostrar las calificaciones por bimestres, promedio y grado
    $SQL = "SELECT 
    e.ID_Estudiante, 
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
    grado g ON e.IdGrado = g.ID_Grado
LEFT JOIN 
    punteos p ON e.ID_Estudiante = p.IdEstudiante
LEFT JOIN 
    tareas t ON p.id_tarea = t.ID_Tarea
LEFT JOIN 
    area a ON t.id_area = a.ID_Area
LEFT JOIN 
    calificaciones c ON e.ID_Estudiante = c.IdEstudiante AND c.id_area = a.ID_Area AND c.ID_Bimestre = t.ID_Bimestre
GROUP BY 
    e.ID_Estudiante, u.Nombre, a.Area, grado_nivel  -- Agrupando por la columna concatenada
ORDER BY 
    e.ID_Estudiante, a.Area;
";

    $resultado = mysqli_query($conexion, $SQL);
} catch (Exception $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Calificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function downloadPDF() {
            let searchTerm = document.getElementById('search-input').value.trim();
            
            if (searchTerm === '') {
                alert('Por favor, ingrese un término de búsqueda antes de descargar el PDF.');
                return; // Evita la redirección si el campo está vacío
            }
            
            window.location.href = `/proyecto/includes/exportar_pdf.php?report=calificaciones&search=${encodeURIComponent(searchTerm)}`;
        }
    </script>
</head>
<body>

<nav class="navbar bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="../">Home</a>
    <form class="d-flex ms-auto">
      <input class="form-control me-2" id="search-input" type="search" placeholder="Buscar" aria-label="Search">
      <button type="button" class="btn btn-outline-primary" onclick="downloadPDF()">Descargar reporte buscado</button>
      <a href="/proyecto/includes/exportar_pdf_completo.php?report=calificaciones" class="btn btn-outline-primary">Descargar Reporte Completo</a>
    </form>
  </div>
</nav>

<div class="container-fluid mt-3">
    <h2>Reporte de Calificaciones por Bimestres</h2>
    <table class="table table-bordered" id="users">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Grado</th>
                <th>Materia</th>
                <th>Bimestre 1</th>
                <th>Bimestre 2</th>
                <th>Bimestre 3</th>
                <th>Bimestre 4</th>
                <th>Promedio Final</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($resultado) > 0) {
                while ($user = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['estudiante']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['grado_nivel']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['nombre_materia']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['bimestre1']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['bimestre2']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['bimestre3']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['bimestre4']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['promedio_bimestres']) . "</td>";
                    echo "<td><button class='btn btn-info' onclick='verDetalles(" . $user['ID_Estudiante'] . ", \"" . htmlspecialchars($user['nombre_materia']) . "\")'>Ver Detalles</button></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No se encontraron resultados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function verDetalles(idEstudiante, materia) {
    // Aquí se puede implementar una consulta para obtener detalles de la zona y examen
    alert("Detalles de " + materia + " para el estudiante con ID: " + idEstudiante);
}
</script>
<script>
        document.getElementById('search-input').addEventListener('keyup', function() {
            let searchTerm = this.value.toLowerCase();
            let tableRows = document.querySelectorAll('#users tbody tr');
            
            tableRows.forEach(function(row) {
                let rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>


