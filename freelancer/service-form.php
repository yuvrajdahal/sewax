<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$user    = current_user();
$isAdmin = current_role() === 'admin';

if (!$isAdmin && current_role() !== 'freelancer') {
    set_flash('error', 'You do not have access to that page.');
    redirect(dashboard_path());
}

$serviceId = (int) ($_GET['id'] ?? 0);
$isEdit    = $serviceId > 0;
$errors    = [];

$backUrl = $isAdmin ? 'admin/services.php' : 'freelancer/dashboard.php';

// Admins manage existing listings only; they do not create new ones.
if ($isAdmin && !$isEdit) {
    redirect('admin/services.php');
}

$service = [
    'title'         => '',
    'category'      => '',
    'description'   => '',
    'price'         => '',
    'delivery_days' => 3,
];

if ($isEdit) {
    if ($isAdmin) {
        $stmt = db()->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$serviceId]);
    } else {
        $stmt = db()->prepare('SELECT * FROM services WHERE id = ? AND freelancer_id = ?');
        $stmt->execute([$serviceId, $user['id']]);
    }
    $found = $stmt->fetch();

    if (!$found) {
        set_flash('error', 'That service was not found.');
        redirect($backUrl);
    }

    $service = $found;
}

$categories = [
    'Web Development',
    'Graphic Design',
    'Mobile App Development',
    'Programming',
    'Content Writing',
    'Digital Marketing',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $service['title']         = trim($_POST['title'] ?? '');
    $service['category']      = trim($_POST['category'] ?? '');
    $service['description']   = trim($_POST['description'] ?? '');
    $service['price']         = trim($_POST['price'] ?? '');
    $service['delivery_days'] = (int) ($_POST['delivery_days'] ?? 0);

    if ($service['title'] === '') {
        $errors[] = 'Please enter a title for the service.';
    }

    if (!in_array($service['category'], $categories, true)) {
        $errors[] = 'Please choose a category.';
    }

    if (strlen($service['description']) < 20) {
        $errors[] = 'Please describe the service in at least 20 characters.';
    }

    if (!is_numeric($service['price']) || (float) $service['price'] <= 0) {
        $errors[] = 'Please enter a price greater than zero.';
    }

    if ($service['delivery_days'] < 1) {
        $errors[] = 'Delivery time must be at least 1 day.';
    }

    if (!$errors) {
        if ($isEdit) {
            if ($isAdmin) {
                $stmt = db()->prepare('
                    UPDATE services
                    SET title = ?, category = ?, description = ?, price = ?, delivery_days = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $service['title'],
                    $service['category'],
                    $service['description'],
                    (float) $service['price'],
                    $service['delivery_days'],
                    $serviceId,
                ]);
            } else {
                $stmt = db()->prepare('
                    UPDATE services
                    SET title = ?, category = ?, description = ?, price = ?, delivery_days = ?
                    WHERE id = ? AND freelancer_id = ?
                ');
                $stmt->execute([
                    $service['title'],
                    $service['category'],
                    $service['description'],
                    (float) $service['price'],
                    $service['delivery_days'],
                    $serviceId,
                    $user['id'],
                ]);
            }

            set_flash('success', 'Service updated.');
        } else {
            $stmt = db()->prepare('
                INSERT INTO services (freelancer_id, title, category, description, price, delivery_days)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $user['id'],
                $service['title'],
                $service['category'],
                $service['description'],
                (float) $service['price'],
                $service['delivery_days'],
            ]);

            set_flash('success', 'Service published.');
        }

        redirect($backUrl);
    }
}

$pageTitle = $isEdit ? 'Edit Service' : 'Add Service';
$pageSubtitle = 'Clients will see this on the home page';
require __DIR__ . '/../includes/dash-top.php';
?>


<div class="dash-card">
    <div class="dash-card-head">
        <h2><?= $isEdit ? 'Edit service' : 'Post a new service' ?></h2>
    </div>
    <div class="dash-card-body pad">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <div class="form-grid">
            <div class="field col-span-2">
                <label for="title">Service title</label>
                <input type="text" id="title" name="title" value="<?= e($service['title']) ?>"
                       placeholder="I will build a responsive website in PHP" required>
            </div>

            <div class="field">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Choose a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>" <?= $service['category'] === $category ? 'selected' : '' ?>>
                            <?= e($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="price">Price (Rs.)</label>
                <input type="number" id="price" name="price" min="1" step="0.01"
                       value="<?= e((string) $service['price']) ?>" required>
            </div>

            <div class="field">
                <label for="delivery_days">Delivery time (days)</label>
                <input type="number" id="delivery_days" name="delivery_days" min="1"
                       value="<?= (int) $service['delivery_days'] ?>" required>
            </div>

            <div class="field">
                <label>&nbsp;</label>
                <div class="hint" style="padding-top:10px">Set a realistic turnaround time.</div>
            </div>

            <div class="field col-span-2">
                <label for="description">Description</label>
                <textarea id="description" name="description" style="min-height:180px" required><?= e($service['description']) ?></textarea>
                <div class="hint">Explain what the client gets, and what you need from them.</div>
            </div>

            <div class="field col-span-2">
                <button class="btn btn-primary btn-block" type="submit">
                    <?= $isEdit ? 'Save changes' : 'Publish service' ?>
                </button>
            </div>
        </div>
    </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/dash-bottom.php'; ?>
