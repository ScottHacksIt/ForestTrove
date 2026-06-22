<?php
// Shared header. Pages set $page_title before including this file.
// Pages in /admin/ must set $in_admin = true before including.

require_once (isset($in_admin) ? __DIR__ . '/../includes/auth.php' : __DIR__ . '/auth.php');

$site_root = isset($in_admin) ? '../' : '';
$page_title = $page_title ?? 'Forest Trove';
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

function nav_class(string $page): string {
    global $current_page, $current_dir;
    $match = ($current_page === $page) || ($current_dir === rtrim($page, '/'));
    return $match ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Forest Trove</title>
    <meta name="description" content="Forest Trove — Find painted rock treasures hidden in the forest.">
    <link rel="stylesheet" href="<?= $site_root ?>assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌲</text></svg>">
</head>
<body>

<header class="site-header">
    <nav class="nav-inner">
        <a href="<?= $site_root ?>index.php" class="nav-logo">
            <span class="logo-icon">🌲</span>
            <span>Forest Trove</span>
        </a>

        <button class="nav-hamburger" id="nav-toggle" aria-label="Open menu">&#9776;</button>

        <ul class="nav-links" id="nav-links">
            <li><a href="<?= $site_root ?>index.php"<?= nav_class('index.php') ?>>Home</a></li>
            <li><a href="<?= $site_root ?>browse.php"<?= nav_class('browse.php') ?>>Browse</a></li>
            <li><a href="<?= $site_root ?>map.php"<?= nav_class('map.php') ?>>Map</a></li>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= $site_root ?>admin/index.php" class="btn-nav-admin">⚙ Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= $site_root ?>logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= $site_root ?>login.php"<?= nav_class('login.php') ?>>Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
