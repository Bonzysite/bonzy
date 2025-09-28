<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = get_db();
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if ($productId > 0) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
    }
    redirect('/cart.php');
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (($_POST['qty'] ?? []) as $pid => $qty) {
        $pid = (int)$pid; $qty = (int)$qty;
        if ($qty <= 0) { unset($_SESSION['cart'][$pid]); } else { $_SESSION['cart'][$pid] = $qty; }
    }
    redirect('/cart.php');
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    redirect('/cart.php');
}

// Build cart details
$cart = $_SESSION['cart'];
$items = [];
$totalCents = 0;
if (!empty($cart)) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $db->prepare("SELECT id, title, price_cents, thumbnail_path FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $pid = (int)$row['id'];
        $qty = (int)$cart[$pid];
        $line = (int)$row['price_cents'] * $qty;
        $totalCents += $line;
        $items[] = [
            'id' => $pid,
            'title' => $row['title'],
            'price_cents' => (int)$row['price_cents'],
            'thumbnail_path' => $row['thumbnail_path'],
            'quantity' => $qty,
            'line_cents' => $line,
        ];
    }
}

render_header('Your cart — AGT Shop');
?>
<main class="container">
    <h2>Your cart</h2>
    <form method="post" action="/cart.php">
        <input type="hidden" name="action" value="update">
        <table>
            <thead>
                <tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?php echo h($it['title']); ?></td>
                    <td><?php echo format_price($it['price_cents']); ?></td>
                    <td><input type="number" name="qty[<?php echo (int)$it['id']; ?>]" value="<?php echo (int)$it['quantity']; ?>" min="0"></td>
                    <td><?php echo format_price($it['line_cents']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <tr><td colspan="4">Your cart is empty.</td></tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3" style="text-align:right">Total</th><th><?php echo format_price($totalCents); ?></th></tr>
            </tfoot>
        </table>
        <div class="actions" style="margin-top:12px">
            <button class="btn" type="submit">Update</button>
            <a class="btn btn-secondary" href="/cart.php?action=clear">Clear</a>
            <?php if ($totalCents > 0): ?>
            <a class="btn" href="/checkout.php">Checkout</a>
            <?php endif; ?>
        </div>
    </form>
</main>
<?php render_footer();

