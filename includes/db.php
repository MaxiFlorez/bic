<?php
// Datos de conexión a la base de datos
$host = 'localhost';     // Servidor de la base de datos (local en este caso)
$dbname = 'db_bic';      // Nombre de la base de datos
$username = 'root';      // Usuario de la base de datos (por defecto en XAMPP/MAMP es 'root')
$password = '';          // Contraseña (vacía por defecto en XAMPP)

try {
    // Crear una nueva conexión PDO a la base de datos
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurar PDO para que genere excepciones en caso de error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En caso de error, muestra un mensaje genérico
    die("Error de conexión. Contacta al administrador.");
}
?>