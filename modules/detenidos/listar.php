<?php
include '../../assets/includes/auth.php'; // Ruta corregida para incluir auth.php
redirectIfNotLoggedIn(); // Asegurar que la función está definida
include '../../assets/includes/header.php';
include '../../assets/includes/db.php';

// Obtener los filtros del formulario
$filtro_nombre = $_GET['filtro_nombre'] ?? '';
$filtro_apellido = $_GET['filtro_apellido'] ?? '';
$filtro_documento = $_GET['filtro_documento'] ?? '';

// Paginación
$pagina = $_GET['pagina'] ?? 1;
$registros_por_pagina = 10;
$offset = ($pagina - 1) * $registros_por_pagina;

// Consulta SQL con filtros y paginación
$query = "
    SELECT p.id, p.nombre, p.apellido, p.documento, d.motivo_detencion, d.fecha_detencion, p.foto,
           dom.calle, dom.numeracion, dom.barrio_villa, dom.mzna, dom.casa, dom.departamento, dom.provincia
    FROM detenidos d
    JOIN personas p ON d.persona_id = p.id
    LEFT JOIN domicilios dom ON p.id = dom.persona_id  -- Relación con domicilios
    WHERE (p.nombre LIKE :filtro_nombre OR :filtro_nombre = '')
      AND (p.apellido LIKE :filtro_apellido OR :filtro_apellido = '')
      AND (p.documento LIKE :filtro_documento OR :filtro_documento = '')
    LIMIT :offset, :registros_por_pagina
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':filtro_nombre', "%$filtro_nombre%", PDO::PARAM_STR);
$stmt->bindValue(':filtro_apellido', "%$filtro_apellido%", PDO::PARAM_STR);
$stmt->bindValue(':filtro_documento', "%$filtro_documento%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':registros_por_pagina', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detenidos - BIC</title>
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
            <h1 class="text-center text-primary mb-4">Listado de Detenidos</h1>

            <!-- Botón para agregar detenido (solo para administradores) -->
            <?php if (isAdmin()): ?>
                <a href="agregar_detenido.php" class="btn btn-primary mb-4">Agregar Detenido</a>
            <?php endif; ?>

            <!-- Formulario de filtrado -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="filtro_nombre" class="form-control" placeholder="Filtrar por nombre" value="<?= htmlspecialchars($filtro_nombre) ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="filtro_apellido" class="form-control" placeholder="Filtrar por apellido" value="<?= htmlspecialchars($filtro_apellido) ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="filtro_documento" class="form-control" placeholder="Filtrar por documento" value="<?= htmlspecialchars($filtro_documento) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary mt-2">Filtrar</button>
            </form>

            
            <table class="table table-bordered">
    <thead>
        <tr>
            <th>Foto</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Documento</th>
            <th>Domicilio</th>
            <th>Motivo de Detención</th>
            <th>Fecha de Detención</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td>
                    <?php if (!empty($row['foto'])): ?>
                        <img src="../../<?= htmlspecialchars($row['foto']) ?>" alt="Foto de <?= htmlspecialchars($row['nombre']) ?>" class="img-thumbnail">
                    <?php else: ?>
                        <span class="text-muted">Sin foto</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['apellido']) ?></td>
                <td>
                    <a href="detalle_detenido.php?id=<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['documento']) ?>
                    </a>
                </td>

                <td>
                    <?php 
                        $domicilio = [];
                        if (!empty($row['calle'])) $domicilio[] = htmlspecialchars($row['calle']) . " " . htmlspecialchars($row['numeracion']);
                        if (!empty($row['barrio_villa'])) $domicilio[] = htmlspecialchars($row['barrio_villa']);
                        if (!empty($row['mzna'])) $domicilio[] = "Mzna " . htmlspecialchars($row['mzna']);
                        if (!empty($row['casa'])) $domicilio[] = "Casa " . htmlspecialchars($row['casa']);
                        if (!empty($row['departamento'])) $domicilio[] = htmlspecialchars($row['departamento']);
                        if (!empty($row['provincia'])) $domicilio[] = htmlspecialchars($row['provincia']);
                        echo !empty($domicilio) ? implode(', ', $domicilio) : 'Sin domicilio';
                    ?>
                </td>
                <td><?= htmlspecialchars($row['motivo_detencion']) ?></td>
                <td><?= htmlspecialchars($row['fecha_detencion']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

            <!-- Enlaces de paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php
                    // Consulta para obtener el total de registros
                    $countQuery = "
                        SELECT COUNT(*) 
                        FROM detenidos d
                        JOIN personas p ON d.persona_id = p.id
                        WHERE (p.nombre LIKE :filtro_nombre OR :filtro_nombre = '')
                          AND (p.apellido LIKE :filtro_apellido OR :filtro_apellido = '')
                          AND (p.documento LIKE :filtro_documento OR :filtro_documento = '')
                    ";
                    $stmtCount = $conn->prepare($countQuery);
                    $stmtCount->bindValue(':filtro_nombre', "%$filtro_nombre%", PDO::PARAM_STR);
                    $stmtCount->bindValue(':filtro_apellido', "%$filtro_apellido%", PDO::PARAM_STR);
                    $stmtCount->bindValue(':filtro_documento', "%$filtro_documento%", PDO::PARAM_STR);
                    $stmtCount->execute();
                    $totalRegistros = $stmtCount->fetchColumn();
                    $totalPaginas = ceil($totalRegistros / $registros_por_pagina);

                    for ($i = 1; $i <= $totalPaginas; $i++): 
                    ?>
                        <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&filtro_nombre=<?= urlencode($filtro_nombre) ?>&filtro_apellido=<?= urlencode($filtro_apellido) ?>&filtro_documento=<?= urlencode($filtro_documento) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>

        </div>
    </div>

    <?php include '../../assets/includes/footer.php'; // Incluir el footer ?>
</body>
</html>
