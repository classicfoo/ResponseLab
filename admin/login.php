<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/view.php';

ensure_session();

if (admin_logged_in()) {
    redirect('/admin');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } elseif (!admin_login($email, $password)) {
        $errors[] = 'Invalid login credentials.';
    } else {
        redirect('/admin');
    }
}

render('admin_login', [
    'title' => 'Admin Login',
    'errors' => $errors,
]);
