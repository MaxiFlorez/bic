<?php
include 'includes/auth.php';          // Verificar sesión
redirectIfNotAdmin();                 // Asegurar que el usuario tenga permisos de administrador
include 'includes/db.php';            // Conexión a la base de datos

// Ruta del archivo CSV
$csvFile = 'import/detenidos.csv';
// Ruta de la carpeta de imágenes
$imgFolder = 'assets/img/';

// Abrir el archivo CSV
if (($handle = fopen($csvFile, "r")) !== false) {
    // Leer la primera línea para obtener los encabezados
    $headers = fgetcsv($handle, 1000, ",");
    
    // Recorrer cada línea del CSV
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        // Combinar los datos con los encabezados
        $row = array_combine($headers, $data);

        // Sanitizar los datos (ejemplo básico)
        $documento = trim($row['documento']);
        $nombre = trim($row['nombre']);
        $apellido = trim($row['apellido']);
        $edad = isset($row['edad']) ? filter_var($row['edad'], FILTER_VALIDATE_INT) : null;
        $sexo = trim($row['sexo']);
        $motivo_detencion = trim($row['motivo_detencion']);
        $fecha_detencion = trim($row['fecha_detencion']);
        $legajo = trim($row['legajo']);
        $unidad_fiscal = trim($row['unidad_fiscal']);

        // Datos de domicilio
        $calle = trim($row['calle']);
        $numeracion = trim($row['numeracion']);
        $barrio_villa = trim($row['barrio_villa']);
        $mzna = trim($row['mzna']);
        $casa = trim($row['casa']);
        $departamento = trim($row['departamento']);
        $provincia = trim($row['provincia']);

        // Construir la ruta de la foto usando el documento y buscar la foto
        // (Asumimos que la foto tiene extensión jpg; se puede ajustar si varía)
        $extension = 'jpg';
        // Verificar si existe un archivo con extensión jpg, png o gif:
        if (file_exists($imgFolder . $documento . '.jpg')) {
            $extension = 'jpg';
        } elseif (file_exists($imgFolder . $documento . '.png')) {
            $extension = 'png';
        } elseif (file_exists($imgFolder . $documento . '.gif')) {
            $extension = 'gif';
        }
        $ruta_imagen = $imgFolder . $documento . '.' . $extension;

        // Opcional: Verificar que el archivo exista
        if (!file_exists($ruta_imagen)) {
            echo "No se encontró la imagen para el documento: $documento<br>";
            continue; // O decide cómo manejar el error
        }

        try {
            // Iniciar transacción (opcional, para mayor integridad)
            $conn->beginTransaction();

            // Insertar la persona en la tabla 'personas'
            $query_persona = "INSERT INTO personas (nombre, apellido, documento, edad, sexo, foto) 
                              VALUES (:nombre, :apellido, :documento, :edad, :sexo, :foto)";
            $stmt_persona = $conn->prepare($query_persona);
            $stmt_persona->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'documento' => $documento,
                'edad' => $edad,
                'sexo' => $sexo,
                'foto' => $ruta_imagen
            ]);
            $persona_id = $conn->lastInsertId();

            // Insertar el domicilio en la tabla 'domicilios'
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

            // Insertar el detenido en la tabla 'detenidos'
            $query_detenido = "INSERT INTO detenidos (persona_id, motivo_detencion, legajo, unidad_fiscal, fecha_detencion) 
                               VALUES (:persona_id, :motivo_detencion, :legajo, :unidad_fiscal, :fecha_detencion)";
            $stmt_detenido = $conn->prepare($query_detenido);
            $stmt_detenido->execute([
                'persona_id' => $persona_id,
                'motivo_detencion' => $motivo_detencion,
                'legajo' => $legajo,
                'unidad_fiscal' => $unidad_fiscal,
                'fecha_detencion' => $fecha_detencion
            ]);

            // Confirmar transacción
            $conn->commit();

            echo "Detenido con documento $documento importado correctamente.<br>";
        } catch (Exception $e) {
            // Revertir en caso de error
            $conn->rollBack();
            echo "Error importando detenido con documento $documento: " . $e->getMessage() . "<br>";
        }
    }
    fclose($handle);
} else {
    echo "No se pudo abrir el archivo CSV.";
}
?>
//este modulo se va a utilizar para ingresar una gran cantidad de datos en la base de datos con respecto a detenidos debo ediatr