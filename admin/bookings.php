<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$filter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending', 'accepted', 'completed', 'declined'];
if (!in_array($filter, $allowed, true)) {
    $filter = 'all';
}

$sql = '
    SELECT b.*, s.title, c.name AS client_name, f.name AS freelancer_name
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    JOIN users c ON c.id = b.client_id
    JOIN users f ON f.id = s.freelancer_id
';
$params = [];
if ($filter !== 'all') {
    $sql .= ' WHERE b.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY b.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'Bookings';
$pageSubtitle = 'Every booking on the platform';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="dash-card">
    <div class="dash-card-head">
        <div class="filter-tabs">
            <?php foreach ($allowed as $f): ?>
                <a class="filter-tab <?= $filter === $f ? 'active' : '' ?>" href="<?= url('admin/bookings.php?status=' . $f) ?>"><?= e(ucfirst($f)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if (!$bookings): ?>
        <div class="dash-card-body pad"><div class="empty"><p>No bookings in this view.</p></div></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data data-rich">
                <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" class="check-all" aria-label="Select all"></th>
                    <th>Client</th><th>Service</th><th>Freelancer</th><th>Date</th><th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $row): ?>
                    <tr>
                        <td class="col-check"><input type="checkbox" class="row-check" aria-label="Select row"></td>
                        <td class="cell-title"><?= e($row['client_name']) ?></td>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['freelancer_name']) ?></td>
                        <td><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
                        <td><span class="badge badge-<?= e($row['status']) ?>"><?= e(status_label($row['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
