<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
if (!($pdo instanceof PDO)) {
    echo json_encode(['success' => false, 'message' => $dbError]);
    exit;
}
$goal = get_goal($pdo);
$total = get_total_collected($pdo);
$percent = $goal > 0 ? min(100, ($total / $goal) * 100) : 0;
echo json_encode([
    'success' => true,
    'goal' => $goal,
    'total' => $total,
    'percent' => number_format($percent, 1),
    'text' => format_amount($total) . ' raised of ' . format_amount($goal),
]);
