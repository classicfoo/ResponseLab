<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold"><?= e($isEdit ? 'Edit Test' : 'Create New Test') ?></h1>
        <p class="text-sm text-gray-500">Upload variant images and configure expiry.</p>
    </div>
    <div class="space-x-2">
        <a href="/admin" class="border px-4 py-2 rounded-lg">Back</a>
        <a href="/admin/logout" class="border px-4 py-2 rounded-lg">Logout</a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="card p-4 mb-4 border-red-200 bg-red-50">
        <ul class="list-disc pl-5 text-sm text-red-700">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="card p-4 mb-4 border-green-200 bg-green-50 text-green-700">
        <?= e($success) ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-6 space-y-6">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Title</label>
            <input type="text" name="title" value="<?= e($test['title'] ?? '') ?>" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Slug</label>
            <input type="text" name="slug" value="<?= e($test['slug'] ?? '') ?>" required class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="new-landing">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" required class="mt-1 w-full border rounded-lg px-3 py-2" rows="4"><?= e($test['description'] ?? '') ?></textarea>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Expiry date/time</label>
            <input type="datetime-local" name="expires_at" value="<?= e($test['expires_at_utc'] ? (new DateTimeImmutable($test['expires_at_utc'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($test['admin_timezone']))->format('Y-m-d\TH:i') : '') ?>" required class="mt-1 w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Admin timezone</label>
            <select name="admin_timezone" class="mt-1 w-full border rounded-lg px-3 py-2">
                <?php foreach (DateTimeZone::listIdentifiers() as $tz): ?>
                    <option value="<?= e($tz) ?>" <?= ($test['admin_timezone'] ?? '') === $tz ? 'selected' : '' ?>><?= e($tz) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Variant A images</label>
            <input type="file" name="images_a[]" multiple accept="image/jpeg,image/png,image/gif,image/webp" class="mt-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Variant B images</label>
            <input type="file" name="images_b[]" multiple accept="image/jpeg,image/png,image/gif,image/webp" class="mt-2">
        </div>
    </div>

    <?php if ($isEdit): ?>
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <h3 class="font-semibold mb-2">Variant A assets</h3>
                <div class="grid gap-3">
                    <?php foreach ($images as $image): ?>
                        <?php if ($image['variant'] !== 'A') { continue; } ?>
                        <div class="flex items-center gap-3 border rounded-lg p-3">
                            <img src="/<?= e($image['file_path']) ?>" alt="Variant A" class="h-16 w-20 object-cover rounded">
                            <form method="post" class="ml-auto">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_image">
                                <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                                <button type="submit" class="text-sm text-red-600">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Variant B assets</h3>
                <div class="grid gap-3">
                    <?php foreach ($images as $image): ?>
                        <?php if ($image['variant'] !== 'B') { continue; } ?>
                        <div class="flex items-center gap-3 border rounded-lg p-3">
                            <img src="/<?= e($image['file_path']) ?>" alt="Variant B" class="h-16 w-20 object-cover rounded">
                            <form method="post" class="ml-auto">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_image">
                                <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                                <button type="submit" class="text-sm text-red-600">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="close_test">
                <button type="submit" class="border px-4 py-2 rounded-lg">Close test early</button>
            </form>
            <p class="text-xs text-gray-500">Closing sets expiry to now and stops participation.</p>
        </div>
    <?php endif; ?>

    <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg">Save Test</button>
</form>
