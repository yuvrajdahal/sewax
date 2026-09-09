<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('freelancer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('freelancer/dashboard.php');
}

verify_csrf();

$stmt = db()->prepare('DELETE FROM services WHERE id = ? AND freelancer_id = ?');
$stmt->execute([(int) ($_POST['id'] ?? 0), current_user()['id']]);

set_flash($stmt->rowCount() ? 'success' : 'error',
    $stmt->rowCount() ? 'Service deleted.' : 'That service could not be deleted.');

redirect('freelancer/dashboard.php');
