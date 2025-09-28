<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = get_db();
$q = trim(get_query_string('q'));

if ($q !== '') {
    $stmt = $db->prepare('SELECT id, title, price_cents, thumbnail_path FROM products WHERE is_active = 1 AND (title LIKE :q OR description LIKE :q) ORDER BY created_at DESC');
    $stmt->execute([':q' => '%' . $q . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $title = 'Search: ' . $q . ' — AGT Shop';
} else {
    $stmt = $db->query('SELECT id, title, price_cents, thumbnail_path FROM products WHERE is_active = 1 ORDER BY created_at DESC');
    $products = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    $title = 'Browse — AGT Shop';
}

render_header($title);
?>
<main class="container">
    <h2>Browse products</h2>
    <?php if ($q !== ''): ?>
        <p>Results for "<?php echo h($q); ?>"</p>
    <?php endif; ?>
    <section class="grid">
        <?php foreach ($products as $product): ?>
            <a class="card" href="/product.php?id=<?php echo (int)$product['id']; ?>">
                <div class="thumb">
                    <?php if (!empty($product['thumbnail_path'])): ?>
                        <img src="<?php echo asset_path($product['thumbnail_path']); ?>" alt="<?php echo h($product['title']); ?>" />
                    <?php else: ?>
                        <div class="thumb-placeholder">No image</div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?php echo h($product['title']); ?></h3>
                    <div class="price"><?php echo format_price((int)$product['price_cents']); ?></div>
                </div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <p>No products found.</p>
        <?php endif; ?>
    </section>
</main>
<?php render_footer();

