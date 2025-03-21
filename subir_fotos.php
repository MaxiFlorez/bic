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
// Resto de tu código para subir fotos...

// Ruta de la carpeta con las fotos
$ruta_fotos = 'assets/imagenes/personas/';

// Verificar si la carpeta existe
if (!is_dir($ruta_fotos)) {
    die("La carpeta de fotos no existe: $ruta_fotos");
}

// Recorrer las fotos en la carpeta
if ($handle = opendir($ruta_fotos)) {
    while (false !== ($archivo = readdir($handle))) {
        // Ignorar los directorios "." y ".."
        if ($archivo != "." && $archivo != ".." && preg_match('/\.(jpg|jpeg|png)$/i', $archivo)) {
            // Extraer el DNI del nombre del archivo
            $dni = pathinfo($archivo, PATHINFO_FILENAME);

            // Ruta relativa de la foto
            $ruta_foto = $ruta_fotos . $archivo;

            // Actualizar la base de datos
            $sql = "UPDATE personas SET foto = :foto WHERE documento = :documento";
            $stmt = $conn->prepare($sql);

            // Verificar si la preparación de la consulta fue exitosa
            if ($stmt === false) {
                die("Error al preparar la consulta: " . $conn->errorInfo()[2]);
            }

            // Vincular parámetros y ejecutar la consulta usando bindValue()
            $stmt->bindValue(':foto', $ruta_foto, PDO::PARAM_STR);
            $stmt->bindValue(':documento', $dni, PDO::PARAM_STR);

            // Ejecutar la consulta
            if (!$stmt->execute()) {
                echo "Error al actualizar el registro para DNI $dni: " . $stmt->errorInfo()[2] . "<br>";
            } else {
                echo "Foto actualizada para DNI $dni: $ruta_foto<br>";
            }

            // Cerrar la declaración
            $stmt->closeCursor();
        }
    }
    closedir($handle);
} else {
    die("No se pudo abrir la carpeta de fotos: $ruta_fotos");
}

// Cerrar conexión
$conn = null;
?>
