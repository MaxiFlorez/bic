<?php
session_start();
include("../../assets/includes/auth.php"); 
include("../../assets/includes/db.php");
include("../../assets/includes/header.php");


// Definir variables para evitar errores de undefined
$mensaje = "";
$error = "";
$modo_edicion = false;

// Obtener el ID del usuario logueado
$usuario_id = $_SESSION['user_id'] ?? null;

if (!$usuario_id) {
    die("Acceso denegado.");
}

// Obtener los datos del usuario
$query = "SELECT nombre, apellido, celular, correo, clave FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si se presiona el botón "Editar"
if (isset($_POST["editar_datos"])) {
    $modo_edicion = true;
}

// Si se presiona "Guardar Cambios"
if (isset($_POST["actualizar_datos"])) {
    $modo_edicion = false; // Se desactiva la edición después de guardar

    $nombre = trim($_POST["nombre"] ?? '');
    $apellido = trim($_POST["apellido"] ?? '');
    $celular = trim($_POST["celular"] ?? '');

    if (empty($nombre) || empty($apellido)) {
        $error = "El nombre y apellido son obligatorios.";
        $modo_edicion = true; // Mantener edición si hay error
    } else {
        $query = "UPDATE usuarios SET nombre = ?, apellido = ?, celular = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt->execute([$nombre, $apellido, $celular, $usuario_id])) {
            $mensaje = "Datos actualizados correctamente.";
            $usuario["nombre"] = $nombre;
            $usuario["apellido"] = $apellido;
            $usuario["celular"] = $celular;
        } else {
            $error = "Error al actualizar los datos.";
            $modo_edicion = true; // Mantener edición si hay error
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-section { 
            background-color: #fff; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Perfil de Usuario</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Sección de datos personales -->
        <div class="form-section">
            <h2>Datos Personales</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" <?= $modo_edicion ? '' : 'readonly' ?> required>
                </div>
                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido:</label>
                    <input type="text" name="apellido" id="apellido" class="form-control" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" <?= $modo_edicion ? '' : 'readonly' ?> required>
                </div>
                <div class="mb-3">
                    <label for="celular" class="form-label">Celular:</label>
                    <input type="text" name="celular" id="celular" class="form-control" value="<?= htmlspecialchars($usuario['celular'] ?? '') ?>" <?= $modo_edicion ? '' : 'readonly' ?>>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo:</label>
                    <p class="form-control-plaintext"><?= htmlspecialchars($usuario['correo'] ?? '') ?></p>
                </div>

                <?php if ($modo_edicion): ?>
                    <button type="submit" name="actualizar_datos" class="btn btn-primary w-100">Guardar Cambios</button>
                <?php else: ?>
                    <button type="submit" name="editar_datos" class="btn btn-secondary w-100">Editar Datos</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Sección de cambio de contraseña -->
        <div class="form-section">
            <h2>Cambiar Contraseña</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="contrasena_actual" class="form-label">Contraseña Actual:</label>
                    <input type="password" name="contrasena_actual" id="contrasena_actual" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
                    <input type="password" name="nueva_contrasena" id="nueva_contrasena" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña:</label>
                    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control" required>
                </div>
                <button type="submit" name="cambiar_contrasena" class="btn btn-warning w-100">Cambiar Contraseña</button>
            </form>
        </div>
    </div>
    <?php include '../../assets/includes/footer.php'; ?>
    </body>
</html>
