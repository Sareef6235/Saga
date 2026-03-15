<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/ui.php';

$donors = [];
$topDonors = [];
$goal = 1000000;
$totalCollected = 0;
$progressPercent = 0;
$remainingAmount = $goal;
$dailyLabels = [];
$dailyValues = [];
$topLabels = [];
$topValues = [];

if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT id, name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
    $donors = $stmt->fetchAll();
    $topDonors = array_slice($donors, 0, 3);
    $goal = get_goal($pdo);
    $totalCollected = get_total_collected($pdo);
    $remainingAmount = max(0, $goal - $totalCollected);
    $progressPercent = $goal > 0 ? min(100, ($totalCollected / $goal) * 100) : 0;

    $dailyRows = $pdo->query("SELECT DATE(created_at) AS d, SUM(amount) AS t FROM donations GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC")->fetchAll();
    foreach ($dailyRows as $d) {
        $dailyLabels[] = $d['d'];
        $dailyValues[] = (float)$d['t'];
    }

    $topDonorRows = $pdo->query('SELECT name, amount FROM donations ORDER BY amount DESC LIMIT 5')->fetchAll();
    foreach ($topDonorRows as $row) {
        $topLabels[] = $row['name'];
        $topValues[] = (float)$row['amount'];
    }
}

$successMessage = isset($_GET['success']) ? 'സംഭാവന വിജയകരമായി ചേർത്തിരിക്കുന്നു.' : '';
$medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
$themeClass = app_theme_class();
?>
<!DOCTYPE html>
<html lang="ml" class="<?= h($themeClass) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#059669">
    <link rel="manifest" href="manifest.json">
    <title>കായകുളം ദർസിലേക്ക് ഒരു സംഭാവന</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { brand: '#059669', accent: '#D4A017', bgsoft: '#F1F5F9' },
                    boxShadow: { soft: '0 10px 30px -12px rgba(0,0,0,0.25)' }
                }
            }
        };
    </script>
    <style>
        .islamic-pattern{background-image:radial-gradient(circle at 1px 1px,rgba(212,160,23,.24) 1px,transparent 0);background-size:24px 24px;}
    </style>
</head>
<body class="bg-bgsoft dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen pb-28 transition-colors duration-300">


<?php render_site_header('കായകുളം ദർസിലേക്ക് ഒരു സംഭാവന', 'Kayamkulam Dars Contribution'); ?>

