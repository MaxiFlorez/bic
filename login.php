<?php
// Incluir archivos 
include 'includes/db.php';  // Conexión a la base de datos
include 'includes/auth.php'; // Funciones de autenticación

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
include 'includes/header.php';
?>

<!-- Contenedor centrado para el formulario de inicio de sesión -->
<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="login-container col-md-6 col-lg-4">
        <h3 class="text-center text-primary mb-4">Bienvenido! Ingrese sus credenciales para continuar</h3>
        
        <!-- Mostrar mensaje de error si las credenciales son incorrectas -->
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulario de inicio de sesión -->
        <form method="POST">
            <div class="mb-3">
                <label for="documento" class="form-label">Documento</label>
                <input type="text" class="form-control" id="documento" name="documento" placeholder="Ingresa tu documento" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Clave</label>
                <input type="password" class="form-control" id="clave" name="clave" placeholder="Ingresa tu clave" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
        </form>
    </div>
</div>

<!-- Incluir el pie de página -->
<?php include 'includes/footer.php'; ?>
