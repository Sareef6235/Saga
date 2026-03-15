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
    $exportRows = $stmt->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="donations.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Organization', 'Phone', 'Amount', 'Date']);
    foreach ($exportRows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_goal']) && $pdo instanceof PDO) {
    $goal = (float)($_POST['goal_amount'] ?? 0);
    if ($goal > 0) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value=:value WHERE setting_key='fundraising_goal'");
        $stmt->execute([':value' => number_format($goal, 2, '.', '')]);
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
    $countRow = $pdo->query('SELECT COUNT(*) AS c FROM donations')->fetch();
    $totalContributors = (int)($countRow['c'] ?? 0);
    $topRow = $pdo->query('SELECT name, amount FROM donations ORDER BY amount DESC LIMIT 1')->fetch();
    if ($topRow) {
        $topDonor = $topRow['name'] . ' (' . format_amount($topRow['amount']) . ')';
    }
    $goal = get_goal($pdo);
    $rows = $pdo->query('SELECT id, name, organization, phone, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC')->fetchAll();
}
$themeClass = app_theme_class();
?>
<!doctype html>
<html lang="en" class="<?= h($themeClass) ?>"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={darkMode:'class'}</script>
</head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 text-slate-800 dark:text-slate-100 p-4 pb-28">
<div class="max-w-6xl mx-auto space-y-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow flex justify-between items-center">
        <h1 class="text-xl font-bold text-emerald-700 dark:text-emerald-400">Admin Dashboard</h1>
        <div class="flex gap-2"><button id="themeToggle">🌓</button><a class="text-sm text-red-600" href="?logout=1">Logout</a></div>
    </div>

    <div class="grid md:grid-cols-3 gap-3">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow">Total Donations<br><b><?= format_amount($totalDonations) ?></b></div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow">Total Contributors<br><b><?= $totalContributors ?></b></div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow">Top Donor<br><b><?= h($topDonor) ?></b></div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow">
        <form method="post" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="text-sm">Fundraising Goal</label>
                <input class="border dark:border-slate-700 bg-white dark:bg-slate-800 rounded p-2" name="goal_amount" type="number" step="0.01" min="1" value="<?= h($goal) ?>">
            </div>
            <button name="update_goal" class="bg-emerald-600 text-white px-4 py-2 rounded">Update Goal</button>
            <a href="dashboard.php?export=1" class="bg-amber-600 text-white px-4 py-2 rounded">Export CSV</a>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow overflow-x-auto">
        <form method="post">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800">
                        <th class="p-2"><input type="checkbox" id="selectAll"></th>
                        <th class="p-2 text-left">Rank</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Organization</th>
                        <th class="p-2 text-left">Phone</th>
                        <th class="p-2 text-right">Amount</th>
                        <th class="p-2 text-left">Date</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $idx => $row): ?>
                        <tr class="border-b dark:border-slate-700">
                            <td class="p-2"><input type="checkbox" name="donation_ids[]" value="<?= $row['id'] ?>"></td>
                            <td class="p-2">#<?= $idx + 1 ?></td>
                            <td class="p-2"><?= h($row['name']) ?></td>
                            <td class="p-2"><?= h($row['organization']) ?></td>
                            <td class="p-2"><?= h($row['phone']) ?></td>
                            <td class="p-2 text-right font-semibold text-emerald-700"><?= format_amount($row['amount']) ?></td>
                            <td class="p-2"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td class="p-2">
                                <a class="text-blue-600" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                                |
                                <a class="text-red-600" href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this donation?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-3 flex gap-2">
                <button name="delete_selected" class="bg-red-600 text-white px-3 py-2 rounded" onclick="return confirm('Delete selected donations?')">Delete Selected</button>
                <button name="delete_all" class="bg-red-800 text-white px-3 py-2 rounded" onclick="return confirm('Delete all donations?')">Delete All</button>
            </div>
        </form>
    </div>
</div>

<footer class="max-w-6xl mx-auto mt-4 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4">
        <p>കായകുളം ദർസ് സംഭാവന ക്യാമ്പെയ്ൻ | Contact: +91 6235 989 198</p>
        <p class="meta mt-1">© 2026 All Rights Reserved | Design by <a class="text-brand font-medium" href="https://mmhnu.online/" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p>
    </div>
</footer>

<script>
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="donation_ids[]"]').forEach(cb => cb.checked = this.checked);
});
document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});
</script>
</body></html>
