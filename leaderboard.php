<?php
require_once __DIR__ . '/db.php';
$rows = $pdo instanceof PDO ? $pdo->query('SELECT name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at DESC')->fetchAll() : [];
?>
<!doctype html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Leaderboard</title><link rel="stylesheet" href="assets/css/tailwind.min.css"></head>
<body class="bg-bgsoft min-h-screen p-4">
<div class="max-w-3xl mx-auto bg-white rounded-2xl p-5 shadow-soft">
<h1 class="text-2xl font-bold text-brand mb-4">Full Leaderboard</h1>
<?php if ($rows): foreach ($rows as $i => $r): ?>
<div class="border rounded-xl p-3 mb-2 flex justify-between"><div><b>#<?= $i+1 ?> <?= h($r['name']) ?></b><div class="text-sm text-slate-500"><?= h($r['organization']) ?> • <?= h($r['created_at']) ?></div></div><div class="text-brand font-bold"><?= format_amount((float)$r['amount']) ?></div></div>
<?php endforeach; else: ?><p>No donations yet.</p><?php endif; ?>
<a href="index.php" class="inline-block mt-3 text-brand">← Back</a>
</div>
</body>
</html>
