<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function list_tests(): array
{
    $db = get_db();
    $stmt = $db->query('SELECT * FROM tests ORDER BY created_at DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_test_by_slug(string $slug): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM tests WHERE slug = :slug LIMIT 1');
    $stmt->execute([':slug' => $slug]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    return $test ?: null;
}

function get_test_by_id(int $id): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM tests WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    return $test ?: null;
}

function create_test(array $data): int
{
    $db = get_db();
    $stmt = $db->prepare(
        'INSERT INTO tests (slug, title, description, expires_at_utc, admin_timezone, created_at, updated_at)
        VALUES (:slug, :title, :description, :expires_at_utc, :admin_timezone, :created_at, :updated_at)'
    );
    $now = now_utc()->format(DATE_ATOM);
    $stmt->execute([
        ':slug' => $data['slug'],
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':expires_at_utc' => $data['expires_at_utc'],
        ':admin_timezone' => $data['admin_timezone'],
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    return (int) $db->lastInsertId();
}

function update_test(int $id, array $data): void
{
    $db = get_db();
    $stmt = $db->prepare(
        'UPDATE tests SET slug = :slug, title = :title, description = :description,
        expires_at_utc = :expires_at_utc, admin_timezone = :admin_timezone, updated_at = :updated_at
        WHERE id = :id'
    );
    $stmt->execute([
        ':slug' => $data['slug'],
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':expires_at_utc' => $data['expires_at_utc'],
        ':admin_timezone' => $data['admin_timezone'],
        ':updated_at' => now_utc()->format(DATE_ATOM),
        ':id' => $id,
    ]);
}

function close_test_now(int $id): void
{
    $db = get_db();
    $stmt = $db->prepare('UPDATE tests SET expires_at_utc = :expires_at_utc, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        ':expires_at_utc' => now_utc()->format(DATE_ATOM),
        ':updated_at' => now_utc()->format(DATE_ATOM),
        ':id' => $id,
    ]);
}

function add_image(int $testId, string $variant, string $filePath, int $sortOrder): void
{
    $db = get_db();
    $stmt = $db->prepare(
        'INSERT INTO images (test_id, variant, file_path, sort_order, uploaded_at)
        VALUES (:test_id, :variant, :file_path, :sort_order, :uploaded_at)'
    );
    $stmt->execute([
        ':test_id' => $testId,
        ':variant' => $variant,
        ':file_path' => $filePath,
        ':sort_order' => $sortOrder,
        ':uploaded_at' => now_utc()->format(DATE_ATOM),
    ]);
}

function delete_image(int $imageId): void
{
    $db = get_db();
    $stmt = $db->prepare('SELECT file_path FROM images WHERE id = :id');
    $stmt->execute([':id' => $imageId]);
    $filePath = $stmt->fetchColumn();
    if ($filePath) {
        $fullPath = __DIR__ . '/../' . ltrim($filePath, '/');
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
    $delete = $db->prepare('DELETE FROM images WHERE id = :id');
    $delete->execute([':id' => $imageId]);
}

function get_images_for_test(int $testId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM images WHERE test_id = :test_id ORDER BY sort_order ASC, id ASC');
    $stmt->execute([':test_id' => $testId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function slug_exists(string $slug, ?int $excludeId = null): bool
{
    $db = get_db();
    if ($excludeId) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM tests WHERE slug = :slug AND id != :id');
        $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
    } else {
        $stmt = $db->prepare('SELECT COUNT(*) FROM tests WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
    }
    return (int) $stmt->fetchColumn() > 0;
}
