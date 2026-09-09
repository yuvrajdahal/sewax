<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_service') {
    verify_csrf();
    $stmt = db()->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([(int) ($_POST['service_id'] ?? 0)]);
    set_flash('success', 'Service listing removed.');
    redirect('admin/services.php');
}

$services = db()->query('
    SELECT s.*, u.name AS freelancer_name
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    ORDER BY s.created_at DESC
')->fetchAll();

$pageTitle = 'Services';
$pageSubtitle = 'All service listings';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="dash-card">
    <div class="dash-card-head"><h2><?= count($services) ?> listings</h2></div>
    <?php if (!$services): ?>
        <div class="dash-card-body pad"><div class="empty"><p>No services have been listed yet.</p></div></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data data-rich">
                <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" class="check-all" aria-label="Select all"></th>
                    <th>Title</th><th>Freelancer</th><th>Category</th><th>Price</th><th class="col-actions"></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $row): ?>
                    <tr>
                        <td class="col-check"><input type="checkbox" class="row-check" aria-label="Select row"></td>
                        <td><a class="cell-title" href="<?= url('service.php?id=' . (int) $row['id']) ?>"><?= e($row['title']) ?></a></td>
                        <td><?= e($row['freelancer_name']) ?></td>
                        <td><span class="tag"><?= e($row['category']) ?></span></td>
                        <td><?= money((float) $row['price']) ?></td>
                        <td class="col-actions">
                            <div class="row-menu">
                                <button class="row-menu-btn" type="button" aria-label="Actions">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                                </button>
                                <div class="row-menu-list">
                                    <a href="<?= url('service.php?id=' . (int) $row['id']) ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span>View</span>
                                    </a>
                                    <a href="<?= url('freelancer/service-form.php?id=' . (int) $row['id']) ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                    <form method="post" data-confirm="Remove this listing?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_service">
                                        <input type="hidden" name="service_id" value="<?= (int) $row['id'] ?>">
                                        <button class="danger" type="submit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            <span>Remove</span>
                                        </button>
                                    </form>
                                </div>
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
