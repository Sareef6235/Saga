<?php
require_once __DIR__ . '/db.php';
$donors = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
    $donors = $stmt->fetchAll();
}
$themeClass = app_theme_class();
?>
<!doctype html>
<html lang="ml" class="<?= h($themeClass) ?>"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Leaderboard</title><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={darkMode:'class'}</script></head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 text-slate-800 dark:text-slate-100 pb-24">
<main class="max-w-4xl mx-auto p-4 space-y-4">
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4"><div class="flex justify-between"><h1 class="text-xl font-bold text-emerald-700 dark:text-emerald-400">🏆 Full Leaderboard</h1><button id="themeToggle">🌓</button></div><p class="text-xs text-slate-500 dark:text-slate-400">Auto updates every 5 seconds</p></div>
<div id="leaderboardCards" class="grid gap-2">
<?php if($donors): foreach($donors as $i=>$d): ?><article class="bg-white dark:bg-slate-900 rounded-xl shadow p-3 flex justify-between"><div><p class="font-semibold">#<?= $i+1 ?> <?= h($d['name']) ?></p><p class="text-xs text-slate-500 dark:text-slate-400"><?= h($d['organization']) ?> • <?= date('d M Y', strtotime($d['created_at'])) ?></p></div><p class="font-bold text-emerald-700"><?= format_amount($d['amount']) ?></p></article><?php endforeach; else: ?><div class="bg-white dark:bg-slate-900 rounded-xl p-3">No data</div><?php endif; ?>
</div>
</main>
<footer class="max-w-xl mx-auto px-4 pb-4 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4">
        <p>കായകുളം ദർസ് സംഭാവന ക്യാമ്പെയ്ൻ | Contact: +91 6235 989 198</p>
        <p class="meta mt-1">© 2026 All Rights Reserved | Design by <a class="text-brand font-medium" href="https://mmhnu.online/" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p>
    </div>
</footer>

<nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t dark:border-slate-700"><div class="max-w-4xl mx-auto grid grid-cols-4 text-center text-xs"><a class="py-3" href="index.php">🏠<br>Home</a><a class="py-3" href="donate.php">💰<br>Donate</a><a class="py-3 text-emerald-700 font-semibold" href="leaderboard.php">🏆<br>Leaderboard</a><a class="py-3" href="profile.php">👤<br>Profile</a></div></nav>
<script>
setInterval(async()=>{const r=await fetch('ajax/leaderboard.php?cards_only=1');const d=await r.json();if(d.success){document.getElementById('leaderboardCards').innerHTML=d.card_html;}},5000);
document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});
</script>
</body></html>
