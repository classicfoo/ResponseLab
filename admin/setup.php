<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/view.php';

ensure_session();

if (admin_exists()) {
    http_response_code(403);
    echo 'Admin already configured.';
    exit;
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($password === '' || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        create_admin($email, $password);
        $success = 'Admin account created. You can now log in.';
    }
}

render('admin_setup', [
    'title' => 'Admin Setup',
    'errors' => $errors,
    'success' => $success,
]);
