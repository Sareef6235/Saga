<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

if (!($pdo instanceof PDO)) {
    echo json_encode(['success' => false]);
    exit;
}

$topDonors = fetch_top_donors($pdo, 3);
$allDonors = $pdo->query('SELECT name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at DESC LIMIT 100')->fetchAll();

$podium = [
    0 => 'bg-yellow-100 dark:bg-yellow-950/40 border border-yellow-300 dark:border-yellow-800',
    1 => 'bg-gray-200 dark:bg-slate-700 border border-gray-300 dark:border-slate-600',
    2 => 'bg-orange-200 dark:bg-orange-950/40 border border-orange-300 dark:border-orange-800',
];
$medals = ['🥇', '🥈', '🥉'];

ob_start();
for ($i = 0; $i < 3; $i++):
    $d = $topDonors[$i] ?? null;
    ?>
    <div class="rounded-xl p-4 shadow card <?= $podium[$i] ?>">
        <p class="text-3xl"><?= $medals[$i] ?></p>
        <p class="font-bold mt-2"><?= h($d['name'] ?? 'Waiting...') ?></p>
        <p class="text-brand font-semibold"><?= format_amount((float)($d['amount'] ?? 0)) ?></p>
    </div>
    <?php
endfor;
$top3_html = ob_get_clean();

ob_start();
if ($allDonors) {
    foreach ($allDonors as $idx => $d) {
        ?>
        <article class="bg-slate-50 dark:bg-slate-800 rounded-xl p-3 flex items-center justify-between card">
            <div>
                <p class="font-semibold">#<?= $idx + 1 ?> <?= h($d['name']) ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400"><?= h($d['organization']) ?> • <?= date('d M Y', strtotime($d['created_at'])) ?></p>
            </div>
            <p class="text-brand font-bold"><?= format_amount((float)$d['amount']) ?></p>
        </article>
        <?php
    }
} else {
    echo '<div class="text-center text-slate-500 p-3">No donations yet.</div>';
}
$card_html = ob_get_clean();

echo json_encode([
    'success' => true,
    'top3_html' => $top3_html,
    'card_html' => $card_html,
    'top_amount' => (float)($topDonors[0]['amount'] ?? 0),
]);
