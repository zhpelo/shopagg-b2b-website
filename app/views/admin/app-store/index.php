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
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">ShopAGG App Store</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">应用商店</h1>
                <p class="mt-1 text-sm text-slate-500">浏览并安装插件和网站主题。</p>
            </div>
            <div class="flex flex-wrap gap-2">
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
            <a class="font-semibold text-amber-800 underline underline-offset-2" href="<?= url('/admin/app-store/themes#app-store-account') ?>">配置账户</a>
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
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($items as $item): ?>
                <article class="flex min-h-[286px] flex-col rounded-xl border border-slate-200 bg-white">
                    <div class="flex gap-3 border-b border-slate-100 p-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-lg text-slate-500"><?php if ($item['image']): ?><img class="h-full w-full object-cover" src="<?= h($item['image']) ?>" alt=""><?php else: ?><i class="fas fa-<?= $item['type'] === 'plugin' ? 'plug' : 'swatchbook' ?>"></i><?php endif; ?></div>
                        <div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-2"><div class="min-w-0"><span class="text-xs font-semibold text-indigo-600"><?= $item['type'] === 'plugin' ? '插件' : '网站主题' ?></span><h3 class="truncate text-base font-bold text-slate-900"><?= h($item['name']) ?></h3></div><span class="shrink-0 rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700"><?= h($item['price_text']) ?></span></div><p class="mt-1 truncate text-xs text-slate-500"><?= h($item['vendor']) ?><?php if ($item['version']): ?> · v<?= h($item['version']) ?><?php endif; ?></p></div>
                    </div>
                    <div class="flex-1 p-4"><p class="line-clamp-3 text-sm leading-6 text-slate-600"><?= h($item['description'] ?: '暂无应用介绍') ?></p><?php if ($item['tags']): ?><div class="mt-3 flex flex-wrap gap-1.5"><?php foreach (array_slice($item['tags'], 0, 3) as $tag): ?><span class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-500"><?= h($tag) ?></span><?php endforeach; ?></div><?php endif; ?><?php if ($item['installed']): ?><p class="mt-3 text-xs font-semibold <?= $item['needs_update'] ? 'text-amber-700' : 'text-emerald-700' ?>"><?= $item['needs_update'] ? '有可用更新' : '已安装' ?><?php if ($item['installed_version']): ?> · v<?= h($item['installed_version']) ?><?php endif; ?></p><?php endif; ?></div>
                    <div class="border-t border-slate-100 p-3"><?php if ($item['installed']): ?><a class="flex w-full items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= url($item['manage_url']) ?>"><?= $item['needs_update'] ? '查看更新' : '管理' ?></a><?php elseif ($item['type'] === 'theme'): ?><a class="flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" href="<?= url($item['detail_url']) ?>">查看并安装</a><?php else: ?><form action="<?= url('/admin/app-store/plugins/install') ?>" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="resource_id" value="<?= (int)$item['id'] ?>"><button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" type="submit">下载安装</button></form><?php endif; ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
