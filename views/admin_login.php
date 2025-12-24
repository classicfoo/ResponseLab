<h1 class="text-2xl font-semibold mb-6">Admin Login</h1>

<?php if (!empty($errors)): ?>
    <div class="card p-4 mb-4 border-red-200 bg-red-50">
        <ul class="list-disc pl-5 text-sm text-red-700">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="card p-6 space-y-4 max-w-md">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div>
        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" required class="mt-1 w-full border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Password</label>
        <input type="password" name="password" required class="mt-1 w-full border rounded-lg px-3 py-2">
    </div>
    <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg">Sign in</button>
    <p class="text-xs text-gray-500">Need to create the first admin? Visit <a href="/admin/setup.php" class="underline">/admin/setup.php</a>.</p>
</form>
