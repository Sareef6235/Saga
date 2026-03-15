<?php
require_once __DIR__ . '/db.php';

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
    $stmt = $pdo->query('SELECT id, name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at DESC LIMIT 100');
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
    <link rel="stylesheet" href="assets/css/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .islamic-pattern{background-image:radial-gradient(circle at 1px 1px,rgba(212,160,23,.24) 1px,transparent 0);background-size:24px 24px;}
        .card{transition:all .3s ease;}
        .card:hover{transform:translateY(-3px);}
    </style>
</head>
<body class="bg-bgsoft dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen pb-32 transition-colors duration-300">
<header class="islamic-pattern bg-gradient-to-r from-emerald-700 to-brand text-white shadow-soft rounded-b-3xl">
    <div class="max-w-5xl mx-auto px-4 py-7">
        <div class="flex justify-between items-center gap-2">
            <div>
                <h1 class="text-2xl md:text-4xl font-bold">കായകുളം ദർസിലേക്ക് ഒരു സംഭാവന</h1>
                <p class="text-emerald-100 mt-1 text-sm">Mosque / Madrasa Fund Tracker</p>
            </div>
            <button id="themeToggle" class="bg-white/15 hover:bg-white/25 px-3 py-2 rounded-xl text-sm">🌓</button>
        </div>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 -mt-3 space-y-5">
    <?php if (!empty($dbError)): ?><div class="bg-red-100 border border-red-200 text-red-700 rounded-xl p-3 text-sm"><?= h($dbError) ?></div><?php endif; ?>
    <?php if ($successMessage): ?><div id="successBanner" class="bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl p-3 text-sm"><?= h($successMessage) ?></div><?php endif; ?>

    <section class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-soft card">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">Fundraising Goal</h2>
            <span id="progressPercent" class="text-sm font-semibold text-emerald-700 dark:text-emerald-400"><?= number_format($progressPercent, 1) ?>%</span>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2"><span id="raisedAmountValue"><?= format_amount($totalCollected) ?></span> raised of <span id="goalAmountValue"><?= format_amount($goal) ?></span></p>
        <p id="remainingText" class="text-xs text-slate-500 dark:text-slate-400 mt-1">Remaining: <?= format_amount($remainingAmount) ?></p>
        <div class="mt-3 h-3 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
            <div id="progressBar" class="h-full bg-gradient-to-r from-brand to-emerald-400 transition-all duration-1000" style="width:<?= number_format($progressPercent, 2) ?>%"></div>
        </div>
    </section>

    <section class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-soft card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">Top 3 Live Leaderboard</h2>
            <a href="donate.php" class="bg-brand hover:bg-emerald-700 text-white px-4 py-2 rounded-full text-sm transition">സംഭാവന ചേർക്കുക</a>
        </div>

        <div id="topLeaderboard" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
            <?php
            $podiumClasses = [
                0 => 'bg-yellow-100 dark:bg-yellow-950/40 border border-yellow-300 dark:border-yellow-800',
                1 => 'bg-gray-200 dark:bg-slate-700 border border-gray-300 dark:border-slate-600',
                2 => 'bg-orange-200 dark:bg-orange-950/40 border border-orange-300 dark:border-orange-800',
            ];
            $medals = ['🥇', '🥈', '🥉'];
            for ($i = 0; $i < 3; $i++):
                $d = $topDonors[$i] ?? null;
            ?>
            <div class="rounded-xl p-4 shadow card <?= $podiumClasses[$i] ?>">
                <p class="text-3xl"><?= $medals[$i] ?></p>
                <p class="font-bold mt-2"><?= h($d['name'] ?? 'Waiting...') ?></p>
                <p class="text-brand font-semibold"><?= format_amount((float)($d['amount'] ?? 0)) ?></p>
            </div>
            <?php endfor; ?>
        </div>
    </section>

    <section class="grid lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-soft card"><h2 class="font-semibold mb-3">Donation Growth (Daily)</h2><canvas id="dailyChart" height="225"></canvas></div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-soft card"><h2 class="font-semibold mb-3">Top Donors</h2><canvas id="topChart" height="225"></canvas></div>
    </section>

    <section class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-soft card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">All Contributors</h2>
            <a href="leaderboard.php" class="text-sm text-brand font-medium">View full leaderboard</a>
        </div>
        <div id="fullDonorCards" class="space-y-2">
            <?php if ($donors): foreach($donors as $idx => $d): ?>
                <article class="bg-slate-50 dark:bg-slate-800 rounded-xl p-3 flex items-center justify-between card">
                    <div>
                        <p class="font-semibold">#<?= $idx + 1 ?> <?= h($d['name']) ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?= h($d['organization']) ?> • <?= date('d M Y', strtotime($d['created_at'])) ?></p>
                    </div>
                    <p class="text-brand font-bold"><?= format_amount((float)$d['amount']) ?></p>
                </article>
            <?php endforeach; else: ?>
                <div class="text-center text-slate-500 p-3">No donations yet.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="max-w-5xl mx-auto px-4 py-6 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-soft p-4 card">
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

document.getElementById('themeToggle').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000`;
});

function animateCounter(id, value){
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const step = value / 50;
    const interval = setInterval(() => {
        start += step;
        if (start >= value) {
            start = value;
            clearInterval(interval);
        }
        el.innerText = '₹' + Math.floor(start).toLocaleString('en-IN') + '.00';
    }, 20);
}

function showNotification(msg){
    const div = document.createElement('div');
    div.className = 'fixed top-5 right-5 bg-emerald-600 text-white px-4 py-2 rounded-lg shadow z-50';
    div.innerText = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}

let lastTopDonation = <?= (float)($topDonors[0]['amount'] ?? 0) ?>;

async function refreshLeaderboard() {
    const res = await fetch('ajax/leaderboard.php');
    const data = await res.json();
    if (data.success) {
        document.getElementById('topLeaderboard').innerHTML = data.top3_html;
        document.getElementById('fullDonorCards').innerHTML = data.card_html;
        if (data.top_amount > lastTopDonation) {
            showNotification('New donation received!');
        }
        lastTopDonation = data.top_amount;
    }
}

async function refreshProgress() {
    const res = await fetch('ajax/progress.php');
    const data = await res.json();
    if (data.success) {
        document.getElementById('remainingText').textContent = 'Remaining: ' + data.remaining;
        document.getElementById('progressPercent').textContent = data.percent + '%';
        document.getElementById('progressBar').style.width = data.percent + '%';
        animateCounter('raisedAmountValue', data.total);
        animateCounter('goalAmountValue', data.goal);
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
