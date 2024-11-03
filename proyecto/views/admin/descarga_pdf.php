<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/tfpdf.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_GET['grado'])) {
    $gradoSeleccionado = $_GET['grado'];
$sql = "SELECT 
            e.ID_Estudiante, 
            u.Nombre AS estudiante, 
            g.nombre_grado, g.nivel,
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
        WHERE 
            e.IdGrado = $gradoSeleccionado
        GROUP BY 
            e.ID_Estudiante, u.Nombre, a.Area
        ORDER BY 
            e.ID_Estudiante, a.Area;";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die('Error al preparar la consulta: ' . $conexion->error);
}
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    
    die("<script>alert('Error: No se encontraron datos para la búsqueda.'); window.location.href = '/proyecto/views/';</script>");
    exit();
}

$pdf = new tFPDF('P', 'mm', 'A4'); // Cambiar a orientación vertical
$pdf->SetMargins(10, 10, 10);

$estudiantes = [];
while ($row = $resultado->fetch_assoc()) {
    $estudiantes[$row['ID_Estudiante']][] = $row; // Agrupar por ID_Estudiante
}

// Generar un PDF por cada estudiante
foreach ($estudiantes as $idEstudiante => $datosEstudiante) {
    $pdf->AddPage();
    $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
    $pdf->SetFont('DejaVu', '', 10);
    $primerDato = $datosEstudiante[0];
    // Encabezado
    $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/proyecto/img/logo.png'; // Ajusta la ruta según tu estructura de directorios

    // Agregar logo
    $pdf->Image($logoPath, 10, 10, 30); // Ajusta la posición y el tamaño del logo
    $pdf->Cell(0, 10, 'INFORME DE RESULTADOS', 0, 1, 'C');
    $pdf->Cell(0, 10, 'CENTRO EDUCATIVO MARANATHA', 0, 1, 'C');
    $pdf->Cell(0, 10, 'NIVEL:'. strtoupper($primerDato['nivel']), 0, 1, 'C');
    $pdf->Ln(10); // Espacio después del encabezado

    // Información del estudiante alineada a la izquierda
    
    $pdf->Cell(30, 10, 'Nombre:', 0, 0, 'L');
    $pdf->Cell(0, 10, strtoupper($primerDato['estudiante']), 0, 1);
    $pdf->Cell(30, 10, 'Grado:', 0, 0, 'L');
    $pdf->Cell(0, 10, strtoupper($primerDato['nombre_grado'] . ' ' . $primerDato['nivel']), 0, 1);
    $pdf->Ln(25); // Espacio después de los datos del estudiante

    // Encabezados de la tabla
    $pdf->Cell(40, 10, 'ASIGNATURA', 0, 0, 'C'); // Sin borde
    $pdf->Cell(30, 10, 'Bimestre I', 0, 0, 'C');
    $pdf->Cell(30, 10, 'Bimestre II', 0, 0, 'C');
    $pdf->Cell(30, 10, 'Bimestre III', 0, 0, 'C');
    $pdf->Cell(30, 10, 'Bimestre IV', 0, 0, 'C');
    $pdf->Cell(30, 10, 'Promedio', 0, 1, 'C');
    $pdf->SetFont('DejaVu', '', 9);

    // Datos de las materias
    foreach ($datosEstudiante as $materia) {
            $pdf->Cell(40, 10, $materia['nombre_materia'], 0, 0);
            $pdf->Cell(30, 10, intval($materia['bimestre1'] ?? 0), 0, 0, 'C');
            $pdf->Cell(30, 10, intval($materia['bimestre2'] ?? 0), 0, 0, 'C');
            $pdf->Cell(30, 10, intval($materia['bimestre3'] ?? 0), 0, 0, 'C');
            $pdf->Cell(30, 10, intval($materia['bimestre4'] ?? 0), 0, 0, 'C');
            $pdf->Cell(30, 10, intval($materia['promedio_bimestres']  ?? 0), 0, 1, 'C'); 
    }

    // Espacio antes de las firmas
    $pdf->Ln(10);
    $pdf->SetFont('DejaVu', '', 10);
    
    // Firmas en una misma línea
    
    $pdf->Cell(90, 10, '___________________________', 0, 0, 'C');
    $pdf->Cell(90, 10, '___________________________', 0, 1, 'C');
    $pdf->Cell(90, 10, 'Vo.Bo. Dirección', 0, 0, 'C');
    $pdf->Cell(90, 10, 'Docente de Grado', 0, 1, 'C');
}

$pdf->Output('D', 'Libreta_de_Calificaciones_'.$gradoSeleccionado.'.pdf');
} else {
    // Si no se proporciona el grado, redirigir o mostrar un mensaje
    echo "<script>alert('Error: No se ha especificado un grado para la descarga.'); window.location.href = '/proyecto/views/';</script>";
    exit();
}

?>

