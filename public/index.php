<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'AGT Shop — Home';

$db = get_db();

// Fetch featured/latest products
try {
    $stmt = $db->prepare('SELECT p.id, p.title, p.price_cents, p.thumbnail_path FROM products p WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 12');
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If products table doesn't exist, initialize empty array and log error
    $products = [];
    error_log("Database error in index.php: " . $e->getMessage());
}

render_header($pageTitle);
?>
<main class="container">
    <section class="hero">
        <h1>AGT Shop</h1>
        <p>Buy and sell goods with confidence.</p>
        <div class="hero-actions">
            <a class="btn" href="/browse.php">Browse products</a>
            <?php if (!current_user()): ?>
                <a class="btn btn-secondary" href="/register.php">Start selling</a>
            <?php else: ?>
                <a class="btn btn-secondary" href="/seller/dashboard.php">Seller dashboard</a>
            <?php endif; ?>
        </div>
    </section>

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
            <p>No products yet. Be the first to <a href="/register.php">list an item</a>.</p>
        <?php endif; ?>
    </section>
</main>
<?php render_footer();

