<?php
require_once __DIR__ . '/../db.php';
require_admin_login();

if (!($pdo instanceof PDO)) {
    die('Database not configured');
}

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
$themeClass = app_theme_class();
?>
<!doctype html>
<html lang="en" class="<?= h($themeClass) ?>"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Donation</title><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={darkMode:'class'}</script></head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 text-slate-800 dark:text-slate-100 p-4">
<div class="max-w-lg mx-auto bg-white dark:bg-slate-900 rounded-xl shadow p-5">
    <div class="flex justify-between items-center"><h1 class="text-xl font-bold text-emerald-700 dark:text-emerald-400">Edit Donation Amount</h1><button id="themeToggle">🌓</button></div>
    <?php if ($donation): ?>
        <form method="post" class="space-y-3 mt-3">
            <input type="hidden" name="id" value="<?= $donation['id'] ?>">
            <p><b><?= h($donation['name']) ?></b> - <?= h($donation['organization']) ?></p>
            <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded p-2" type="number" step="0.01" min="1" name="amount" value="<?= h($donation['amount']) ?>" required>
            <button class="bg-emerald-600 text-white px-4 py-2 rounded">Save</button>
            <a class="ml-2" href="dashboard.php">Cancel</a>
        </form>
    <?php else: ?>
        <p class="mt-3">Donation not found.</p><a href="dashboard.php">Back</a>
    <?php endif; ?>
</div>
<footer class="max-w-lg mx-auto mt-4 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4">
        <p>കായകുളം ദർസ് സംഭാവന ക്യാമ്പെയ്ൻ | Contact: +91 6235 989 198</p>
        <p class="meta mt-1">© 2026 All Rights Reserved | Design by <a class="text-brand font-medium" href="https://mmhnu.online/" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p>
    </div>
</footer>

<script>document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});</script>
</body></html>
