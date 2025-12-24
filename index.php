<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/test_repository.php';
require_once __DIR__ . '/../app/submission_repository.php';
require_once __DIR__ . '/../app/view.php';

ensure_session();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '') {
    render('home', [
        'title' => 'ResponseLab',
    ]);
    exit;
}

if (preg_match('#^/t/([a-z0-9-]+)$#', $path, $matches)) {
    $slug = $matches[1];
    $test = get_test_by_slug($slug);
    if (!$test) {
        http_response_code(404);
        echo 'Test not found.';
        exit;
    }

    $images = get_images_for_test((int) $test['id']);
    $variantAImages = array_values(array_filter($images, fn ($img) => $img['variant'] === 'A'));
    $variantBImages = array_values(array_filter($images, fn ($img) => $img['variant'] === 'B'));

    $editToken = isset($_GET['edit']) ? (string) $_GET['edit'] : null;
    $cookieName = 'abtest_' . $slug;
    if ($editToken) {
        setcookie($cookieName, $editToken, [
            'expires' => time() + 60 * 60 * 24 * 365,
            'path' => '/t/' . $slug,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } elseif (!empty($_COOKIE[$cookieName])) {
        $editToken = (string) $_COOKIE[$cookieName];
    }

    $existingSubmission = null;
    if ($editToken) {
        $existingSubmission = get_submission_by_token((int) $test['id'], hash_token($editToken));
    }

    $errors = [];
    $success = null;
    $editLink = $_SESSION['edit_link'] ?? null;
    unset($_SESSION['edit_link']);

    $isExpired = now_utc() > new DateTimeImmutable($test['expires_at_utc'], new DateTimeZone('UTC'));

    $currentToken = $editToken;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_post_csrf();
        if ($isExpired) {
            $errors[] = 'This test is closed. Submissions are read-only.';
        } else {
            $choice = strtoupper(trim((string) ($_POST['choice'] ?? '')));
            $feedbackKey = $choice === 'A' ? 'feedback_a' : 'feedback_b';
            $feedback = trim((string) ($_POST[$feedbackKey] ?? ''));
            $anonymous = isset($_POST['anonymous']) ? 1 : 0;
            $firstname = trim((string) ($_POST['firstname'] ?? ''));
            $lastname = trim((string) ($_POST['lastname'] ?? ''));
            $company = trim((string) ($_POST['company'] ?? ''));

            if (!in_array($choice, ['A', 'B'], true)) {
                $errors[] = 'Please choose a variant.';
            }
            if ($feedback === '') {
                $errors[] = 'Please provide feedback.';
            }
            if ($anonymous === 0) {
                if ($firstname === '' || $lastname === '') {
                    $errors[] = 'First name and last name are required when not anonymous.';
                }
            } else {
                $firstname = '';
                $lastname = '';
                $company = '';
            }

            if (empty($errors)) {
                $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
                $ipHash = ip_hash($_SERVER['REMOTE_ADDR'] ?? null);

                if ($existingSubmission) {
                    update_submission((int) $existingSubmission['id'], [
                        'choice' => $choice,
                        'feedback_text' => $feedback,
                        'anonymous' => $anonymous,
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'company' => $company,
                    ]);
                    $success = 'Your submission has been updated.';
                } else {
                    $newToken = random_token(32);
                    $currentToken = $newToken;
                    $tokenHash = hash_token($newToken);
                    create_submission((int) $test['id'], [
                        'edit_token_hash' => $tokenHash,
                        'choice' => $choice,
                        'feedback_text' => $feedback,
                        'anonymous' => $anonymous,
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'company' => $company,
                        'user_agent' => $userAgent,
                        'ip_hash' => $ipHash,
                    ]);

                    setcookie($cookieName, $newToken, [
                        'expires' => time() + 60 * 60 * 24 * 365,
                        'path' => '/t/' . $slug,
                        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                    $editLink = '/t/' . $slug . '?edit=' . urlencode($newToken);
                    $_SESSION['edit_link'] = $editLink;
                    redirect($editLink);
                }
                if ($currentToken) {
                    $existingSubmission = get_submission_by_token((int) $test['id'], hash_token($currentToken));
                }
            }
        }
    }

    $stats = submission_stats((int) $test['id']);
    $feedback = feedback_by_variant((int) $test['id']);
    render('participant_test', [
        'title' => $test['title'],
        'test' => $test,
        'variantAImages' => $variantAImages,
        'variantBImages' => $variantBImages,
        'existingSubmission' => $existingSubmission,
        'errors' => $errors,
        'success' => $success,
        'editLink' => $editLink,
        'isExpired' => $isExpired,
        'stats' => $stats,
        'feedback' => $feedback,
    ]);
    exit;
}

http_response_code(404);
echo 'Not found.';
