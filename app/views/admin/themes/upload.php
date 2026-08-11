<?php $requiredFiles = is_array($requiredFiles ?? null) ? $requiredFiles : []; ?>
<div class="mx-auto max-w-5xl space-y-5">
    <header class="rounded-xl border border-slate-200 bg-white p-5">
        <a href="<?= url('/admin/app-store/themes') ?>" class="text-sm font-semibold text-indigo-600">← 返回主题管理</a>
        <h1 class="mt-3 text-2xl font-bold text-slate-900">上传主题 ZIP</h1>
        <p class="mt-1 text-sm text-slate-500">选择文件后一次完成校验与安装。</p>
    </header>

    <?php if (isset($_GET['error'])): ?><div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= h($_GET['error']) ?></div><?php endif; ?>
    <?php if (isset($_GET['success'])): ?><div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= h($_GET['success']) ?></div><?php endif; ?>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-bold text-slate-900">选择主题包</h2>
            <p class="mt-1 text-sm text-slate-500">仅支持包含一套主题的 `.zip` 文件。</p>
            <form action="<?= url('/admin/app-store/themes/upload') ?>" method="post" enctype="multipart/form-data" class="mt-5 space-y-4">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="file" name="theme_zip" accept=".zip,application/zip" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700" required>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">上传并安装</button>
            </form>
        </section>

        <aside class="space-y-3">
            <details class="rounded-xl border border-slate-200 bg-white" open><summary class="cursor-pointer px-4 py-3 font-semibold text-slate-800">校验规则</summary><div class="space-y-2 border-t border-slate-100 p-4 text-sm leading-6 text-slate-600"><p>压缩包中只能包含一套网站主题。</p><p>主题根目录必须包含 `style.css`，并提供 `Theme Name`。</p><p>系统会自动检查模板文件；目录已存在时不会覆盖。</p></div></details>
            <details class="rounded-xl border border-slate-200 bg-white"><summary class="cursor-pointer px-4 py-3 font-semibold text-slate-800">必需模板文件</summary><div class="grid grid-cols-2 gap-2 border-t border-slate-100 p-4 text-xs text-slate-600"><?php foreach ($requiredFiles as $file): ?><span class="rounded-md bg-slate-50 px-2 py-1.5 font-mono"><?= h($file) ?></span><?php endforeach; ?></div></details>
        </aside>
    </div>
</div>
