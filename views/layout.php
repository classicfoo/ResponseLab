<?php
/** @var string $title */
/** @var string $view_file */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'ResponseLab') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <?php include $view_file; ?>
    </div>
    <script src="/assets/app.js"></script>
</body>
</html>
