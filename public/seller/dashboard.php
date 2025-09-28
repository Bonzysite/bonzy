<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_login();
$db = get_db();
$user = current_user();

// Handle deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid > 0) {
        $stmt = $db->prepare('DELETE FROM products WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => $pid, ':uid' => $user['id']]);
    }
    redirect('/seller/dashboard.php');
}

// Fetch seller products
$stmt = $db->prepare('SELECT id, title, price_cents, is_active, created_at FROM products WHERE user_id = :uid ORDER BY created_at DESC');
$stmt->execute([':uid' => $user['id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

render_header('Seller dashboard — AGT Shop');
?>
<main class="container">
    <div class="actions" style="margin:16px 0">
        <a class="btn" href="/seller/new.php">New product</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo h($p['title']); ?></td>
                <td><?php echo format_price((int)$p['price_cents']); ?></td>
                <td><?php echo ((int)$p['is_active'] === 1) ? 'Active' : 'Hidden'; ?></td>
                <td class="actions">
                    <a class="btn btn-secondary" href="/seller/edit.php?id=<?php echo (int)$p['id']; ?>">Edit</a>
                    <form method="post" action="/seller/dashboard.php" onsubmit="return confirm('Delete this product?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                        <button class="btn" style="background:#7f1d1d" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <tr><td colspan="4">No products yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</main>
<?php render_footer();

