<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function render(string $view, array $params = []): void
{
    extract($params, EXTR_SKIP);
    $view_file = __DIR__ . '/../views/' . $view . '.php';
    if (!is_file($view_file)) {
        http_response_code(500);
        echo 'View not found.';
        exit;
    }
    include __DIR__ . '/../views/layout.php';
}
