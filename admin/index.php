<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/test_repository.php';
require_once __DIR__ . '/../app/submission_repository.php';
require_once __DIR__ . '/../app/view.php';

ensure_session();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');

if ($path === '/admin/login') {
    redirect('/admin/login.php');
}

if ($path === '/admin/logout') {
    admin_logout();
    redirect('/admin/login.php');
}

require_admin();

if ($path === '/admin' || $path === '') {
    $tests = list_tests();
    render('admin_dashboard', [
        'title' => 'Admin Dashboard',
        'tests' => $tests,
    ]);
    exit;
}

if ($path === '/admin/test/new' || $path === '/admin/test/edit') {
    $isEdit = $path === '/admin/test/edit';
    $errors = [];
    $success = null;
    $test = [
        'id' => null,
        'slug' => '',
        'title' => '',
        'description' => '',
        'expires_at_utc' => '',
        'admin_timezone' => config()['default_timezone'],
    ];
    $images = [];

    if ($isEdit) {
        $id = (int) ($_GET['id'] ?? 0);
        $test = $id ? get_test_by_id($id) : null;
        if (!$test) {
            http_response_code(404);
            echo 'Test not found.';
            exit;
        }
        $images = get_images_for_test((int) $test['id']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_post_csrf();
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'delete_image') {
            $imageId = (int) ($_POST['image_id'] ?? 0);
            if ($imageId) {
                delete_image($imageId);
            }
            redirect('/admin/test/edit?id=' . (int) $test['id']);
        }

        if ($action === 'close_test') {
            close_test_now((int) $test['id']);
            redirect('/admin/test/edit?id=' . (int) $test['id']);
        }

        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $expiresLocal = trim((string) ($_POST['expires_at'] ?? ''));
        $adminTimezone = trim((string) ($_POST['admin_timezone'] ?? config()['default_timezone']));

        if ($slug === '' || !is_valid_slug($slug)) {
            $errors[] = 'Slug must be lowercase letters, numbers, and hyphens only.';
        }
        if (slug_exists($slug, $isEdit ? (int) $test['id'] : null)) {
            $errors[] = 'Slug is already in use.';
        }
        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        if ($description === '') {
            $errors[] = 'Description is required.';
        }
        if ($expiresLocal === '') {
            $errors[] = 'Expiry date/time is required.';
        }
        try {
            $localDate = new DateTimeImmutable($expiresLocal, new DateTimeZone($adminTimezone));
            $expiresUtc = $localDate->setTimezone(new DateTimeZone('UTC'))->format(DATE_ATOM);
        } catch (Exception $exception) {
            $errors[] = 'Invalid expiry date/time.';
            $expiresUtc = '';
        }

        if (empty($errors)) {
            $payload = [
                'slug' => $slug,
                'title' => $title,
                'description' => $description,
                'expires_at_utc' => $expiresUtc,
                'admin_timezone' => $adminTimezone,
            ];
            if ($isEdit) {
                update_test((int) $test['id'], $payload);
                $test = get_test_by_id((int) $test['id']);
            } else {
                $newId = create_test($payload);
                $test = get_test_by_id($newId);
                $isEdit = true;
            }

            handle_uploads((int) $test['id'], $slug, 'A');
            handle_uploads((int) $test['id'], $slug, 'B');

            $success = 'Test saved.';
            $images = get_images_for_test((int) $test['id']);
        }
    }

    render('admin_test_form', [
        'title' => $isEdit ? 'Edit Test' : 'New Test',
        'test' => $test,
        'isEdit' => $isEdit,
        'errors' => $errors,
        'success' => $success,
        'images' => $images,
    ]);
    exit;
}

if ($path === '/admin/test/results') {
    $id = (int) ($_GET['id'] ?? 0);
    $test = $id ? get_test_by_id($id) : null;
    if (!$test) {
        http_response_code(404);
        echo 'Test not found.';
        exit;
    }
    $stats = submission_stats((int) $test['id']);
    $feedback = feedback_by_variant((int) $test['id']);
    $submissions = list_submissions((int) $test['id']);
    render('results_public', [
        'title' => 'Results - ' . $test['title'],
        'test' => $test,
        'stats' => $stats,
        'feedback' => $feedback,
        'submissions' => $submissions,
        'isAdmin' => true,
    ]);
    exit;
}

if ($path === '/admin/test/export') {
    $id = (int) ($_GET['id'] ?? 0);
    $test = $id ? get_test_by_id($id) : null;
    if (!$test) {
        http_response_code(404);
        echo 'Test not found.';
        exit;
    }
    $submissions = list_submissions((int) $test['id']);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="test-' . $test['slug'] . '-submissions.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'submission_id', 'created_at', 'updated_at', 'choice', 'feedback_text', 'anonymous',
        'firstname', 'lastname', 'company', 'user_agent', 'ip_hash', 'test_slug', 'test_title'
    ]);
    foreach ($submissions as $submission) {
        fputcsv($out, [
            $submission['id'],
            $submission['created_at'],
            $submission['updated_at'],
            $submission['choice'],
            $submission['feedback_text'],
            $submission['anonymous'],
            $submission['firstname'],
            $submission['lastname'],
            $submission['company'],
            $submission['user_agent'],
            $submission['ip_hash'],
            $test['slug'],
            $test['title'],
        ]);
    }
    fclose($out);
    exit;
}

http_response_code(404);
echo 'Not found.';

function handle_uploads(int $testId, string $slug, string $variant): void
{
    $config = config();
    $key = $variant === 'A' ? 'images_a' : 'images_b';
    if (empty($_FILES[$key]['name'][0])) {
        return;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    $uploadDir = rtrim($config['uploads_dir'], '/') . '/' . $slug;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $db = get_db();
    $stmt = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM images WHERE test_id = :test_id AND variant = :variant');
    $stmt->execute([':test_id' => $testId, ':variant' => $variant]);
    $sortOrder = (int) $stmt->fetchColumn();

    $fileCount = count($_FILES[$key]['name']);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES[$key]['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        if ($_FILES[$key]['size'][$i] > $config['max_upload_bytes']) {
            continue;
        }
        $tmpName = $_FILES[$key]['tmp_name'][$i];
        $mime = $finfo->file($tmpName);
        if (!isset($allowed[$mime])) {
            continue;
        }
        $extension = $allowed[$mime];
        $filename = random_token(16) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;
        if (move_uploaded_file($tmpName, $destination)) {
            $relativePath = 'uploads/' . $slug . '/' . $filename;
            $sortOrder++;
            add_image($testId, $variant, $relativePath, $sortOrder);
        }
    }
}
