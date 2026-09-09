<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('freelancer');

$user = current_user();

$stmt = db()->prepare('SELECT * FROM services WHERE freelancer_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$services = $stmt->fetchAll();

$stmt = db()->prepare('
    SELECT b.*, s.title, s.price, u.name AS client_name, u.email AS client_email
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    JOIN users u ON u.id = b.client_id
    WHERE s.freelancer_id = ?
    ORDER BY FIELD(b.status, "pending", "accepted", "completed", "declined"), b.created_at DESC
');
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

$pending = 0;
$earned  = 0.0;
foreach ($bookings as $booking) {
    if ($booking['status'] === 'pending') {
        $pending++;
    }
    if ($booking['status'] === 'completed') {
        $earned += (float) ($booking['budget'] ?? $booking['price']);
    }
}

$pageTitle = 'Dashboard';
$pageSubtitle = 'Manage your services and booking requests';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="metric-grid">
    <div class="metric">
        <div class="metric-icon"><?= dash_icon('briefcase') ?></div>
        <div><div class="metric-value"><?= count($services) ?></div><div class="metric-label">My services</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon violet"><?= dash_icon('list') ?></div>
        <div><div class="metric-value"><?= count($bookings) ?></div><div class="metric-label">Total bookings</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon amber"><?= dash_icon('clock') ?></div>
        <div><div class="metric-value"><?= $pending ?></div><div class="metric-label">Pending requests</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon green"><?= dash_icon('wallet') ?></div>
        <div><div class="metric-value"><?= money($earned) ?></div><div class="metric-label">Completed value</div></div>
    </div>
</div>

<div class="dash-card">
    <div class="dash-card-head">
        <h2>Recent booking requests</h2>
        <a class="btn btn-outline btn-sm" href="<?= url('freelancer/bookings.php') ?>">Manage bookings</a>
    </div>

    <?php if (!$bookings): ?>
        <div class="dash-card-body pad"><div class="empty"><p>No booking requests yet.</p></div></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data data-rich">
                <thead>
                <tr>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                    <tr>
                        <td>
                            <span class="cell-title"><?= e($booking['client_name']) ?></span>
                            <div class="cell-sub"><?= e($booking['client_email']) ?></div>
                        </td>
                        <td><?= e($booking['title']) ?></td>
                        <td><?= e(date('d M Y', strtotime($booking['created_at']))) ?></td>
                        <td><span class="badge badge-<?= e($booking['status']) ?>"><?= e(status_label($booking['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
