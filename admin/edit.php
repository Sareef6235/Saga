<?php
require_once __DIR__ . '/../db.php';
require_admin_login();
if (!($pdo instanceof PDO)) { die('DB error'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    if ($id > 0 && $amount > 0) {
        $stmt = $pdo->prepare('UPDATE donations SET amount=:amount WHERE id=:id');
        $stmt->execute([':amount' => number_format($amount, 2, '.', ''), ':id' => $id]);
    }
    header('Location: dashboard.php');
    exit;
}

$donation = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM donations WHERE id=:id');
    $stmt->execute([':id' => $id]);
    $donation = $stmt->fetch();
}
?><!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Donation</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-[#F1F5F9] p-4"><div class="max-w-lg mx-auto bg-white rounded-xl shadow p-5"><h1 class="text-xl font-bold text-emerald-700 mb-4">Edit Donation Amount</h1><?php if($donation): ?><form method="post" class="space-y-3"><input type="hidden" name="id" value="<?= $donation['id'] ?>"><p><b><?= h($donation['name']) ?></b> - <?= h($donation['organization']) ?></p><input class="w-full border rounded p-2" type="number" step="0.01" min="1" name="amount" value="<?= h($donation['amount']) ?>" required><button class="bg-emerald-600 text-white px-4 py-2 rounded">Save</button><a class="ml-2" href="dashboard.php">Cancel</a></form><?php else: ?><p>Donation not found.</p><a href="dashboard.php">Back</a><?php endif; ?></div></body></html>
