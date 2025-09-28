<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = get_db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = get_post_string('name');
    $email = get_post_string('email');
    $password = get_post_string('password');
    $password2 = get_post_string('password2');

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    }
    if (empty($errors)) {
        if (create_user($db, $name, $email, $password)) {
            $user = authenticate_user($db, $email, $password);
            if ($user) {
                login_user($user);
                redirect('/seller/dashboard.php');
            }
        } else {
            $errors[] = 'Email already registered or invalid.';
        }
    }
}

render_header('Register — AGT Shop');
?>
<main class="container">
    <h2>Create your account</h2>
    <?php if (!empty($errors)): ?>
        <div class="card" style="padding:12px;color:#fca5a5;border-color:#b91c1c"><?php echo h(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form class="form" method="post">
        <label>
            Name
            <input type="text" name="name" value="<?php echo h(get_post_string('name')); ?>" required>
        </label>
        <label>
            Email
            <input type="email" name="email" value="<?php echo h(get_post_string('email')); ?>" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <label>
            Confirm Password
            <input type="password" name="password2" required>
        </label>
        <button class="btn" type="submit">Register</button>
    </form>
</main>
<?php render_footer();

