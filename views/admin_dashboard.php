<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
        <p class="text-sm text-gray-500">Manage A/B tests and review submissions.</p>
    </div>
    <div class="space-x-2">
        <a href="/admin/test/new" class="bg-black text-white px-4 py-2 rounded-lg">New Test</a>
        <a href="/admin/logout" class="border px-4 py-2 rounded-lg">Logout</a>
    </div>
</div>

<div class="grid gap-4">
    <?php if (empty($tests)): ?>
        <div class="card p-6 text-gray-500">No tests yet. Create one to get started.</div>
    <?php endif; ?>

    <?php foreach ($tests as $test): ?>
        <?php $expired = now_utc() > new DateTimeImmutable($test['expires_at_utc'], new DateTimeZone('UTC')); ?>
        <div class="card p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold"><?= e($test['title']) ?></h2>
                    <span class="badge"><?= $expired ? 'Expired' : 'Active' ?></span>
                </div>
                <p class="text-sm text-gray-500">Slug: /t/<?= e($test['slug']) ?></p>
                <p class="text-sm text-gray-500">Closes: <?= e(format_in_timezone($test['expires_at_utc'], $test['admin_timezone'])) ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/t/<?= e($test['slug']) ?>" class="border px-3 py-2 rounded-lg">View</a>
                <a href="/admin/test/edit?id=<?= (int) $test['id'] ?>" class="border px-3 py-2 rounded-lg">Edit</a>
                <a href="/admin/test/results?id=<?= (int) $test['id'] ?>" class="border px-3 py-2 rounded-lg">Results</a>
                <a href="/admin/test/export?id=<?= (int) $test['id'] ?>" class="border px-3 py-2 rounded-lg">Export CSV</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
