<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = get_db();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$stmt = $db->prepare('SELECT p.*, u.name as seller_name FROM products p JOIN users u ON u.id = p.user_id WHERE p.id = :id AND p.is_active = 1');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    echo 'Product not found';
    exit;
}

render_header(h($product['title']) . ' — AGT Shop');
?>
<main class="container">
    <article class="card" style="padding:16px">
        <div class="thumb" style="height:320px">
            <?php if (!empty($product['thumbnail_path'])): ?>
                <img src="<?php echo asset_path($product['thumbnail_path']); ?>" alt="<?php echo h($product['title']); ?>" />
            <?php else: ?>
                <div class="thumb-placeholder">No image</div>
            <?php endif; ?>
        </div>
        <h1><?php echo h($product['title']); ?></h1>
        <div class="price"><?php echo format_price((int)$product['price_cents']); ?></div>
        <p>Seller: <?php echo h($product['seller_name']); ?></p>
        <p><?php echo nl2br(h((string)$product['description'])); ?></p>
        <form method="post" action="/cart.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
            <label>Quantity <input type="number" name="quantity" value="1" min="1" max="99"></label>
            <button class="btn" type="submit">Add to cart</button>
        </form>
    </article>
</main>
<?php render_footer();

