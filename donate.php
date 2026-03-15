<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/ui.php';

$errors = [];
$messages = [];
$old = [
    'name' => '',
    'organization' => '',
    'phone' => '',
    'amount' => '',
];

$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'admin_login') {
    $adminKey = trim($_POST['admin_key'] ?? '');
    $expected = getenv('ADMIN_EDIT_KEY') ?: 'admin123';

    if ($adminKey !== '' && hash_equals($expected, $adminKey)) {
        $_SESSION['is_admin'] = true;
        $messages[] = 'Admin mode enabled.';
    } else {
        $errors[] = 'Invalid admin key.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'admin_logout') {
    $_SESSION['is_admin'] = false;
    $messages[] = 'Admin mode disabled.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['organization'] = trim($_POST['organization'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['amount'] = trim($_POST['amount'] ?? '');
    $donorPassword = trim($_POST['donor_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($old['name'] === '') {
        $errors[] = 'Name is required.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9]{10,15}$/', $old['phone'])) {
        $errors[] = 'Phone must be 10-15 digits.';
    }
    if (!is_numeric($old['amount']) || (float) $old['amount'] <= 0) {
        $errors[] = 'Amount must be greater than 0.';
    }
    if (mb_strlen($donorPassword) < 4) {
        $errors[] = 'Personal password must be at least 4 characters.';
    }
    if ($donorPassword !== $confirmPassword) {
        $errors[] = 'Password and confirm password must match.';
    }
    if (!($pdo instanceof PDO)) {
        $errors[] = $dbError ?: 'Database not configured.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO donations (name, organization, phone, amount, donor_password_hash) VALUES (:name, :organization, :phone, :amount, :donor_password_hash)');
        $stmt->execute([
            ':name' => $old['name'],
            ':organization' => $old['organization'],
            ':phone' => $old['phone'],
            ':amount' => number_format((float) $old['amount'], 2, '.', ''),
            ':donor_password_hash' => password_hash($donorPassword, PASSWORD_DEFAULT),
        ]);

        $messages[] = 'Contribution saved successfully.';
        $old = ['name' => '', 'organization' => '', 'phone' => '', 'amount' => ''];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $editPassword = trim($_POST['edit_password'] ?? '');

    if (!($pdo instanceof PDO)) {
        $errors[] = $dbError ?: 'Database not configured.';
    } else {
        $stmt = $pdo->prepare('SELECT id, donor_password_hash FROM donations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $errors[] = 'Donation not found.';
        }

        if ($name === '') {
            $errors[] = 'Name is required.';
        }
        if ($phone !== '' && !preg_match('/^[0-9]{10,15}$/', $phone)) {
            $errors[] = 'Phone must be 10-15 digits.';
        }
        if (!is_numeric($amount) || (float) $amount <= 0) {
            $errors[] = 'Amount must be greater than 0.';
        }

        if (!is_admin()) {
            if ($editPassword === '') {
                $errors[] = 'Enter personal password to edit this donation.';
            } elseif ($existing && !password_verify($editPassword, (string) $existing['donor_password_hash'])) {
                $errors[] = 'Invalid personal password.';
            }
        }

        if (!$errors && $existing) {
            $update = $pdo->prepare('UPDATE donations SET name = :name, organization = :organization, phone = :phone, amount = :amount WHERE id = :id');
            $update->execute([
                ':name' => $name,
                ':organization' => $organization,
                ':phone' => $phone,
                ':amount' => number_format((float) $amount, 2, '.', ''),
                ':id' => $id,
            ]);
            $messages[] = 'Contribution updated successfully.';
        }
    }
}

if (($pdo instanceof PDO) && isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT id, name, organization, phone, amount FROM donations WHERE id = :id');
    $stmt->execute([':id' => $editId]);
    $editItem = $stmt->fetch() ?: null;
}

$rows = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT id, name, organization, phone, amount FROM donations ORDER BY id DESC');
    $rows = $stmt->fetchAll();
}

