<?php
include('includes/db.php');

$nombre = "Juan";
$apellido = "Pérez";
$documento = "12345678";
$jerarquia = "Sargento";
$rol_id = 1; // 1 = Administrador, 2 = Usuario normal
$clave = "123"; // Clave sin encriptar

// Encriptar la clave
$hashedClave = password_hash($clave, PASSWORD_DEFAULT);

// Insertar en la base de datos
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, documento, jerarquia, rol_id, clave) VALUES (:nombre, :apellido, :documento, :jerarquia, :rol_id, :clave)");
$stmt->execute([
    'nombre' => $nombre,
    'apellido' => $apellido,
    'documento' => $documento,
    'jerarquia' => $jerarquia,
    'rol_id' => $rol_id,
    'clave' => $hashedClave
]);

echo "Usuario creado con éxito.";
?>
