<?php
function render_site_header($title, $subtitle = '')
{
    global $pdo;
    $logo = site_logo_url($pdo instanceof PDO ? $pdo : null);
    ?>
    <header class="bg-gradient-to-r from-emerald-700 to-brand text-white shadow-soft rounded-b-3xl mb-4" style="background-image:radial-gradient(circle at 1px 1px,rgba(212,160,23,.24) 1px,transparent 0);background-size:24px 24px;">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-3">
            <a href="/index.php" class="flex items-center gap-3 min-w-0">
                <img src="<?= h($logo) ?>" alt="Site logo" class="h-11 w-11 rounded-xl object-cover border border-white/30 bg-white/20">
                <div class="min-w-0">
                    <p class="font-bold text-base md:text-xl truncate"><?= h($title) ?></p>
                    <?php if ($subtitle !== ''): ?><p class="text-emerald-100 text-xs md:text-sm truncate"><?= h($subtitle) ?></p><?php endif; ?>
                </div>
            </a>
            <button type="button" class="toggle-theme bg-white/15 hover:bg-white/25 px-3 py-2 rounded-xl text-sm">🌓</button>
        </div>
    </header>
    <script>
    document.querySelectorAll('.toggle-theme').forEach((btn)=>{
      btn.addEventListener('click',()=>{
        document.documentElement.classList.toggle('dark');
        const d=document.documentElement.classList.contains('dark');
        document.cookie=`theme=${d?'dark':'light'}; path=/; max-age=31536000`;
      });
    });
    </script>
    <?php
}
