<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('client');

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    verify_csrf();

    $stmt = db()->prepare('DELETE FROM bookings WHERE id = ? AND client_id = ? AND status = "pending"');
    $stmt->execute([(int) ($_POST['booking_id'] ?? 0), $user['id']]);

    set_flash($stmt->rowCount() ? 'success' : 'error',
        $stmt->rowCount() ? 'Booking request cancelled.' : 'That request can no longer be cancelled.');
    redirect('client/dashboard.php');
}

$stmt = db()->prepare('
    SELECT b.*, s.title, s.price, s.category, u.name AS freelancer_name
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    JOIN users u ON u.id = s.freelancer_id
    WHERE b.client_id = ?
    ORDER BY b.created_at DESC
');
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

$counts = ['pending' => 0, 'accepted' => 0, 'completed' => 0, 'declined' => 0];
foreach ($bookings as $booking) {
    $counts[$booking['status']]++;
}

$pageTitle = 'Dashboard';
$pageSubtitle = 'Track every service you have requested';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="metric-grid">
    <div class="metric">
        <div class="metric-icon"><?= dash_icon('list') ?></div>
        <div><div class="metric-value"><?= count($bookings) ?></div><div class="metric-label">Total requests</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon amber"><?= dash_icon('clock') ?></div>
        <div><div class="metric-value"><?= $counts['pending'] ?></div><div class="metric-label">Pending</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon"><?= dash_icon('briefcase') ?></div>
        <div><div class="metric-value"><?= $counts['accepted'] ?></div><div class="metric-label">Accepted</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon green"><?= dash_icon('check') ?></div>
        <div><div class="metric-value"><?= $counts['completed'] ?></div><div class="metric-label">Completed</div></div>
    </div>
</div>

<div class="dash-card">
    <div class="dash-card-head">
        <h2>Booking history</h2>
        <a class="btn btn-primary btn-sm" href="<?= url('index.php') ?>">Browse services</a>
    </div>

    <?php if (!$bookings): ?>
        <div class="dash-card-body pad">
            <div class="empty">
                <p>You have not booked any service yet.</p>
                <a class="btn btn-primary" href="<?= url('index.php') ?>">Find a service</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                <tr>
                    <th>Service</th>
                    <th>Freelancer</th>
                    <th>Engagement</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>
                            <a href="<?= url('service.php?id=' . (int) $booking['service_id']) ?>"><?= e($booking['title']) ?></a>
                            <div class="muted" style="font-size:.82rem"><?= e($booking['category']) ?></div>
                        </td>
                        <td><?= e($booking['freelancer_name']) ?></td>
                        <td>
                            <?= $booking['engagement_type'] === 'hourly' ? 'Hourly' : 'Project' ?>
                            <?php if ($booking['budget'] !== null): ?>
                                <div class="muted" style="font-size:.82rem"><?= money((float) $booking['budget']) ?><?= $booking['engagement_type'] === 'hourly' ? '/hr' : '' ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= e(date('d M Y', strtotime($booking['created_at']))) ?></td>
                        <td>
                            <?php if ($booking['status'] === 'accepted'): ?>
                                <span class="badge badge-completed">Selected &#10003;</span>
                            <?php else: ?>
                                <span class="badge badge-<?= e($booking['status']) ?>"><?= e(status_label($booking['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <form method="post" class="inline-form" data-confirm="Cancel this booking request?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
