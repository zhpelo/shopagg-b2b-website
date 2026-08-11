<?php
/** @var array $themes */
/** @var array $appStore */
$appStore = $appStore ?? ['has_token' => false, 'masked_token' => '', 'site_domain' => base_url(), 'wechat_pay' => null];
$validThemeCount = count(array_filter($themes, static fn(array $theme): bool => !empty($theme['is_valid'])));
?>
<div class="space-y-5">
    <header class="rounded-xl border border-slate-200 bg-white px-5 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">应用商店</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">网站主题</h1>
                <p class="mt-1 text-sm text-slate-500">管理已安装主题；在线主题请前往应用商店浏览。</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= url('/admin/app-store?type=theme') ?>">浏览主题</a>
                <a class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700" href="<?= url('/admin/app-store/themes/upload') ?>"><i class="fas fa-upload mr-2"></i>上传 ZIP</a>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 border-t border-slate-100 pt-4 text-sm text-slate-500">
            <span><strong class="text-slate-900"><?= count($themes) ?></strong> 个已安装</span>
            <span><strong class="text-slate-900"><?= $validThemeCount ?></strong> 个可用</span>
            <span>当前主题：<strong class="text-slate-900"><?= h($currentThemeName) ?></strong></span>
        </div>
    </header>

    <?php if (isset($_GET['success'])): ?><div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= h($_GET['success']) ?></div><?php endif; ?>
    <?php if (isset($_GET['error'])): ?><div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= h($_GET['error']) ?></div><?php endif; ?>

    <?php if (!empty($appStore['wechat_pay'])): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><span>微信支付订单 <?= h($appStore['wechat_pay']['order_id'] ?? '') ?> 已创建，支付完成后可返回应用商店安装。</span><a class="font-semibold underline underline-offset-2" href="<?= h($appStore['wechat_pay']['code_url'] ?? '#') ?>" target="_blank" rel="noreferrer">打开支付链接</a></div>
        </div>
    <?php endif; ?>

    <details id="app-store-account" class="rounded-xl border border-slate-200 bg-white">
        <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800"><span><i class="fas fa-user-circle mr-2 text-slate-400"></i>ShopAGG 账户与授权</span><span class="text-xs font-normal <?= !empty($appStore['has_token']) ? 'text-emerald-700' : 'text-slate-500' ?>"><?= !empty($appStore['has_token']) ? '已配置' : '未配置' ?></span></summary>
        <div class="border-t border-slate-100 p-4">
            <?php if (!empty($appStore['has_token'])): ?>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div class="text-sm text-slate-600"><p>授权站点：<strong class="text-slate-800"><?= h($appStore['site_domain'] ?? base_url()) ?></strong></p><p class="mt-1">Token：<span class="font-mono text-xs"><?= h($appStore['masked_token'] ?? '') ?></span></p></div><form action="<?= url('/admin/app-store/themes/settings') ?>" method="post" onsubmit="return confirm('确认解除当前站点的 ShopAGG 账户绑定吗？')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="clear_token" value="1"><button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700" type="submit">解除绑定</button></form></div>
            <?php else: ?>
                <form class="flex flex-col gap-2 sm:flex-row" action="<?= url('/admin/app-store/themes/settings') ?>" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-400" type="password" name="api_token" placeholder="粘贴 App Store API Token" autocomplete="off"><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" type="submit">保存 Token</button></form><p class="mt-2 text-xs text-slate-500">可在 <a class="font-semibold text-indigo-600" href="https://www.shopagg.com/dashboard/api-tokens" target="_blank" rel="noreferrer">ShopAGG API Tokens</a> 页面获取。</p>
            <?php endif; ?>
        </div>
    </details>

    <div class="flex items-center justify-between"><div><h2 class="text-lg font-bold text-slate-900">已安装主题</h2><p class="mt-0.5 text-sm text-slate-500">启用新主题只需一次确认。</p></div></div>

    <?php if (empty($themes)): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><i class="fas fa-swatchbook text-2xl text-slate-300"></i><h2 class="mt-3 font-bold text-slate-800">尚未安装主题</h2><p class="mt-1 text-sm text-slate-500">从应用商店选择主题，或上传主题 ZIP。</p></div>
    <?php else: ?>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($themes as $theme): $canDeleteTheme = empty($theme['is_active']) && $theme['slug'] !== 'default'; ?>
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="aspect-[16/9] overflow-hidden border-b border-slate-100 bg-slate-100"><?php if (!empty($theme['preview_url'])): ?><img src="<?= h($theme['preview_url']) ?>" alt="<?= h($theme['name']) ?>" class="h-full w-full object-cover"><?php else: ?><div class="flex h-full items-center justify-center text-sm text-slate-400">无预览图片</div><?php endif; ?></div>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3"><div class="min-w-0"><h2 class="truncate font-bold text-slate-900"><?= h($theme['name']) ?></h2><p class="mt-1 text-xs text-slate-500"><?= h($theme['slug']) ?><?= $theme['version'] !== '' ? ' · v' . h($theme['version']) : '' ?></p></div><span class="shrink-0 rounded-md px-2 py-1 text-xs font-semibold <?= !empty($theme['is_active']) ? 'bg-indigo-50 text-indigo-700' : (!empty($theme['is_valid']) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700') ?>"><?= !empty($theme['is_active']) ? '当前使用' : (!empty($theme['is_valid']) ? '可用' : '待修正') ?></span></div>
                        <p class="mt-3 line-clamp-2 min-h-[40px] text-sm leading-5 text-slate-600"><?= h($theme['description'] !== '' ? $theme['description'] : '暂无主题描述') ?></p>
                        <?php if (empty($theme['is_valid'])): ?><div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800"><?php foreach ($theme['errors'] as $themeError): ?><p><?= h($themeError) ?></p><?php endforeach; ?></div><?php endif; ?>
                    </div>
                    <div class="border-t border-slate-100 p-3">
                        <?php if (!empty($theme['is_active'])): ?><p class="py-2 text-center text-sm font-semibold text-indigo-700"><i class="fas fa-circle-check mr-2"></i>当前前台主题</p><?php elseif (!empty($theme['is_valid'])): ?><form action="<?= url('/admin/app-store/themes/activate') ?>" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="theme" value="<?= h($theme['slug']) ?>"><button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">启用主题</button></form><?php else: ?><button type="button" disabled class="w-full cursor-not-allowed rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">暂不可启用</button><?php endif; ?>
                        <?php if ($canDeleteTheme): ?><details class="mt-2 text-sm"><summary class="cursor-pointer py-1 text-center text-xs text-slate-500">更多操作</summary><form class="mt-2" action="<?= url('/admin/app-store/themes/delete') ?>" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="theme" value="<?= h($theme['slug']) ?>"><button type="submit" class="w-full rounded-lg border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700" data-confirm-message="确定删除主题「<?= h($theme['name']) ?>」吗？此操作不可恢复。">删除主题</button></form></details><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
