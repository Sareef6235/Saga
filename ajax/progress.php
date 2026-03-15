<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

if (!($pdo instanceof PDO)) {
    echo json_encode(['success' => false]);
    exit;
}

$goal = get_goal($pdo);
$total = get_total_collected($pdo);
$remaining = max(0, $goal - $total);
$percent = $goal > 0 ? min(100, round(($total / $goal) * 100, 1)) : 0;

echo json_encode([
    'success' => true,
    'text' => format_amount($total) . ' raised of ' . format_amount($goal),
    'remaining' => format_amount($remaining),
    'percent' => $percent,
    'total' => $total,
    'goal' => $goal,
]);
