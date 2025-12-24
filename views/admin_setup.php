<h1 class="text-2xl font-semibold mb-6">Admin Setup</h1>

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

<form method="post" class="card p-6 space-y-4 max-w-md">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div>
        <label class="block text-sm font-medium">Admin Email</label>
        <input type="email" name="email" required class="mt-1 w-full border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Password</label>
        <input type="password" name="password" required class="mt-1 w-full border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Confirm Password</label>
        <input type="password" name="confirm_password" required class="mt-1 w-full border rounded-lg px-3 py-2">
    </div>
    <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg">Create Admin</button>
    <p class="text-xs text-gray-500">After creation, log in at <a href="/admin/login.php" class="underline">/admin/login.php</a>.</p>
</form>
