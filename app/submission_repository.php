<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function get_submission_by_token(int $testId, string $tokenHash): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM submissions WHERE test_id = :test_id AND edit_token_hash = :token_hash LIMIT 1');
    $stmt->execute([
        ':test_id' => $testId,
        ':token_hash' => $tokenHash,
    ]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    return $submission ?: null;
}

function create_submission(int $testId, array $data): int
{
    $db = get_db();
    $stmt = $db->prepare(
        'INSERT INTO submissions (test_id, edit_token_hash, choice, feedback_text, anonymous,
        firstname, lastname, company, created_at, updated_at, user_agent, ip_hash)
        VALUES (:test_id, :edit_token_hash, :choice, :feedback_text, :anonymous,
        :firstname, :lastname, :company, :created_at, :updated_at, :user_agent, :ip_hash)'
    );
    $now = now_utc()->format(DATE_ATOM);
    $stmt->execute([
        ':test_id' => $testId,
        ':edit_token_hash' => $data['edit_token_hash'],
        ':choice' => $data['choice'],
        ':feedback_text' => $data['feedback_text'],
        ':anonymous' => $data['anonymous'],
        ':firstname' => $data['firstname'],
        ':lastname' => $data['lastname'],
        ':company' => $data['company'],
        ':created_at' => $now,
        ':updated_at' => $now,
        ':user_agent' => $data['user_agent'],
        ':ip_hash' => $data['ip_hash'],
    ]);
    return (int) $db->lastInsertId();
}

function update_submission(int $id, array $data): void
{
    $db = get_db();
    $stmt = $db->prepare(
        'UPDATE submissions SET choice = :choice, feedback_text = :feedback_text, anonymous = :anonymous,
        firstname = :firstname, lastname = :lastname, company = :company, updated_at = :updated_at
        WHERE id = :id'
    );
    $stmt->execute([
        ':choice' => $data['choice'],
        ':feedback_text' => $data['feedback_text'],
        ':anonymous' => $data['anonymous'],
        ':firstname' => $data['firstname'],
        ':lastname' => $data['lastname'],
        ':company' => $data['company'],
        ':updated_at' => now_utc()->format(DATE_ATOM),
        ':id' => $id,
    ]);
}

function list_submissions(int $testId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM submissions WHERE test_id = :test_id ORDER BY created_at DESC');
    $stmt->execute([':test_id' => $testId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function submission_stats(int $testId): array
{
    $db = get_db();
    $stmt = $db->prepare(
        'SELECT choice, COUNT(*) as count FROM submissions WHERE test_id = :test_id GROUP BY choice'
    );
    $stmt->execute([':test_id' => $testId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats = ['A' => 0, 'B' => 0, 'total' => 0];
    foreach ($rows as $row) {
        $choice = $row['choice'];
        $count = (int) $row['count'];
        if (isset($stats[$choice])) {
            $stats[$choice] = $count;
            $stats['total'] += $count;
        }
    }
    return $stats;
}

function feedback_by_variant(int $testId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT choice, feedback_text, anonymous, firstname, lastname, company, created_at FROM submissions WHERE test_id = :test_id ORDER BY created_at DESC');
    $stmt->execute([':test_id' => $testId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $grouped = ['A' => [], 'B' => []];
    foreach ($rows as $row) {
        $grouped[$row['choice']][] = $row;
    }
    return $grouped;
}
