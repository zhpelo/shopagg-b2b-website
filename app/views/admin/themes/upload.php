<?php
/**
 * 后台 - 上传网站主题
 * @var array $requiredFiles
 */

$requiredFiles = is_array($requiredFiles ?? null) ? $requiredFiles : [];
?>

<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-sky-600 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">上传主题 ZIP</h1>
                    <p class="mt-1 text-indigo-100">上传本地网站模版包，系统会自动校验并安装到 `/themes` 目录。</p>
                </div>
                <a href="<?= url('/admin/appearance/themes') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/35 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    <i class="fas fa-arrow-left"></i>
                    返回网站模版
                </a>
            </div>
        </div>

        <div class="p-6">
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                    <span><?= h($_GET['error']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <i class="fas fa-check"></i>
                    </span>
                    <span><?= h($_GET['success']) ?></span>
                </div>
            <?php endif; ?>

            <div class="grid gap-6 xl:grid-cols-12">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 xl:col-span-7">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                            <i class="fas fa-cloud-arrow-up"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">选择主题包</h2>
                            <p class="text-sm text-slate-500">仅支持 `.zip` 格式的网站主题包。</p>
                        </div>
                    </div>

                    <form action="<?= url('/admin/appearance/themes/upload') ?>" method="post" enctype="multipart/form-data" class="space-y-5">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">主题 ZIP 文件</span>
                            <input type="file" name="theme_zip" accept=".zip,application/zip" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-medium file:text-indigo-600 hover:file:bg-indigo-100" required>
                        </label>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white transition hover:bg-indigo-700">
                            <i class="fas fa-cloud-arrow-up"></i>
                            上传并安装主题
                        </button>
                    </form>
                </section>

                <aside class="space-y-6 xl:col-span-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                                <i class="fas fa-list-check"></i>
                            </span>
                            <h2 class="text-lg font-bold text-slate-900">上传校验规则</h2>
                        </div>
                        <div class="space-y-3 text-sm leading-6 text-slate-600">
                            <p>1. 压缩包中只能包含一套网站主题。</p>
                            <p>2. 主题根目录必须包含 `style.css`，并在头部注释里提供 `Theme Name`。</p>
                            <p>3. 系统会检查主题运行所需的模板文件是否齐全，不符合要求的 zip 会直接拒绝安装。</p>
                            <p>4. 如果目标主题目录已经存在，请先删除旧主题或更换主题目录名。</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                <i class="fas fa-file-code"></i>
                            </span>
                            <h2 class="text-lg font-bold text-slate-900">必需模板文件</h2>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                            <?php foreach ($requiredFiles as $file): ?>
                                <span class="rounded-lg bg-slate-50 px-3 py-2 font-mono"><?= h($file) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>
