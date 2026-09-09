<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('
    SELECT s.*, u.name AS freelancer_name, u.email AS freelancer_email, u.skills AS freelancer_skills
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    WHERE s.id = ?
');
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    set_flash('error', 'That service does not exist.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    require_role('client');

    $message = trim($_POST['message'] ?? '');
    $engagement = $_POST['engagement_type'] ?? 'project';
    $budgetRaw = trim($_POST['budget'] ?? '');
    $budget = ($budgetRaw !== '' && is_numeric($budgetRaw) && (float) $budgetRaw > 0) ? (float) $budgetRaw : null;

    if (!in_array($engagement, ['hourly', 'project'], true)) {
        $engagement = 'project';
    }

    $check = db()->prepare('
        SELECT id FROM bookings
        WHERE service_id = ? AND client_id = ? AND status IN ("pending", "accepted")
    ');
    $check->execute([$service['id'], current_user()['id']]);

    if ($check->fetch()) {
        set_flash('error', 'You already have an open booking for this service.');
    } else {
        $insert = db()->prepare('INSERT INTO bookings (service_id, client_id, message, engagement_type, budget) VALUES (?, ?, ?, ?, ?)');
        $insert->execute([$service['id'], current_user()['id'], $message ?: null, $engagement, $budget]);

        set_flash('success', 'Booking request sent. The freelancer will respond soon.');
        redirect('client/dashboard.php');
    }
}

$pageTitle = $service['title'];
require __DIR__ . '/includes/header.php';
?>

<div class="detail-grid">
    <div class="detail-main">
        <div class="detail-header">
            <span class="tag"><?= e($service['category']) ?></span>
            <h1><?= e($service['title']) ?></h1>

            <div class="detail-seller">
                <span class="seller-avatar"><?= e(strtoupper(substr($service['freelancer_name'], 0, 1))) ?></span>
                <div>
                    <div class="seller-name">Offered by <strong><?= e($service['freelancer_name']) ?></strong></div>
                    <?php if ($service['freelancer_skills']): ?>
                        <div class="skill-chips">
                            <?php foreach (array_filter(array_map('trim', explode(',', $service['freelancer_skills']))) as $skill): ?>
                                <span class="skill-chip"><?= e($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="detail-body">
            <h3>About this service</h3>
            <p><?= nl2br(e($service['description'])) ?></p>
        </div>
    </div>

    <aside class="detail-side">
        <div class="detail-price"><?= money((float) $service['price']) ?></div>
        <div class="detail-delivery">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
            Delivery in <?= (int) $service['delivery_days'] ?> day(s)
        </div>

        <hr class="detail-hr">

        <?php if (!is_logged_in()): ?>
            <p class="muted">Log in as a client to send a booking request.</p>
            <a class="btn btn-primary btn-block" href="<?= url('login.php') ?>">Log in to book</a>

        <?php elseif (current_role() === 'client'): ?>
            <form method="post">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="engagement_type">Engagement type</label>
                    <select id="engagement_type" name="engagement_type">
                        <option value="project">Project basis (fixed)</option>
                        <option value="hourly">Hourly basis</option>
                    </select>
                </div>

                <div class="field">
                    <label for="budget">Your budget (Rs.)</label>
                    <input type="number" id="budget" name="budget" min="1" step="0.01" placeholder="e.g. 10000">
                    <div class="hint">Fixed amount, or your hourly rate for hourly engagements.</div>
                </div>

                <div class="field">
                    <label for="message">Message to the freelancer</label>
                    <textarea id="message" name="message" placeholder="Describe what you need, and any deadline."></textarea>
                </div>

                <button class="btn btn-primary btn-block" type="submit">Send booking request</button>
            </form>

        <?php elseif ((int) $service['freelancer_id'] === (int) current_user()['id']): ?>
            <p class="muted">This is your own listing.</p>
            <a class="btn btn-outline btn-block" href="<?= url('freelancer/service-form.php?id=' . (int) $service['id']) ?>">Edit this service</a>

        <?php else: ?>
            <p class="muted">Only client accounts can send booking requests.</p>
        <?php endif; ?>
    </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
