<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();
$db = get_db();
$user = current_user();

$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$cart = $_SESSION['cart'];
if (empty($cart)) {
    redirect('/cart.php');
}

// Load items and compute total
$placeholders = implode(',', array_fill(0, count($cart), '?'));
$stmt = $db->prepare("SELECT id, title, price_cents FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($cart));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCents = 0;
$items = [];
foreach ($rows as $row) {
    $pid = (int)$row['id'];
    $qty = (int)$cart[$pid];
    $line = (int)$row['price_cents'] * $qty;
    $totalCents += $line;
    $items[] = [
        'product_id' => $pid,
        'title' => $row['title'],
        'price_cents' => (int)$row['price_cents'],
        'quantity' => $qty,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('INSERT INTO orders (buyer_id, total_cents, status) VALUES (:buyer, :total, :status)');
        $stmt->execute([':buyer' => $user['id'], ':total' => $totalCents, ':status' => 'paid']);
        $orderId = (int)$db->lastInsertId();

        $stmtItem = $db->prepare('INSERT INTO order_items (order_id, product_id, title, price_cents, quantity) VALUES (:oid, :pid, :title, :price, :qty)');
        foreach ($items as $it) {
            $stmtItem->execute([
                ':oid' => $orderId,
                ':pid' => $it['product_id'],
                ':title' => $it['title'],
                ':price' => $it['price_cents'],
                ':qty' => $it['quantity'],
            ]);
        }

        $db->commit();
        $_SESSION['cart'] = [];
        render_header('Order confirmed — AGT Shop');
        echo '<main class="container"><h2>Thank you!</h2><p>Your order has been placed.</p></main>';
        render_footer();
        exit;
    } catch (Throwable $e) {
        $db->rollBack();
        $error = 'Checkout failed. Please try again.';
    }
}

render_header('Checkout — AGT Shop');
?>
<main class="container">
    <h2>Checkout</h2>
    <?php if (!empty($error)): ?>
        <div class="card" style="padding:12px;color:#fca5a5;border-color:#b91c1c"><?php echo h($error); ?></div>
    <?php endif; ?>
    <p>Total: <strong><?php echo format_price($totalCents); ?></strong></p>
    <form method="post">
        <button class="btn" type="submit">Place order</button>
    </form>
</main>
<?php render_footer();

