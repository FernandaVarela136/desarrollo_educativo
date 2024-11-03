<?php
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    die('Acceso denegado.');
}

$idUsuario = $_SESSION['id_usuario'];

$queryProfesor = "SELECT id_rol FROM usuario WHERE id_usuario = $idUsuario AND id_rol = 3"; // Rol 3 es Profesor
$resultProfesor = mysqli_query($conexion, $queryProfesor);

if (!$resultProfesor || mysqli_num_rows($resultProfesor) == 0) {
    echo "<h2>Este usuario no es profesor.</h2>";
    echo "<p>Será redirigido a la página anterior en 5 segundos...</p>";
    echo "<p>Si no es redirigido automáticamente, haga clic <a href='/proyecto/views/'>aquí</a>.</p>";
    header("refresh:5;url=/proyecto/views/");
    exit();
}

$sql = "SELECT MAX(id_tarea) AS max_id FROM tareas";
$resultadoid = mysqli_query($conexion, $sql);
$idm = 1;
if ($resultadoid && mysqli_num_rows($resultadoid) > 0) {
    $row = mysqli_fetch_assoc($resultadoid);
    $idm = $row['max_id'] + 1;
}

$sql_roles = "SELECT IDRol, Nombre FROM roles";
$roles_result = mysqli_query($conexion, $sql_roles);

// Cargar los bimestres desde la base de datos
$sql_bimestres = "SELECT id_bimestre, nombre_bimestre FROM bimestres"; // Ajusta la consulta según tu tabla de bimestres
$bimestres_result = mysqli_query($conexion, $sql_bimestres);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $idgrado = $_POST['grado'];
    $fecha = $_POST['fecha'];
    $descripcion = $_POST['descripcion'];
    $puntaje = $_POST['puntaje'];
    $id_area = $_POST['area'];
    $id_bimestre = $_POST['bimestre']; // Obtener el id_bimestre del formulario
    
    // Insertar la nueva tarea
    $sqlinsmar = $conexion->prepare("INSERT INTO `tareas`(`ID_Tarea`, `id_Grado`, `Fecha_entrega`, `descripcion`, `puntaje_obtener`, `id_area`, `nombre_tarea`, `id_bimestre`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $sqlinsmar->bind_param("iisssssi", $idm, $idgrado, $fecha, $descripcion, $puntaje, $id_area, $nombre, $id_bimestre); // Asegúrate de que el orden coincida

    if ($sqlinsmar->execute()) {
        header("Location: ../index.php?message=2");
    } else {
        header("Location: ../index.php?message=3");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Tarea</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none; /* Hidden por defecto */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .modal-content button {
            margin-top: 10px;
        }
        .btn-gray {
            background-color: #6c757d; /* Color gris */
            color: white;
        }
        .btn-gray:hover {
            background-color: #5a6268; /* Color gris oscuro en hover */
        }
        .btn-container {
            text-align: right; /* Alinear a la derecha */
            margin-bottom: 20px; /* Espaciado inferior */
        }
    </style>
    <script>
        function showModal(message) {
            const modal = document.getElementById('myModal');
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `<p>${message}</p><button onclick="closeModal()">Cerrar</button>`;
            modal.style.display = 'flex';
        }

        function closeModal() {
            const modal = document.getElementById('myModal');
            modal.style.display = 'none';
            window.location.href = "usuario.php"; // Redirigir después de cerrar el modal
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');

            if (message === '2') {
                showModal('Usuario agregado con éxito');
            } else if (message === '3') {
                showModal('Error al agregar usuario');
            }
        };
    </script>
</head>
<body>
    <div class="container mt-4">
        <div class="btn-container">
            <button class="btn btn-gray" onclick="location.href='../'">Home</button>
        </div>
        <h2>Añadir Tarea</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Tarea:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <input type="text" class="form-control" id="descripcion" name="descripcion" required>
            </div>
            <div class="form-group">
                <label for="puntaje">Puntaje a obtener:</label>
                <input type="number" class="form-control" id="puntaje" name="puntaje" required>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="form-group">
                <label for="area">Área:</label>
                <select class="form-control" id="area" name="area">
                    <option value="">Seleccione un área</option>
                    <?php
                    // Cargar áreas desde la base de datos
                    $sql_areas = " SELECT 
        asignaciones.ID_Area,
        area.Area 
    FROM 
        asignaciones
    JOIN 
        area ON asignaciones.ID_Area = area.ID_Area
    JOIN 
        profesor ON asignaciones.ID_Profesor = profesor.ID_Profesor
    JOIN 
        usuario ON profesor.id_usuario = usuario.id_usuario
    WHERE 
        asignaciones.ID_Profesor = (
            SELECT ID_Profesor FROM profesor WHERE id_usuario = $idUsuario
        );";
                    $areas_result = mysqli_query($conexion, $sql_areas);
                    if ($areas_result) {
                        while ($area = mysqli_fetch_assoc($areas_result)) {
                            echo "<option value='" . $area['ID_Area'] . "'>" . htmlspecialchars($area['Area']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="grado">Grado:</label>
                <select class="form-control" id="grado" name="grado">
                    <option value="">Seleccione un grado</option>
                    <?php
                    // Cargar grados desde la base de datos
                    $sql_grados = " SELECT 
        asignaciones.ID_Grado, 
        grado.nombre_grado
    FROM 
        asignaciones
    JOIN 
        grado ON asignaciones.ID_Grado = grado.ID_Grado
    JOIN 
        area ON asignaciones.ID_Area = area.ID_Area
    JOIN 
        profesor ON asignaciones.ID_Profesor = profesor.ID_Profesor
    JOIN 
        usuario ON profesor.id_usuario = usuario.id_usuario
    WHERE 
        asignaciones.ID_Profesor = (
            SELECT ID_Profesor FROM profesor WHERE id_usuario = $idUsuario
        )";
                    $grados_result = mysqli_query($conexion, $sql_grados);
                    if ($grados_result) {
                        while ($grado = mysqli_fetch_assoc($grados_result)) {
                            echo "<option value='" . $grado['ID_Grado'] . "'>" . htmlspecialchars($grado['nombre_grado']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="bimestre">Bimestre:</label>
                <select class="form-control" id="bimestre" name="bimestre">
                    <option value="">Seleccione un bimestre</option>
                    <?php
                    // Cargar bimestres desde la base de datos
                    if ($bimestres_result) {
                        while ($bimestre = mysqli_fetch_assoc($bimestres_result)) {
                            echo "<option value='" . $bimestre['id_bimestre'] . "'>" . htmlspecialchars($bimestre['nombre_bimestre']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Agregar tarea</button>
        </form>
    </div>
    <!-- Modal para mostrar mensajes -->
    <div id="myModal" class="modal">
        <div class="modal-content" id="modalContent"></div>
    </div>
</body>
</html>
