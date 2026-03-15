<?php
// includes/ui.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function app_theme_class(): string
{
    return ($_COOKIE['theme'] ?? 'light') === 'dark' ? 'dark' : '';
}

function current_user_name(): string
{
    return trim($_SESSION['user_name'] ?? '');
}

function is_admin(): bool
{
    return !empty($_SESSION['is_admin']);
}

function set_user_name(string $name): void
{
    $_SESSION['user_name'] = $name;
}

function render_site_header(string $titleMl, string $titleEn): void
{
    echo '<header class="max-w-xl mx-auto px-4 pt-4">';
    echo '<div class="bg-white dark:bg-slate-900 rounded-2xl shadow p-4">';
    echo '<h1 class="font-bold text-lg">' . h($titleMl) . '</h1>';
    echo '<p class="text-sm text-slate-500 dark:text-slate-400">' . h($titleEn) . '</p>';

    if (current_user_name() !== '') {
        echo '<p class="mt-2 text-xs text-emerald-700 dark:text-emerald-400">Logged in as: ' . h(current_user_name()) . '</p>';
    }
    if (is_admin()) {
        echo '<p class="text-xs text-amber-600">Admin mode enabled</p>';
    }

    echo '</div>';
    echo '</header>';
}
