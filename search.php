<?php
require_once __DIR__ . '/includes/auth.php';

$search   = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort     = $_GET['sort'] ?? 'newest';

$sortMap = [
    'newest'     => 's.created_at DESC',
    'price_low'  => 's.price ASC',
    'price_high' => 's.price DESC',
    'fast'       => 's.delivery_days ASC',
];
if (!isset($sortMap[$sort])) {
    $sort = 'newest';
}

$sql = '
    SELECT s.*, u.name AS freelancer_name
    FROM services s
    JOIN users u ON u.id = s.freelancer_id
    WHERE 1
';
$params = [];

if ($search !== '') {
    $sql .= ' AND (s.title LIKE ? OR s.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category !== '') {
    $sql .= ' AND s.category = ?';
    $params[] = $category;
}
$sql .= ' ORDER BY ' . $sortMap[$sort];

$stmt = db()->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$categories = db()->query('SELECT DISTINCT category FROM services ORDER BY category')->fetchAll();

$pageTitle = 'Browse Services';
require __DIR__ . '/includes/header.php';
?>

<div class="browse-head">
    <div>
        <h1>Browse services</h1>
        <p class="muted"><?= count($services) ?> service<?= count($services) === 1 ? '' : 's' ?> found</p>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('search.php') ?>">
    <div class="filter-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search services — e.g. website, logo, PHP">
    </div>
    <select name="category">
        <option value="">All categories</option>
        <?php foreach ($categories as $row): ?>
            <option value="<?= e($row['category']) ?>" <?= $category === $row['category'] ? 'selected' : '' ?>><?= e($row['category']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit">Search</button>
    <?php if ($search !== '' || $category !== ''): ?>
        <a class="btn btn-ghost" href="<?= url('search.php') ?>">Reset</a>
    <?php endif; ?>
</form>

<div class="chip-row">
    <a class="chip <?= $category === '' ? 'active' : '' ?>" href="<?= url('search.php' . ($search !== '' ? '?q=' . urlencode($search) : '')) ?>">All</a>
    <?php foreach ($categories as $row): ?>
        <a class="chip <?= $category === $row['category'] ? 'active' : '' ?>"
           href="<?= url('search.php?category=' . urlencode($row['category']) . ($search !== '' ? '&q=' . urlencode($search) : '')) ?>">
            <?= e($row['category']) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (!$services): ?>
    <div class="empty">
        <p>No services match your filters.</p>
        <a class="btn btn-primary" href="<?= url('search.php') ?>">Clear filters</a>
    </div>
<?php else: ?>
    <div class="service-grid">
        <?php foreach ($services as $service): ?>
            <article class="service-card">
                <span class="tag"><?= e($service['category']) ?></span>
                <h3><a href="<?= url('service.php?id=' . (int) $service['id']) ?>"><?= e($service['title']) ?></a></h3>
                <p><?php $d = $service['description']; echo e(mb_strlen($d) > 110 ? mb_substr($d, 0, 110) . '...' : $d); ?></p>
                <div class="service-meta">
                    <div>
                        <div class="price"><?= money((float) $service['price']) ?></div>
                        <div class="seller">by <?= e($service['freelancer_name']) ?></div>
                    </div>
                    <a class="btn btn-outline btn-sm" href="<?= url('service.php?id=' . (int) $service['id']) ?>">View</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
