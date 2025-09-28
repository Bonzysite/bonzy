<?php
declare(strict_types=1);

function create_user(PDO $db, string $name, string $email, string $password): bool {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)');
    try {
        return $stmt->execute([
            ':name' => $name,
            ':email' => strtolower($email),
            ':hash' => $hash,
        ]);
    } catch (Throwable $e) {
        return false;
    }
}

function authenticate_user(PDO $db, string $email, string $password): ?array {
    $stmt = $db->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, (string)$user['password_hash'])) {
        unset($user['password_hash']);
        return $user;
    }
    return null;
}

function login_user(array $user): void {
    $_SESSION['user'] = $user;
}

function logout_user(): void {
    unset($_SESSION['user']);
}

