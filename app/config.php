<?php

declare(strict_types=1);

return [
    'db_path' => __DIR__ . '/../data/app.sqlite',
    'uploads_dir' => __DIR__ . '/../uploads',
    'uploads_web_path' => '/uploads',
    'max_upload_bytes' => 15 * 1024 * 1024,
    'csrf_key' => 'change-this-secret',
    'token_hmac_key' => 'change-this-token-secret',
    'ip_hash_salt' => 'change-this-ip-salt',
    'default_timezone' => 'Australia/Sydney',
];
