<?php
include("../../assets/includes/auth.php");
redirectIfNotAdmin();
include("../../assets/includes/header.php");
include("../../assets/includes/db.php");

$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $dominio = trim($_POST['dominio']);
    $color = trim($_POST['color']);
    $titular_nombre = trim($_POST['titular_nombre']);
    $titular_apellido = trim($_POST['titular_apellido']);
    $titular_documento = trim($_POST['titular_documento']);
    $foto_vehiculo = $_FILES['foto_vehiculo'];
    $pdf_secuestro = $_FILES['pdf_secuestro'];
    $estado = trim($_POST['estado']);

    // Validar campos obligatorios
    if (empty($marca) || empty($modelo) || empty($dominio) || empty($titular_nombre) || empty($titular_apellido) || empty($titular_documento) || empty($pdf_secuestro['name'])) {
        $error = 'Todos los campos obligatorios deben completarse.';
    } else {
        // Verificar si el titular existe en la tabla personas
        $stmt_persona = $conn->prepare("SELECT id FROM personas WHERE documento = ?");
        $stmt_persona->execute([$titular_documento]);
        $persona = $stmt_persona->fetch(PDO::FETCH_ASSOC);
        
        if (!$persona) {
            // Insertar nueva persona en la tabla personas
            $query_insert_persona = "INSERT INTO personas (nombre, apellido, documento) VALUES (:nombre, :apellido, :documento)";
            $stmt_insert_persona = $conn->prepare($query_insert_persona);
            $stmt_insert_persona->execute([
                'nombre' => $titular_nombre,
                'apellido' => $titular_apellido,
                'documento' => $titular_documento
            ]);
            $titular_id = $conn->lastInsertId();
        } else {
            $titular_id = $persona['id'];
        }

        // Validar y mover archivos
        $directorio_fotos = 'C:/xampp/htdocs/bic/documentos/pdf_vehiculos/';
        $directorio_pdfs = 'C:/xampp/htdocs/bic/documentos/pdf_vehiculos/';
        if (!is_dir($directorio_fotos)) {
            mkdir($directorio_fotos, 0777, true);
        }
        if (!is_dir($directorio_pdfs)) {
            mkdir($directorio_pdfs, 0777, true);
        }

        // Mover foto del vehículo
        $ruta_foto = null;
        if (!empty($foto_vehiculo['name'])) {
            $extension_foto = pathinfo($foto_vehiculo['name'], PATHINFO_EXTENSION);
            $nombre_foto = md5($dominio . time()) . '.' . $extension_foto;
            $ruta_foto = $directorio_fotos . $nombre_foto;
            move_uploaded_file($foto_vehiculo['tmp_name'], $ruta_foto);
        }

        // Mover PDF de secuestro
        $extension_pdf = pathinfo($pdf_secuestro['name'], PATHINFO_EXTENSION);
        $nombre_pdf = md5($dominio . time()) . '.' . $extension_pdf;
        $ruta_pdf = $directorio_pdfs . $nombre_pdf;
        move_uploaded_file($pdf_secuestro['tmp_name'], $ruta_pdf);

        try {
            $query_vehiculo = "INSERT INTO vehiculos_pedidos (marca, modelo, dominio, color, titular_id, foto_vehiculo, pdf_secuestro, estado) 
                               VALUES (:marca, :modelo, :dominio, :color, :titular_id, :foto_vehiculo, :pdf_secuestro, :estado)";
            $stmt_vehiculo = $conn->prepare($query_vehiculo);
            $stmt_vehiculo->execute([
                'marca' => $marca,
                'modelo' => $modelo,
                'dominio' => $dominio,
                'color' => $color,
                'titular_id' => $titular_id,
                'foto_vehiculo' => $ruta_foto,
                'pdf_secuestro' => $ruta_pdf,
                'estado' => $estado
            ]);
            $success = 'Vehículo agregado correctamente.';
        } catch (PDOException $e) {
            $error = 'Error al procesar: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Vehículo - BIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="main-container">
            <a href="listar_vehiculos.php" class="btn btn-secondary mb-3 float-end">⬅ Regresar</a>
            <h1 class="text-center text-primary mb-4">Agregar Vehículo</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-control" id="marca" required>
                </div>
                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control" id="modelo" required>
                </div>
                <div class="mb-3">
                    <label for="dominio" class="form-label">Dominio</label>
                    <input type="text" name="dominio" class="form-control" id="dominio" required>
                </div>
                <div class="mb-3">
                    <label for="color" class="form-label">Color</label>
                    <input type="text" name="color" class="form-control" id="color">
                </div>
                <div class="mb-3">
                    <label for="titular_nombre" class="form-label">Nombre del Titular</label>
                    <input type="text" name="titular_nombre" class="form-control" id="titular_nombre" required>
                </div>
                <div class="mb-3">
                    <label for="titular_apellido" class="form-label">Apellido del Titular</label>
                    <input type="text" name="titular_apellido" class="form-control" id="titular_apellido" required>
                </div>
                <div class="mb-3">
                    <label for="titular_documento" class="form-label">Documento del Titular</label>
                    <input type="text" name="titular_documento" class="form-control" id="titular_documento" required>
                </div>
                <div class="mb-3">
                    <label for="foto_vehiculo" class="form-label">Foto del Vehículo</label>
                    <input type="file" name="foto_vehiculo" class="form-control" id="foto_vehiculo">
                </div>
                <div class="mb-3">
                    <label for="pdf_secuestro" class="form-label">PDF del Secuestro</label>
                    <input type="file" name="pdf_secuestro" class="form-control" id="pdf_secuestro" required>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" class="form-control" id="estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Agregar Vehículo</button>
            </form>
        </div>
    </div>
    <?php include '../../assets/includes/footer.php'; ?>
    <script src="../../assets/js/scripts.js"></script>
</body>
</html>