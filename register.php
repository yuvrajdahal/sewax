<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(dashboard_path());
}

$errors = [];
$name   = '';
$email  = '';
$skills = '';
$role   = in_array($_GET['role'] ?? '', ['client', 'freelancer'], true) ? $_GET['role'] : 'client';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $skills   = trim($_POST['skills'] ?? '');
    $role     = $_POST['role'] ?? 'client';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!in_array($role, ['client', 'freelancer'], true)) {
        $errors[] = 'Please choose a valid account type.';
        $role     = 'client';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'That email address is already registered.';
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (name, email, password, role, skills) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role,
            $role === 'freelancer' ? ($skills ?: null) : null,
        ]);

        set_flash('success', 'Account created. Please log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="card auth-card">
    <h1>Create an account</h1>
    <p class="muted">Register as a client to book services, or as a freelancer to offer them.</p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" data-validate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= e($name) ?>" required>
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        </div>

        <div class="field">
            <label for="role">I want to</label>
            <select id="role" name="role">
                <option value="client" <?= $role === 'client' ? 'selected' : '' ?>>Hire freelancers (Client)</option>
                <option value="freelancer" <?= $role === 'freelancer' ? 'selected' : '' ?>>Offer my services (Freelancer)</option>
            </select>
        </div>

        <div class="field">
            <label for="skills">Skills <span class="muted">(freelancers only)</span></label>
            <input type="text" id="skills" name="skills" value="<?= e($skills) ?>" placeholder="Web Development, PHP, MySQL">
        </div>

        <div class="field-row">
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="hint">At least 6 characters.</div>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Register</button>
    </form>

    <p class="auth-alt">Already have an account? <a href="<?= url('login.php') ?>">Log in</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
