<?php
include '../../assets/includes/auth.php'; // Ruta corregida para incluir auth.php
redirectIfNotAdmin(); // Solo administradores pueden acceder a esta página
include '../../assets/includes/db.php'; // Ruta corregida para incluir db.php

redirectIfNotLoggedIn();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Token CSRF inválido");
    }

    if (isset($_POST['eliminar'])) {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("SELECT foto FROM personas WHERE id = ?");
            $stmt->execute([$id]);
            $foto = $stmt->fetchColumn();

            $conn->prepare("DELETE FROM detenidos WHERE persona_id = ?")->execute([$id]);
            $conn->prepare("DELETE FROM personas WHERE id = ?")->execute([$id]);

            if ($foto && file_exists('../../' . $foto)) {
                unlink('../../' . $foto);
            }

            $conn->commit();
            $_SESSION['success'] = 'Registro eliminado exitosamente';
            header('Location: listar.php');
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Error al eliminar: ' . $e->getMessage();
        }
    } else {
        try {
            $conn->beginTransaction();

            // Manejo de la imagen
            $nueva_imagen = null;
            if (!empty($_FILES['foto']['name'])) {
                // Ajusta la ruta para que guarde en la carpeta centralizada
                $directorio = '../../assets/imagenes/personas/';
                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nombre_imagen = md5($_POST['documento'] . time()) . '.' . $extension;
                $ruta_imagen = $directorio . $nombre_imagen;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_imagen)) {
                    $nueva_imagen = 'assets/imagenes/personas/' . $nombre_imagen;
                    
                    // Eliminar imagen anterior
                    $stmt_foto = $conn->prepare("SELECT foto FROM personas WHERE id = ?");
                    $stmt_foto->execute([$id]);
                    $foto_anterior = $stmt_foto->fetchColumn();
                    
                    if ($foto_anterior && file_exists('../../' . $foto_anterior)) {
                        unlink('../../' . $foto_anterior);
                    }
                }
            }

            // Actualizar persona
            $query_persona = "UPDATE personas SET 
                nombre = ?, apellido = ?, documento = ?, edad = ?, sexo = ?" 
                . ($nueva_imagen ? ", foto = ?" : "") . " WHERE id = ?";
            
            $params_persona = [
                $_POST['nombre'],
                $_POST['apellido'],
                $_POST['documento'],
                $_POST['edad'],
                $_POST['sexo']
            ];
            
            if ($nueva_imagen) {
                $params_persona[] = $nueva_imagen;
            }
            
            $params_persona[] = $id;
            
            $stmt_persona = $conn->prepare($query_persona);
            $stmt_persona->execute($params_persona);

            // Actualizar domicilio
            $stmt_domicilio = $conn->prepare("
                UPDATE domicilios SET
                    calle = ?, numeracion = ?, barrio_villa = ?, mzna = ?, casa = ?, 
                    departamento = ?, provincia = ?
                WHERE persona_id = ?
            ");
            $stmt_domicilio->execute([
                $_POST['calle'],
                $_POST['numeracion'],
                $_POST['barrio_villa'],
                $_POST['mzna'],
                $_POST['casa'],
                $_POST['departamento'],
                $_POST['provincia'],
                $id
            ]);

            // Actualizar detenido
            $stmt_detenido = $conn->prepare("
                UPDATE detenidos SET
                    motivo_detencion = ?, legajo = ?, unidad_fiscal = ?, fecha_detencion = ?
                WHERE persona_id = ?
            ");
            $stmt_detenido->execute([
                $_POST['motivo_detencion'],
                $_POST['legajo'],
                $_POST['unidad_fiscal'],
                $_POST['fecha_detencion'],
                $id
            ]);

            $conn->commit();
            $success = 'Datos actualizados exitosamente';
            
            // Actualizar datos mostrados
            $stmt = $conn->prepare("SELECT p.*, d.*, dom.* 
                FROM personas p
                LEFT JOIN detenidos d ON d.persona_id = p.id
                LEFT JOIN domicilios dom ON dom.persona_id = p.id
                WHERE p.id = ?");
            $stmt->execute([$id]);
            $detenido = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("
    SELECT p.*, d.*, dom.* 
    FROM personas p
    LEFT JOIN detenidos d ON d.persona_id = p.id
    LEFT JOIN domicilios dom ON dom.persona_id = p.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$detenido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$detenido) die("Registro no encontrado");

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include("../../assets/includes/header.php");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Detenido - BIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            <a href="listar.php" class="btn btn-secondary mb-3">⬅ Regresar</a>
            <h1 class="text-center text-primary mb-4">Detalles de Detenido</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form method="POST" id="form-edicion" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Datos Generales</h5>
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
                                    <img src="../../<?= htmlspecialchars($detenido['foto']) ?>" 
                                         class="img-fluid mb-3 read-mode" 
                                         id="preview-foto" 
                                         alt="Foto de <?= htmlspecialchars($detenido['nombre']) ?>">
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
                                    <label for="documento">Documento:</label>
                                    <span class="read-mode"><?= htmlspecialchars($detenido['documento']) ?></span>
                                    <input type="text" id="documento" name="documento" 
                                           class="form-control edit-mode" 
                                           pattern="\d{8}"
                                           value="<?= htmlspecialchars($detenido['documento']) ?>"
                                           required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre">Nombre:</label>
                                            <span class="read-mode"><?= htmlspecialchars($detenido['nombre']) ?></span>
                                            <input type="text" id="nombre" name="nombre" 
                                                   class="form-control edit-mode" 
                                                   value="<?= htmlspecialchars($detenido['nombre']) ?>"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="apellido">Apellido:</label>
                                            <span class="read-mode"><?= htmlspecialchars($detenido['apellido']) ?></span>
                                            <input type="text" id="apellido" name="apellido" 
                                                   class="form-control edit-mode" 
                                                   value="<?= htmlspecialchars($detenido['apellido']) ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="edad">Edad:</label>
                                            <span class="read-mode"><?= htmlspecialchars($detenido['edad']) ?></span>
                                            <input type="number" id="edad" name="edad" 
                                                   class="form-control edit-mode" 
                                                   min="1" max="120"
                                                   value="<?= htmlspecialchars($detenido['edad']) ?>"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="mb-3">
                                            <label for="sexo">Sexo:</label>
                                            <span class="read-mode"><?= ucfirst($detenido['sexo']) ?></span>
                                            <select id="sexo" name="sexo" class="form-select edit-mode" required>
                                                <option value="masculino" <?= $detenido['sexo'] == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                                                <option value="femenino" <?= $detenido['sexo'] == 'femenino' ? 'selected' : '' ?>>Femenino</option>
                                                <option value="otro" <?= $detenido['sexo'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Fin card-body -->
                    
                    <!-- Sección Domicilio -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6>Domicilio</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="calle">Calle:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['calle']) ?></span>
                                        <input type="text" id="calle" name="calle" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['calle']) ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="numeracion">Numeración:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['numeracion']) ?></span>
                                        <input type="text" id="numeracion" name="numeracion" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['numeracion']) ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="barrio_villa">Barrio/Villa:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['barrio_villa']) ?></span>
                                        <input type="text" id="barrio_villa" name="barrio_villa" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['barrio_villa']) ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="mzna">Manzana:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['mzna']) ?></span>
                                        <input type="text" id="mzna" name="mzna" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['mzna']) ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="casa">Casa:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['casa']) ?></span>
                                        <input type="text" id="casa" name="casa" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['casa']) ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="departamento">Departamento:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['departamento']) ?></span>
                                        <input type="text" id="departamento" name="departamento" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['departamento']) ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="provincia">Provincia:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['provincia']) ?></span>
                                        <input type="text" id="provincia" name="provincia" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['provincia']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección Judicial -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning">
                            <h6>Detalles Judiciales</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="motivo_detencion">Motivo Detención:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['motivo_detencion']) ?></span>
                                        <textarea id="motivo_detencion" name="motivo_detencion" 
                                                  class="form-control edit-mode" 
                                                  rows="3" required><?= htmlspecialchars($detenido['motivo_detencion']) ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="fecha_detencion">Fecha Detención:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['fecha_detencion']) ?></span>
                                        <input type="date" id="fecha_detencion" name="fecha_detencion" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['fecha_detencion']) ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="legajo">Legajo:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['legajo']) ?></span>
                                        <input type="text" id="legajo" name="legajo" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['legajo']) ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="unidad_fiscal">Unidad Fiscal:</label>
                                        <span class="read-mode"><?= htmlspecialchars($detenido['unidad_fiscal']) ?></span>
                                        <input type="text" id="unidad_fiscal" name="unidad_fiscal" 
                                               class="form-control edit-mode"
                                               value="<?= htmlspecialchars($detenido['unidad_fiscal']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones Edición -->
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
                    ¿Estás seguro de eliminar este registro definitivamente?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" name="eliminar" class="btn btn-danger">Eliminar</button>
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
            <h5 class="modal-title" id="fotoModalLabel">Foto de <?= htmlspecialchars($detenido['nombre']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body text-center">
            <img src="../../<?= htmlspecialchars($detenido['foto']) ?>" alt="Foto de <?= htmlspecialchars($detenido['nombre']) ?>" class="img-fluid">
          </div>
        </div>
      </div>
    </div>

    <script src="../../assets/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../../assets/includes/footer.php'; ?>
</body>
</html>
