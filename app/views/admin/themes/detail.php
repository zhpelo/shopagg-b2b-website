<?php
/**
 * 后台 - App Store B2B 网站主题详情
 * @var array $theme
 * @var array $appStore
 */

if (!function_exists('app_store_theme_detail_rich_text')) {
    function app_store_theme_detail_rich_text(string $content): string {
        $content = trim($content);
        if ($content === '') {
            return '<p>暂无详情内容</p>';
        }

        if ($content === strip_tags($content)) {
            return nl2br(h($content));
        }

        $allowedTags = '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4><blockquote><code><pre><table><thead><tbody><tr><th><td><hr><img><a>';
        $clean = strip_tags($content, $allowedTags);
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*(\"[^\"]*\"|'[^']*'|[^\s>]+)/i", '', $clean) ?? $clean;
        $clean = preg_replace('/\s+(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', ' $1="#"', $clean) ?? $clean;
        $clean = preg_replace('/\s+href\s*=\s*([\'"])\s*data:[^\'"]*\1/i', ' href="#"', $clean) ?? $clean;
        $clean = preg_replace('/\s+src\s*=\s*([\'"])\s*data:(?!image\/(?:png|jpeg|jpg|gif|webp);base64,)[^\'"]*\1/i', ' src="#"', $clean) ?? $clean;
        return $clean;
    }
}

$theme = is_array($theme ?? null) ? $theme : [];
$appStore = is_array($appStore ?? null) ? $appStore : [];

$themeId = (int)($theme['id'] ?? 0);
$name = (string)($theme['name'] ?? '未命名主题');
$slug = (string)($theme['slug'] ?? '');
$version = (string)($theme['version'] ?? '');
$description = trim((string)($theme['description'] ?? $theme['short_description'] ?? ''));
$sections = is_array($theme['sections'] ?? null) ? $theme['sections'] : [];
$details = trim((string)($theme['details'] ?? ''));
$detailContent = $details !== '' ? $details : (string)($sections['description'] ?? $description);
$installation = trim((string)($sections['installation'] ?? ''));
$changelog = trim((string)($sections['changelog'] ?? ''));
$coverImage = (string)($theme['cover_image'] ?? '');
$banners = is_array($theme['banners'] ?? null) ? $theme['banners'] : [];
if ($coverImage === '') {
    $coverImage = (string)($banners['high'] ?? $banners['low'] ?? '');
}

$screenshots = [];
foreach ((array)($theme['screenshots'] ?? []) as $screenshot) {
    if (is_string($screenshot) && trim($screenshot) !== '') {
        $screenshots[] = ['url' => trim($screenshot), 'caption' => ''];
    } elseif (is_array($screenshot) && trim((string)($screenshot['url'] ?? '')) !== '') {
        $screenshots[] = [
            'url' => trim((string)$screenshot['url']),
            'caption' => trim((string)($screenshot['caption'] ?? '')),
        ];
    }
}

$updateHistory = is_array($theme['update_history'] ?? null) ? $theme['update_history'] : [];
$isFree = !empty($theme['is_free']);
$hasLicense = !empty($theme['has_license']);
$canDownload = !empty($theme['can_download']);
$licenseRequired = !empty($theme['license_required']);
$isInstalled = !empty($theme['_installed']);
$localTheme = is_array($theme['_local_theme'] ?? null) ? $theme['_local_theme'] : null;
$installedSlug = (string)($theme['_installed_slug'] ?? $slug);
$installedVersion = (string)($theme['_installed_version'] ?? '');
$needsUpdate = !empty($theme['_needs_update']);
$isActive = $localTheme && !empty($localTheme['is_active']);
$formattedPrice = trim((string)($theme['price_formatted'] ?? ''));
$priceText = $isFree ? '免费' : ($formattedPrice !== '' ? $formattedPrice : '¥' . number_format((float)($theme['price'] ?? 0), 2));
$returnTo = '/admin/appearance/themes/app-store/' . $themeId;
$ratingAverage = $theme['rating_average'] ?? null;
$ratingText = $ratingAverage !== null && $ratingAverage !== '' ? number_format((float)$ratingAverage, 1) : '暂无评分';
$lastUpdated = format_date((string)($theme['last_updated'] ?? ''), 'Y-m-d');
?>

