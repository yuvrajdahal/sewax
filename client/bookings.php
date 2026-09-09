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
    redirect('client/bookings.php');
}

$filter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending', 'accepted', 'completed', 'declined'];
if (!in_array($filter, $allowed, true)) {
    $filter = 'all';
}

$sql = '
    SELECT b.*, s.title, s.category, u.name AS freelancer_name
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    JOIN users u ON u.id = s.freelancer_id
    WHERE b.client_id = ?
';
$params = [$user['id']];
if ($filter !== 'all') {
    $sql .= ' AND b.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY b.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle    = 'Bookings';
$pageSubtitle = 'Every service you have requested';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="dash-card">
    <div class="dash-card-head">
        <div class="filter-tabs">
            <?php foreach ($allowed as $f): ?>
                <a class="filter-tab <?= $filter === $f ? 'active' : '' ?>" href="<?= url('client/bookings.php?status=' . $f) ?>"><?= e(ucfirst($f)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$bookings): ?>
        <div class="dash-card-body pad">
            <div class="empty">
                <p>No bookings in this view yet.</p>
                <a class="btn btn-primary" href="<?= url('index.php') ?>">Find a service</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data data-rich">
                <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" class="check-all" aria-label="Select all"></th>
                    <th>Service</th>
                    <th>Freelancer</th>
                    <th>Engagement</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th class="col-actions"></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td class="col-check"><input type="checkbox" class="row-check" aria-label="Select row"></td>
                        <td>
                            <a class="cell-title" href="<?= url('service.php?id=' . (int) $booking['service_id']) ?>"><?= e($booking['title']) ?></a>
                            <div class="cell-sub"><?= e($booking['category']) ?></div>
                        </td>
                        <td><?= e($booking['freelancer_name']) ?></td>
                        <td>
                            <?= $booking['engagement_type'] === 'hourly' ? 'Hourly' : 'Project' ?>
                            <?php if ($booking['budget'] !== null): ?>
                                <div class="cell-sub"><?= money((float) $booking['budget']) ?><?= $booking['engagement_type'] === 'hourly' ? '/hr' : '' ?></div>
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
                        <td class="col-actions">
                            <?php if ($booking['status'] === 'pending'): ?>
                                <form method="post" class="inline-form" data-confirm="Cancel this booking request?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">&mdash;</span>
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
