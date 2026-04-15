<?php
/**
 * Configurazione - Modifica questi valori per adattarli al tuo ambiente
 * 
 * BASE_PATH: la directory radice da scansionare (es. '/srv/http', '/var/www/html')
 * EXCLUDED_DIRS: cartelle da escludere dall'elenco
 */
if (!defined('BASE_PATH')) define('BASE_PATH', '/srv/http');
if (!defined('EXCLUDED_DIRS')) define('EXCLUDED_DIRS', ['.git', 'api', 'localhostIndex', 'index.php']);
if (!defined('EXTRA_PATHS')) define('EXTRA_PATHS', ['/usr/share/webapps']);

$pathParam = $_GET['path'] ?? '';
$pathParam = $pathParam ? '/' . ltrim($pathParam, '/') : '';

$currentPath = BASE_PATH . $pathParam;
if (!is_dir($currentPath)) {
    $currentPath = BASE_PATH;
}

if (!empty($pathParam) && is_dir($currentPath)) {
    foreach (['index.php', 'index.html'] as $indexFile) {
        $indexPath = $currentPath . '/' . $indexFile;
        if (file_exists($indexPath) && realpath($indexPath) !== realpath(__FILE__)) {
            header('Location: ' . $pathParam . '/');
            exit;
        }
    }
}

$relativePath = $pathParam ?: '/';
$currentUrl = $pathParam ?: '/';
$parentUrl = dirname($currentUrl);
if ($parentUrl === '\\') $parentUrl = '/';
if ($currentUrl === '/' || $currentUrl === '') $parentUrl = null;

header('Cache-Control: no-store, no-cache, must-revalidate');
$items = [];

$scanDir = function($base) {
    $dirs = array_filter(scandir($base), function($d) {
        return $d[0] !== '.';
    });
    $result = [];
    foreach ($dirs as $dir) {
        $path = $base . '/' . $dir;
        if (is_dir($path) || is_link($path)) {
            $result[] = ['name' => $dir, 'path' => $path, 'isDir' => true];
        } elseif (is_file($path)) {
            $result[] = ['name' => $dir, 'path' => $path, 'isDir' => false];
        }
    }
    usort($result, function($a, $b) {
        if ($a['isDir'] !== $b['isDir']) return $a['isDir'] ? -1 : 1;
        return strcasecmp($a['name'], $b['name']);
    });
    return $result;
};

