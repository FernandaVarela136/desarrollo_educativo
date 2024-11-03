<?php
require_once('tfpdf.php'); 
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$reportType = $_GET['report'] ?? 'tareas';
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (empty($search)) {
    echo "<script>alert('Por favor, ingrese un término de búsqueda.'); window.location.href = '/proyecto/views/';</script>";
    exit();
}
switch ($reportType) {
    case 'tareas':

$sql = "SELECT t.nombre_tarea, t.descripcion, t.fecha_entrega, t.puntaje_obtener, 
               g.nombre_grado, g.nivel, a.area, b.nombre_bimestre
        FROM tareas t
        JOIN grado g ON t.id_Grado = g.ID_Grado
        JOIN area a ON t.id_area = a.ID_Area
        JOIN bimestres b ON t.id_bimestre = b.ID_Bimestre
        WHERE t.nombre_tarea LIKE ? 
           OR t.descripcion LIKE ? 
           OR g.nombre_grado LIKE ? 
           OR g.nivel LIKE ? 
           OR a.area LIKE ? 
           OR b.nombre_bimestre LIKE ?";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conexion->error);
}

$searchTerm = "%$search%";
$stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die('No se encontraron datos para la búsqueda.');
}

$pdf = new tFPDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->SetFont('DejaVu', '', 10);
$pdf->Cell(0, 10, 'Reporte de Tareas', 0, 1, 'C'); // Centrar el título
$pdf->Ln(5); // Espacio después del título

$pdf->Cell(30, 8, 'Bimestre', 1, 0, 'C'); 
$pdf->Cell(30, 8, 'Materia', 1, 0, 'C'); 
$pdf->Cell(45, 8, 'Nombre de Tarea', 1, 0, 'C');
$pdf->Cell(75, 8, 'Descripción', 1, 0, 'C');
$pdf->Cell(35, 8, 'Fecha de Entrega', 1, 0, 'C');
$pdf->Cell(35, 8, 'Puntaje a Obtener', 1, 0, 'C');
$pdf->Cell(35, 8, 'Grado Asociado', 1, 1, 'C');

$pdf->SetFont('DejaVu', '', 8);

while ($row = $resultado->fetch_assoc()) {
    $pdf->Cell(30, 8, $row['nombre_bimestre'], 1);
    $pdf->Cell(30, 8, $row['area'], 1);
    $pdf->Cell(45, 8, $row['nombre_tarea'], 1);
    $pdf->Cell(75, 8, substr($row['descripcion'], 0, 50), 1); // Limitar la descripción
    $pdf->Cell(35, 8, date('d/m/Y', strtotime($row['fecha_entrega'])), 1, 0, 'C');
    $pdf->Cell(35, 8, number_format($row['puntaje_obtener'], 2), 1, 0, 'C');
    $pdf->Cell(35, 8, $row['nombre_grado'] . " " . $row['nivel'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'Reporte_de_Tareas.pdf');
break;
case 'calificaciones':
    $sql = "SELECT 
    e.ID_Estudiante, 
    u.Nombre AS estudiante, 
    g.nombre_grado, 
    g.nivel,
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
WHERE 
    a.area LIKE ? 
    OR g.nombre_grado LIKE ? 
    OR g.nivel LIKE ? 
    OR u.Nombre LIKE ? 
GROUP BY 
    e.ID_Estudiante, u.Nombre, a.Area, g.nombre_grado, g.nivel
ORDER BY 
    e.ID_Estudiante, a.Area;";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conexion->error);
}

$searchTerm = "%$search%";
$stmt->bind_param("ssss",$searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die('No se encontraron datos para la búsqueda.');
}

$pdf = new tFPDF('L', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->SetFont('DejaVu', '', 10);
$pdf->Cell(0, 10, 'Reporte de Tareas', 0, 1, 'C'); // Centrar el título
$pdf->Ln(5); // Espacio después del título


$pdf->Cell(80, 8, 'Estudiante', 1, 0, 'C'); 
$pdf->Cell(35, 8, 'Grado', 1, 0, 'C'); 
$pdf->Cell(50, 8, 'Materia', 1, 0, 'C');
$pdf->Cell(20, 8, 'Bimestre 1', 1, 0, 'C');
$pdf->Cell(20, 8, 'Bimestre 2', 1, 0, 'C');
$pdf->Cell(20, 8, 'Bimestre 3', 1, 0, 'C');
$pdf->Cell(20, 8, 'Bimestre 4', 1, 0, 'C');
$pdf->Cell(20, 8, 'Promedio', 1, 1, 'C');
$pdf->SetFont('DejaVu', '', 8);

while ($row = $resultado->fetch_assoc()) {
    $headers = ['', '', '', '', '', 'bimestre3', 'bimestre4', ''];
    $pdf->Cell(80, 8, $row['estudiante'], 1);
    $pdf->Cell(35, 8, $row['nombre_grado'] . " " . $row['nivel'], 1);
    $pdf->Cell(50, 8, $row['nombre_materia'], 1);
    $pdf->Cell(20, 8, $row['bimestre1'] ?? '0', 1); // Default to '0' if not set
    $pdf->Cell(20, 8, $row['bimestre2'] ?? '0', 1);
    $pdf->Cell(20, 8, $row['bimestre3'] ?? '0', 1);
    $pdf->Cell(20, 8, $row['bimestre4'] ?? '0', 1);
    $pdf->Cell(20, 8, $row['promedio_bimestres'] ?? '0', 1);
    $pdf->Ln();
}

$pdf->Output('D', 'Reporte_de_Calificaciones.pdf');
break;
default:
die('Tipo de reporte no válido.');
}
?>

