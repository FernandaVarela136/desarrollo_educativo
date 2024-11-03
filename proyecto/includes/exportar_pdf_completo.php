<?php
require_once('tfpdf.php'); 
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
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

// Mostrar errores (solo para desarrollo, remover en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Recuperar el tipo de reporte
$reportType = $_GET['report'] ?? 'tareas';

// Preparar las consultas SQL según el tipo de reporte
switch ($reportType) {
    case 'tareas':
        $SQL = "SELECT t.nombre_tarea, t.descripcion, t.fecha_entrega, t.puntaje_obtener, 
                       CONCAT(g.nombre_grado, ' ', g.nivel) AS grado_nivel, a.area, b.nombre_bimestre
                FROM tareas t
                JOIN grado g ON t.id_Grado = g.ID_Grado
                JOIN area a ON t.id_area = a.ID_Area
                JOIN bimestres b ON t.id_bimestre = b.ID_Bimestre
                ORDER BY b.nombre_bimestre DESC";
        $title = 'Reporte de Tareas';
        $headers = ['nombre_bimestre', 'area', 'nombre_tarea', 'descripcion', 'fecha_entrega', 'puntaje_obtener', 'grado_nivel'];
        $headerTitles = ['Bimestre', 'Materia', 'Nombre de Tarea', 'Descripción', 'Fecha de Entrega', 'Puntaje a Obtener', 'Grado'];
        $widths = [30, 50, 60, 40, 30, 40, 30]; 
        // Preparar la consulta
$stmt = $conexion->prepare($SQL);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conexion->error);
}

$stmt->execute();
$resultado = $stmt->get_result();
        break;

    case 'calificaciones':
        $SQL = "SELECT 
                    e.ID_Estudiante, 
                    u.Nombre AS estudiante, 
                    CONCAT(g.nombre_grado, ' ', g.nivel) AS grado_nivel,
                    a.Area AS nombre_materia,
                    COALESCE(SUM(CASE WHEN t.ID_Bimestre = 1 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre1,
                    COALESCE(SUM(CASE WHEN t.ID_Bimestre = 2 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre2,
                    COALESCE(SUM(CASE WHEN t.ID_Bimestre = 3 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre3,
                    COALESCE(SUM(CASE WHEN t.ID_Bimestre = 4 THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) AS bimestre4,
                    CASE 
                        WHEN COUNT(DISTINCT CASE WHEN t.ID_Bimestre IN (1, 2, 3, 4) AND (p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0)) > 0 THEN 1 END) > 0 THEN 
                            (COALESCE(SUM(CASE WHEN t.ID_Bimestre IN (1, 2, 3, 4) THEN p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0) END), 0) / 
                            COUNT(DISTINCT CASE WHEN t.ID_Bimestre IN (1, 2, 3, 4) AND (p.Puntaje + COALESCE(c.calificacion_examen, 0) + COALESCE(c.calificacion_parcial, 0)) > 0 THEN t.ID_Bimestre END)) 
                        ELSE 0 
                    END AS promedio_bimestres
                FROM 
                    estudiante e
                LEFT JOIN usuario u ON e.Id_usuario = u.Id_usuario
                LEFT JOIN grado g ON e.IdGrado = g.ID_Grado
                LEFT JOIN punteos p ON e.ID_Estudiante = p.IdEstudiante
                LEFT JOIN tareas t ON p.id_tarea = t.ID_Tarea
                LEFT JOIN area a ON t.id_area = a.ID_Area
                LEFT JOIN calificaciones c ON e.ID_Estudiante = c.IdEstudiante 
                                          AND c.id_area = a.ID_Area 
                                          AND c.ID_Bimestre = t.ID_Bimestre
                GROUP BY e.ID_Estudiante, u.Nombre, a.Area, grado_nivel
                ORDER BY e.ID_Estudiante, a.Area;";
        $title = 'Reporte de Calificaciones';
        $headers = ['estudiante', 'grado_nivel', 'nombre_materia', 'bimestre1', 'bimestre2', 'bimestre3', 'bimestre4', 'promedio_bimestres'];
        $headerTitles = ['Estudiante', 'Grado', 'Materia', 'Bimestre 1', 'Bimestre 2', 'Bimestre 3', 'Bimestre 4', 'Promedio'];
        $widths = [80, 35, 50, 20, 20, 20, 20, 20];
        // Preparar la consulta
$stmt = $conexion->prepare($SQL);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conexion->error);
}

$stmt->execute();
$resultado = $stmt->get_result();
        break;
        case 'calificaciones2':
            $SQL = "SELECT 
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
    ea.ID_Profesor = (SELECT ID_Profesor FROM profesor WHERE Id_usuario = ?);";
$stmt = mysqli_prepare($conexion, $SQL);
mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
            $title = 'Reporte de Calificaciones';
            $headers = ['Nombre_Bimestre', 'estudiante', 'nombre_materia', 'calificacion_examen', 'calificacion_parcial', 'zona', 'nombre_grado','nivel'];
            $headerTitles = ['Bimestre', 'Nombre Estudiante', 'Materia', 'Examen', 'Parcial', 'zona', 'Grado', 'Nivel'];
            $widths = [35, 80, 50, 20, 20, 20, 20, 20,];
            break;
    default:
        die('Tipo de reporte no válido.');
}



if ($resultado->num_rows == 0) {
    die('No se encontraron datos para generar el PDF.');
}

// Crear el PDF
$pdf = new tFPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->SetFont('DejaVu', '', 10);
$pdf->Cell(0, 10, $title, 0, 1, 'C');

// Encabezados de la tabla
foreach ($headerTitles as $index => $headerTitle) {
    $pdf->Cell($widths[$index], 10, $headerTitle, 1, 0, 'C');
}
$pdf->Ln();

// Rellenar la tabla con los datos
while ($row = $resultado->fetch_assoc()) {
    foreach ($headers as $index => $header) {
        $value = $row[$header] ?? 'N/A';
        $pdf->Cell($widths[$index], 10, $value, 1, 0, 'L');
    }
    $pdf->Ln();
}

// Enviar el PDF al navegador
$pdf->Output('D', $title . '.pdf');
?>

<?php } ?>