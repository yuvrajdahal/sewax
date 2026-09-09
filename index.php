<?php
require_once __DIR__ . '/includes/auth.php';

$search   = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

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

$sql .= ' ORDER BY s.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$categories = db()->query('SELECT DISTINCT category FROM services ORDER BY category')->fetchAll();

$pageTitle = 'Browse Services';
require __DIR__ . '/includes/header.php';
?>

<section class="home-hero">
    <div class="home-hero-inner">
        <h1>Skilled freelancers,<br>ready to build what you need</h1>

        <form class="hero-search" method="get" action="<?= url('search.php') ?>">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search services — e.g. website, logo, PHP">
            <button type="submit" aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>

        <?php if ($categories): ?>
            <div class="hero-pills">
                <?php foreach (array_slice($categories, 0, 6) as $row): ?>
                    <a class="hero-pill" href="<?= url('search.php?category=' . urlencode($row['category'])) ?>">
                        <?= e($row['category']) ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<section class="feature-band">
    <h2>Make it all happen with freelancers</h2>
    <div class="feature-grid">
        <div class="feature">
            <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
            <h3>A pool of local talent</h3>
            <p>Web developers, designers and programmers across every category.</p>
        </div>
        <div class="feature">
            <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg></span>
            <h3>A simple booking flow</h3>
            <p>Send a request, get matched, and track it from one place.</p>
        </div>
        <div class="feature">
            <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1 0-4h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg></span>
            <h3>Work within budget</h3>
            <p>Set an hourly rate or a fixed project budget on every booking.</p>
        </div>
        <div class="feature">
            <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></span>
            <h3>Clear from start to done</h3>
            <p>See every booking's status, from request through completion.</p>
        </div>
    </div>
</section>

<section class="listing-section">
    <div class="listing-head">
        <h2>Latest services</h2>
        <a class="btn btn-outline btn-sm" href="<?= url('search.php') ?>">Browse all services</a>
    </div>
</section>

<?php if (!$services): ?>
    <div class="empty">
        <p>No services match your search yet.</p>
    </div>
<?php else: ?>
    <div class="service-grid">
        <?php foreach (array_slice($services, 0, 8) as $service): ?>
            <article class="service-card">
                <span class="tag"><?= e($service['category']) ?></span>

                <h3>
                    <a href="<?= url('service.php?id=' . (int) $service['id']) ?>">
                        <?= e($service['title']) ?>
                    </a>
                </h3>

                <p>
                    <?php
                    $summary = $service['description'];
                    echo e(mb_strlen($summary) > 110 ? mb_substr($summary, 0, 110) . '...' : $summary);
                    ?>
                </p>

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
