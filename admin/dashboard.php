<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$admin = current_user();

$stats = [
    'clients'     => (int) db()->query('SELECT COUNT(*) AS c FROM users WHERE role = "client"')->fetch()['c'],
    'freelancers' => (int) db()->query('SELECT COUNT(*) AS c FROM users WHERE role = "freelancer"')->fetch()['c'],
    'services'    => (int) db()->query('SELECT COUNT(*) AS c FROM services')->fetch()['c'],
    'bookings'    => (int) db()->query('SELECT COUNT(*) AS c FROM bookings')->fetch()['c'],
];

$recentServices = db()->query('
    SELECT s.*, u.name AS freelancer_name
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    ORDER BY s.created_at DESC
    LIMIT 5
')->fetchAll();

$recentBookings = db()->query('
    SELECT b.*, s.title, c.name AS client_name
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    JOIN users c ON c.id = b.client_id
    ORDER BY b.created_at DESC
    LIMIT 5
')->fetchAll();

$pageTitle = 'Overview';
$pageSubtitle = 'Signed in as ' . $admin['name'];
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="metric-grid">
    <div class="metric">
        <div class="metric-icon"><?= dash_icon('users') ?></div>
        <div><div class="metric-value"><?= $stats['clients'] ?></div><div class="metric-label">Clients</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon green"><?= dash_icon('briefcase') ?></div>
        <div><div class="metric-value"><?= $stats['freelancers'] ?></div><div class="metric-label">Freelancers</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon violet"><?= dash_icon('list') ?></div>
        <div><div class="metric-value"><?= $stats['services'] ?></div><div class="metric-label">Services</div></div>
    </div>
    <div class="metric">
        <div class="metric-icon amber"><?= dash_icon('calendar') ?></div>
        <div><div class="metric-value"><?= $stats['bookings'] ?></div><div class="metric-label">Bookings</div></div>
    </div>
</div>

<div class="bento-grid">
    <div class="dash-card">
        <div class="dash-card-head">
            <h2>Recent services</h2>
            <a class="btn btn-outline btn-sm" href="<?= url('admin/services.php') ?>">View all</a>
        </div>
        <?php if (!$recentServices): ?>
            <div class="dash-card-body pad"><div class="empty"><p>No services yet.</p></div></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data data-rich">
                    <thead><tr><th>Title</th><th>Freelancer</th><th>Price</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentServices as $row): ?>
                        <tr>
                            <td>
                                <a class="cell-title" href="<?= url('service.php?id=' . (int) $row['id']) ?>"><?= e($row['title']) ?></a>
                                <div class="cell-sub"><?= e($row['category']) ?></div>
                            </td>
                            <td><?= e($row['freelancer_name']) ?></td>
                            <td><?= money((float) $row['price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <h2>Recent bookings</h2>
            <a class="btn btn-outline btn-sm" href="<?= url('admin/bookings.php') ?>">View all</a>
        </div>
        <?php if (!$recentBookings): ?>
            <div class="dash-card-body pad"><div class="empty"><p>No bookings yet.</p></div></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data data-rich">
                    <thead><tr><th>Client</th><th>Service</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentBookings as $row): ?>
                        <tr>
                            <td>
                                <span class="cell-title"><?= e($row['client_name']) ?></span>
                                <div class="cell-sub"><?= e(date('d M Y', strtotime($row['created_at']))) ?></div>
                            </td>
                            <td><?= e($row['title']) ?></td>
                            <td><span class="badge badge-<?= e($row['status']) ?>"><?= e(status_label($row['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
