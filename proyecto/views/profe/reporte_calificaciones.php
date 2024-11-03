<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
$idUsuario = $_SESSION['id_usuario'];
$rolEsperado = 3;

// Consulta para verificar el rol del profesor
$query = "SELECT r.Nombre FROM usuario u
          JOIN roles r ON u.id_rol = r.IDRol
          WHERE u.Id_usuario = $idUsuario AND r.IDRol = $rolEsperado";
$result = mysqli_query($conexion, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-success' role='alert'><h2>No se encontró un profesor asociado con este usuario.</h2>";
    echo "<p>Será redirigido a la página anterior en 5 segundos...</p>";
    echo "<p>Si no es redirigido automáticamente, haga clic <a href='/proyecto/views/' class='alert-link'>aquí</a>.</p></div>";
    header("refresh:5;url=/proyecto/views/");
    exit();
} else {
    $id_usuario = $_SESSION['id_usuario'];

    // Consulta SQL
    $SQL = "
        SELECT 
    e.ID_Estudiante, 
    u.Nombre AS estudiante, 
    a.Area AS nombre_materia, 
    COALESCE(ROUND(puntajes_totales.zona), 0) AS zona, 
    COALESCE(ROUND(c.calificacion_parcial), 0) AS calificacion_parcial,
    COALESCE(ROUND(c.calificacion_examen), 0) AS calificacion_examen, 
    g.nombre_grado, 
    g.nivel,
    b.Nombre_Bimestre  -- Incluye el nombre del bimestre
FROM 
    estudiante e 
LEFT JOIN 
    usuario u ON e.Id_usuario = u.Id_usuario 
LEFT JOIN 
    calificaciones c ON e.ID_Estudiante = c.IdEstudiante 
LEFT JOIN 
    area a ON c.id_area = a.ID_Area 
LEFT JOIN 
    grado g ON g.ID_Grado = e.IDGrado 
LEFT JOIN 
    bimestres b ON c.id_bimestre = b.ID_Bimestre  -- Relaciona la tabla de bimestres
LEFT JOIN 
    (SELECT 
        p.IdEstudiante, 
        ROUND(SUM(p.Puntaje)) AS zona 
     FROM 
        punteos p 
     GROUP BY 
        p.IdEstudiante) puntajes_totales ON e.ID_Estudiante = puntajes_totales.IdEstudiante 
LEFT JOIN 
    asignaciones ea ON ea.ID_Grado = g.ID_Grado 
                   AND ea.ID_Area = a.ID_Area 
WHERE 
    ea.ID_Profesor = (SELECT ID_Profesor FROM profesor WHERE Id_usuario = ?);
    ";
    
    $stmt = mysqli_prepare($conexion, $SQL);
    mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tareas</title>
    <!-- Enlace a Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar mejorada -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="../">Home</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <form class="d-flex ms-auto" id="search-form">
                <input class="form-control me-2" id="search-input" type="search" placeholder="Buscar tarea">
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid mt-3">
    <a href="/proyecto/includes/exportar_pdf_completo.php?report=calificaciones2" class="btn btn-primary mb-3">Descargar Reporte de calificaciones Completo</a>
    
    <table class="table table-bordered" id="users">
        <thead>
            <tr>
                <th>Bimestre</th>
                <th>Estudiante</th>
                <th>Materia</th>
                <th>Calificación Examen</th>
                <th>Calificación Parcial</th>
                <th>Zona</th>
                <th>Grado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($resultado) > 0) {
                while ($user = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . $user['Nombre_Bimestre'] ." " .$user['nivel'] . "</td>";
                    echo "<td>" . $user['estudiante'] . "</td>";
                    echo "<td>" . $user['nombre_materia'] . "</td>";
                    echo "<td>" . $user['calificacion_examen'] . "</td>";
                    echo "<td>" . $user['calificacion_parcial'] . "</td>";
                    echo "<td>" . $user['zona'] . "</td>";
                    echo "<td>" . $user['nombre_grado'] ." " .$user['nivel'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No existen registros</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Enlace a Bootstrap JS y Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
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

<?php } ?>

