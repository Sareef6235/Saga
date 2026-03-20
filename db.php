<?php
// db.php
$dsn = getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=saga;charset=utf8mb4';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$pdo = null;
$dbError = null;

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}
