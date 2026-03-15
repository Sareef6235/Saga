<?php
require_once __DIR__ . '/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $name = trim($_POST['name'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);

    if ($name === '' || $organization === '' || $amount <= 0) {
        $error = 'Please fill all fields correctly.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO donations(name, organization, amount, created_at) VALUES(:name, :org, :amount, :created_at)');
        $stmt->execute([
            ':name' => $name,
            ':org' => $organization,
            ':amount' => $amount,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        header('Location: receipt.php?id=' . (int)$pdo->lastInsertId());
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Donation</title>
  <link rel="stylesheet" href="assets/css/tailwind.min.css">
</head>
<body class="bg-bgsoft min-h-screen p-4">
  <div class="max-w-xl mx-auto bg-white rounded-2xl p-5 shadow-soft">
    <h1 class="text-2xl font-bold text-brand">Add Donation</h1>
    <?php if ($error): ?><p class="text-red-700 mt-2"><?= h($error) ?></p><?php endif; ?>
    <form method="post" class="space-y-2 mt-3">
      <input class="w-full border rounded-lg p-3" name="name" placeholder="Donor name" required>
      <input class="w-full border rounded-lg p-3" name="organization" placeholder="Organization" required>
      <input class="w-full border rounded-lg p-3" name="amount" type="number" min="1" step="0.01" placeholder="Amount" required>
      <button class="w-full bg-brand text-white rounded-lg p-3 font-semibold">Save Donation</button>
    </form>
    <a href="index.php" class="inline-block mt-4 text-brand">← Back</a>
  </div>
</body>
</html>
