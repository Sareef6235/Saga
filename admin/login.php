<?php
require_once __DIR__ . '/../db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username=:username LIMIT 1');
    $stmt->execute([':username' => $usernameInput]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($passwordInput, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid credentials';
}
$themeClass = app_theme_class();
?>
<!doctype html>
<html lang="en" class="<?= h($themeClass) ?>"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={darkMode:'class'}</script>
</head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 min-h-screen flex items-center justify-center p-4 text-slate-800 dark:text-slate-100">
<form method="post" class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-3">
    <div class="flex justify-between items-center"><h1 class="text-xl font-bold text-emerald-700 dark:text-emerald-400">Admin Login</h1><button type="button" id="themeToggle">🌓</button></div>
    <?php if ($error): ?><p class="text-red-600 text-sm"><?= h($error) ?></p><?php endif; ?>
    <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 p-3 rounded-lg" name="username" placeholder="Username" required>
    <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 p-3 rounded-lg" type="password" name="password" placeholder="Password" required>
    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg">Login</button>
    <p class="text-xs text-slate-500">Default: admin / admin123</p>
</form>
<footer class="max-w-lg mx-auto mt-4 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4">
        <p>കായകുളം ദർസ് സംഭാവന ക്യാമ്പെയ്ൻ | Contact: +91 6235 989 198</p>
        <p class="meta mt-1">© 2026 All Rights Reserved | Design by <a class="text-brand font-medium" href="https://mmhnu.online/" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p>
    </div>
</footer>

<script>document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});</script>
</body></html>
