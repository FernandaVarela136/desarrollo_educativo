<?php
error_reporting(0);
session_start();
$id_rol = $_SESSION['id_rol'];
$usuario = $_SESSION['usuario'];
$id_usuario = $_SESSION['id_usuario'];
if (!isset($_SESSION['usuario'])) {
    echo "<div class='alert alert-success' role='alert'><script>alert(`Primero debes iniciar sesion y validar tus credenciales..');</script></div>";

    echo "<script>location.assign('../index.php');</script>";
    exit();
}
?>