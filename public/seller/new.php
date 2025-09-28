<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

require_login();
$db = get_db();
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = get_post_string('title');
    $description = get_post_string('description');
    $priceCents = (int)round(((float)($_POST['price'] ?? '0')) * 100);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $thumbPath = '';
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
        $stmt = $db->prepare('INSERT INTO products (user_id, title, description, price_cents, thumbnail_path, is_active) VALUES (:uid, :title, :desc, :price, :thumb, :active)');
        $stmt->execute([
            ':uid' => $user['id'],
            ':title' => $title,
            ':desc' => $description,
            ':price' => $priceCents,
            ':thumb' => $thumbPath !== '' ? $thumbPath : null,
            ':active' => $isActive,
        ]);
        redirect('/seller/dashboard.php');
    }
}

render_header('New product — AGT Shop');
?>
<main class="container">
    <h2>New product</h2>
    <?php if (!empty($errors)): ?>
        <div class="card" style="padding:12px;color:#fca5a5;border-color:#b91c1c"><?php echo h(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form class="form" method="post" enctype="multipart/form-data">
        <label>Title <input type="text" name="title" required></label>
        <label>Description <textarea name="description"></textarea></label>
        <label>Price (USD) <input type="number" name="price" step="0.01" min="0" required></label>
        <label>Thumbnail <input type="file" name="thumbnail" accept="image/*"></label>
        <label><input type="checkbox" name="is_active" checked> Active</label>
        <button class="btn" type="submit">Create</button>
    </form>
</main>
<?php render_footer();

