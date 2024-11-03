<?php include "../includes/configSesion.php"; 
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php'; ?>

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Home - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #E2EAF4; /* Fondo claro */
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .welcome {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .btn-group .btn {
            margin: 5px;
        }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background-color: #0056b3; /* Azul */
            color: white;
        }
    </style>
</head>  
<body>  
    <div class="container">  
        <?php
        session_start();
        if (isset($_SESSION['usuario'])) {
            $usuario = $_SESSION['usuario'];
            $nombre = $_SESSION['nombre']; 
            echo "<div class='welcome'><h2>Bienvenido, " . htmlspecialchars($nombre) . " (" . htmlspecialchars($usuario) . ")!</h2></div>";
        } else {
            header("Location: ../../index.php");
            exit();
        }
        ?>
        
        <p class="text-center">¿Quieres cerrar sesión? <a href="../includes/sesion/cerrarSesion.php">Salir</a></p>  
        
        <?php if ($id_rol == 2) { ?>
            <div class="btn-group d-flex justify-content-center" role="group" aria-label="Opciones de administración">
                <button type="button" class="btn btn-outline-primary" onclick="location.href='../views/admin/agregar_usuario.php'">Añadir usuario</button>
                <button type="button" class="btn btn-outline-primary" onclick="location.href='../views/admin/usuario.php'">Usuarios</button>
                <button type="button" class="btn btn-outline-primary" onclick="location.href='../views/admin/reporte_tareas.php'">Reportes de tareas</button>
                <button type="button" class="btn btn-outline-primary" onclick="location.href='../views/admin/reporte_calificaciones.php'">Reportes de calificaciones</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/admin/agregar_circular.php'">agregar circular</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/admin/libreta_calificaciones.php'">libreta de calificaciones por grado</button>
            </div>
        <?php } elseif ($id_rol == 1) { 
            session_start();
            if (!isset($_SESSION['nivel'])) {
    die("Error: El nivel de sesión no está definido.");
}

$nivel = $_SESSION['nivel'];
$id_rol = $_SESSION['rol'];

// Recuperar circulares según el nivel
$query = "SELECT ID_circular, Contenido, FechaEnvio, Titulo 
          FROM circulares 
          WHERE Nivel = ? AND FechaEnvio >= CURDATE()";
$stmt = $conexion->prepare($query);
$stmt->bind_param('s', $nivel);
$stmt->execute();
$result = $stmt->get_result();
$circulares = $result->fetch_all(MYSQLI_ASSOC);

// Verificar el resultado
if (empty($circulares)) {
    echo "No hay circulares disponibles.";
} ?>   <div class="toast-container" id="toast-container"></div> <!-- Contenedor de toasts -->

<?php
// Recuperar las circulares para el usuario
$nivel = $_SESSION['nivel'];
$query = "SELECT Titulo, Contenido FROM circulares WHERE Nivel = ? AND FechaEnvio >= CURDATE()";
$stmt = $conexion->prepare($query);
$stmt->bind_param('s', $nivel);
$stmt->execute();
$result = $stmt->get_result();
$circulares = $result->fetch_all(MYSQLI_ASSOC);
?>

</div>  

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    <?php foreach ($circulares as $circular): ?>
        // Crear dinámicamente un toast para cada circular
        let toast = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Circular: <?php echo $circular['Titulo']; ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?php echo $circular['Contenido']; ?>
                </div>
            </div>`;
        $('#toast-container').append(toast);
    <?php endforeach; ?>
});
</script>
            <div class="btn-group d-flex justify-content-center" role="group" aria-label="Opciones de padres">
                <button type="button" class="btn btn-outline-primary" onclick="location.href='../views/padres/calificaciones.php'">Calificaciones</button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#progresoModal">Ver Progreso</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/padres/circulares.php'">Circulares</button>
            </div>

            <div class="modal fade" id="progresoModal" tabindex="-1" aria-labelledby="progresoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="progresoModalLabel">Progreso del Estudiante</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <canvas id="progresoChart"></canvas>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('#progresoModal').on('show.bs.modal', function() {
                        $.ajax({
                            url: 'obtener_progreso.php',
                            method: 'GET',
                            success: function(response) {
                                var progreso = JSON.parse(response);
                                if (progreso.length > 0) {
                                    var materias = [];
                                    var zonas = [];
                                    var calificacionesParciales = [];
                                    var calificacionesExamenes = [];

                                    progreso.forEach(function(item) {
                                        materias.push(item.nombre_materia);
                                        zonas.push(item.zona);
                                        calificacionesParciales.push(item.calificacion_parcial);
                                        calificacionesExamenes.push(item.calificacion_examen);
                                    });

                                    var ctx = document.getElementById('progresoChart').getContext('2d');
                                    new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: materias,
                                            datasets: [
                                                {
                                                    label: 'Zona',
                                                    data: zonas,
                                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                                    borderColor: 'rgba(75, 192, 192, 1)',
                                                    borderWidth: 1
                                                },
                                                {
                                                    label: 'Calificación Parcial',
                                                    data: calificacionesParciales,
                                                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                                                    borderColor: 'rgba(255, 159, 64, 1)',
                                                    borderWidth: 1
                                                },
                                                {
                                                    label: 'Calificación Examen',
                                                    data: calificacionesExamenes,
                                                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                                    borderColor: 'rgba(153, 102, 255, 1)',
                                                    borderWidth: 1
                                                }
                                            ]
                                        },
                                        options: {
                                            scales: {
                                                y: {
                                                    beginAtZero: true
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    alert("No hay datos de progreso disponibles.");
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error al obtener los datos: " + error);
                            }
                        });
                    });
                });

            </script>
            
        <?php } elseif ($id_rol == 3) { ?>  
            <div class="btn-group d-flex justify-content-center" role="group" aria-label="Opciones de profesores">
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/profe/insertar_tareas.php'">Añadir actividad</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/profe/ingresar_notas.php'">Ingresar calificaciones</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/profe/reporte_calificaciones.php'">Reporte de calificaciones</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/profe/agregar_circular.php'">agregar circular</button>
                <button type="button" class="btn btn-outline-primary"  onclick="location.href='../views/profe/ingresar_calificaciones.php'">ingresar nota examen y parcial</button>
            </div>
        <?php } ?>
    </div>  
</body>  
</html>
