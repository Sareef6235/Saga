<?php
require_once __DIR__ . '/../db.php';
require_admin_login();
if ($pdo instanceof PDO && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM donations WHERE id=:id');
    $stmt->execute([':id' => (int)$_GET['id']]);
}
header('Location: dashboard.php');
exit;
