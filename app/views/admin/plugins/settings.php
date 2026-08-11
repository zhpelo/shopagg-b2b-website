<?php $schema = $plugin['manifest']['settings'] ?? []; ?>
<div class="mx-auto max-w-3xl space-y-4">
    <header class="rounded-xl border border-slate-200 bg-white p-5"><a class="text-sm font-semibold text-indigo-600" href="<?= url('/admin/app-store/plugins') ?>">← 返回插件管理</a><h1 class="mt-3 text-2xl font-bold text-slate-900"><?= h($plugin['name']) ?>设置</h1><p class="mt-1 text-sm text-slate-500">修改后保存即可生效。</p></header>
    <section class="rounded-xl border border-slate-200 bg-white p-5">
    <?php if (!empty($_GET['success'])): ?><div class="mb-4 rounded-xl bg-emerald-50 p-3 text-emerald-700"><?= h($_GET['success']) ?></div><?php endif; ?>
    <?php if (!empty($_GET['error'])): ?><div class="mb-4 rounded-xl bg-rose-50 p-3 text-rose-700"><?= h($_GET['error']) ?></div><?php endif; ?>
    <form class="space-y-5" action="<?= url('/admin/app-store/plugins/settings') ?>" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="plugin_id" value="<?= h($plugin['plugin_id']) ?>">
        <?php foreach ($schema as $key => $field): $type = $field['type'] ?? 'text'; $value = $values[$key] ?? ''; ?>
            <label class="block"><span class="mb-2 block text-sm font-semibold text-slate-700"><?= h($field['label'] ?? $key) ?></span>
                <?php if ($type === 'textarea'): ?><textarea class="w-full rounded-lg border border-slate-200 px-3 py-2.5" name="<?= h($key) ?>" rows="5"><?= h($value) ?></textarea>
                <?php elseif ($type === 'select'): ?><select class="w-full rounded-lg border border-slate-200 px-3 py-2.5" name="<?= h($key) ?>"><?php foreach (($field['options'] ?? []) as $optionValue => $label): ?><option value="<?= h($optionValue) ?>" <?= (string)$value === (string)$optionValue ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select>
                <?php elseif ($type === 'checkbox'): ?><input type="hidden" name="<?= h($key) ?>" value="0"><input class="h-5 w-5" type="checkbox" name="<?= h($key) ?>" value="1" <?= $value ? 'checked' : '' ?>>
                <?php else: ?><input class="w-full rounded-lg border border-slate-200 px-3 py-2.5" type="<?= in_array($type, ['number','password','email','url'], true) ? h($type) : 'text' ?>" name="<?= h($key) ?>" value="<?= h($value) ?>"><?php endif; ?>
                <?php if (!empty($field['description'])): ?><span class="mt-2 block text-xs text-slate-500"><?= h($field['description']) ?></span><?php endif; ?>
            </label>
        <?php endforeach; ?>
        <button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white" type="submit">保存设置</button>
    </form>
    </section>
</div>
