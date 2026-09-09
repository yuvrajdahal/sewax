<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(dashboard_path());
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = attempt_login($email, $password, 'admin');

    if ($user === null) {
        $error = 'Invalid administrator credentials.';
    } else {
        login_user($user);
        set_flash('success', 'Signed in as administrator.');
        redirect('admin/dashboard.php');
    }
}

$pageTitle = 'Admin Login';
require __DIR__ . '/includes/header.php';
?>

<div class="card auth-card">
    <h1>Administrator login</h1>
    <p class="muted">There is a single administrator account, created during setup.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <div class="field">
            <label for="email">Admin email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Log in as admin</button>
    </form>

    <p class="auth-alt"><a href="<?= url('login.php') ?>">Back to the normal login</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