foreach ($scanDir($currentPath) as $item) {
    $itemName = basename($item['path']);
    if (!in_array($itemName, EXCLUDED_DIRS)) {
        $url = '/' . ltrim($currentUrl . '/' . $item['name'], '/');
        $items[] = ['name' => $item['name'], 'url' => $url, 'isDir' => $item['isDir']];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($relativePath) ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #0a0a0a; --bg-alt: #111111; --border: #222222; --text: #888888; --text-dim: #555555; --accent: #333333; --accent-hover: #444444; --highlight: #eeeeee; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); font-family: 'JetBrains Mono', monospace; color: var(--text); font-size: 14px; line-height: 1.6; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .logo { color: var(--highlight); font-weight: 600; font-size: 16px; }
        .breadcrumbs { display: flex; align-items: center; gap: 4px; color: var(--text-dim); font-size: 13px; overflow-x: auto; }
        .breadcrumbs a { color: var(--text); text-decoration: none; }
        .breadcrumbs a:hover { color: var(--highlight); }
        .breadcrumbs span { color: var(--text-dim); }
        .count { font-size: 12px; color: var(--text-dim); }
        .search { margin-bottom: 20px; }
        .search input { width: 100%; background: var(--bg-alt); border: 1px solid var(--border); color: var(--text); font-family: inherit; font-size: 13px; padding: 10px 14px; outline: none; }
        .search input:focus { border-color: var(--text-dim); }
        .search input::placeholder { color: var(--text-dim); }
        .list { display: flex; flex-direction: column; gap: 2px; }
        .item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: var(--bg-alt); border: 1px solid transparent; cursor: pointer; transition: all 0.1s; }
        .item:hover, .item.selected { background: var(--accent); border-color: var(--border); }
        .item:active, .item.selected:active { background: var(--accent-hover); }
        .item .icon { width: 20px; text-align: center; color: var(--highlight); }
        .item .name { flex: 1; color: var(--highlight); font-weight: 500; }
        .item .url { color: var(--text-dim); font-size: 11px; }
        .item .arrow { color: var(--text-dim); font-size: 11px; opacity: 0; }
        .item:hover .arrow, .item.selected .arrow { opacity: 1; }
        .item.pinned { background: #1a1a1a; border-left: 2px solid var(--text); }
        .item .pin { color: var(--text-dim); font-size: 10px; opacity: 0; cursor: pointer; }
        .item:hover .pin, .item.pinned .pin { opacity: 1; }
        .item.pinned .pin { color: var(--highlight); }
        .empty { text-align: center; padding: 40px; color: var(--text-dim); }
        footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border); font-size: 11px; color: var(--text-dim); display: flex; justify-content: space-between; }
        footer kbd { background: var(--bg-alt); padding: 2px 6px; border-radius: 2px; border: 1px solid var(--border); }
        @media (max-width: 600px) { body { padding: 12px; } .item { padding: 12px; } .item .url { display: none; } }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <div class="breadcrumbs">
                    <a href="/">/</a>
                    <?php $parts = array_filter(explode('/', $relativePath)); ?>
                    <?php $path = ''; ?>
                    <?php foreach ($parts as $part): ?>
                        <?php $path .= '/' . $part; ?>
                        <span>/</span><a href="/localhostIndex/index.php?path=<?= htmlspecialchars($path) ?>"><?= htmlspecialchars($part) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="count"><span id="count">0</span> items</div>
        </header>
        <div class="search">
            <input type="text" id="search" placeholder="filter..." autocomplete="off" autofocus>
        </div>
        <div class="list" id="list"></div>
        <footer>
            <span><kbd>↑</kbd><kbd>↓</kbd> navigate <kbd>enter</kbd> open <kbd>/</kbd> search <kbd>p</kbd> pin <kbd>backspace</kbd> back</span>
            <span><?= htmlspecialchars($relativePath) ?></span>
        </footer>
    </div>
    <script>
        const currentPath = <?= json_encode($currentUrl) ?>;
        const parentPath = <?= json_encode($parentUrl) ?>;
        const pinned = JSON.parse(localStorage.getItem('pinned') || '[]');
        const sites = <?= json_encode($items) ?>;
        let items = sites.map(s => ({...s, pinned: pinned.includes(s.name)}));
        items.sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0));
        let filtered = [...items];
        let selected = 0;
        const listEl = document.getElementById("list");
        const searchEl = document.getElementById("search");
        const countEl = document.getElementById("count");

        function savePinned() {
            const pinnedNames = items.filter(i => i.pinned).map(i => i.name);
            localStorage.setItem('pinned', JSON.stringify(pinnedNames));
        }

        function togglePin(index) {
            items[index].pinned = !items[index].pinned;
            items.sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0));
            selected = items.findIndex(i => i.url === items[index].url);
            savePinned();
            filter(searchEl.value);
        }

        function render() {
            countEl.textContent = filtered.length;
            if (filtered.length === 0) {
                listEl.innerHTML = '<div class="empty">no matches</div>';
                return;
            }
            listEl.innerHTML = filtered.map((site, i) => `
                <div class="item${site.pinned ? ' pinned' : ''}${i === selected ? ' selected' : ''}" data-url="${site.url}" data-dir="${site.isDir}" data-index="${i}">
                    <div class="pin" data-action="pin">&bull;</div>
                    <div class="icon">${site.isDir ? '>' : 'f'}</div>
                    <div class="name">${site.name}</div>
                    <div class="url">${site.url}</div>
                    <div class="arrow">&rarr;</div>
                </div>
            `).join('');
            if (listEl.children[selected]) listEl.children[selected].scrollIntoView({ block: 'nearest' });
        }

        function filter(term) {
            selected = 0;
            if (!term) {
                filtered = [...items];
            } else {
                const t = term.toLowerCase();
                filtered = items.filter(s => s.name.toLowerCase().includes(t) || s.url.toLowerCase().includes(t));
            }
            render();
        }

        function navigate(delta) {
            if (filtered.length === 0) return;
            selected = (selected + delta + filtered.length) % filtered.length;
            render();
        }

        function openCurrent() {
            if (filtered[selected]) {
                const isDir = filtered[selected].isDir;
                if (isDir) {
                    window.location.href = '/localhostIndex/index.php?path=' + encodeURIComponent(filtered[selected].url);
                } else {
                    window.location.href = filtered[selected].url;
                }
            }
        }

        function goBack() {
            if (parentPath) window.location.href = '/localhostIndex/index.php?path=' + encodeURIComponent(parentPath);
        }

        searchEl.addEventListener("input", e => filter(e.target.value));

        listEl.addEventListener("click", e => {
            const pinBtn = e.target.closest("[data-action='pin']");
            if (pinBtn) {
                e.stopPropagation();
                const index = parseInt(e.target.closest(".item").dataset.index);
                const realIndex = items.findIndex(i => i.url === filtered[index].url);
                togglePin(realIndex);
                return;
            }
            const item = e.target.closest(".item");
            if (item) {
                const isDir = item.dataset.dir === 'true';
                if (isDir) {
                    window.location.href = '/localhostIndex/index.php?path=' + encodeURIComponent(item.dataset.url);
                } else {
                    window.location.href = item.dataset.url;
                }
            }
        });

        document.addEventListener("keydown", e => {
            if (e.key === "ArrowDown" || e.key === "j") { e.preventDefault(); navigate(1); }
            else if (e.key === "ArrowUp" || e.key === "k") { e.preventDefault(); navigate(-1); }
            else if (e.key === "Enter") { e.preventDefault(); openCurrent(); }
            else if (e.key === "/") { e.preventDefault(); searchEl.focus(); }
            else if (e.key === "Escape") { searchEl.blur(); searchEl.value = ''; filter(''); }
            else if (e.key === "p") { e.preventDefault(); const realIndex = items.findIndex(i => i.url === filtered[selected].url); togglePin(realIndex); }
            else if (e.key === "Backspace" && document.activeElement.tagName !== 'INPUT') { e.preventDefault(); goBack(); }
        });

        render();
        searchEl.focus();
    </script>
</body>
</html>