<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario ha iniciado sesión.
 * Retorna true si la variable de sesión 'user_id' está definida, de lo contrario, retorna false.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Verifica si el usuario tiene rol de administrador.
 * Retorna true si la variable de sesión 'role' está definida y es igual a 1 (ID de administrador).
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 1; // 1 = ID de "administrador"
}

/**
 * Redirige al usuario a la página de inicio de sesión (login.php) si no ha iniciado sesión.
 * Se usa para proteger páginas que requieren autenticación.
 */
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirige al usuario al dashboard (dashboard.php) si no es administrador.
 * Se usa para restringir el acceso a páginas exclusivas de administradores.
 */
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Función para autenticar al usuario.
 * Verifica las credenciales y crea la sesión si son correctas.
 */
function authenticateUser($documento, $clave) {
    include('includes/db.php'); // Asegúrate de que esta ruta sea correcta

    // Prepara y ejecuta la consulta para verificar las credenciales
    $stmt = $conn->prepare("
        SELECT usuarios.*, roles.nombre as rol_nombre 
        FROM usuarios 
        JOIN roles ON usuarios.rol_id = roles.id 
        WHERE documento = :documento
    ");
    $stmt->execute(['documento' => $documento]);
    
    $user = $stmt->fetch();

    // Verifica si el usuario existe y las claves coinciden
    if ($user && password_verify($clave, $user['clave'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['rol_id']; // Almacena el ID del rol (1 o 2)
        $_SESSION['usuario_autenticado'] = $user['nombre']; // Nombre del usuario
        return true; // Autenticación exitosa
    }
    return false; // Credenciales incorrectas
}

