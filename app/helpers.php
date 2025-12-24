<?php

declare(strict_types=1);

function config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function now_utc(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone('UTC'));
}

function format_in_timezone(string $utcIso, string $timezone): string
{
    $date = new DateTimeImmutable($utcIso, new DateTimeZone('UTC'));
    $tz = new DateTimeZone($timezone);
    return $date->setTimezone($tz)->format('Y-m-d H:i') . ' ' . $timezone;
}

function is_valid_slug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9-]+$/', $slug);
}

function random_token(int $bytes = 32): string
{
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function hash_token(string $token): string
{
    $config = config();
    return hash_hmac('sha256', $token, $config['token_hmac_key']);
}

function ip_hash(?string $ip): ?string
{
    if ($ip === null || $ip === '') {
        return null;
    }
    $config = config();
    return hash_hmac('sha256', $ip, $config['ip_hash_salt']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = random_token(32);
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function ensure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_post_csrf(): void
{
    if (!isset($_POST['csrf_token']) || !verify_csrf((string) $_POST['csrf_token'])) {
        http_response_code(400);
        echo 'Invalid CSRF token.';
        exit;
    }
}
