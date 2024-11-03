<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

// Verificar si el usuario está autenticado y si el ID de usuario está presente
if (!isset($_SESSION['usuario']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: usuario.php");
    exit();
}

$id_usuario = $_GET['id'];

// Consultar la información del usuario
$sql = "SELECT * FROM usuario WHERE Id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "No se encontró el usuario.";
    exit();
}

// Obtener la lista de roles
$sql_roles = "SELECT IDRol, Nombre FROM roles";
$roles_result = mysqli_query($conexion, $sql_roles);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y actualizar los datos del usuario
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $estado = isset($_POST['estado']) && $_POST['estado'] == 'on' ? 'activo' : 'inactivo';

    $sql_update = "UPDATE usuario SET Nombre = ?, Correo = ?, id_rol = ?, estado = ? WHERE Id_usuario = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("ssisi", $nombre, $correo, $rol, $estado, $id_usuario);

    if ($stmt_update->execute()) {
        header("Location: editar.php?id=$id_usuario&message=2");
        exit();
    } else {
        header("Location: editar.php?id=$id_usuario&message=3");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2 {
            color: #343a40;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
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
    </style>
    <script>
        function toggleEstadoText() {
            var checkbox = document.getElementById('estado');
            var label = document.getElementById('estadoLabel');
            label.textContent = checkbox.checked ? 'Activo' : 'Inactivo';
        }

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
                showModal('Usuario actualizado con éxito');
            } else if (message === '3') {
                showModal('Error al actualizar el usuario');
            }
        };
    </script>
</head>
<body>
    <div class="container mt-4">
        <div class="text-end mb-3">
            <a href="/proyecto/views/" class="btn btn-secondary">Home</a>
        </div>
        <h2>Editar Usuario</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo:</label>
                <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($user['Correo']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select class="form-select" id="rol" name="rol" required>
                    <?php
                        while ($role = mysqli_fetch_assoc($roles_result)) {
                            echo "<option value='" . $role['IDRol'] . "'" . ($role['IDRol'] == $user['id_rol'] ? ' selected' : '') . ">" . htmlspecialchars($role['Nombre']) . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="estado" name="estado" onclick="toggleEstadoText()" <?php echo $user['estado'] == 'activo' ? 'checked' : ''; ?>>
                <label class="form-check-label" id="estadoLabel" for="estado"><?php echo $user['estado'] == 'activo' ? 'Activo' : 'Inactivo'; ?></label>
            </div>
            <div class="mb-3 text-center">
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span id="modalContent"></span>
        </div>
    </div>
</body>
</html>
