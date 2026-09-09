<?php
require_once __DIR__ . '/auth.php';
require_login();

$pageTitle = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$user  = current_user();
$role  = $user['role'];
$flash = take_flash();
$current = basename($_SERVER['SCRIPT_NAME']);

function dash_icon(string $name): string
{
    $paths = [
        'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        'plus'      => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9v11h14V9"/>',
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'list'      => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'wallet'    => '<path d="M20 12V8H6a2 2 0 0 1 0-4h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
    ];
    $d = $paths[$name] ?? '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
}

$navByRole = [
    'client' => [
        ['dashboard.php', 'grid', 'Dashboard', url('client/dashboard.php')],
        ['bookings.php', 'calendar', 'Bookings', url('client/bookings.php')],
    ],
    'freelancer' => [
        ['dashboard.php', 'grid', 'Dashboard', url('freelancer/dashboard.php')],
        ['services.php', 'briefcase', 'Services', url('freelancer/services.php'), ['services.php', 'service-form.php']],
        ['bookings.php', 'calendar', 'Bookings', url('freelancer/bookings.php')],
    ],
    'admin' => [
        ['dashboard.php', 'grid', 'Overview', url('admin/dashboard.php')],
        ['users.php', 'users', 'Users', url('admin/users.php')],
        ['services.php', 'briefcase', 'Services', url('admin/services.php')],
        ['bookings.php', 'calendar', 'Bookings', url('admin/bookings.php')],
    ],
];
$navItems = $navByRole[$role] ?? [];
$initial = strtoupper(substr($user['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="dash-shell">
    <aside class="dash-sidebar" id="dashSidebar">
        <a class="dash-brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">F</span>
            <span><?= e(APP_NAME) ?></span>
        </a>

        <nav class="dash-nav">
            <?php foreach ($navItems as $item): ?>
                <?php $activeOn = $item[4] ?? [$item[0]]; ?>
                <a class="dash-nav-item <?= in_array($current, $activeOn, true) ? 'active' : '' ?>" href="<?= e($item[3]) ?>">
                    <?= dash_icon($item[1]) ?>
                    <span><?= e($item[2]) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="dash-side-foot">
            <div class="dash-user">
                <div class="dash-avatar"><?= e($initial) ?></div>
                <div class="dash-user-meta">
                    <div class="dash-user-name"><?= e($user['name']) ?></div>
                    <div class="dash-user-role"><?= e(ucfirst($role)) ?></div>
                </div>
            </div>
            <a class="dash-nav-item" href="<?= url('logout.php') ?>">
                <?= dash_icon('logout') ?>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="dash-content">
        <header class="dash-topbar">
            <button class="dash-menu-toggle" type="button" aria-label="Toggle menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <div class="dash-topbar-actions">
                <button class="icon-btn" type="button" aria-label="Notifications">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>

                <div class="avatar-menu" id="avatarMenu">
                    <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-circle"><?= e($initial) ?></span>
                        <svg class="avatar-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <div class="avatar-dropdown">
                        <div class="avatar-dropdown-head">
                            <span class="avatar-circle lg"><?= e($initial) ?></span>
                            <div class="avatar-dropdown-meta">
                                <div class="avatar-dropdown-name"><?= e($user['name']) ?></div>
                                <div class="avatar-dropdown-email"><?= e($user['email']) ?></div>
                            </div>
                        </div>
                        <a class="avatar-dropdown-item" href="<?= url(dashboard_path()) ?>">
                            <?= dash_icon('grid') ?><span>Dashboard</span>
                        </a>
                        <a class="avatar-dropdown-item" href="<?= url('index.php') ?>">
                            <?= dash_icon('home') ?><span>Home</span>
                        </a>
                        <a class="avatar-dropdown-item danger" href="<?= url('logout.php') ?>">
                            <?= dash_icon('logout') ?><span>Log out</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="dash-crumbbar">
            <nav class="crumbs">
                <a href="<?= url(dashboard_path()) ?>">Dashboard</a>
                <?php if ($current !== 'dashboard.php'): ?>
                    <span class="crumb-sep">/</span>
                    <span class="crumb-current"><?= e($pageTitle) ?></span>
                <?php endif; ?>
            </nav>
        </div>

        <div class="dash-body">
            <div class="page-header">
                <div>
                    <h1><?= e($pageTitle) ?></h1>
                    <?php if ($pageSubtitle): ?><div class="subtitle"><?= e($pageSubtitle) ?></div><?php endif; ?>
                </div>
                <?php if (!empty($pageActions)): ?>
                    <div class="page-header-right">
                        <div class="page-actions"><?= $pageActions ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
