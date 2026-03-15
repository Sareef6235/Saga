<?php
require_once __DIR__ . '/db.php';
$donors = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at ASC');
    $donors = $stmt->fetchAll();
}
?><!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Leaderboard</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-[#F1F5F9] pb-24"><main class="max-w-4xl mx-auto p-4"><div class="bg-white rounded-2xl shadow p-4"><h1 class="text-xl font-bold text-emerald-700 mb-3">🏆 Full Leaderboard</h1><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-slate-100"><th class="p-2 text-left">Rank</th><th class="p-2 text-left">Name</th><th class="p-2 text-left">Organization</th><th class="p-2 text-right">Amount</th><th class="p-2 text-left">Date</th></tr></thead><tbody id="leaderboardRows"><?php if($donors): foreach($donors as $i=>$d): ?><tr class="border-b"><td class="p-2">#<?= $i+1 ?></td><td class="p-2"><?= h($d['name']) ?></td><td class="p-2"><?= h($d['organization']) ?></td><td class="p-2 text-right text-emerald-700 font-semibold"><?= format_amount($d['amount']) ?></td><td class="p-2"><?= date('d M Y', strtotime($d['created_at'])) ?></td></tr><?php endforeach; else: ?><tr><td class="p-3 text-center" colspan="5">No data</td></tr><?php endif; ?></tbody></table></div></div></main>
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t"><div class="max-w-4xl mx-auto grid grid-cols-4 text-center text-xs"><a class="py-3" href="index.php">🏠<br>Home</a><a class="py-3" href="donate.php">💰<br>Donate</a><a class="py-3 text-emerald-700 font-semibold" href="leaderboard.php">🏆<br>Leaderboard</a><a class="py-3" href="admin/login.php">👤<br>Profile</a></div></nav>
<script>
setInterval(async()=>{const r=await fetch('ajax/leaderboard.php?list_only=1');const d=await r.json();if(d.success){document.getElementById('leaderboardRows').innerHTML=d.list_html;}},5000);
</script></body></html>
