<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

$SQL = "SELECT t.nombre_tarea, t.descripcion, t.fecha_entrega, t.puntaje_obtener, 
               CONCAT(g.nombre_grado, ' ', g.nivel) AS grado_nivel, a.area, b.nombre_bimestre
        FROM tareas t
        JOIN grado g ON t.id_Grado = g.ID_Grado
        JOIN area a ON t.id_area = a.ID_Area
        JOIN bimestres b ON t.id_bimestre = b.ID_Bimestre
        ORDER BY b.nombre_bimestre DESC";

$resultado = mysqli_query($conexion, $SQL);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tareas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function downloadPDF() {
            let searchTerm = document.getElementById('search-input').value.trim();
            
            if (searchTerm === '') {
                alert('Por favor, ingrese un término de búsqueda antes de descargar el PDF.');
                return; // Evita la redirección si el campo está vacío
            }
            
            window.location.href = `/proyecto/includes/exportar_pdf.php?report=tareas&search=${encodeURIComponent(searchTerm)}`;
        }
    </script>
</head>

<body>
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="../">Home</a>
    <form class="d-flex ms-auto" id="search-form">
      <input class="form-control me-2" id="search-input" type="search" placeholder="Buscar" aria-label="Search">
      <button type="button" class="btn btn-outline-primary" onclick="downloadPDF()">Descargar reporte buscado</button>
      <a href="/proyecto/includes/exportar_pdf_completo.php?report=tareas" class="btn btn-outline-primary">Descargar Reporte Completo</a>
    </form>
  </div>
</nav>
    <div class="container-fluid">
        
        <table class="table table-bordered" id="users">
            <thead>    
                <tr>
                    <th>Bimestre</th>
                    <th>Materia</th> 
                    <th>Nombre de Tarea</th>
                    <th>Descripción</th>
                    <th>Fecha de Entrega</th>
                    <th>Puntaje a Obtener</th>
                    <th>Grado Asociado</th>   
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($resultado) > 0) {
                    while ($user = mysqli_fetch_assoc($resultado)) {
                        echo "<tr>";
                        echo "<td>" . $user['nombre_bimestre'] . "</td>";
                        echo "<td>" . $user['area'] . "</td>";
                        echo "<td>" . $user['nombre_tarea'] . "</td>";
                        echo "<td>" . $user['descripcion'] . "</td>";
                        echo "<td>" . $user['fecha_entrega'] . "</td>";
                        echo "<td>" . $user['puntaje_obtener'] . "</td>";
                        echo "<td>" . $user['grado_nivel'] ." " .$user['nivel'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No existen registros</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
    </div>

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