<main class="max-w-5xl mx-auto px-4 -mt-3 space-y-5">
    <?php if (!empty($dbError)): ?><div class="bg-red-100 border border-red-200 text-red-700 rounded-xl p-3 text-sm"><?= h($dbError) ?></div><?php endif; ?>
    <?php if ($successMessage): ?><div id="successBanner" class="bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl p-3 text-sm"><?= h($successMessage) ?></div><?php endif; ?>

    <section class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-soft">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">Fundraising Goal</h2>
            <span id="progressPercent" class="text-sm font-semibold text-emerald-700 dark:text-emerald-400"><?= number_format($progressPercent, 1) ?>%</span>
        </div>
        <p id="progressText" class="text-sm text-slate-600 dark:text-slate-300 mt-2"><?= format_amount($totalCollected) ?> raised of <?= format_amount($goal) ?></p>
        <p id="remainingText" class="text-xs text-slate-500 dark:text-slate-400 mt-1">Remaining: <?= format_amount($remainingAmount) ?></p>
        <div class="mt-3 h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
            <div id="progressBar" class="h-full bg-gradient-to-r from-brand to-emerald-400 transition-all duration-1000" style="width:<?= number_format($progressPercent, 2) ?>%"></div>
        </div>
    </section>

    <section class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-soft">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">Top 3 Live Leaderboard</h2>
            <a href="donate.php" class="bg-brand hover:bg-emerald-700 text-white px-4 py-2 rounded-full text-sm transition">സംഭാവന ചേർക്കുക</a>
        </div>
        <div id="topLeaderboard" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <?php for ($i = 1; $i <= 3; $i++): $d = $topDonors[$i - 1] ?? null; ?>
                <article class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/80">
                    <p class="text-sm text-slate-500 dark:text-slate-300">Rank <?= $i ?> <?= $medals[$i] ?></p>
                    <p class="font-semibold mt-2"><?= $d ? h($d['name']) : 'Waiting...' ?></p>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?= $d ? h($d['organization']) : '-' ?></p>
                    <p class="text-brand font-bold mt-2"><?= $d ? format_amount($d['amount']) : '₹0.00' ?></p>
                </article>
            <?php endfor; ?>
        </div>
    </section>

    <section class="grid lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-soft"><h2 class="font-semibold mb-3">Donation Growth (Daily)</h2><canvas id="dailyChart" height="225"></canvas></div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-soft"><h2 class="font-semibold mb-3">Top Donors</h2><canvas id="topChart" height="225"></canvas></div>
    </section>

    <section class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-soft">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">All Contributors</h2>
            <a href="leaderboard.php" class="text-sm text-brand font-medium">View full leaderboard</a>
        </div>
        <div id="fullDonorCards" class="space-y-2">
            <?php if ($donors): foreach($donors as $idx => $d): ?>
                <article class="bg-slate-50 dark:bg-slate-800 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">#<?= $idx + 1 ?> <?= h($d['name']) ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?= h($d['organization']) ?> • <?= date('d M Y', strtotime($d['created_at'])) ?></p>
                    </div>
                    <p class="text-brand font-bold"><?= format_amount($d['amount']) ?></p>
                </article>
            <?php endforeach; else: ?>
                <div class="text-center text-slate-500 p-3">No donations yet.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="max-w-5xl mx-auto px-4 py-6 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-soft p-4">
        <p>കായകുളം ദർസ് സംഭാവന ക്യാമ്പെയ്ൻ | Contact: +91 6235 989 198</p>
        <p class="meta mt-1">© 2026 All Rights Reserved | Design by <a class="text-brand font-medium" href="https://mmhnu.online/" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p>
    </div>
</footer>

<nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t dark:border-slate-700 shadow-[0_-4px_20px_rgba(0,0,0,.08)]">
    <div class="max-w-5xl mx-auto grid grid-cols-4 text-center text-xs">
        <a class="py-3 text-brand font-semibold" href="index.php">🏠<br>Home</a>
        <a class="py-3 hover:text-brand" href="donate.php">💰<br>Donate</a>
        <a class="py-3 hover:text-brand" href="leaderboard.php">🏆<br>Leaderboard</a>
        <a class="py-3 hover:text-brand" href="profile.php">👤<br>Profile</a>
    </div>
</nav>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js');
}

async function refreshLeaderboard() {
    const res = await fetch('ajax/leaderboard.php');
    const data = await res.json();
    if (data.success) {
        document.getElementById('topLeaderboard').innerHTML = data.top3_html;
        document.getElementById('fullDonorCards').innerHTML = data.card_html;
    }
}
async function refreshProgress() {
    const res = await fetch('ajax/progress.php');
    const data = await res.json();
    if (data.success) {
        document.getElementById('progressText').textContent = data.text;
        document.getElementById('remainingText').textContent = 'Remaining: ' + data.remaining;
        document.getElementById('progressPercent').textContent = data.percent + '%';
        document.getElementById('progressBar').style.width = data.percent + '%';
    }
}
setInterval(() => { refreshLeaderboard(); refreshProgress(); }, 5000);
setTimeout(() => { const banner = document.getElementById('successBanner'); if (banner) banner.remove(); }, 3500);

const dailyLabels = <?= json_encode($dailyLabels) ?>;
const dailyValues = <?= json_encode($dailyValues) ?>;
const topLabels = <?= json_encode($topLabels) ?>;
const topValues = <?= json_encode($topValues) ?>;
new Chart(document.getElementById('dailyChart'), {type:'line',data:{labels:dailyLabels,datasets:[{label:'Daily Donations',data:dailyValues,borderColor:'#059669',backgroundColor:'rgba(5,150,105,.14)',fill:true,tension:.3}]}});
new Chart(document.getElementById('topChart'), {type:'bar',data:{labels:topLabels,datasets:[{label:'Top Donors',data:topValues,backgroundColor:'#D4A017'}]}});
</script>
</body>
</html>
