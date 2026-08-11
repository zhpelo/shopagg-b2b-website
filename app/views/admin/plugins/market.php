<div class="space-y-5">
    <header class="rounded-xl border border-slate-200 bg-white p-5"><a class="text-sm font-semibold text-indigo-600" href="<?= url('/admin/app-store') ?>">← 返回应用商店</a><h1 class="mt-3 text-2xl font-bold text-slate-900">插件市场</h1></header>
    <?php $error = trim((string)($_GET['error'] ?? $marketError ?? '')); if ($error): ?><div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700"><?= h($error) ?></div><?php endif; ?>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($plugins as $plugin): ?><article class="rounded-xl border border-slate-200 bg-white p-4"><h2 class="font-bold text-slate-900"><?= h($plugin['name'] ?? 'Plugin') ?></h2><p class="mt-1 text-xs text-slate-500">v<?= h($plugin['version'] ?? '') ?></p><p class="mt-3 min-h-12 text-sm text-slate-600"><?= h($plugin['description'] ?? '') ?></p><form class="mt-4" action="<?= url('/admin/app-store/plugins/install') ?>" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="resource_id" value="<?= (int)($plugin['id'] ?? 0) ?>"><button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" type="submit">下载安装</button></form></article><?php endforeach; ?>
        <?php if ($plugins === [] && !$error): ?><div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-slate-500">市场暂无可用插件。</div><?php endif; ?>
    </div>
</div>
