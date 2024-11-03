<?php
session_start (); 
include "../db.php";
 $usuario = $_POST['usuario']; 
 $clave = $_POST['clave'];

$stmt = $conexion->prepare("SELECT 
    usuario.id_usuario, 
    usuario.usuario, 
    usuario.clave, 
    usuario.id_rol, 
    usuario.nombre, 
    g.nivel 
FROM 
    usuario 
LEFT JOIN 
    estudiante e ON e.Id_usuario = usuario.Id_usuario 
LEFT JOIN 
    grado g ON g.ID_Grado = e.IDGrado 
WHERE 
    usuario.usuario = ?");
$stmt->bind_param("s", $usuario); 
$stmt->execute(); 
$result = $stmt->get_result();

if ($result->num_rows > 0) {
     $row = $result->fetch_assoc(); 
     $hash_clave = $row['clave'];
     if ($clave==$hash_clave) {
        $_SESSION ['usuario'] = $usuario; 
        $_SESSION['id_rol'] = $row['id_rol'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['nivel'] = $row['nivel'];
        header ("Location: ../../views/index.php");
        exit();
        
     } else {
        header ("Location: ../../index.php?error=1");
        exit();
     }
}else {
    header("Location:../../index.php?error=1");
    exit();
}?>