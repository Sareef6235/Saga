<?php
require_once __DIR__ . '/../db.php';
if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username=:username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials';
}
?><!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-[#F1F5F9] min-h-screen flex items-center justify-center p-4"><form method="post" class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 space-y-3"><h1 class="text-xl font-bold text-emerald-700">Admin Login</h1><?php if($error): ?><p class="text-red-600 text-sm"><?= h($error) ?></p><?php endif; ?><input class="w-full border p-2 rounded" name="username" placeholder="Username" required><input class="w-full border p-2 rounded" type="password" name="password" placeholder="Password" required><button class="w-full bg-emerald-600 text-white py-2 rounded">Login</button><p class="text-xs text-slate-500">Default: admin / admin123</p></form></body></html>
