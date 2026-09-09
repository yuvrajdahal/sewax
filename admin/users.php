<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    verify_csrf();
    $stmt = db()->prepare('DELETE FROM users WHERE id = ? AND role <> "admin"');
    $stmt->execute([(int) ($_POST['user_id'] ?? 0)]);
    set_flash($stmt->rowCount() ? 'success' : 'error',
        $stmt->rowCount() ? 'User removed.' : 'That user could not be removed.');
    redirect('admin/users.php');
}

$users = db()->query('
    SELECT id, name, email, role, created_at
    FROM users
    ORDER BY FIELD(role, "admin", "freelancer", "client"), created_at DESC
')->fetchAll();

$pageTitle = 'Users';
$pageSubtitle = 'Everyone registered on the platform';
require __DIR__ . '/../includes/dash-top.php';
?>

<div class="dash-card">
    <div class="dash-card-head"><h2><?= count($users) ?> users</h2></div>
    <div class="table-wrap">
        <table class="data data-rich">
            <thead>
            <tr>
                <th class="col-check"><input type="checkbox" class="check-all" aria-label="Select all"></th>
                <th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th class="col-actions"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $row): ?>
                <tr>
                    <td class="col-check"><input type="checkbox" class="row-check" aria-label="Select row"></td>
                    <td class="cell-title"><?= e($row['name']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><span class="role-chip role-<?= e($row['role']) ?>"><?= e(ucfirst($row['role'])) ?></span></td>
                    <td><?= e(date('d M Y', strtotime($row['created_at']))) ?></td>
                    <td class="col-actions">
                        <?php if ($row['role'] !== 'admin'): ?>
                            <form method="post" class="inline-form" data-confirm="Delete this user along with their services and bookings?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= (int) $row['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
