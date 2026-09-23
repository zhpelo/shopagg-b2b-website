<?php $requiredFiles = is_array($requiredFiles ?? null) ? $requiredFiles : ['plugin.json', 'README.md']; ?>
<div class="mx-auto max-w-5xl space-y-5">
    <header class="rounded-xl border border-slate-200 bg-white p-5">
        <a href="<?= url('/admin/app-store/plugins') ?>" class="text-sm font-semibold text-indigo-600">← 返回插件管理</a>
        <h1 class="mt-3 text-2xl font-bold text-slate-900">上传插件 ZIP</h1>
        <p class="mt-1 text-sm text-slate-500">选择文件后一次完成安全检查、结构校验与安装。</p>
    </header>

    <?php if (isset($_GET['error'])): ?><div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= h($_GET['error']) ?></div><?php endif; ?>
    <?php if (isset($_GET['success'])): ?><div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= h($_GET['success']) ?></div><?php endif; ?>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-bold text-slate-900">选择插件包</h2>
            <p class="mt-1 text-sm text-slate-500">仅支持包含一个 SHOPAGG 网站插件的 `.zip` 文件。</p>
            <form action="<?= url('/admin/app-store/plugins/upload') ?>" method="post" enctype="multipart/form-data" class="mt-5 space-y-4">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="file" name="plugin_zip" accept=".zip,application/zip" class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700" required>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">上传并安装</button>
            </form>

            <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800">
                <p class="font-semibold"><i class="fas fa-shield-halved mr-2" aria-hidden="true"></i>请只上传可信来源的插件</p>
                <p class="mt-1">插件可以运行服务器端代码。安装完成后不会自动启用，您可以返回插件管理页检查后再启用。</p>
            </div>
        </section>

        <aside class="space-y-3">
            <details class="rounded-xl border border-slate-200 bg-white" open>
                <summary class="cursor-pointer px-4 py-3 font-semibold text-slate-800">校验规则</summary>
                <div class="space-y-2 border-t border-slate-100 p-4 text-sm leading-6 text-slate-600">
                    <p>压缩包中只能包含一个插件包。</p>
                    <p>插件根目录必须包含有效的 `plugin.json` 和 `README.md`。</p>
                    <p>版本号必须使用 SemVer，插件 ID 只能包含小写字母、数字和连字符。</p>
                    <p>系统会检查 PHP 语法、PSR-4 目录、依赖和运行环境；已存在的相同版本不会覆盖。</p>
                </div>
            </details>
            <details class="rounded-xl border border-slate-200 bg-white">
                <summary class="cursor-pointer px-4 py-3 font-semibold text-slate-800">必需文件</summary>
                <div class="space-y-3 border-t border-slate-100 p-4">
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                        <?php foreach ($requiredFiles as $file): ?><span class="rounded-md bg-slate-50 px-2 py-1.5 font-mono"><?= h($file) ?></span><?php endforeach; ?>
                    </div>
                    <p class="text-xs leading-5 text-slate-500">`plugin.json` 还需声明插件名称、版本、环境要求和至少一个 PSR-4 自动加载目录。</p>
                </div>
            </details>
        </aside>
    </div>
</div>
