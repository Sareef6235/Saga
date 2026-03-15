<?php
$dbError = '';
$pdo = null;

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/data.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('CREATE TABLE IF NOT EXISTS donations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        organization TEXT NOT NULL,
        amount REAL NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )');

    $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES(:k, :v)');
    $stmt->execute([':k' => 'goal', ':v' => '1000000']);
} catch (Throwable $e) {
    $dbError = 'Database error: ' . $e->getMessage();
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function app_theme_class(): string
{
    $theme = $_COOKIE['theme'] ?? 'light';
    return $theme === 'dark' ? 'dark' : 'light';
}

function format_amount(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

function get_goal(PDO $pdo): float
{
    $stmt = $pdo->query("SELECT value FROM settings WHERE key='goal' LIMIT 1");
    $value = $stmt->fetchColumn();
    return $value !== false ? (float)$value : 1000000;
}

function get_total_collected(PDO $pdo): float
{
    $stmt = $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM donations');
    return (float)$stmt->fetchColumn();
}

function fetch_top_donors(PDO $pdo, int $limit = 3): array
{
    $stmt = $pdo->prepare('SELECT id, name, organization, amount, created_at FROM donations ORDER BY amount DESC, created_at DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
