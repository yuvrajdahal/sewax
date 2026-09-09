<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('freelancer');

$user = current_user();

$stmt = db()->prepare('SELECT * FROM services WHERE freelancer_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$services = $stmt->fetchAll();

$pageTitle    = 'Services';
$pageSubtitle = 'Manage your service listings and track bookings';
$pageActions  = '<a class="btn btn-primary" href="' . url('freelancer/service-form.php') . '">+ Create service</a>';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="dash-card">
    <?php if (!$services): ?>
        <div class="dash-card-body pad">
            <div class="empty">
                <p>You have not listed any service yet.</p>
                <a class="btn btn-primary" href="<?= url('freelancer/service-form.php') ?>">Create your first service</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data data-rich" id="servicesTable">
                <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" class="check-all" aria-label="Select all"></th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td class="col-check"><input type="checkbox" class="row-check" aria-label="Select row"></td>
                        <td>
                            <a class="cell-title" href="<?= url('service.php?id=' . (int) $service['id']) ?>"><?= e($service['title']) ?></a>
                            <div class="cell-sub">#<?= (int) $service['id'] ?> &middot; <?= e(date('d M Y', strtotime($service['created_at']))) ?></div>
                        </td>
                        <td><span class="tag"><?= e($service['category']) ?></span></td>
                        <td><?= money((float) $service['price']) ?></td>
                        <td><?= (int) $service['delivery_days'] ?> day(s)</td>
                        <td><span class="pill pill-active">Active</span></td>
                        <td class="col-actions">
                            <div class="row-menu">
                                <button class="row-menu-btn" type="button" aria-label="Actions">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                                </button>
                                <div class="row-menu-list">
                                    <a href="<?= url('service.php?id=' . (int) $service['id']) ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span>View</span>
                                    </a>
                                    <a href="<?= url('freelancer/service-form.php?id=' . (int) $service['id']) ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                    <form method="post" action="<?= url('freelancer/service-delete.php') ?>" data-confirm="Delete this service and all of its bookings?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                                        <button class="danger" type="submit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            <span>Delete</span>
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
