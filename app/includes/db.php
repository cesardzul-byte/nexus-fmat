<?php
/**
 * Conexión a la base de datos: una sola instancia de PDO por petición.
 */
declare(strict_types=1);

/**
 * Devuelve la conexión PDO, creándola la primera vez que se llama.
 *
 * Si la conexión falla, el detalle va al log y el usuario solo ve un mensaje
 * genérico, para no exponer el usuario, el host ni la contraseña de la BD.
 *
 * @return PDO Conexión en utf8mb4, con excepciones, FETCH_ASSOC y consultas preparadas reales.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $ex) {
        error_log('Nexus FMAT · error de conexión a la BD: ' . $ex->getMessage());
        abortar(500, 'No se pudo conectar con la base de datos. Intenta más tarde.');
    }
    return $pdo;
}
