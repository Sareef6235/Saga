<?php
require_once __DIR__ . '/db.php';

$donors = [];
$topDonors = [];
$goal = 1000000;
$totalCollected = 0;
$progressPercent = 0;

if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT id, name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
    $donors = $stmt->fetchAll();
    $topDonors = array_slice($donors, 0, 3);
    $goal = get_goal($pdo);
    $totalCollected = get_total_collected($pdo);
    $progressPercent = $goal > 0 ? min(100, ($totalCollected / $goal) * 100) : 0;
}

$successMessage = isset($_GET['success']) ? 'സംഭാവന വിജയകരമായി ചേർത്തിരിക്കുന്നു.' : '';
$medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
?>
<!DOCTYPE html>
<html lang="ml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>കായകുളം ദർസിലേക്ക് ഒരു സംഭാവന</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand:'#059669', accent:'#D4A017', bgsoft:'#F1F5F9' }, boxShadow:{soft:'0 10px 28px -12px rgba(15,23,42,.25)'} } } };
    </script>
    <style>
        .islamic-pattern{background-image:radial-gradient(circle at 1px 1px,rgba(212,160,23,.2) 1px,transparent 0);background-size:24px 24px;}
    </style>
</head>
<body class="bg-bgsoft text-slate-800 min-h-screen pb-24">
<header class="islamic-pattern bg-gradient-to-r from-emerald-700 to-brand text-white shadow-soft rounded-b-3xl">
    <div class="max-w-5xl mx-auto px-4 py-8 text-center">
        <h1 class="text-2xl md:text-4xl font-bold">കായകുളം ദർസിലേക്ക് ഒരു സംഭാവന</h1>
        <p class="text-emerald-100 mt-2">Kayamkulam Dars Contribution</p>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 -mt-4 space-y-5">
    <?php if (!empty($dbError)): ?><div class="bg-red-100 text-red-700 border border-red-200 rounded-xl p-3 text-sm"><?= h($dbError) ?></div><?php endif; ?>
    <?php if ($successMessage): ?><div id="successBanner" class="bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl p-3 text-sm"><?= h($successMessage) ?></div><?php endif; ?>

    <section class="bg-white rounded-2xl p-5 shadow-soft">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-lg text-emerald-700">Fundraising Goal</h2>
            <span id="progressPercent" class="text-sm font-semibold text-emerald-700"><?= number_format($progressPercent, 1) ?>%</span>
        </div>
        <p id="progressText" class="text-sm text-slate-600 mt-2"><?= format_amount($totalCollected) ?> raised of <?= format_amount($goal) ?></p>
        <div class="mt-3 h-3 bg-slate-100 rounded-full overflow-hidden"><div id="progressBar" class="h-full bg-gradient-to-r from-brand to-emerald-400 transition-all duration-1000" style="width:<?= number_format($progressPercent, 2) ?>%"></div></div>
    </section>

    <section class="bg-white rounded-2xl p-5 shadow-soft">
        <div class="flex items-center justify-between mb-4"><h2 class="font-bold text-lg text-emerald-700">Top 3 Live Leaderboard</h2><a href="donate.php" class="bg-brand text-white px-4 py-2 rounded-full text-sm">സംഭാവന ചേർക്കുക</a></div>
        <div id="topLeaderboard" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <?php for($i=1;$i<=3;$i++): $d=$topDonors[$i-1]??null; ?>
                <article class="rounded-xl border p-4 bg-slate-50">
                    <p class="text-sm text-slate-500">Rank <?= $i ?> <?= $medals[$i] ?></p>
                    <p class="font-semibold mt-2"><?= $d ? h($d['name']) : 'Waiting...' ?></p>
                    <p class="text-sm text-slate-500"><?= $d ? h($d['organization']) : '-' ?></p>
                    <p class="text-brand font-bold mt-2"><?= $d ? format_amount($d['amount']) : '₹0.00' ?></p>
                </article>
            <?php endfor; ?>
        </div>
    </section>

    <section class="bg-white rounded-2xl p-5 shadow-soft">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-emerald-700">All Contributors</h2>
            <a href="leaderboard.php" class="text-sm text-brand font-medium">View full leaderboard</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="bg-slate-100"><th class="p-2 text-left">Rank</th><th class="p-2 text-left">Name</th><th class="p-2 text-left">Organization</th><th class="p-2 text-right">Amount</th><th class="p-2 text-left">Date</th></tr></thead>
                <tbody id="fullDonorList">
                <?php if ($donors): foreach($donors as $idx=>$d): ?>
                    <tr class="border-b"><td class="p-2">#<?= $idx+1 ?></td><td class="p-2"><?= h($d['name']) ?></td><td class="p-2"><?= h($d['organization']) ?></td><td class="p-2 text-right text-brand font-semibold"><?= format_amount($d['amount']) ?></td><td class="p-2"><?= date('d M Y', strtotime($d['created_at'])) ?></td></tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="p-3 text-center text-slate-500">No donations yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-[0_-4px_20px_rgba(0,0,0,.08)]">
    <div class="max-w-5xl mx-auto grid grid-cols-4 text-center text-xs">
        <a class="py-3 text-brand font-semibold" href="index.php">🏠<br>Home</a>
        <a class="py-3" href="donate.php">💰<br>Donate</a>
        <a class="py-3" href="leaderboard.php">🏆<br>Leaderboard</a>
        <a class="py-3" href="admin/login.php">👤<br>Profile</a>
    </div>
</nav>

<script>
async function refreshLeaderboard(){
  const res = await fetch('ajax/leaderboard.php');
  const data = await res.json();
  if(data.success){
    document.getElementById('topLeaderboard').innerHTML = data.top3_html;
    document.getElementById('fullDonorList').innerHTML = data.list_html;
  }
}
async function refreshProgress(){
  const res = await fetch('ajax/progress.php');
  const data = await res.json();
  if(data.success){
    document.getElementById('progressText').textContent = data.text;
    document.getElementById('progressPercent').textContent = data.percent + '%';
    document.getElementById('progressBar').style.width = data.percent + '%';
  }
}
setInterval(()=>{refreshLeaderboard();refreshProgress();},5000);
setTimeout(()=>{const b=document.getElementById('successBanner');if(b){b.remove();}},3500);
</script>
</body>
</html>
