<?php
include 'assets/includes/db.php';  // Conexión a la base de datos
include 'assets/includes/auth.php'; // Funciones de autenticación

$error = '';

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['documento']; // Obtener el documento ingresado por el usuario
    $clave = $_POST['clave']; // Obtener la clave ingresada por el usuario

    // Validar formato del documento (Ej: DNI debe tener exactamente 8 dígitos)
    if (!preg_match('/^\d{8}$/', $documento)) {
        $error = "Documento inválido. Debe tener 8 dígitos.";
    } else {
        // Intentar autenticar al usuario
        if (authenticateUser($documento, $clave)) {
            // Si la autenticación es exitosa, redirigir al dashboard
            header('Location: dashboard.php');
            exit(); // Terminar la ejecución del script
        } else {
            // Si las credenciales son incorrectas, mostrar un mensaje de error
            $error = "Credenciales incorrectas.";
        }
    }
}

// Incluir el encabezado HTML
include 'assets/includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión de Seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-container { max-width: 400px; margin: 100px auto; background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center text-primary">Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="documento" class="form-label">Documento</label>
                <input type="text" name="documento" class="form-control" id="documento" placeholder="Ingresa tu documento" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Clave</label>
                <input type="password" name="clave" class="form-control" id="clave" placeholder="Ingresa tu clave" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
        </form>
    </div>
    <?php include 'assets/includes/footer.php'; ?>
</body>
</html>