<?php
/**
 * Configuración de Base de Datos para Cuentas de Cobro
 * Soporta detección automática de entorno (Local MAMP / Producción Dreamhost)
 */

// Evitar almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Detectar si el servidor es local
$is_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']) 
            || (isset($_SERVER['HTTP_HOST']) && preg_match('/localhost|127\.0\.0\.1/', $_SERVER['HTTP_HOST']));

$pdo = null;

if ($is_local) {
    // --- ENTORNO LOCAL (MAMP) ---
    // Intentar conectar en puertos comunes de MAMP: 3306 (estándar) y 8889 (por defecto en MAMP)
    $host = '127.0.0.1';
    $ports = ['3306', '8889'];
    $db_name = 'cuentas_cobro';
    $db_user = 'root';
    $db_pass = 'root'; // Contraseña predeterminada en MAMP
    $connected = false;
    $error_msg = '';

    foreach ($ports as $port) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            
            // Definir constantes del entorno local activo
            define('DB_HOST', $host);
            define('DB_PORT', $port);
            define('DB_NAME', $db_name);
            define('DB_USER', $db_user);
            define('DB_PASS', $db_pass);
            $connected = true;
            break;
        } catch (\PDOException $e) {
            $error_msg = $e->getMessage();
        }
    }

    if (!$connected) {
        die("<h3>Error de conexión a la base de datos local (MAMP).</h3>" .
            "<p>Asegúrese de que el servidor MySQL en MAMP esté encendido.</p>" .
            "<p><strong>Detalle del error:</strong> {$error_msg}</p>");
    }
} else {
    // --- ENTORNO DE PRODUCCIÓN (DREAMHOST) ---
    // NOTA: Reemplace estos valores con las credenciales de su base de datos creada en Dreamhost
    define('DB_HOST', 'mysql.advantascience.com'); // Hostname de Dreamhost
    define('DB_PORT', '3306');
    define('DB_NAME', 'cuentas_cobro');            // Nombre de la base de datos
    define('DB_USER', 'jhtrujilloadvant');         // Usuario de la base de datos
    define('DB_PASS', 'JT-sq16cy21');              // Contraseña de base de datos

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (\PDOException $e) {
        die("<h3>Error de conexión en producción (Dreamhost).</h3>" .
            "<p>Verifique las credenciales en el archivo <code>config.php</code>.</p>" .
            "<p><strong>Detalle del error:</strong> " . $e->getMessage() . "</p>");
    }
}
?>
