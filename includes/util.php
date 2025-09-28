<?php
declare(strict_types=1);

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function format_price(int $priceCents, string $currency = 'USD'): string {
    $amount = number_format($priceCents / 100, 2);
    return "$amount $currency";
}

function asset_path(string $relative): string {
    // public web root is '/'
    if (str_starts_with($relative, '/')) {
        return $relative;
    }
    return '/' . ltrim($relative, '/');
}

function redirect(string $to): void {
    header('Location: ' . $to);
    exit;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        redirect('/login.php');
    }
}

function get_post_string(string $key): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function get_query_string(string $key): string {
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : '';
}

