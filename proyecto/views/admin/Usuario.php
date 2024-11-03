<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/proyecto/includes/configSesion.php';

$SQL = "SELECT usuario.Id_usuario, usuario.Nombre, usuario.Correo, usuario.usuario, usuario.estado, roles.Nombre as roles 
        FROM usuario 
        LEFT JOIN roles ON usuario.id_rol = roles.IDRol";

$resultado = mysqli_query($conexion, $SQL);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function downloadPDF() {
            let searchTerm = document.getElementById('search-input').value.trim();
            
            if (searchTerm === '') {
                alert('Por favor, ingrese un término de búsqueda antes de descargar el PDF.');
                return; // Evita la redirección si el campo está vacío
            }
            
            window.location.href = `/proyecto/includes/exportar_pdf.php?report=usuarios&search=${encodeURIComponent(searchTerm)}`;
        }
    </script>
</head>

<body>
<nav class="navbar bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="../">Home</a>
    <form class="d-flex ms-auto" id="search-form">
      <input class="form-control me-2" id="search-input" type="search" placeholder="Buscar usuario" aria-label="Search">
    </form>
  </div>
</nav>

<div class="container-fluid mt-4">
    <table class="table table-bordered" id="users">
        <thead>
            <tr>
                <th>Código Usuario</th>
                <th>Nombre</th>
                <th>Correo Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Editar</th>
                <th>Cambiar Contraseña</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($resultado) > 0) {
                while ($user = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . $user['Id_usuario'] . "</td>";
                    echo "<td>" . $user['Nombre'] . "</td>";
                    echo "<td>" . $user['Correo'] . "</td>";
                    echo "<td>" . $user['roles'] . "</td>";
                    echo "<td>" . $user['estado'] . "</td>";
                    echo "<td><a href='editar.php?id=" . $user['Id_usuario'] . "' class='btn btn-secondary'>Editar</a></td>";
                    echo "<td><a href='cambiar_clave.php?id=" . $user['Id_usuario'] . "' class='btn btn-outline-primary'>Cambiar Contraseña</a></td>"; 
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No existen registros</td></tr>";
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
