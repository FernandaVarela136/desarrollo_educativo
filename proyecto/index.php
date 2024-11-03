<!DOCTYPE html>
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Login</title> 
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>  

<style>
    body {
        background: #E2EAF4; /* Fondo claro */
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        font-family: 'Arial', sans-serif;
    }
    
    .login {
        padding: 40px;
        background: rgba(255, 255, 255, 0.85); /* Fondo blanco con opacidad */
        backdrop-filter: blur(15px);
        border-radius: 20px;
        width: 400px;
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
    }
    
    .login h2 {
        text-align: center;
        font-size: 2.5em;
        font-weight: 600;
        color: #0056b3; /* Azul */
        margin-bottom: 20px;
    }
    
    .login .inputBox {
        margin-bottom: 20px;
    }
    
    .login .inputBox input {
        width: 100%;
        padding: 10px;
        outline: none;
        font-size: 1.1em;
        color: #333;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    
    .login .inputBox input::placeholder {
        color: #aaa;
    }
    
    .login button {
        width: 100%;
        padding: 10px;
        background: #dc3545; /* Rojo */
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1.2em;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .login button:hover {
        background: #c82333; /* Rojo más oscuro al pasar el mouse */
    }
    
    .alert-danger {
        text-align: center;
        color: red;
    }
</style>

<body>  
    <div class="login">    
        <h2>LOGIN</h2>  
        <p class="text-center">Ingrese su usuario y contraseña</p>  
        
        <?php
        if (isset($_GET['error']) && $_GET['error'] == 1) {
            echo "<div class='alert alert-danger' role='alert'>Usuario y/o contraseña incorrectos...</div>";
        }
        ?>
        
        <form action="./includes/sesion/validarSesion.php" method="post">  
            <div class="inputBox">  
                <label for="usuario" class="form-label">Usuario:</label>  
                <input type="text" placeholder="Usuario" id="usuario" name="usuario" required>  
            </div>  
            <div class="inputBox">  
                <label for="clave" class="form-label">Contraseña:</label>  
                <input type="password" placeholder="Contraseña" id="clave" name="clave" required>  
            </div>  
            <div class="inputBox">
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </form>  
    </div>  
</body>
</html>
