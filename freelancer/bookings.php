<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('freelancer');

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'booking_status') {
    verify_csrf();

    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $status    = $_POST['status'] ?? '';

    if (!in_array($status, ['accepted', 'declined', 'completed'], true)) {
        set_flash('error', 'Unknown booking action.');
        redirect('freelancer/bookings.php');
    }

    $stmt = db()->prepare('
        UPDATE bookings b
        JOIN services s ON s.id = b.service_id
        SET b.status = ?
        WHERE b.id = ? AND s.freelancer_id = ?
    ');
    $stmt->execute([$status, $bookingId, $user['id']]);

    $msg = $status === 'accepted' ? 'Client selected. They can see they were chosen.' : 'Booking marked as ' . $status . '.';
    set_flash('success', $msg);
    redirect('freelancer/bookings.php');
}

$filter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending', 'accepted', 'completed', 'declined'];
if (!in_array($filter, $allowed, true)) {
    $filter = 'all';
}

$sql = '
    SELECT b.*, s.title, s.price, u.name AS client_name, u.email AS client_email
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    JOIN users u ON u.id = b.client_id
    WHERE s.freelancer_id = ?
';
$params = [$user['id']];
if ($filter !== 'all') {
    $sql .= ' AND b.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY FIELD(b.status, "pending", "accepted", "completed", "declined"), b.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

function engagement_label(array $b): string
{
    $type = $b['engagement_type'] === 'hourly' ? 'Hourly' : 'Project';
    if ($b['budget'] !== null) {
        return $type . ' &middot; ' . money((float) $b['budget']) . ($b['engagement_type'] === 'hourly' ? '/hr' : '');
    }
    return $type;
}

$pageTitle    = 'Bookings';
$pageSubtitle = 'Clients who booked your services';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="dash-card">
    <div class="dash-card-head">
        <div class="filter-tabs">
            <?php foreach ($allowed as $f): ?>
                <a class="filter-tab <?= $filter === $f ? 'active' : '' ?>" href="<?= url('freelancer/bookings.php?status=' . $f) ?>"><?= e(ucfirst($f)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$bookings): ?>
        <div class="dash-card-body pad"><div class="empty"><p>No bookings in this view yet.</p></div></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data data-rich">
                <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" class="check-all" aria-label="Select all"></th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Engagement</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="col-actions">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td class="col-check"><input type="checkbox" class="row-check" aria-label="Select row"></td>
                        <td>
                            <span class="cell-title"><?= e($booking['client_name']) ?></span>
                            <div class="cell-sub"><?= e($booking['client_email']) ?></div>
                        </td>
                        <td><?= e($booking['title']) ?></td>
                        <td><?= engagement_label($booking) ?></td>
                        <td class="muted" style="max-width:240px"><?= $booking['message'] ? e($booking['message']) : '&mdash;' ?></td>
                        <td><?= e(date('d M Y', strtotime($booking['created_at']))) ?></td>
                        <td><span class="badge badge-<?= e($booking['status']) ?>"><?= e(status_label($booking['status'])) ?></span></td>
                        <td class="col-actions">
                            <div class="actions" style="justify-content:flex-end">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <form method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="booking_status">
                                        <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                        <input type="hidden" name="status" value="accepted">
                                        <button class="btn btn-success btn-sm" type="submit">Choose</button>
                                    </form>
                                    <form method="post" class="inline-form" data-confirm="Decline this request?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="booking_status">
                                        <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                        <input type="hidden" name="status" value="declined">
                                        <button class="btn btn-danger btn-sm" type="submit">Decline</button>
                                    </form>
                                <?php elseif ($booking['status'] === 'accepted'): ?>
                                    <form method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="booking_status">
                                        <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button class="btn btn-primary btn-sm" type="submit">Mark complete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">&mdash;</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
