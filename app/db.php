<?php

declare(strict_types=1);

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $pdo = new PDO('sqlite:' . $config['db_path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL
        );"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            expires_at_utc TEXT NOT NULL,
            admin_timezone TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            test_id INTEGER NOT NULL,
            variant TEXT NOT NULL,
            file_path TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            uploaded_at TEXT NOT NULL,
            FOREIGN KEY(test_id) REFERENCES tests(id) ON DELETE CASCADE
        );"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            test_id INTEGER NOT NULL,
            edit_token_hash TEXT NOT NULL,
            choice TEXT NOT NULL,
            feedback_text TEXT NOT NULL,
            anonymous INTEGER NOT NULL DEFAULT 1,
            firstname TEXT,
            lastname TEXT,
            company TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            user_agent TEXT,
            ip_hash TEXT,
            FOREIGN KEY(test_id) REFERENCES tests(id) ON DELETE CASCADE
        );"
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_images_test ON images(test_id);');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_submissions_test ON submissions(test_id);');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_submissions_token ON submissions(edit_token_hash);');

    return $pdo;
}
