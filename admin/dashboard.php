<?php
require_once __DIR__ . '/../db.php';
require_admin_login();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (isset($_GET['export']) && $pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT name, organization, phone, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
    $rows = $stmt->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="donations.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Organization', 'Phone', 'Amount', 'Date']);
    foreach ($rows as $row) { fputcsv($out, $row); }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_goal']) && $pdo instanceof PDO) {
    $goal = (float)($_POST['goal_amount'] ?? 0);
    if ($goal > 0) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value=:v WHERE setting_key='fundraising_goal'");
        $stmt->execute([':v' => number_format($goal, 2, '.', '')]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected']) && $pdo instanceof PDO) {
    $ids = array_filter($_POST['donation_ids'] ?? [], 'is_numeric');
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM donations WHERE id IN ($placeholders)");
        $stmt->execute(array_values($ids));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all']) && $pdo instanceof PDO) {
    $pdo->exec('DELETE FROM donations');
}

$totalDonations = 0;
$totalContributors = 0;
$topDonor = '-';
$goal = 1000000;
$rows = [];

if ($pdo instanceof PDO) {
    $totalDonations = get_total_collected($pdo);
    $stmt = $pdo->query('SELECT COUNT(*) c FROM donations');
    $totalContributors = (int)$stmt->fetch()['c'];
    $stmt = $pdo->query('SELECT name, amount FROM donations ORDER BY amount DESC LIMIT 1');
    $td = $stmt->fetch();
    if ($td) { $topDonor = $td['name'] . ' (' . format_amount($td['amount']) . ')'; }
    $goal = get_goal($pdo);
    $stmt = $pdo->query('SELECT id, name, organization, phone, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
    $rows = $stmt->fetchAll();
}
?><!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-[#F1F5F9] p-4">
<div class="max-w-6xl mx-auto space-y-4">
<div class="bg-white rounded-xl p-4 shadow flex items-center justify-between"><h1 class="text-xl font-bold text-emerald-700">Admin Dashboard</h1><a class="text-sm text-red-600" href="?logout=1">Logout</a></div>
<div class="grid md:grid-cols-3 gap-3"><div class="bg-white p-4 rounded-xl shadow">Total Donations<br><b><?= format_amount($totalDonations) ?></b></div><div class="bg-white p-4 rounded-xl shadow">Total Contributors<br><b><?= $totalContributors ?></b></div><div class="bg-white p-4 rounded-xl shadow">Top Donor<br><b><?= h($topDonor) ?></b></div></div>
<div class="bg-white rounded-xl p-4 shadow"><form method="post" class="flex gap-2 items-end"><div><label class="text-sm">Fundraising Goal</label><input class="border rounded p-2" name="goal_amount" type="number" step="0.01" min="1" value="<?= h($goal) ?>"></div><button name="update_goal" class="bg-emerald-600 text-white px-4 py-2 rounded">Update Goal</button><a href="edit.php" class="px-4 py-2 rounded bg-slate-700 text-white">Edit Amounts</a><a href="dashboard.php?export=1" class="px-4 py-2 rounded bg-amber-600 text-white">Export CSV</a></form></div>

<div class="bg-white rounded-xl p-4 shadow overflow-x-auto">
<form method="post" id="bulkForm">
<table class="w-full text-sm"><thead><tr class="bg-slate-100"><th class="p-2"><input type="checkbox" id="selectAll"></th><th class="p-2 text-left">Rank</th><th class="p-2 text-left">Name</th><th class="p-2 text-left">Org</th><th class="p-2 text-left">Phone</th><th class="p-2 text-right">Amount</th><th class="p-2 text-left">Date</th><th class="p-2 text-left">Action</th></tr></thead><tbody><?php foreach($rows as $i=>$r): ?><tr class="border-b"><td class="p-2"><input type="checkbox" name="donation_ids[]" value="<?= $r['id'] ?>"></td><td class="p-2">#<?= $i+1 ?></td><td class="p-2"><?= h($r['name']) ?></td><td class="p-2"><?= h($r['organization']) ?></td><td class="p-2"><?= h($r['phone']) ?></td><td class="p-2 text-right"><?= format_amount($r['amount']) ?></td><td class="p-2"><?= date('d M Y', strtotime($r['created_at'])) ?></td><td class="p-2"><a class="text-blue-600" href="edit.php?id=<?= $r['id'] ?>">Edit</a> | <a class="text-red-600" href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete this donation?')">Delete</a></td></tr><?php endforeach; ?></tbody></table>
<div class="mt-3 flex gap-2"><button name="delete_selected" class="bg-red-600 text-white px-3 py-2 rounded" onclick="return confirm('Delete selected donations?')">Delete Selected</button><button name="delete_all" class="bg-red-800 text-white px-3 py-2 rounded" onclick="return confirm('Delete ALL donations?')">Delete All</button></div>
</form>
</div>
</div>
<script>
document.getElementById('selectAll').addEventListener('change',function(){document.querySelectorAll('input[name="donation_ids[]"]').forEach(c=>c.checked=this.checked);});
</script>
</body></html>
