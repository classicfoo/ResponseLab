<div class="mb-6">
    <h1 class="text-3xl font-semibold mb-2"><?= e($test['title']) ?></h1>
    <p class="text-gray-600 mb-3"><?= nl2br(e($test['description'])) ?></p>
    <p class="text-sm text-gray-500">Closes: <?= e(format_in_timezone($test['expires_at_utc'], $test['admin_timezone'])) ?></p>
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

<?php if (!empty($editLink)): ?>
    <div class="card p-4 mb-4 border-blue-200 bg-blue-50 text-blue-700">
        <p class="font-medium">Your edit link (bookmark this):</p>
        <a class="underline break-all" href="<?= e($editLink) ?>"><?= e($editLink) ?></a>
        <p class="text-xs text-blue-700 mt-2">We also saved an edit cookie on this device (best effort for shared hosting).</p>
    </div>
<?php endif; ?>

<?php if ($isExpired): ?>
    <div class="card p-4 mb-4 border-yellow-200 bg-yellow-50 text-yellow-700">
        This test is closed. Submissions are read-only.
    </div>
<?php endif; ?>

<form method="post" class="card p-6 space-y-6">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="grid gap-6 md:grid-cols-2">
        <section class="space-y-3">
            <div class="flex items-center gap-2">
                <input type="radio" name="choice" value="A" id="choice-a" <?= ($existingSubmission['choice'] ?? '') === 'A' ? 'checked' : '' ?> <?= $isExpired ? 'disabled' : '' ?>>
                <label for="choice-a" class="font-semibold">Choose A</label>
            </div>
            <div class="image-grid grid gap-3">
                <?php if (empty($variantAImages)): ?>
                    <div class="text-sm text-gray-500">No images uploaded for variant A.</div>
                <?php endif; ?>
                <?php foreach ($variantAImages as $image): ?>
                    <img src="/<?= e($image['file_path']) ?>" alt="Variant A" class="w-full object-cover">
                <?php endforeach; ?>
            </div>
            <div class="<?= ($existingSubmission['choice'] ?? '') === 'A' ? '' : 'hidden' ?>" data-feedback="A">
                <label class="block text-sm font-medium">Feedback for A</label>
                <textarea name="feedback_a" rows="4" class="mt-1 w-full border rounded-lg px-3 py-2" <?= $isExpired ? 'disabled' : '' ?>><?= e(($existingSubmission['choice'] ?? '') === 'A' ? ($existingSubmission['feedback_text'] ?? '') : '') ?></textarea>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex items-center gap-2">
                <input type="radio" name="choice" value="B" id="choice-b" <?= ($existingSubmission['choice'] ?? '') === 'B' ? 'checked' : '' ?> <?= $isExpired ? 'disabled' : '' ?>>
                <label for="choice-b" class="font-semibold">Choose B</label>
            </div>
            <div class="image-grid grid gap-3">
                <?php if (empty($variantBImages)): ?>
                    <div class="text-sm text-gray-500">No images uploaded for variant B.</div>
                <?php endif; ?>
                <?php foreach ($variantBImages as $image): ?>
                    <img src="/<?= e($image['file_path']) ?>" alt="Variant B" class="w-full object-cover">
                <?php endforeach; ?>
            </div>
            <div class="<?= ($existingSubmission['choice'] ?? '') === 'B' ? '' : 'hidden' ?>" data-feedback="B">
                <label class="block text-sm font-medium">Feedback for B</label>
                <textarea name="feedback_b" rows="4" class="mt-1 w-full border rounded-lg px-3 py-2" <?= $isExpired ? 'disabled' : '' ?>><?= e(($existingSubmission['choice'] ?? '') === 'B' ? ($existingSubmission['feedback_text'] ?? '') : '') ?></textarea>
            </div>
        </section>
    </div>

    <div>
        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="anonymous" id="anonymous-toggle" <?= ($existingSubmission['anonymous'] ?? 1) ? 'checked' : '' ?> <?= $isExpired ? 'disabled' : '' ?>>
            Submit anonymously
        </label>
    </div>

    <div id="identity-fields" class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="block text-sm font-medium">First name</label>
            <input type="text" name="firstname" value="<?= e($existingSubmission['firstname'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2" <?= $isExpired ? 'disabled' : '' ?>>
        </div>
        <div>
            <label class="block text-sm font-medium">Last name</label>
            <input type="text" name="lastname" value="<?= e($existingSubmission['lastname'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2" <?= $isExpired ? 'disabled' : '' ?>>
        </div>
        <div>
            <label class="block text-sm font-medium">Company (optional)</label>
            <input type="text" name="company" value="<?= e($existingSubmission['company'] ?? '') ?>" class="mt-1 w-full border rounded-lg px-3 py-2" <?= $isExpired ? 'disabled' : '' ?>>
        </div>
    </div>

    <?php if (!$isExpired): ?>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg"><?= $existingSubmission ? 'Update submission' : 'Submit feedback' ?></button>
    <?php endif; ?>
</form>

<?php if ($isExpired): ?>
    <div class="mt-8">
        <?php include __DIR__ . '/results_public.php'; ?>
    </div>
<?php endif; ?>
