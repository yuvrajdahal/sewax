<?php
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? APP_NAME;
$user      = current_user();
$flash     = take_flash();
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

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= url('index.php') ?>">
            <span class="brand-mark">F</span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Toggle navigation">&#9776;</button>

        <nav class="site-nav">
            <?php if ($user): ?>
                <div class="avatar-menu" id="avatarMenu">
                    <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-circle"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span>
                        <svg class="avatar-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <div class="avatar-dropdown">
                        <div class="avatar-dropdown-head">
                            <span class="avatar-circle lg"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span>
                            <div class="avatar-dropdown-meta">
                                <div class="avatar-dropdown-name"><?= e($user['name']) ?></div>
                                <div class="avatar-dropdown-email"><?= e($user['email']) ?></div>
                            </div>
                        </div>

                        <a class="avatar-dropdown-item" href="<?= url(dashboard_path()) ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                            <span>Dashboard</span>
                        </a>

                        <a class="avatar-dropdown-item danger" href="<?= url('logout.php') ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            <span>Log out</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= url('login.php') ?>">Login</a>
                <a class="btn btn-primary btn-sm" href="<?= url('register.php') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container page">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
