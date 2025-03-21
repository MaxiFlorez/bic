<?php
include '../../assets/includes/auth.php';
redirectIfNotLoggedIn();
include '../../assets/includes/header.php';
include '../../assets/includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: listar_vehiculos.php");
    exit;
}

$error = '';
$success = '';

// Obtener los datos del vehículo
$stmt = $conn->prepare("SELECT vp.*, p.nombre AS titular_nombre, p.apellido AS titular_apellido, p.documento AS titular_documento FROM vehiculos_pedidos vp JOIN personas p ON vp.titular_id = p.id WHERE vp.id = ?");
$stmt->execute([$id]);
$vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehiculo) {
    header("Location: listar_vehiculos.php");
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    $dominio = trim($_POST['dominio']);
    $color = trim($_POST['color']);
    $titular_nombre = trim($_POST['titular_nombre']);
    $titular_apellido = trim($_POST['titular_apellido']);
    $titular_documento = trim($_POST['titular_documento']);
    $estado = trim($_POST['estado']);

    // Validar campos obligatorios
    if (empty($marca) || empty($modelo) || empty($dominio) || empty($titular_nombre) || empty($titular_apellido) || empty($titular_documento)) {
        $error = 'Todos los campos obligatorios deben completarse.';
    } else {
        try {
            // Actualizar los datos del vehículo
            $query_vehiculo = "UPDATE vehiculos_pedidos 
                               SET marca = :marca, modelo = :modelo, dominio = :dominio, color = :color, estado = :estado 
                               WHERE id = :id";
            $stmt_vehiculo = $conn->prepare($query_vehiculo);
            $stmt_vehiculo->execute([
                'marca' => $marca,
                'modelo' => $modelo,
                'dominio' => $dominio,
                'color' => $color,
                'estado' => $estado,
                'id' => $id
            ]);

            // Actualizar los datos del titular si han cambiado
            if ($titular_documento != $vehiculo['titular_documento']) {
                $query_persona = "UPDATE personas SET nombre = :nombre, apellido = :apellido, documento = :documento WHERE id = :id";
                $stmt_persona = $conn->prepare($query_persona);
                $stmt_persona->execute([
                    'nombre' => $titular_nombre,
                    'apellido' => $titular_apellido,
                    'documento' => $titular_documento,
                    'id' => $vehiculo['titular_id']
                ]);
            }

            $success = 'Vehículo actualizado correctamente.';
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
    <title>Detalle Vehículo - BIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-mode { display: none; }
        .read-mode { display: block; }
        .editing .edit-mode { display: block !important; }
        .editing .read-mode { display: none !important; }
        input[readonly], .read-mode { background: transparent !important; border: none; }
        .is-invalid { border-color: #dc3545 !important; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <a href="listar_vehiculos.php" class="btn btn-secondary mb-3">⬅ Regresar</a>
            <h1 class="text-center text-primary mb-4">Detalle Vehículo</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" id="form-edicion" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Datos del Vehículo</h5>
                        <div>
                            <button type="button" id="btn-editar" class="btn btn-warning" onclick="toggleEdit()">Editar</button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                                Eliminar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <!-- Modo lectura: imagen clickeable para ampliar -->
                                <a href="#" data-bs-toggle="modal" data-bs-target="#fotoModal">
                                    <img src="../../<?= htmlspecialchars($vehiculo['foto_vehiculo']) ?>" 
                                         class="img-fluid mb-3 read-mode" 
                                         id="preview-foto" 
                                         alt="Foto de <?= htmlspecialchars($vehiculo['dominio']) ?>"
                                         data-original-src="../../<?= htmlspecialchars($vehiculo['foto_vehiculo']) ?>">
                                </a>
                                <!-- Modo edición: input para cambiar foto -->
                                <div class="edit-mode">
                                    <label for="foto">Foto:</label>
                                    <input type="file" id="foto" name="foto" 
                                           class="form-control" 
                                           accept="image/jpeg, image/png"
                                           onchange="previewImage(this)">
                                    <small class="text-muted">Formatos permitidos: JPG, PNG (Máx. 2MB)</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="marca" class="form-label">Marca</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['marca']) ?></span>
                                    <input type="text" name="marca" class="form-control edit-mode" id="marca" value="<?= htmlspecialchars($vehiculo['marca']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="modelo" class="form-label">Modelo</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['modelo']) ?></span>
                                    <input type="text" name="modelo" class="form-control edit-mode" id="modelo" value="<?= htmlspecialchars($vehiculo['modelo']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dominio" class="form-label">Dominio</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['dominio']) ?></span>
                                    <input type="text" name="dominio" class="form-control edit-mode" id="dominio" value="<?= htmlspecialchars($vehiculo['dominio']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['color']) ?></span>
                                    <input type="text" name="color" class="form-control edit-mode" id="color" value="<?= htmlspecialchars($vehiculo['color']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="titular_nombre" class="form-label">Nombre del Titular</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['titular_nombre']) ?></span>
                                    <input type="text" name="titular_nombre" class="form-control edit-mode" id="titular_nombre" value="<?= htmlspecialchars($vehiculo['titular_nombre']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="titular_apellido" class="form-label">Apellido del Titular</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['titular_apellido']) ?></span>
                                    <input type="text" name="titular_apellido" class="form-control edit-mode" id="titular_apellido" value="<?= htmlspecialchars($vehiculo['titular_apellido']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="titular_documento" class="form-label">Documento del Titular</label>
                                    <span class="read-mode"><?= htmlspecialchars($vehiculo['titular_documento']) ?></span>
                                    <input type="text" name="titular_documento" class="form-control edit-mode" id="titular_documento" value="<?= htmlspecialchars($vehiculo['titular_documento']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <span class="read-mode"><?= ucfirst($vehiculo['estado']) ?></span>
                                    <select name="estado" class="form-control edit-mode" id="estado">
                                        <option value="activo" <?= ($vehiculo['estado'] == 'activo') ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= ($vehiculo['estado'] == 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                                <div class="edit-mode text-end mt-4">
                                    <button type="button" class="btn btn-secondary" onclick="toggleEdit()">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de eliminar este vehículo definitivamente?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="eliminar_vehiculo.php">
                        <input type="hidden" name="id" value="<?= $vehiculo['id'] ?>">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ampliar la foto -->
    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoModalLabel">Foto de <?= htmlspecialchars($vehiculo['dominio']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="../../<?= htmlspecialchars($vehiculo['foto_vehiculo']) ?>" alt="Foto de <?= htmlspecialchars($vehiculo['dominio']) ?>" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../assets/includes/footer.php'; ?>
</body>
</html>