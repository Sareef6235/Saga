<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

if (!($pdo instanceof PDO)) {
    echo json_encode(['success' => false, 'message' => $dbError]);
    exit;
}

$stmt = $pdo->query('SELECT id, name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
$donors = $stmt->fetchAll();
$top = array_slice($donors, 0, 3);
$medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];

ob_start();
for ($i = 1; $i <= 3; $i++) {
    $d = $top[$i - 1] ?? null;
    echo '<article class="rounded-xl border p-4 bg-slate-50">';
    echo '<p class="text-sm text-slate-500">Rank ' . $i . ' ' . $medals[$i] . '</p>';
    echo '<p class="font-semibold mt-2">' . ($d ? h($d['name']) : 'Waiting...') . '</p>';
    echo '<p class="text-sm text-slate-500">' . ($d ? h($d['organization']) : '-') . '</p>';
    echo '<p class="text-brand font-bold mt-2">' . ($d ? format_amount($d['amount']) : '₹0.00') . '</p>';
    echo '</article>';
}
$topHtml = ob_get_clean();

ob_start();
if ($donors) {
    foreach ($donors as $idx => $d) {
        echo '<tr class="border-b"><td class="p-2">#' . ($idx + 1) . '</td><td class="p-2">' . h($d['name']) . '</td><td class="p-2">' . h($d['organization']) . '</td><td class="p-2 text-right text-brand font-semibold">' . format_amount($d['amount']) . '</td><td class="p-2">' . date('d M Y', strtotime($d['created_at'])) . '</td></tr>';
    }
} else {
    echo '<tr><td colspan="5" class="p-3 text-center text-slate-500">No donations yet.</td></tr>';
}
$listHtml = ob_get_clean();

echo json_encode(['success' => true, 'top3_html' => $topHtml, 'list_html' => $listHtml]);
