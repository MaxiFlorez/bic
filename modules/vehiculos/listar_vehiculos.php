<?php
include '../../assets/includes/auth.php';
redirectIfNotLoggedIn();
include '../../assets/includes/header.php';
include '../../assets/includes/db.php';

// Obtener los filtros del formulario
$filtro_marca = $_GET['filtro_marca'] ?? '';
$filtro_modelo = $_GET['filtro_modelo'] ?? '';
$filtro_dominio = $_GET['filtro_dominio'] ?? '';
$filtro_titular = $_GET['filtro_titular'] ?? '';

// Paginación
$pagina = $_GET['pagina'] ?? 1;
$registros_por_pagina = 10;
$offset = ($pagina - 1) * $registros_por_pagina;

// Consulta SQL con filtros y paginación
$query = "
    SELECT vp.id, vp.marca, vp.modelo, vp.dominio, vp.color, vp.foto_vehiculo, vp.pdf_secuestro, vp.estado, vp.creado_en,
           p.nombre AS titular_nombre, p.apellido AS titular_apellido, p.documento AS titular_documento
    FROM vehiculos_pedidos vp
    JOIN personas p ON vp.titular_id = p.id
    WHERE (vp.marca LIKE :filtro_marca OR :filtro_marca = '')
      AND (vp.modelo LIKE :filtro_modelo OR :filtro_modelo = '')
      AND (vp.dominio LIKE :filtro_dominio OR :filtro_dominio = '')
      AND (p.nombre LIKE :filtro_titular OR p.apellido LIKE :filtro_titular OR :filtro_titular = '')
    LIMIT :offset, :registros_por_pagina
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':filtro_marca', "%$filtro_marca%", PDO::PARAM_STR);
$stmt->bindValue(':filtro_modelo', "%$filtro_modelo%", PDO::PARAM_STR);
$stmt->bindValue(':filtro_dominio', "%$filtro_dominio%", PDO::PARAM_STR);
$stmt->bindValue(':filtro_titular', "%$filtro_titular%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':registros_por_pagina', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehículos - BIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .main-container { padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .table img { max-width: 100px; height: auto; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <h1 class="text-center text-primary mb-4">Listado de Vehículos</h1>

            <!-- Botón para agregar vehículo (solo para administradores) -->
            <?php if (isAdmin()): ?>
                <a href="agregar_vehiculo.php" class="btn btn-primary mb-4">Agregar Vehículo</a>
            <?php endif; ?>

            <!-- Formulario de filtrado -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="filtro_marca" class="form-control" placeholder="Filtrar por marca" value="<?= htmlspecialchars($filtro_marca) ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="filtro_modelo" class="form-control" placeholder="Filtrar por modelo" value="<?= htmlspecialchars($filtro_modelo) ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="filtro_dominio" class="form-control" placeholder="Filtrar por dominio" value="<?= htmlspecialchars($filtro_dominio) ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="filtro_titular" class="form-control" placeholder="Filtrar por titular" value="<?= htmlspecialchars($filtro_titular) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary mt-2">Filtrar</button>
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Dominio</th>
                        <th>Color</th>
                        <th>Titular</th>
                        <th>Documento</th>
                        <th>PDF Secuestro</th>
                        <th>Estado</th>
                        <th>Fecha de Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['foto_vehiculo'])): ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#fotoModal<?= $row['id'] ?>">
                                            <img src="../../assets/imagenes/vehiculos/<?= htmlspecialchars($row['foto_vehiculo']) ?>" alt="Foto de <?= htmlspecialchars($row['dominio']) ?>" class="img-thumbnail">
                                        </a>

                                        <!-- Modal para ampliar la foto -->
                                        <div class="modal fade" id="fotoModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="fotoModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="fotoModalLabel<?= $row['id'] ?>">Foto de <?= htmlspecialchars($row['dominio']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="../../assets/imagenes/vehiculos/<?= htmlspecialchars($row['foto_vehiculo']) ?>" alt="Foto de <?= htmlspecialchars($row['dominio']) ?>" class="img-fluid">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Sin foto</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['marca']) ?></td>
                                <td><?= htmlspecialchars($row['modelo']) ?></td>
                                <td>
                                    <a href="detalle_vehiculo.php?id=<?= $row['id'] ?>">
                                        <?= htmlspecialchars($row['dominio']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($row['color']) ?></td>
                                <td><?= htmlspecialchars($row['titular_nombre']) . ' ' . htmlspecialchars($row['titular_apellido']) ?></td>
                                <td><?= htmlspecialchars($row['titular_documento']) ?></td>
                                <td>
                                    <?php if (!empty($row['pdf_secuestro'])): ?>
                                        <embed src="../../assets/documentos/vehiculos/<?= htmlspecialchars($row['pdf_secuestro']) ?>" width="100%" height="200px" type="application/pdf" />
                                    <?php else: ?>
                                        <span class="text-muted">Sin PDF</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['estado']) ?></td>
                                <td><?= htmlspecialchars($row['creado_en']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No se encontraron vehículos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Enlaces de paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php
                    // Consulta para obtener el total de registros
                    $countQuery = "
                        SELECT COUNT(*) 
                        FROM vehiculos_pedidos vp
                        JOIN personas p ON vp.titular_id = p.id
                        WHERE (vp.marca LIKE :filtro_marca OR :filtro_marca = '')
                          AND (vp.modelo LIKE :filtro_modelo OR :filtro_modelo = '')
                          AND (vp.dominio LIKE :filtro_dominio OR :filtro_dominio = '')
                          AND (p.nombre LIKE :filtro_titular OR p.apellido LIKE :filtro_titular OR :filtro_titular = '')
                    ";
                    $stmtCount = $conn->prepare($countQuery);
                    $stmtCount->bindValue(':filtro_marca', "%$filtro_marca%", PDO::PARAM_STR);
                    $stmtCount->bindValue(':filtro_modelo', "%$filtro_modelo%", PDO::PARAM_STR);
                    $stmtCount->bindValue(':filtro_dominio', "%$filtro_dominio%", PDO::PARAM_STR);
                    $stmtCount->bindValue(':filtro_titular', "%$filtro_titular%", PDO::PARAM_STR);
                    $stmtCount->execute();
                    $totalRegistros = $stmtCount->fetchColumn();
                    $totalPaginas = ceil($totalRegistros / $registros_por_pagina);

                    for ($i = 1; $i <= $totalPaginas; $i++): 
                    ?>
                        <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&filtro_marca=<?= urlencode($filtro_marca) ?>&filtro_modelo=<?= urlencode($filtro_modelo) ?>&filtro_dominio=<?= urlencode($filtro_dominio) ?>&filtro_titular=<?= urlencode($filtro_titular) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <?php include '../../assets/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>