<?php
require_once __DIR__ . '/db.php';
$errors = [];
$old = ['name'=>'','organization'=>'','phone'=>'','amount'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['organization'] = trim($_POST['organization'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['amount'] = trim($_POST['amount'] ?? '');

    if ($old['name'] === '') { $errors[] = 'Name is required.'; }
    if ($old['organization'] === '') { $errors[] = 'Organization is required.'; }
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
?>
<!doctype html><html lang="ml"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Donate</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-[#F1F5F9] min-h-screen pb-24 text-slate-800">
<main class="max-w-xl mx-auto p-4">
<div class="bg-white rounded-2xl shadow-lg p-5">
<h1 class="text-xl font-bold text-emerald-700">സംഭാവന ചേർക്കുക</h1>
<p class="text-sm text-slate-500 mb-4">Donation intention form</p>
<?php if($errors): ?><div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm mb-4"><ul class="list-disc pl-5"><?php foreach($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="space-y-3" id="donateForm" novalidate>
<input class="w-full border rounded-lg p-2" name="name" placeholder="Name" value="<?= h($old['name']) ?>" required>
<input class="w-full border rounded-lg p-2" name="organization" placeholder="Organization Name" value="<?= h($old['organization']) ?>" required>
<input class="w-full border rounded-lg p-2" name="phone" id="phone" placeholder="Phone Number" value="<?= h($old['phone']) ?>" required>
<input class="w-full border rounded-lg p-2" type="number" min="1" step="0.01" id="amount" name="amount" placeholder="Donation Amount (₹)" value="<?= h($old['amount']) ?>" required>
<button class="w-full bg-emerald-600 text-white py-3 rounded-lg font-semibold">Submit</button>
</form>
</div></main>
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t"><div class="max-w-xl mx-auto grid grid-cols-4 text-center text-xs"><a class="py-3" href="index.php">🏠<br>Home</a><a class="py-3 text-brand font-semibold" href="donate.php">💰<br>Donate</a><a class="py-3" href="leaderboard.php">🏆<br>Leaderboard</a><a class="py-3" href="admin/login.php">👤<br>Profile</a></div></nav>
<script>
document.getElementById('donateForm').addEventListener('submit',e=>{const p=document.getElementById('phone').value.trim();const a=parseFloat(document.getElementById('amount').value);if(!/^[0-9]{10,15}$/.test(p)||!(a>0)){e.preventDefault();alert('Valid phone and amount required.');}});
</script>
</body></html>
