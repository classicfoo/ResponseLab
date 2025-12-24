<?php
$total = (int) ($stats['total'] ?? 0);
$aCount = (int) ($stats['A'] ?? 0);
$bCount = (int) ($stats['B'] ?? 0);
$aPercent = $total ? round(($aCount / $total) * 100) : 0;
$bPercent = $total ? round(($bCount / $total) * 100) : 0;
?>

<div class="card p-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">Results</h2>
            <p class="text-sm text-gray-500">Total submissions: <?= (int) ($stats['total'] ?? 0) ?></p>
        </div>
        <?php if (!empty($isAdmin)): ?>
            <a href="/admin/test/export?id=<?= (int) $test['id'] ?>" class="border px-3 py-2 rounded-lg">Export CSV</a>
        <?php endif; ?>
    </div>

    <div class="grid gap-4 mt-6 md:grid-cols-2">
        <div class="card p-4">
            <h3 class="font-semibold">Variant A</h3>
            <p class="text-2xl font-semibold mt-2"><?= $aCount ?></p>
            <p class="text-sm text-gray-500"><?= $aPercent ?>%</p>
        </div>
        <div class="card p-4">
            <h3 class="font-semibold">Variant B</h3>
            <p class="text-2xl font-semibold mt-2"><?= $bCount ?></p>
            <p class="text-sm text-gray-500"><?= $bPercent ?>%</p>
        </div>
    </div>

    <div class="grid gap-6 mt-8 md:grid-cols-2">
        <div>
            <h3 class="font-semibold mb-2">Feedback for A</h3>
            <?php if (empty($feedback['A'])): ?>
                <p class="text-sm text-gray-500">No feedback yet.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($feedback['A'] as $entry): ?>
                        <li class="border rounded-lg p-3">
                            <p class="text-sm text-gray-700">"<?= e($entry['feedback_text']) ?>"</p>
                            <p class="text-xs text-gray-500 mt-2"><?= e($entry['anonymous'] ? 'Anonymous' : trim($entry['firstname'] . ' ' . $entry['lastname'])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div>
            <h3 class="font-semibold mb-2">Feedback for B</h3>
            <?php if (empty($feedback['B'])): ?>
                <p class="text-sm text-gray-500">No feedback yet.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($feedback['B'] as $entry): ?>
                        <li class="border rounded-lg p-3">
                            <p class="text-sm text-gray-700">"<?= e($entry['feedback_text']) ?>"</p>
                            <p class="text-xs text-gray-500 mt-2"><?= e($entry['anonymous'] ? 'Anonymous' : trim($entry['firstname'] . ' ' . $entry['lastname'])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($submissions)): ?>
        <div class="mt-8">
            <h3 class="font-semibold mb-3">Submissions</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2">Submitted</th>
                            <th class="py-2">Choice</th>
                            <th class="py-2">Name</th>
                            <th class="py-2">Company</th>
                            <th class="py-2">Anonymous</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr class="border-t">
                                <td class="py-2"><?= e($submission['created_at']) ?></td>
                                <td class="py-2"><?= e($submission['choice']) ?></td>
                                <td class="py-2"><?= e(trim($submission['firstname'] . ' ' . $submission['lastname'])) ?></td>
                                <td class="py-2"><?= e($submission['company']) ?></td>
                                <td class="py-2"><?= $submission['anonymous'] ? 'Yes' : 'No' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
