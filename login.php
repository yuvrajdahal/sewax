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

    if ($email === '' || $password === '') {
        $error = 'Please enter both your email and password.';
    } else {
        $user = attempt_login($email, $password);

        if ($user === null) {
            $error = 'Incorrect email or password.';
        } else {
            login_user($user);
            set_flash('success', 'Welcome back, ' . $user['name'] . '.');
            redirect(dashboard_path($user['role']));
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="card auth-card">
    <h1>Log in</h1>
    <p class="muted">Use the account you registered with.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Log in</button>
    </form>

    <p class="auth-alt">
        No account yet? <a href="<?= url('register.php') ?>">Register</a><br>
        <a href="<?= url('admin-login.php') ?>">Administrator login</a>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
