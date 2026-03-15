<?php
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_database_user';
$password = 'your_database_password';
$charset = 'utf8mb4';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = null;
$dbError = '';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    $dbError = 'Database connection failed. Please update db.php credentials.';
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function format_amount($amount)
{
    return '₹' . number_format((float)$amount, 2);
}

function get_goal(PDO $pdo)
{
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key='fundraising_goal' LIMIT 1");
    $row = $stmt->fetch();
    return $row ? (float)$row['setting_value'] : 1000000;
}

function get_total_collected(PDO $pdo)
{
    $stmt = $pdo->query('SELECT COALESCE(SUM(amount),0) AS total FROM donations');
    $row = $stmt->fetch();
    return (float)($row['total'] ?? 0);
}

function require_admin_login()
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
