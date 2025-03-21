<?php
include 'assets/includes/auth.php';
redirectIfNotLoggedIn();
include 'assets/includes/header.php';
include 'assets/includes/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; color: #333; }
        .main-container { padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .card { border: none; border-radius: 10px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .card-title { font-weight: bold; }
        .btn-custom { background-color: #26a69a; color: white; transition: all 0.3s ease-in-out; }
        .btn-custom:hover { background-color: #00796b; }
        .btn-danger-custom { background-color: #d32f2f; color: white; transition: all 0.3s ease-in-out; }
        .btn-danger-custom:hover { background-color: #c62828; }
    </style>
</head>
<body>
    <!-- Contenedor principal -->
    <div class="container">
        <div class="main-container">
            <h1 class="text-center text-primary mb-4">Bienvenido al Sistema de Gestión de Seguridad</h1>
            <p class="text-center text-secondary mb-5">Seleccione una opción para continuar</p>

            <div class="row">
                <!-- Opción 1: Detenidos -->
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Detenidos</h5>
                            <p class="card-text">Acceda al listado de personas detenidas<br> en la Brigada Central</p>
                            <a href="modules/detenidos/listar.php" class="btn btn-custom w-100">Detenidos</a>
                        </div>
                    </div>
                </div>

                <!-- Opción 2: Vehículos -->
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-success">Vehículos</h5>
                            <p class="card-text">Revise los vehículos con órdenes de secuestro o en investigación.</p>
                            <a href="modules/vehiculos/listar_vehiculos.php" class="btn btn-success w-100">Ir a Vehículos</a>
                        </div>
                    </div>
                </div>

                <!-- Opción 3: Prófugos -->
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-danger">Prófugos</h5>
                            <p class="card-text">Consulta el listado de personas prófugas o con pedidos de captura.</p>
                            <a href="modules/profugos/listar.php" class="btn btn-danger-custom w-100">Ir a Prófugos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'assets/includes/footer.php'; ?>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>