<?php
require_once __DIR__ . '/db.php';

$donation = null;
$id = (int)($_GET['id'] ?? 0);
if ($pdo instanceof PDO && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM donations WHERE id=:id');
    $stmt->execute([':id' => $id]);
    $donation = $stmt->fetch();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt</title>
  <link rel="stylesheet" href="assets/css/tailwind.min.css">
</head>
<body class="bg-bgsoft min-h-screen p-4">
  <div class="max-w-xl mx-auto bg-white rounded-2xl p-5 shadow-soft">
    <h1 class="text-2xl font-bold text-brand">Donation Receipt</h1>
    <?php if ($donation): ?>
      <p class="mt-3"><b>Receipt ID:</b> #<?= (int)$donation['id'] ?></p>
      <p><b>Name:</b> <?= h($donation['name']) ?></p>
      <p><b>Organization:</b> <?= h($donation['organization']) ?></p>
      <p><b>Amount:</b> <?= format_amount((float)$donation['amount']) ?></p>
      <p><b>Date:</b> <?= h($donation['created_at']) ?></p>
      <div class="mt-4 flex gap-2">
        <button onclick="window.print()" class="bg-brand text-white rounded-lg px-4 py-2">Print Receipt</button>
        <a href="index.php?success=1" class="border rounded-lg px-4 py-2">Go Dashboard</a>
      </div>
    <?php else: ?>
      <p class="text-red-700 mt-3">Receipt not found.</p>
      <a href="index.php" class="text-brand">Back</a>
    <?php endif; ?>
  </div>
</body>
</html>
