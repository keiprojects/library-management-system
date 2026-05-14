<?php

declare(strict_types=1);

/**
 * Creates and reuses a PDO database connection.
 *
 * @return PDO Active PDO connection for the whole request.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo '<h1>Database Connection Error</h1>';
        echo '<p>Please update your database settings in <code>.env</code> or <code>includes/config.php</code>.</p>';
        echo '<pre>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        exit;
    }

    return $pdo;
}
