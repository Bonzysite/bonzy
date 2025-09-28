<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_login();
$db = get_db();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM products WHERE id = :id AND user_id = :uid');
$stmt->execute([':id' => $id, ':uid' => $user['id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    http_response_code(404);
    echo 'Product not found';
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = get_post_string('title');
    $description = get_post_string('description');
    $priceCents = (int)round(((float)($_POST['price'] ?? '0')) * 100);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $thumbPath = $product['thumbnail_path'];
    if (!empty($_FILES['thumbnail']['name']) && is_uploaded_file($_FILES['thumbnail']['tmp_name'])) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $safeName = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dest = AGT_UPLOADS_PATH . '/' . $safeName;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dest)) {
            @chmod($dest, 0664);
            $thumbPath = '/uploads/' . $safeName;
        }
    }

    if ($title === '' || $priceCents <= 0) {
        $errors[] = 'Title and positive price are required.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare('UPDATE products SET title = :title, description = :desc, price_cents = :price, thumbnail_path = :thumb, is_active = :active, updated_at = datetime("now") WHERE id = :id AND user_id = :uid');
        $stmt->execute([
            ':title' => $title,
            ':desc' => $description,
            ':price' => $priceCents,
            ':thumb' => $thumbPath !== '' ? $thumbPath : null,
            ':active' => $isActive,
            ':id' => $id,
            ':uid' => $user['id'],
        ]);
        redirect('/seller/dashboard.php');
    }
}

render_header('Edit product — AGT Shop');
?>
<main class="container">
    <h2>Edit product</h2>
    <?php if (!empty($errors)): ?>
        <div class="card" style="padding:12px;color:#fca5a5;border-color:#b91c1c"><?php echo h(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form class="form" method="post" enctype="multipart/form-data">
        <label>Title <input type="text" name="title" value="<?php echo h($product['title']); ?>" required></label>
        <label>Description <textarea name="description"><?php echo h((string)$product['description']); ?></textarea></label>
        <label>Price (USD) <input type="number" name="price" step="0.01" min="0" value="<?php echo number_format(((int)$product['price_cents'])/100, 2, '.', ''); ?>" required></label>
        <label>Thumbnail <input type="file" name="thumbnail" accept="image/*"></label>
        <label><input type="checkbox" name="is_active" <?php echo ((int)$product['is_active'] === 1) ? 'checked' : ''; ?>> Active</label>
        <button class="btn" type="submit">Save</button>
    </form>
</main>
<?php render_footer();

