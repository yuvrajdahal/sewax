<?php

require_once __DIR__ . '/config/config.php';

$steps  = [];
$failed = false;

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');
    $steps[] = 'Database `' . DB_NAME . '` is ready.';

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `name`       VARCHAR(100) NOT NULL,
            `email`      VARCHAR(150) NOT NULL UNIQUE,
            `password`   VARCHAR(255) NOT NULL,
            `role`       ENUM('client','freelancer','admin') NOT NULL DEFAULT 'client',
            `skills`     VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `services` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `freelancer_id` INT NOT NULL,
            `title`         VARCHAR(150) NOT NULL,
            `category`      VARCHAR(80) NOT NULL,
            `description`   TEXT NOT NULL,
            `price`         DECIMAL(10,2) NOT NULL,
            `delivery_days` INT NOT NULL DEFAULT 3,
            `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_services_user` FOREIGN KEY (`freelancer_id`)
                REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `bookings` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `service_id` INT NOT NULL,
            `client_id`  INT NOT NULL,
            `message`    TEXT,
            `engagement_type` ENUM('hourly','project') NOT NULL DEFAULT 'project',
            `budget`     DECIMAL(10,2) DEFAULT NULL,
            `status`     ENUM('pending','accepted','declined','completed') NOT NULL DEFAULT 'pending',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`)
                REFERENCES `services` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_bookings_client` FOREIGN KEY (`client_id`)
                REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $steps[] = 'Tables `users`, `services` and `bookings` are ready.';

    $stmt = $pdo->prepare('SELECT id FROM users WHERE role = "admin" LIMIT 1');
    $stmt->execute();

    if ($stmt->fetch()) {
        $steps[] = 'Admin account already exists, left untouched.';
    } else {
        $insert = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "admin")');
        $insert->execute(['Administrator', ADMIN_EMAIL, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT)]);
        $steps[] = 'Seeded the admin account (' . ADMIN_EMAIL . ').';
    }

    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM services');

    if ((int) $stmt->fetch()['total'] === 0) {
        $demoFreelancers = [
            ['Sita Sharma',  'sita@demo.local',  'Web Development, PHP, MySQL'],
            ['Bibek Thapa',  'bibek@demo.local', 'Graphic Design, Logo, Branding'],
        ];

        $ids  = [];
        $user = $pdo->prepare('INSERT INTO users (name, email, password, role, skills) VALUES (?, ?, ?, "freelancer", ?)');

        foreach ($demoFreelancers as $freelancer) {
            $user->execute([$freelancer[0], $freelancer[1], password_hash('demo123', PASSWORD_DEFAULT), $freelancer[2]]);
            $ids[] = (int) $pdo->lastInsertId();
        }

        $client = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "client")');
        $client->execute(['Anita Rai', 'anita@demo.local', password_hash('demo123', PASSWORD_DEFAULT)]);

        $demoServices = [
            [$ids[0], 'Responsive business website in PHP and MySQL', 'Web Development',
             'A complete multi page website built with HTML, CSS, PHP and MySQL. Includes an admin panel, contact form and a mobile friendly layout.', 12000, 7],
            [$ids[0], 'Bug fixing and small changes on your PHP site', 'Web Development',
             'Send me the error and I will fix it. Covers PHP notices, database queries, broken forms and layout problems.', 2500, 2],
            [$ids[1], 'Modern logo design with source files', 'Graphic Design',
             'Three logo concepts, unlimited colour revisions on the chosen one, and the final files in PNG, SVG and AI format.', 4500, 4],
            [$ids[1], 'Social media post pack for one month', 'Graphic Design',
             'Twelve ready to publish posts sized for Facebook and Instagram, matched to your brand colours.', 6000, 5],
        ];

        $service = $pdo->prepare('
            INSERT INTO services (freelancer_id, title, category, description, price, delivery_days)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        foreach ($demoServices as $row) {
            $service->execute($row);
        }

        $steps[] = 'Added demo freelancers, a demo client and 4 sample listings.';
    } else {
        $steps[] = 'Listings already present, demo data skipped.';
    }
} catch (PDOException $e) {
    $failed = true;
    $error  = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup &middot; <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<main class="container page">
    <div class="card auth-card" style="max-width:620px">
        <h1>Installation</h1>

        <?php if ($failed): ?>
            <div class="alert alert-error">
                Setup failed: <?= htmlspecialchars($error) ?>
            </div>
            <p class="muted">
                Check that MySQL is running and that the credentials in
                <code>config/config.php</code> are correct.
            </p>
        <?php else: ?>
            <div class="alert alert-success">Setup completed.</div>

            <ul>
                <?php foreach ($steps as $step): ?>
                    <li><?= htmlspecialchars($step) ?></li>
                <?php endforeach; ?>
            </ul>

            <h3 class="section-head">Login details</h3>
            <table class="data">
                <tr><th>Role</th><th>Email</th><th>Password</th></tr>
                <tr><td>Admin</td><td><?= htmlspecialchars(ADMIN_EMAIL) ?></td><td><?= htmlspecialchars(ADMIN_PASSWORD) ?></td></tr>
                <tr><td>Freelancer</td><td>sita@demo.local</td><td>demo123</td></tr>
                <tr><td>Client</td><td>anita@demo.local</td><td>demo123</td></tr>
            </table>

            <p class="muted" style="margin-top:16px">
                Delete <code>setup.php</code> once you are done, so it cannot be run again.
            </p>

            <a class="btn btn-primary btn-block" href="<?= BASE_URL ?>/index.php">Go to the home page</a>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
