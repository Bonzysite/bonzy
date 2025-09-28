<?php
declare(strict_types=1);

function render_header(string $title): void {
    $user = current_user();
    echo "<!doctype html>\n";
    echo "<html lang=\"en\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n<title>" . h($title) . "</title>\n<link rel=\"stylesheet\" href=\"/assets/css/style.css\">\n</head>\n<body>\n";
    echo "<header class=\"site-header\">\n  <div class=\"container\">\n    <a class=\"brand\" href=\"/\">AGT Shop</a>\n    <form class=\"search\" action=\"/browse.php\" method=\"get\">\n      <input type=\"text\" name=\"q\" placeholder=\"Search products\" value=\"" . h(get_query_string('q')) . "\">\n      <button type=\"submit\">Search</button>\n    </form>\n    <nav>\n      <a href=\"/browse.php\">Browse</a>\n      <a href=\"/cart.php\">Cart</a>\n";
    if ($user) {
        echo "      <a href=\"/seller/dashboard.php\">Sell</a>\n      <span class=\"user\">" . h($user['name']) . "</span>\n      <a href=\"/logout.php\">Logout</a>\n";
    } else {
        echo "      <a href=\"/register.php\">Register</a>\n      <a href=\"/login.php\">Login</a>\n";
    }
    echo "    </nav>\n  </div>\n</header>\n";
}

function render_footer(): void {
    echo "<footer class=\"site-footer\"><div class=\"container\">&copy; " . date('Y') . " AGT Shop</div></footer>\n</body>\n</html>";
}

