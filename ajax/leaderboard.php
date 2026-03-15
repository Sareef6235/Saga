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
    echo '<article class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/80">';
    echo '<p class="text-sm text-slate-500 dark:text-slate-300">Rank ' . $i . ' ' . $medals[$i] . '</p>';
    echo '<p class="font-semibold mt-2">' . ($d ? h($d['name']) : 'Waiting...') . '</p>';
    echo '<p class="text-sm text-slate-500 dark:text-slate-400">' . ($d ? h($d['organization']) : '-') . '</p>';
    echo '<p class="text-brand font-bold mt-2">' . ($d ? format_amount($d['amount']) : '₹0.00') . '</p>';
    echo '</article>';
}
$topHtml = ob_get_clean();

ob_start();
if ($donors) {
    foreach ($donors as $idx => $d) {
        echo '<article class="bg-slate-50 dark:bg-slate-800 rounded-xl p-3 flex items-center justify-between">';
        echo '<div><p class="font-semibold">#' . ($idx + 1) . ' ' . h($d['name']) . '</p>';
        echo '<p class="text-xs text-slate-500 dark:text-slate-400">' . h($d['organization']) . ' • ' . date('d M Y', strtotime($d['created_at'])) . '</p></div>';
        echo '<p class="text-brand font-bold">' . format_amount($d['amount']) . '</p></article>';
    }
} else {
    echo '<div class="text-center text-slate-500 p-3">No donations yet.</div>';
}
$cardHtml = ob_get_clean();

echo json_encode(['success' => true, 'top3_html' => $topHtml, 'card_html' => $cardHtml]);
