<?php
include '../../includes/auth.php';
redirectIfNotLoggedIn();
include '../../includes/header.php';
include '../../includes/db.php';
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Autos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .main-container { padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <h1 class="text-center mb-4">Gestión de Autos</h1>
            
            <!-- Buscador y botón para agregar auto -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="vehiculos.php" method="GET">

                    
                        <div class="input-group">
                            <input type="text" class="form-control" name="buscar" placeholder="Buscar autos...">
                            <button class="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarAuto">Agregar Auto</button>
                </div>
            </div>
            
            <!-- Tabla de autos -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Placa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí se iterarán los autos obtenidos de la BD -->
                        <!-- Ejemplo:
                        <tr>
                            <td>1</td>
                            <td>Ford</td>
                            <td>Fiesta</td>
                            <td>2018</td>
                            <td>ABC123</td>
                            <td>
                                <a href="editar_auto.php?id=1" class="btn btn-sm btn-warning">Editar</a>
                                <a href="eliminar_auto.php?id=1" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                        -->
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <nav aria-label="Paginación de autos">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Anterior</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal para agregar auto -->
    <div class="modal fade" id="modalAgregarAuto" tabindex="-1" aria-labelledby="modalAgregarAutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="agregar_auto.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarAutoLabel">Agregar Auto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca:</label>
                            <input type="text" name="marca" id="marca" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="modelo" class="form-label">Modelo:</label>
                            <input type="text" name="modelo" id="modelo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="anio" class="form-label">Año:</label>
                            <input type="number" name="anio" id="anio" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="placa" class="form-label">Placa:</label>
                            <input type="text" name="placa" id="placa" class="form-control" required>
                        </div>
                        <!-- Agrega otros campos que necesites -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Agregar Auto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php';?>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