$themeClass = app_theme_class();
?>
<!doctype html>
<html lang="ml" class="<?= h($themeClass) ?>">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Donate - DARUL HIDAYA DARS OLD STUDENTS ORGANIZATION</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class'}</script>
</head>
<body class="bg-[#F1F5F9] dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen pb-24">
<?php render_site_header('സംഭാവന ചേർക്കുക', 'Add / Edit Contribution (Single Page)'); ?>
<main class="max-w-4xl mx-auto p-4 space-y-4">
    <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 text-white rounded-2xl p-5 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">സംഭാവന ചേർക്കുക / തിരുത്തുക</h1>
                <p class="text-emerald-100 text-sm">Single page: add + list + edit</p>
            </div>
            <button id="themeToggle" type="button" class="bg-white/20 px-2 py-1 rounded">🌓</button>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm"><ul class="list-disc pl-5"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($messages): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 text-sm"><ul class="list-disc pl-5"><?php foreach ($messages as $m): ?><li><?= h($m) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5">
            <h2 class="font-semibold mb-3">New Contribution</h2>
            <form method="post" id="donateForm" class="space-y-3" novalidate>
                <input type="hidden" name="action" value="create">
                <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="name" placeholder="Name" value="<?= h($old['name']) ?>" required>
                <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="organization" placeholder="Organization Name" value="<?= h($old['organization']) ?>">
                <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="phone" id="phone" placeholder="Phone Number" value="<?= h($old['phone']) ?>">
                <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" type="number" min="1" step="0.01" id="amount" name="amount" placeholder="Donation Amount (₹)" value="<?= h($old['amount']) ?>" required>
                <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" type="password" name="donor_password" placeholder="Personal Password (for future edit)" required>
                <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" type="password" name="confirm_password" placeholder="Confirm Personal Password" required>
                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-semibold transition">Submit Contribution</button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5 space-y-4">
            <h2 class="font-semibold">Admin Access</h2>
            <?php if (!is_admin()): ?>
                <form method="post" class="space-y-2">
                    <input type="hidden" name="action" value="admin_login">
                    <input type="password" name="admin_key" class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" placeholder="Admin key">
                    <button class="w-full rounded-lg bg-amber-600 text-white py-2">Enable Admin Mode</button>
                </form>
            <?php else: ?>
                <p class="text-sm text-amber-600">Admin can edit any row without personal password.</p>
                <form method="post">
                    <input type="hidden" name="action" value="admin_logout">
                    <button class="w-full rounded-lg bg-slate-700 text-white py-2">Disable Admin Mode</button>
                </form>
            <?php endif; ?>

            <?php if ($editItem): ?>
                <div class="border-t dark:border-slate-700 pt-4">
                    <h2 class="font-semibold mb-3">Edit Contribution #<?= (int) $editItem['id'] ?></h2>
                    <form method="post" class="space-y-3">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
                        <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="name" value="<?= h((string) $editItem['name']) ?>" required>
                        <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="organization" value="<?= h((string) $editItem['organization']) ?>">
                        <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" name="phone" value="<?= h((string) $editItem['phone']) ?>">
                        <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" type="number" min="1" step="0.01" name="amount" value="<?= h((string) $editItem['amount']) ?>" required>
                        <?php if (!is_admin()): ?>
                            <input class="w-full border dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg p-3" type="password" name="edit_password" placeholder="Enter personal password to save" required>
                        <?php endif; ?>
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold">Save Changes</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="text-sm text-slate-500">Select any row from table below to edit.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5">
        <h2 class="font-semibold text-emerald-700 dark:text-emerald-400 mb-2">Contribution List</h2>
        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Organization</th>
                        <th class="p-2 text-left">Phone</th>
                        <th class="p-2 text-left">Amount</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr class="border-t dark:border-slate-700">
                            <td class="p-2"><?= h((string) $row['name']) ?></td>
                            <td class="p-2"><?= h((string) $row['organization']) ?></td>
                            <td class="p-2"><?= h((string) $row['phone']) ?></td>
                            <td class="p-2">₹<?= h(number_format((float) $row['amount'], 2)) ?></td>
                            <td class="p-2">
                                <a class="text-emerald-700 font-semibold" href="donate.php?edit=<?= (int) $row['id'] ?>">Edit</a>
                                <?php if (!is_admin()): ?><span class="text-xs text-slate-400"> (password required)</span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-5">
        <h2 class="font-semibold text-emerald-700 dark:text-emerald-400">Online Payment Options</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Use these options after confirming intended amount.</p>
        <div class="grid grid-cols-2 gap-2">
            <button type="button" class="rounded-xl bg-[#D4A017] text-white py-2">UPI Payment</button>
            <button type="button" class="rounded-xl bg-slate-800 text-white py-2">Razorpay Payment</button>
        </div>
    </div>
</main>

<footer class="max-w-4xl mx-auto px-4 pb-4 text-center text-sm text-slate-600 dark:text-slate-300">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4">
        <p>DARUL HIDAYA DARS OLD STUDENTS ORGANIZATION | Contact: +91 6235 989 198</p>
        <p class="meta mt-1">© 2026 All Rights Reserved | Design by <a class="text-brand font-medium" href="https://doso.mmhnu.online" target="_blank" rel="noopener noreferrer">Muhsin Faizy</a></p>
    </div>
</footer>

<script>
document.getElementById('donateForm').addEventListener('submit', (e) => {
    const p = document.getElementById('phone').value.trim();
    const a = parseFloat(document.getElementById('amount').value);

    if (p !== '' && !/^[0-9]{10,15}$/.test(p)) {
        e.preventDefault();
        alert('Phone must be 10-15 digits.');
        return;
    }

    if (!(a > 0)) {
        e.preventDefault();
        alert('Enter a valid amount.');
    }
});
document.getElementById('themeToggle').addEventListener('click',()=>{document.documentElement.classList.toggle('dark');const d=document.documentElement.classList.contains('dark');document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;});
</script>
</body>
</html>
