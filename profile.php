<?php require_once __DIR__ . '/db.php'; $themeClass = app_theme_class(); ?>
<!doctype html>
<html lang="ml" class="<?= h($themeClass) ?>"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Profile</title><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={darkMode:'class'}</script></head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen pb-24">
<main class="max-w-3xl mx-auto p-4 space-y-4">
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5"><div class="flex justify-between"><h1 class="text-xl font-bold text-emerald-700 dark:text-emerald-400">👤 Campaign Profile</h1><button id="themeToggle">🌓</button></div><p class="text-sm mt-2">Support the Dars initiative and track live ranking progress. This app supports PWA installation and offline shell loading.</p></div>
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5"><h2 class="font-semibold text-emerald-700 dark:text-emerald-400">Quick Links</h2><div class="mt-3 grid md:grid-cols-2 gap-2"><a href="donate.php" class="p-3 rounded-xl bg-emerald-600 text-white text-center">Make a Contribution</a><a href="admin/login.php" class="p-3 rounded-xl bg-slate-800 text-white text-center">Admin Login</a></div></div>
</main>
<nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 border-t dark:border-slate-700"><div class="max-w-3xl mx-auto grid grid-cols-4 text-center text-xs"><a class="py-3" href="index.php">🏠<br>Home</a><a class="py-3" href="donate.php">💰<br>Donate</a><a class="py-3" href="leaderboard.php">🏆<br>Leaderboard</a><a class="py-3 text-emerald-700 font-semibold" href="profile.php">👤<br>Profile</a></div></nav>
<script>document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});</script>
</body></html>
