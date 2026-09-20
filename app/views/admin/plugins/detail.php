<?php
$plugin = is_array($plugin ?? null) ? $plugin : [];
$sections = is_array($plugin['sections'] ?? null) ? $plugin['sections'] : [];
$resourceId = (int)($plugin['id'] ?? 0);
$name = (string)($plugin['name'] ?? '未命名插件');
$version = (string)($plugin['version'] ?? '');
$vendor = (string)($plugin['vendor'] ?? $plugin['author'] ?? $plugin['developer'] ?? 'SHOPAGG');
$description = trim((string)($plugin['description'] ?? $plugin['short_description'] ?? ''));
$image = (string)($plugin['cover_image'] ?? $plugin['banner_image'] ?? $plugin['icon_url'] ?? '');
$isFree = !empty($plugin['is_free']) || (float)($plugin['price'] ?? 0) <= 0;
$formattedPrice = trim((string)($plugin['price_formatted'] ?? ''));
$priceText = $isFree ? '免费' : ($formattedPrice !== '' ? $formattedPrice : '¥' . number_format((float)($plugin['price'] ?? 0), 2));
$canDownload = array_key_exists('can_download', $plugin) ? !empty($plugin['can_download']) : ($isFree || !empty($plugin['has_license']));
$installedPlugin = is_array($installedPlugin ?? null) ? $installedPlugin : null;
$installedVersion = (string)($installedPlugin['installed_version'] ?? '');
$details = trim((string)($plugin['details'] ?? $sections['description'] ?? $description));
$details = preg_replace('~<\s*br\s*/?\s*>|</\s*(?:p|div|li|h[1-6])\s*>~i', "\n", $details) ?? $details;
$details = trim(html_entity_decode(strip_tags($details), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
$installation = trim((string)($sections['installation'] ?? ''));
$changelog = trim((string)($sections['changelog'] ?? ''));
?>
<div class="space-y-6">
    <?php if (isset($_GET['error'])): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= h($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="grid lg:grid-cols-2">
            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-indigo-50 via-violet-100 to-indigo-300">
                <?php if ($image !== ''): ?>
                    <img class="h-full w-full object-cover" src="<?= h($image) ?>" alt="">
                <?php else: ?>
                    <div class="flex h-full flex-col items-center justify-center gap-5 text-indigo-600" aria-hidden="true">
                        <i class="fas fa-plug text-7xl"></i>
                        <span class="text-xl font-bold tracking-wide">SHOPAGG</span>
                    </div>
                <?php endif; ?>
                <div class="absolute left-5 top-5 flex flex-wrap gap-2">
                    <span class="bg-white px-3 py-1.5 text-sm font-semibold text-slate-800 shadow-sm">插件</span>
                    <span class="px-3 py-1.5 text-sm font-semibold text-white shadow-sm <?= $isFree ? 'bg-emerald-500' : 'bg-indigo-600' ?>"><?= h($priceText) ?></span>
                </div>
            </div>

            <div class="flex flex-col p-6 sm:p-8">
                <a class="text-sm font-semibold text-slate-500 hover:text-slate-900" href="<?= url('/admin/app-store?type=plugin') ?>"><i class="fas fa-arrow-left mr-2" aria-hidden="true"></i>返回应用商店</a>
                <h1 class="mt-6 text-2xl font-bold text-slate-900 sm:text-3xl"><?= h($name) ?></h1>
                <p class="mt-2 text-sm text-slate-500"><?= $version !== '' ? '最新版本 v' . h($version) . ' · ' : '' ?><?= h($vendor) ?></p>
                <p class="mt-6 text-base leading-7 text-slate-600"><?= h($description !== '' ? $description : '暂无插件简介') ?></p>

                <div class="mt-8 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg bg-slate-50 px-4 py-3"><p class="text-slate-400">安装量</p><p class="mt-1 font-semibold text-slate-800"><?= number_format(max(0, (int)($plugin['installs_count'] ?? 0))) ?></p></div>
                    <div class="rounded-lg bg-slate-50 px-4 py-3"><p class="text-slate-400">本地状态</p><p class="mt-1 font-semibold text-slate-800"><?= $installedPlugin === null ? '未安装' : (!empty($needsUpdate) ? '可更新' : '已安装' . ($installedVersion !== '' ? ' · v' . h($installedVersion) : '')) ?></p></div>
                </div>

                <div class="mt-auto pt-8">
                    <?php if ($canDownload && ($installedPlugin === null || !empty($needsUpdate))): ?>
                        <form action="<?= url('/admin/app-store/plugins/install') ?>" method="post">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="resource_id" value="<?= $resourceId ?>">
                            <input type="hidden" name="from_detail" value="1">
                            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" type="submit"><i class="fas fa-download" aria-hidden="true"></i><?= $installedPlugin === null ? '下载安装' : '安装最新版本' ?></button>
                        </form>
                    <?php elseif ($installedPlugin !== null): ?>
                        <a class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" href="<?= url('/admin/app-store/plugins') ?>">管理已安装插件</a>
                    <?php else: ?>
                        <p class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">此插件需要购买或授权后才能安装。<?php if (!$hasToken): ?><a class="font-semibold underline" href="<?= url('/admin/app-store/themes#app-store-account') ?>">配置 App Store 账户</a><?php endif; ?></p>
                    <?php endif; ?>
                    <?php if ($installedPlugin !== null && !empty($needsUpdate)): ?><a class="mt-3 block text-center text-sm font-semibold text-slate-500 hover:text-slate-800" href="<?= url('/admin/app-store/plugins') ?>">管理已安装插件</a><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
        <h2 class="text-lg font-bold text-slate-900">插件详情</h2>
        <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600"><?= h($details !== '' ? $details : ($description !== '' ? $description : '暂无详情内容')) ?></p>
        <?php if ($installation !== ''): ?>
            <div class="mt-6 border-t border-slate-100 pt-6"><h3 class="font-semibold text-slate-900">安装说明</h3><p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600"><?= h(strip_tags($installation)) ?></p></div>
        <?php endif; ?>
        <?php if ($changelog !== ''): ?>
            <div class="mt-6 border-t border-slate-100 pt-6"><h3 class="font-semibold text-slate-900">更新日志</h3><p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600"><?= h(strip_tags($changelog)) ?></p></div>
        <?php endif; ?>
    </section>
</div>
