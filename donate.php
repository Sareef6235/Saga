<?php
require_once __DIR__ . '/db.php';
$errors = [];
$old = ['name' => '', 'organization' => '', 'phone' => '', 'amount' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['organization'] = trim($_POST['organization'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['amount'] = trim($_POST['amount'] ?? '');

    if ($old['name'] === '') { $errors[] = 'Name is required.'; }
    if ($old['organization'] === '') { $errors[] = 'Organization name is required.'; }
    if (!preg_match('/^[0-9]{10,15}$/', $old['phone'])) { $errors[] = 'Phone must be 10-15 digits.'; }
    if (!is_numeric($old['amount']) || (float)$old['amount'] <= 0) { $errors[] = 'Amount must be greater than 0.'; }
    if (!($pdo instanceof PDO)) { $errors[] = $dbError ?: 'Database not configured.'; }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO donations (name, organization, phone, amount) VALUES (:name,:organization,:phone,:amount)');
        $stmt->execute([
            ':name' => $old['name'],
            ':organization' => $old['organization'],
            ':phone' => $old['phone'],
            ':amount' => number_format((float)$old['amount'], 2, '.', ''),
        ]);
        header('Location: index.php?success=1');
        exit;
    }
}
$themeClass = app_theme_class();
?>
<!doctype html>
<html lang="ml" class="<?= h($themeClass) ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Donate - കായകുളം ദർസിലേക്ക് ഒരു സംഭാവന</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class'}</script>
</head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen pb-24">
<main class="max-w-xl mx-auto p-4 space-y-4">
    <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 text-white rounded-2xl p-5 shadow-lg">
        <div class="flex items-center justify-between">
            <div><h1 class="text-xl font-bold">സംഭാവന ചേർക്കുക</h1><p class="text-emerald-100 text-sm">Donation Intention Form</p></div>
            <button id="themeToggle" class="bg-white/20 px-2 py-1 rounded">🌓</button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5">
        <?php if($errors): ?><div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm"><ul class="list-disc pl-5"><?php foreach($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <form method="post" id="donateForm" class="space-y-3" novalidate>
            <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="name" placeholder="Name" value="<?= h($old['name']) ?>" required>
            <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="organization" placeholder="Organization Name" value="<?= h($old['organization']) ?>" required>
            <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="phone" id="phone" placeholder="Phone Number" value="<?= h($old['phone']) ?>" required>
            <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" type="number" min="1" step="0.01" id="amount" name="amount" placeholder="Donation Amount (₹)" value="<?= h($old['amount']) ?>" required>
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-semibold transition">Submit Contribution</button>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5">
        <h2 class="font-semibold text-emerald-700 dark:text-emerald-400">Online Payment Options</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Use these options after confirming intended amount.</p>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" class="rounded-xl bg-[#D4A017] text-white py-2">UPI Payment</button>
            <button type="button" class="rounded-xl bg-slate-800 text-white py-2">Razorpay Payment</button>
        </div>
    </div>
</main>

<nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t dark:border-slate-700"><div class="max-w-xl mx-auto grid grid-cols-4 text-center text-xs"><a class="py-3" href="index.php">🏠<br>Home</a><a class="py-3 text-emerald-700 font-semibold" href="donate.php">💰<br>Donate</a><a class="py-3" href="leaderboard.php">🏆<br>Leaderboard</a><a class="py-3" href="profile.php">👤<br>Profile</a></div></nav>

<script>
document.getElementById('donateForm').addEventListener('submit', (e) => {
    const p = document.getElementById('phone').value.trim();
    const a = parseFloat(document.getElementById('amount').value);
    if (!/^[0-9]{10,15}$/.test(p) || !(a > 0)) {
        e.preventDefault();
        alert('Enter valid phone and positive amount.');
    }
});
document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});
</script>
</body>
</html>
