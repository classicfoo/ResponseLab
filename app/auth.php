<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function admin_logged_in(): bool
{
    ensure_session();
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        redirect('/admin/login');
    }
}

function admin_login(string $email, string $password): bool
{
    $db = get_db();
    $stmt = $db->prepare('SELECT id, password_hash FROM admins WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    ensure_session();
    $_SESSION['admin_id'] = (int) $admin['id'];
    return true;
}

function admin_logout(): void
{
    ensure_session();
    unset($_SESSION['admin_id']);
}

function admin_exists(): bool
{
    $db = get_db();
    $stmt = $db->query('SELECT COUNT(*) as count FROM admins');
    $count = (int) $stmt->fetchColumn();
    return $count > 0;
}

function create_admin(string $email, string $password): void
{
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO admins (email, password_hash, created_at) VALUES (:email, :password_hash, :created_at)');
    $stmt->execute([
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':created_at' => now_utc()->format(DATE_ATOM),
    ]);
}
