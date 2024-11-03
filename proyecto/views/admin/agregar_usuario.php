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

$queryProfesor = "SELECT id_rol FROM usuario WHERE id_usuario = $idUsuario AND id_rol = 2"; // Rol 2 es Administrador
$resultProfesor = mysqli_query($conexion, $queryProfesor);

if (!$resultProfesor || mysqli_num_rows($resultProfesor) == 0) {
    echo "<h2>Este usuario no es administrador.</h2>";
    echo "<p>Será redirigido a la página anterior en 5 segundos...</p>";
    header("refresh:5;url=/proyecto/views/");
    exit();
}

$sql_roles = "SELECT IDRol, Nombre FROM roles";
$roles_result = mysqli_query($conexion, $sql_roles);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreusu = $_POST['nombreusu'];
    $correo = $_POST['correo'];
    $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    $id_rol = $_POST['rol'];
    $usuario = $_POST['usuario'];
    $estado = isset($_POST['estado']) ? 'activo' : 'inactivo';

    // Insertar el nuevo usuario
    $sqlinsmar = $conexion->prepare("INSERT INTO usuario (Id_usuario, Nombre, Correo, Clave, id_rol, usuario, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $idm = 1; // Inicialización del ID
    $sqlinsmar->bind_param("isssiss", $idm, $nombreusu, $correo, $clave, $id_rol, $usuario, $estado);

    if ($sqlinsmar->execute()) {
        if ($id_rol == 2) { // Si es profesor
            // Obtener áreas y grados seleccionados
            $areas = isset($_POST['areas']) ? $_POST['areas'] : [];
            $grados = isset($_POST['grados']) ? $_POST['grados'] : [];

            // Agregar a la tabla profesor
            $sqlProfesor = $conexion->prepare("INSERT INTO profesor (Id_Profesor, Id_Usuario) VALUES (?, ?)");
            $sqlProfesor->bind_param("ii", $idm, $idm);
            $sqlProfesor->execute();

            // Agregar asignaciones de áreas y grados
            for ($i = 0; $i < count($areas); $i++) {
                $area = $areas[$i];
                $grado = $grados[$i];

                $sqlAsignacion = $conexion->prepare("INSERT INTO Estructuraasignaciones (ID_Area, ID_Grado, ID_Profesor) VALUES (?, ?, ?)");
                $sqlAsignacion->bind_param("iii", $area, $grado, $idm);
                $sqlAsignacion->execute();
            }
        } elseif ($id_rol == 4) { // Si es estudiante (rol ID 4)
            $gradoEstudiante = $_POST['grado_estudiante'];
            $sqlEstudiante = $conexion->prepare("INSERT INTO Estudiante (IDGrado, ID_Estudiante, Id_usuario) VALUES (?, ?, ?)");
            $sqlEstudiante->bind_param("iii", $gradoEstudiante, $idm, $idm);
            $sqlEstudiante->execute();
        }

        header("Location: usuario.php?message=2");
    } else {
        header("Location: usuario.php?message=3");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <style>
        body {
    background-color: #f8f9fa;
}

h2 {
    color: #343a40;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.modal-header {
    background-color: #007bff;
    color: white;
}

.modal-footer .btn {
    background-color: #007bff;
    color: white;
}

    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="text-end mb-3">
            <a href="/proyecto/views/" class="btn btn-secondary">Home</a> <!-- Botón de Home -->
        </div>
        <h2 class="text-center">Añadir Usuario</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="nombreusu" class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombreusu" name="nombreusu" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo:</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Nombre de Usuario:</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option selected disabled value="">Seleccione un rol</option>
                    <?php
                    if ($roles_result) {
                        while ($role = mysqli_fetch_assoc($roles_result)) {
                            echo "<option value='" . $role['IDRol'] . "'>" . htmlspecialchars($role['Nombre']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Error al cargar roles</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" id="estado" name="estado" value="activo" required>
                <label class="form-check-label" for="estado">Activo</label>
            </div>

            <div id="asignacionesContainer" style="display: none;">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#asignacionModal">Agregar Asignaciones</button>
                </div>
            </div>

            <div id="gradoContainer" style="display: none;">
                <div class="mb-3">
                    <label for="grado_estudiante" class="form-label">Grado del Estudiante:</label>
                    <select class="form-select" id="grado_estudiante" name="grado_estudiante">
                        <?php
                        $sql_grados = "SELECT ID_Grado, nombre_grado FROM grado";
                        $grados_result = mysqli_query($conexion, $sql_grados);
                        if ($grados_result) {
                            while ($grado = mysqli_fetch_assoc($grados_result)) {
                                echo "<option value='" . $grado['ID_Grado'] . "'>" . htmlspecialchars($grado['nombre_grado']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 text-center">
                <button class="btn btn-primary" type="submit">Agregar Usuario</button>
            </div>
        </form>
    </div>

    <!-- Modal para agregar asignaciones -->
    <div class="modal fade" id="asignacionModal" tabindex="-1" aria-labelledby="asignacionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="asignacionModalLabel">Agregar Asignaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="asignacionesList">
                        <div class="mb-3 asignacion">
                            <label for="areas" class="form-label">Área:</label>
                            <select class="form-select area" name="areas[]" required>
                                <?php
                                $sql_areas = "SELECT ID_Area, Area FROM area";
                                $areas_result = mysqli_query($conexion, $sql_areas);
                                if ($areas_result) {
                                    while ($area = mysqli_fetch_assoc($areas_result)) {
                                        echo "<option value='" . $area['ID_Area'] . "'>" . htmlspecialchars($area['Area']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <label for="grados" class="form-label">Grado:</label>
                            <select class="form-select grado" name="grados[]" required>
                                <?php
                                $sql_grados = "SELECT ID_Grado, nombre_grado FROM grado";
                                $grados_result = mysqli_query($conexion, $sql_grados);
                                if ($grados_result) {
                                    while ($grado = mysqli_fetch_assoc($grados_result)) {
                                        echo "<option value='" . $grado['ID_Grado'] . "'>" . htmlspecialchars($grado['nombre_grado']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addAsignacion">Agregar Otra Asignación</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const rolSelect = document.getElementById('rol');
        const asignacionesContainer = document.getElementById('asignacionesContainer');
        const gradoContainer = document.getElementById('gradoContainer');

        rolSelect.addEventListener('change', function() {
            if (this.value == 3) { // Profesor
                asignacionesContainer.style.display = 'block';
                gradoContainer.style.display = 'none';
            } else if (this.value == 1) { // Estudiante
                asignacionesContainer.style.display = 'none';
                gradoContainer.style.display = 'block';
            } else {
                asignacionesContainer.style.display = 'none';
                gradoContainer.style.display = 'none';
            }
        });

        document.getElementById('addAsignacion').addEventListener('click', function() {
            const asignacionesList = document.getElementById('asignacionesList');
            const newAsignacion = document.createElement('div');
            newAsignacion.classList.add('mb-3', 'asignacion');
            newAsignacion.innerHTML = `
                <label for="areas" class="form-label">Área:</label>
                <select class="form-select area" name="areas[]" required>
                    <?php
                    $sql_areas = "SELECT ID_Area, Area FROM area";
                    $areas_result = mysqli_query($conexion, $sql_areas);
                    if ($areas_result) {
                        while ($area = mysqli_fetch_assoc($areas_result)) {
                            echo "<option value='" . $area['ID_Area'] . "'>" . htmlspecialchars($area['Area']) . "</option>";
                        }
                    }
                    ?>
                </select>
                <label for="grados" class="form-label">Grado:</label>
                <select class="form-select grado" name="grados[]" required>
                    <?php
                    $sql_grados = "SELECT ID_Grado, nombre_grado FROM grado";
                    $grados_result = mysqli_query($conexion, $sql_grados);
                    if ($grados_result) {
                        while ($grado = mysqli_fetch_assoc($grados_result)) {
                            echo "<option value='" . $grado['ID_Grado'] . "'>" . htmlspecialchars($grado['nombre_grado']) . "</option>";
                        }
                    }
                    ?>
                </select>
            `;
            asignacionesList.appendChild(newAsignacion);
        });
    </script>
</body>
</html>
