<?php
include '../../assets/includes/auth.php'; // Ruta corregida para incluir auth.php
redirectIfNotAdmin(); // Solo administradores pueden agregar detenidos
include '../../assets/includes/header.php'; // Ruta corregida para incluir header.php
include '../../assets/includes/db.php'; // Ruta corregida para incluir db.php

$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario y sanitizar
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $documento = trim($_POST['documento']);
    $edad = isset($_POST['edad']) ? filter_var($_POST['edad'], FILTER_VALIDATE_INT) : null;
    $sexo = $_POST['sexo'];
    $motivo_detencion = trim($_POST['motivo_detencion']);
    $fecha_detencion = $_POST['fecha_detencion'];

    // Datos de domicilio
    $calle = trim($_POST['calle']);
    $numeracion = trim($_POST['numeracion']);
    $barrio_villa = trim($_POST['barrio_villa']);
    $mzna = trim($_POST['mzna']);
    $casa = trim($_POST['casa']);
    $departamento = trim($_POST['departamento']);
    $provincia = trim($_POST['provincia']);

    // Usar isset() para evitar el error de clave no definida
    $legajo = isset($_POST['legajo']) ? trim($_POST['legajo']) : null; 
    $unidad_fiscal = isset($_POST['unidad_fiscal']) ? trim($_POST['unidad_fiscal']) : null;
    $foto = $_FILES['foto'];

    // Validar campos obligatorios
    if (empty($nombre) || empty($apellido) || empty($documento) || empty($motivo_detencion) || empty($fecha_detencion) || empty($foto['name'])) {
        $error = 'Todos los campos obligatorios deben completarse.';
    } elseif ($edad === false || $edad < 0) {
        $error = 'Edad inválida.';
    } else {
        // VALIDACIONES ADICIONALES
        // Validación adicional del documento (8 dígitos)
        if (!preg_match('/^\d{8}$/', $documento)) {
            $error = 'El documento debe tener 8 dígitos numéricos';
        }
        // Validación del tipo de archivo de imagen
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($foto['type'], $allowed_types)) {
            $error = 'Solo se permiten imágenes JPEG o PNG';
        }
        // Sanitización de motivo_detencion
        $motivo_detencion = filter_var($motivo_detencion, FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($error)) {
            // Manejo de imágenes
            $directorio_imagenes = '../../assets/imagenes/personas/';  // Ubicación donde se guardarán las fotos
            if (!is_dir($directorio_imagenes)) {
                mkdir($directorio_imagenes, 0777, true);
            }

            // Eliminar caracteres especiales del documento para formar el nombre de la imagen
            $nombre_limpio = preg_replace("/[^a-zA-Z0-9]/", "", $documento);

            // Usar el documento limpio como nombre único para la imagen
            $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $nombre_imagen = $nombre_limpio . '.' . $extension;
            $ruta_imagen = $directorio_imagenes . $nombre_imagen;

            // Verificar si la imagen ya existe
            if (file_exists($ruta_imagen)) {
                throw new Exception("¡Imagen ya existe!");
            }

            // Intentar subir la imagen al servidor
            if (move_uploaded_file($foto['tmp_name'], $ruta_imagen)) {
                // Iniciar transacción
                $conn->beginTransaction();

                try {
                    // 1. Insertar persona
                    $query_persona = "INSERT INTO personas (nombre, apellido, documento, edad, sexo, foto) VALUES (:nombre, :apellido, :documento, :edad, :sexo, :foto)";
                    $stmt_persona = $conn->prepare($query_persona);
                    $stmt_persona->execute([
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'documento' => $documento,
                        'edad' => $edad,
                        'sexo' => $sexo,
                        'foto' => $ruta_imagen  // Se almacena la ruta completa
                    ]);
                    $persona_id = $conn->lastInsertId();

                    // 2. Insertar domicilio
                    $query_domicilio = "INSERT INTO domicilios (persona_id, calle, numeracion, barrio_villa, mzna, casa, departamento, provincia) 
                                        VALUES (:persona_id, :calle, :numeracion, :barrio_villa, :mzna, :casa, :departamento, :provincia)";
                    $stmt_domicilio = $conn->prepare($query_domicilio);
                    $stmt_domicilio->execute([
                        'persona_id' => $persona_id,
                        'calle' => $calle,
                        'numeracion' => $numeracion,
                        'barrio_villa' => $barrio_villa,
                        'mzna' => $mzna,
                        'casa' => $casa,
                        'departamento' => $departamento,
                        'provincia' => $provincia
                    ]);

                    // 3. Insertar detenido
                    $query_detenido = "INSERT INTO detenidos (persona_id, motivo_detencion, legajo, unidad_fiscal, fecha_detencion) 
                                       VALUES (:persona_id, :motivo_detencion, :legajo, :unidad_fiscal, :fecha_detencion)";
                    $stmt_detenido = $conn->prepare($query_detenido);
                    $stmt_detenido->execute([
                        'persona_id' => $persona_id,
                        'motivo_detencion' => $motivo_detencion,
                        'legajo' => $legajo, // Puede ser nulo si no se completó
                        'unidad_fiscal' => $unidad_fiscal, // Puede ser nulo si no se completó
                        'fecha_detencion' => $fecha_detencion
                    ]);

                    // Confirmar transacción
                    $conn->commit();
                    $success = 'Detenido agregado correctamente.';
                } catch (PDOException $e) {
                    // Revertir TODOS los cambios en caso de error
                    $conn->rollBack();
                    $error = 'Error al procesar: ' . $e->getMessage();
                }
            } else {
                $error = 'Error al subir la imagen.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Detenido - BIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="main-container">
            <a href="listar.php" class="btn btn-secondary mb-3 float-end">⬅ Regresar</a>
            <h1 class="text-center text-primary mb-4">Agregar Detenido</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" id="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" id="apellido" required>
                </div>
                <div class="mb-3">
                    <label for="documento" class="form-label">Documento</label>
                    <input type="text" name="documento" class="form-control" id="documento" required>
                </div>
                <div class="mb-3">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="number" name="edad" class="form-control" id="edad" required>
                </div>
                <div class="mb-3">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select name="sexo" class="form-control" id="sexo">
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="motivo_detencion" class="form-label">Motivo de Detención</label>
                    <textarea name="motivo_detencion" class="form-control" id="motivo_detencion" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="fecha_detencion" class="form-label">Fecha de Detención</label>
                    <input type="date" name="fecha_detencion" class="form-control" id="fecha_detencion" required>
                </div>
                <div class="mb-3">
                    <label for="legajo" class="form-label">Legajo</label>
                    <input type="text" name="legajo" class="form-control" id="legajo">
                </div>
                <div class="mb-3">
                    <label for="unidad_fiscal" class="form-label">Unidad Fiscal</label>
                    <input type="text" name="unidad_fiscal" class="form-control" id="unidad_fiscal">
                </div>
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto</label>
                    <input type="file" name="foto" class="form-control" id="foto" required>
                </div>
                <!-- Información del domicilio -->
                <div class="mb-3">
                    <label for="calle" class="form-label">Calle</label>
                    <input type="text" name="calle" class="form-control" id="calle">
                </div>
                <div class="mb-3">
                    <label for="numeracion" class="form-label">Numeración</label>
                    <input type="text" name="numeracion" class="form-control" id="numeracion">
                </div>
                <div class="mb-3">
                    <label for="barrio_villa" class="form-label">Barrio/Villa</label>
                    <input type="text" name="barrio_villa" class="form-control" id="barrio_villa">
                </div>
                <div class="mb-3">
                    <label for="mzna" class="form-label">Manzana</label>
                    <input type="text" name="mzna" class="form-control" id="mzna">
                </div>
                <div class="mb-3">
                    <label for="casa" class="form-label">Casa</label>
                    <input type="text" name="casa" class="form-control" id="casa">
                </div>
                <div class="mb-3">
                    <label for="departamento" class="form-label">Departamento</label>
                    <input type="text" name="departamento" class="form-control" id="departamento">
                </div>
                <div class="mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <input type="text" name="provincia" class="form-control" id="provincia">
                </div>
                <button type="submit" class="btn btn-primary">Agregar Detenido</button>
            </form>
        </div>
    </div>
    <?php include '../../assets/includes/footer.php'; // Incluir el footer ?>
</body>
</html>
