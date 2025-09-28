<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = get_db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = get_post_string('email');
    $password = get_post_string('password');
    $user = authenticate_user($db, $email, $password);
    if ($user) {
        login_user($user);
        redirect('/');
    } else {
        $errors[] = 'Invalid credentials.';
    }
}

render_header('Login — AGT Shop');
?>
<main class="container">
    <h2>Login</h2>
    <?php if (!empty($errors)): ?>
        <div class="card" style="padding:12px;color:#fca5a5;border-color:#b91c1c"><?php echo h(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form class="form" method="post">
        <label>
            Email
            <input type="email" name="email" value="<?php echo h(get_post_string('email')); ?>" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button class="btn" type="submit">Login</button>
    </form>
</main>
<?php render_footer();

