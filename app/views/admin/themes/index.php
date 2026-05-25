<?php
/**
 * 后台 - 网站模版管理
 * @var array $themes
 * @var array $requiredFiles
 * @var string $currentTheme
 * @var string $currentThemeName
 */

$validThemeCount = count(array_filter($themes, static fn(array $theme): bool => $theme['is_valid']));
$invalidThemeCount = count($themes) - $validThemeCount;
?>

<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-sky-600 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">网站模版</h1>
                    <p class="mt-1 text-indigo-100">读取 `/themes` 目录中的网站主题，自动识别 `screenshot.jpg` 预览图，并支持上传 zip 主题包。</p>
                </div>
                <div class="inline-flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 text-sm text-white backdrop-blur">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <div>
                        <p class="font-semibold">当前主题</p>
                        <p class="text-indigo-100"><?= h($currentThemeName) ?> <span class="text-xs opacity-80">(<?= h($currentTheme) ?>)</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <i class="fas fa-check"></i>
                    </span>
                    <span><?= h($_GET['success']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                    <span><?= h($_GET['error']) ?></span>
                </div>
            <?php endif; ?>

            <div class="grid gap-6 xl:grid-cols-12">
                <div class="xl:col-span-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                                <i class="fas fa-upload"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">主题上传</h2>
                                <p class="text-sm text-slate-500">参考 WordPress 主题上传流程，上传后自动校验并解压到 `/themes`。</p>
                            </div>
                        </div>

                        <form action="<?= url('/admin/appearance/themes/upload') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <label class="block">
                                <span class="mb-2 block text-sm font-medium text-slate-700">选择主题 zip 文件</span>
                                <input type="file" name="theme_zip" accept=".zip,application/zip" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-medium file:text-indigo-600 hover:file:bg-indigo-100">
                            </label>

                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white transition hover:bg-indigo-700">
                                <i class="fas fa-cloud-arrow-up"></i>
                                上传主题 ZIP
                            </button>
                        </form>

                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <p class="font-semibold text-slate-900">上传校验规则</p>
                            <p>1. 压缩包中只能包含一套网站主题。</p>
                            <p>2. 主题根目录必须包含 `style.css`，并在头部注释里提供 `Theme Name`。</p>
                            <p>3. 系统会检查主题运行所需的模板文件是否齐全，不符合要求的 zip 会直接提示“此文件不符合”。</p>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-7">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm text-slate-500">已安装主题</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900"><?= count($themes) ?></p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-sm text-emerald-700">可启用主题</p>
                                <p class="mt-2 text-3xl font-bold text-emerald-800"><?= $validThemeCount ?></p>
                            </div>
                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-sm text-amber-700">待修正主题</p>
                                <p class="mt-2 text-3xl font-bold text-amber-800"><?= $invalidThemeCount ?></p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="mb-3 text-sm font-semibold text-slate-900">主题必需文件</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($requiredFiles as $requiredFile): ?>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600"><?= h($requiredFile) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="mb-3 text-sm font-semibold text-slate-900">`style.css` 头部示例</p>
                            <pre class="overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">/*
Theme Name: 你的主题名称
Theme URI: 主题官网地址
Author: 作者名称
Author URI: 作者网址
Description: 主题的简短描述
Version: 1.0
License: MIT
*/</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($themes)): ?>
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fas fa-swatchbook text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900">`/themes` 目录下暂无主题</h2>
            <p class="mt-2 text-slate-500">请先上传一个符合要求的 zip 网站主题包。</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($themes as $theme): ?>
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="aspect-[16/10] overflow-hidden border-b border-slate-200 bg-slate-100">
                        <?php if (!empty($theme['preview_url'])): ?>
                            <img src="<?= h($theme['preview_url']) ?>" alt="<?= h($theme['name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-sm font-medium text-slate-500">
                                无预览图片
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-bold text-slate-900"><?= h($theme['name']) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= h($theme['slug']) ?></p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2">
                                <?php if ($theme['is_active']): ?>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">当前启用</span>
                                <?php endif; ?>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $theme['is_valid'] ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-700' ?>">
                                    <?= $theme['is_valid'] ? '可用' : '待修正' ?>
                                </span>
                            </div>
                        </div>

                        <p class="mt-4 min-h-[48px] text-sm leading-6 text-slate-600">
                            <?= $theme['description'] !== '' ? h($theme['description']) : '暂无主题描述' ?>
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">版本</p>
                                <p class="mt-1 font-medium text-slate-700"><?= h($theme['version'] !== '' ? $theme['version'] : '未填写') ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">作者</p>

                                <?php if ($theme['author_uri'] !== ''): ?>
                     
                                    <a href="<?= h($theme['author_uri']) ?>" target="_blank" rel="noreferrer" class="font-medium text-indigo-600 hover:text-indigo-700">
                                        <?= h($theme['author'] !== '' ? $theme['author'] : '未填写') ?>
                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                <?php else: ?>
                                    <p class="mt-1 font-medium text-slate-700"><?= h($theme['author'] !== '' ? $theme['author'] : '未填写') ?></p>
                                <?php endif; ?>
                                
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">License</p>
                                <p class="mt-1 font-medium text-slate-700"><?= h($theme['license'] !== '' ? $theme['license'] : '未填写') ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">官网</p>
                                <?php if ($theme['theme_uri'] !== ''): ?>
                                    <a href="<?= h($theme['theme_uri']) ?>" target="_blank" rel="noreferrer" class="mt-1 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-700">
                                        访问
                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                <?php else: ?>
                                    <p class="mt-1 font-medium text-slate-700">未填写</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        

                        <?php if (!$theme['is_valid']): ?>
                            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                <p class="font-semibold">此主题暂不符合系统要求</p>
                                <?php foreach ($theme['errors'] as $error): ?>
                                    <p class="mt-2 leading-6"><?= h($error) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
                        <?php if ($theme['is_active']): ?>
                            <div class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600">
                                <i class="fas fa-circle-check"></i>
                                当前前台正在使用这个主题
                            </div>
                        <?php elseif ($theme['is_valid']): ?>
                            <form action="<?= url('/admin/appearance/themes/activate') ?>" method="post">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="theme" value="<?= h($theme['slug']) ?>">
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">
                                    <i class="fas fa-bolt"></i>
                                    设为当前主题
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-200 px-4 py-3 font-semibold text-slate-500">
                                <i class="fas fa-ban"></i>
                                当前不可启用
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