<div class="space-y-6">
    <?php if (isset($_GET['success'])): ?>
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <i class="fas fa-check"></i>
            </span>
            <span><?= h($_GET['success']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <i class="fas fa-triangle-exclamation"></i>
            </span>
            <span><?= h($_GET['error']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($appStore['wechat_pay'])): ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="font-semibold">微信支付订单已创建：<?= h($appStore['wechat_pay']['resource_name'] ?? 'B2B 网站主题') ?></p>
                    <p class="mt-1 text-emerald-700">订单号：<?= h($appStore['wechat_pay']['order_id'] ?? '') ?>。支付完成后刷新详情页，再点击下载安装。</p>
                </div>
                <a href="<?= h($appStore['wechat_pay']['code_url'] ?? '#') ?>" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 font-semibold text-white transition hover:bg-emerald-700">
                    <i class="fas fa-qrcode"></i>
                    打开微信支付链接
                </a>
            </div>
            <p class="mt-3 break-all rounded-xl bg-white/70 px-3 py-2 font-mono text-xs text-emerald-900"><?= h($appStore['wechat_pay']['code_url'] ?? '') ?></p>
        </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-0 lg:grid-cols-12">
            <div class="relative min-h-[280px] bg-slate-100 lg:col-span-7">
                <?php if ($coverImage !== ''): ?>
                    <img src="<?= h($coverImage) ?>" alt="<?= h($name) ?>" class="h-full min-h-[280px] w-full object-cover">
                <?php else: ?>
                    <div class="flex h-full min-h-[280px] items-center justify-center bg-gradient-to-br from-sky-50 to-slate-100 text-slate-500">
                        <div class="text-center">
                            <i class="fas fa-store mb-3 text-4xl text-sky-400"></i>
                            <p class="text-sm font-semibold">App Store B2B Theme</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col justify-between p-6 lg:col-span-5">
                <div>
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <a href="<?= url('/admin/appearance/themes') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                            <i class="fas fa-arrow-left text-xs"></i>
                            返回网站模版
                        </a>
                        <span class="rounded-full <?= $isFree ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-700' ?> px-3 py-1 text-xs font-semibold"><?= h($priceText) ?></span>
                    </div>

                    <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl"><?= h($name) ?></h1>
                    <p class="mt-2 break-all text-sm text-slate-500"><?= h($slug) ?></p>

                    <p class="mt-5 text-sm leading-7 text-slate-600">
                        <?= $description !== '' ? h($description) : '暂无主题简介' ?>
                    </p>

                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">v<?= h($version !== '' ? $version : '未标注') ?></span>
                        <?php if ($isInstalled): ?>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"><?= $needsUpdate ? '可更新' : '已安装' ?></span>
                        <?php endif; ?>
                        <?php if ($isActive): ?>
                            <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">当前启用</span>
                        <?php elseif ($hasLicense): ?>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">已授权</span>
                        <?php elseif ($licenseRequired): ?>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">需授权</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                        <p class="text-xs text-slate-400">安装量</p>
                        <p class="mt-1 font-semibold text-slate-800"><?= number_format((int)($theme['installs_count'] ?? 0)) ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                        <p class="text-xs text-slate-400">评分</p>
                        <p class="mt-1 font-semibold text-slate-800"><?= h($ratingText) ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                        <p class="text-xs text-slate-400">更新日期</p>
                        <p class="mt-1 font-semibold text-slate-800"><?= h($lastUpdated !== '' ? $lastUpdated : '未标注') ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                        <p class="text-xs text-slate-400">授权站点</p>
                        <p class="mt-1 truncate font-semibold text-slate-800"><?= h((string)($theme['bound_domain'] ?? $appStore['site_domain'] ?? '未绑定')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <?php if ($screenshots !== []): ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                            <i class="fas fa-images"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">主题截图</h2>
                            <p class="text-sm text-slate-500">来自 App Store 资源管理中发布的展示图。</p>
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <?php foreach ($screenshots as $screenshot): ?>
                            <figure class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <a href="<?= h($screenshot['url']) ?>" target="_blank" rel="noreferrer">
                                    <img src="<?= h($screenshot['url']) ?>" alt="<?= h($screenshot['caption'] !== '' ? $screenshot['caption'] : $name) ?>" class="aspect-[4/3] w-full object-cover">
                                </a>
                                <?php if ($screenshot['caption'] !== ''): ?>
                                    <figcaption class="px-4 py-3 text-sm text-slate-600"><?= h($screenshot['caption']) ?></figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <i class="fas fa-align-left"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">主题详情</h2>
                        <p class="text-sm text-slate-500">功能介绍、适用场景和安装说明。</p>
                    </div>
                </div>

                <div class="rich-content rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <?= app_store_theme_detail_rich_text($detailContent) ?>
                </div>

                <?php if ($installation !== ''): ?>
                    <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 p-5">
                        <p class="mb-2 flex items-center gap-2 text-sm font-bold text-sky-800">
                            <i class="fas fa-circle-info"></i>
                            安装说明
                        </p>
                        <div class="text-sm leading-7 text-sky-800"><?= nl2br(h($installation)) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-code-branch"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">版本记录</h2>
                        <p class="text-sm text-slate-500">最近发布的主题版本和变更说明。</p>
                    </div>
                </div>

                <?php if ($updateHistory !== []): ?>
                    <div class="space-y-4">
                        <?php foreach ($updateHistory as $versionItem): ?>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="font-semibold text-slate-900">v<?= h((string)($versionItem['version'] ?? '')) ?></p>
                                    <p class="text-sm text-slate-500"><?= h(format_date((string)($versionItem['released_at'] ?? ''), 'Y-m-d')) ?></p>
                                </div>
                                <?php if (!empty($versionItem['changelog'])): ?>
                                    <p class="mt-3 text-sm leading-7 text-slate-600"><?= nl2br(h((string)$versionItem['changelog'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($changelog !== ''): ?>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-7 text-slate-600">
                        <?= nl2br(h($changelog)) ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-500">
                        暂无版本记录。
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="space-y-6 xl:col-span-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <i class="fas fa-bolt"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">主题操作</h2>
                        <p class="text-sm text-slate-500">安装、购买授权或启用已安装主题。</p>
                    </div>
                </div>

                <div class="mb-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <?php if (!$appStore['has_token']): ?>
                        <p class="font-semibold text-amber-700">当前站点还未绑定 API Token。</p>
                        <p class="mt-2">请返回网站模版页面绑定 ShopAGG 账户后再安装或购买主题。</p>
                    <?php elseif ($licenseRequired && !$hasLicense && !$canDownload): ?>
                        <p class="font-semibold text-amber-700">此主题需要购买授权。</p>
                        <p class="mt-2">授权会按当前站点域名绑定：<?= h($appStore['site_domain'] ?? base_url()) ?></p>
                    <?php elseif ($isInstalled): ?>
                        <p class="font-semibold text-emerald-700"><?= $needsUpdate ? '当前主题有新版本可更新。' : '此主题已经安装到本地。' ?></p>
                        <?php if ($installedVersion !== ''): ?>
                            <p class="mt-2">本地版本：<?= h($installedVersion) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="font-semibold text-emerald-700">当前账户可以下载此主题。</p>
                        <p class="mt-2">点击下载安装后，系统会校验授权并解压到 `/themes` 目录。</p>
                    <?php endif; ?>
                </div>

                <div class="space-y-3">
                    <?php if (!$appStore['has_token']): ?>
                        <a href="<?= url('/admin/appearance/themes') ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">
                            <i class="fas fa-link"></i>
                            返回绑定 API Token
                        </a>
                    <?php elseif ($licenseRequired && !$hasLicense && !$canDownload): ?>
                        <form action="<?= url('/admin/appearance/themes/app-store/purchase') ?>" method="post" class="space-y-3">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="resource_id" value="<?= $themeId ?>">
                            <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
                            <select name="payment_method" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                <option value="alipay">支付宝</option>
                                <option value="wechat">微信支付</option>
                            </select>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 font-semibold text-white transition hover:bg-amber-600">
                                <i class="fas fa-credit-card"></i>
                                购买授权
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= url('/admin/appearance/themes/app-store/install') ?>" method="post">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="resource_id" value="<?= $themeId ?>">
                            <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-3 font-semibold text-white transition hover:bg-sky-700">
                                <i class="fas fa-download"></i>
                                <?= $isInstalled ? ($needsUpdate ? '更新安装' : '重新安装') : '下载安装' ?>
                            </button>
                        </form>

                        <?php if ($isInstalled && !$isActive && $localTheme && !empty($localTheme['is_valid'])): ?>
                            <form action="<?= url('/admin/appearance/themes/activate') ?>" method="post">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="theme" value="<?= h($installedSlug) ?>">
                                <input type="hidden" name="return_to" value="<?= h($returnTo) ?>">
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">
                                    <i class="fas fa-bolt"></i>
                                    启用此主题
                                </button>
                            </form>
                        <?php elseif ($isActive): ?>
                            <div class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-600">
                                <i class="fas fa-circle-check"></i>
                                当前前台正在使用这个主题
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                        <i class="fas fa-list-check"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">资源信息</h2>
                        <p class="text-sm text-slate-500">App Store 发布信息和本地状态。</p>
                    </div>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-2">
                        <dt class="text-slate-500">资源 ID</dt>
                        <dd class="font-semibold text-slate-800">#<?= $themeId ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-2">
                        <dt class="text-slate-500">价格</dt>
                        <dd class="font-semibold text-slate-800"><?= h($priceText) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-2">
                        <dt class="text-slate-500">远程版本</dt>
                        <dd class="font-semibold text-slate-800"><?= h($version !== '' ? $version : '未标注') ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-2">
                        <dt class="text-slate-500">本地版本</dt>
                        <dd class="font-semibold text-slate-800"><?= h($installedVersion !== '' ? $installedVersion : '未安装') ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-2">
                        <dt class="text-slate-500">Token</dt>
                        <dd class="font-mono text-xs font-semibold text-slate-800"><?= h($appStore['masked_token'] ?? '未配置') ?></dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</div>
