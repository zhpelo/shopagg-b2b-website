<?php
$query = (string)($filters['query'] ?? '');
$type = (string)($filters['type'] ?? 'all');
$status = (string)($filters['status'] ?? 'all');
$price = (string)($filters['price'] ?? 'all');
$fetchedAt = (int)($catalogStatus['fetched_at'] ?? 0);
$age = $fetchedAt > 0 ? max(0, time() - $fetchedAt) : 0;
$updatedText = $fetchedAt <= 0 ? '尚未取得远程目录' : ($age < 60 ? '刚刚更新' : (int)floor($age / 60) . ' 分钟前更新');
$refreshQuery = http_build_query(array_filter([
    'q' => $query,
    'type' => $type !== 'all' ? $type : null,
    'status' => $status !== 'all' ? $status : null,
    'price' => $price !== 'all' ? $price : null,
    'refresh' => 1,
], static fn($value): bool => $value !== null && $value !== ''));
?>
<div class="space-y-5">
    <header class="rounded-xl border border-slate-200 bg-white px-5 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">SHOPAGG App Store</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">应用商店</h1>
                <p class="mt-1 text-sm text-slate-500">浏览并安装插件和网站主题。</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= url('/admin/app-store/settings') ?>"><i class="fas fa-key mr-2 text-slate-400"></i>账户设置</a>
                <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= url('/admin/app-store/plugins') ?>"><i class="fas fa-plug mr-2 text-slate-400"></i>管理插件</a>
                <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= url('/admin/app-store/themes') ?>"><i class="fas fa-swatchbook mr-2 text-slate-400"></i>管理主题</a>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 pt-4 text-sm text-slate-500">
            <span><strong class="text-slate-900"><?= (int)$counts['plugin'] ?></strong> 个插件</span>
            <span><strong class="text-slate-900"><?= (int)$counts['theme'] ?></strong> 个主题</span>
            <span><strong class="text-slate-900"><?= (int)$counts['installed'] ?></strong> 个已安装</span>
            <span class="ml-auto text-xs"><?= h($updatedText) ?> · <a class="font-semibold text-indigo-600" href="<?= url('/admin/app-store?' . $refreshQuery) ?>">刷新目录</a></span>
        </div>
    </header>

    <?php if (!$hasToken): ?>
        <div class="flex flex-col gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 sm:flex-row sm:items-center sm:justify-between">
            <span>浏览无需绑定账户；安装付费或授权资源时需要配置 App Store Token。</span>
            <a class="font-semibold text-amber-800 underline underline-offset-2" href="<?= url('/admin/app-store/settings') ?>">配置账户</a>
        </div>
    <?php endif; ?>
    <?php if (($catalogStatus['notice'] ?? '') !== ''): ?><div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"><?= h($catalogStatus['notice']) ?></div><?php endif; ?>
    <?php foreach ($errors as $error): ?><div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= h($error) ?></div><?php endforeach; ?>

    <form class="rounded-xl border border-slate-200 bg-white p-4" method="get" action="<?= url('/admin/app-store') ?>">
        <div class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_160px_160px_140px_auto] lg:items-end">
            <label class="block"><span class="mb-1.5 block text-xs font-semibold text-slate-600">搜索</span><input class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="search" name="q" value="<?= h($query) ?>" placeholder="名称、开发者或功能"></label>
            <label><span class="mb-1.5 block text-xs font-semibold text-slate-600">类型</span><select class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" name="type"><option value="all">全部</option><option value="plugin" <?= $type === 'plugin' ? 'selected' : '' ?>>插件</option><option value="theme" <?= $type === 'theme' ? 'selected' : '' ?>>网站主题</option></select></label>
            <label><span class="mb-1.5 block text-xs font-semibold text-slate-600">状态</span><select class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" name="status"><option value="all">全部</option><option value="available" <?= $status === 'available' ? 'selected' : '' ?>>未安装</option><option value="installed" <?= $status === 'installed' ? 'selected' : '' ?>>已安装</option><option value="update" <?= $status === 'update' ? 'selected' : '' ?>>可更新</option></select></label>
            <label><span class="mb-1.5 block text-xs font-semibold text-slate-600">价格</span><select class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" name="price"><option value="all">全部</option><option value="free" <?= $price === 'free' ? 'selected' : '' ?>>免费</option><option value="paid" <?= $price === 'paid' ? 'selected' : '' ?>>付费</option></select></label>
            <div class="flex gap-2"><button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700" type="submit">筛选</button><a class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50" href="<?= url('/admin/app-store') ?>">重置</a></div>
        </div>
    </form>

    <div class="flex items-center justify-between"><div><h2 class="text-lg font-bold text-slate-900">应用与主题</h2><p class="mt-0.5 text-sm text-slate-500"><?= count($items) ?> 个结果</p></div></div>

    <?php if ($items === []): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><i class="fas fa-search text-2xl text-slate-300"></i><h2 class="mt-3 font-bold text-slate-800">没有找到匹配应用</h2><p class="mt-1 text-sm text-slate-500">请更换关键词或清除筛选条件。</p></div>
    <?php else: ?>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
            <?php foreach ($items as $item): ?>
                <article class="min-w-0 overflow-hidden border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">
                    <a class="group flex h-full flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-600" href="<?= url($item['detail_url']) ?>">
                        <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-indigo-50 via-violet-100 to-indigo-300">
                            <?php if ($item['image']): ?>
                                <img class="h-full w-full object-cover" src="<?= h($item['image']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="flex h-full flex-col items-center justify-center gap-4 text-indigo-600" aria-hidden="true">
                                    <i class="fas fa-<?= $item['type'] === 'plugin' ? 'plug' : 'swatchbook' ?> text-6xl"></i>
                                    <span class="text-lg font-bold tracking-wide">SHOPAGG</span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute left-4 top-4 flex max-w-[calc(100%-2rem)] flex-wrap items-start gap-2">
                                <span class="bg-white px-3 py-1.5 text-sm font-semibold text-slate-800 shadow-sm"><?= $item['type'] === 'plugin' ? '插件' : '网站主题' ?></span>
                                <span class="px-3 py-1.5 text-sm font-semibold text-white shadow-sm <?= $item['is_free'] ? 'bg-emerald-500' : 'bg-indigo-600' ?>"><?= h($item['price_text']) ?></span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col px-6 pb-6 pt-7">
                            <h3 class="line-clamp-2 text-xl font-bold leading-snug text-slate-900 group-hover:text-blue-600" title="<?= h($item['name']) ?>"><?= h($item['name']) ?></h3>
                            <p class="mt-2 text-sm text-slate-400"><?php if ($item['version']): ?>v<?= h($item['version']) ?> · <?php endif; ?><?= h($item['vendor']) ?></p>
                            <div class="mt-5 min-h-[112px] flex-1">
                                <p class="line-clamp-4 text-base leading-7 text-slate-600"><?= h($item['description'] ?: '暂无应用介绍') ?></p>
                                <?php if ($item['installed']): ?><p class="mt-3 text-xs font-semibold <?= $item['needs_update'] ? 'text-amber-700' : 'text-emerald-700' ?>"><?= $item['needs_update'] ? '有可用更新' : '已安装' ?><?php if ($item['installed_version']): ?> · v<?= h($item['installed_version']) ?><?php endif; ?></p><?php endif; ?>
                            </div>
                            <div class="mt-5 flex flex-wrap items-center justify-between gap-x-3 gap-y-2 border-t border-slate-200 pt-4 text-sm">
                                <span class="inline-flex items-center gap-2 text-slate-500"><i class="fas fa-download" aria-hidden="true"></i><?= number_format($item['installs_count']) ?> 次安装</span>
                                <span class="font-semibold text-blue-600"><?= $item['version'] !== '' ? '最新版本 v' . h($item['version']) : '版本未标注' ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
