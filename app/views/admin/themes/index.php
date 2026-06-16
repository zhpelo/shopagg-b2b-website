<?php
/**
 * 后台 - 网站模版管理
 * @var array $themes
 * @var array $requiredFiles
 * @var string $currentTheme
 * @var string $currentThemeName
 * @var array $appStore
 */

$appStore = $appStore ?? [
    'has_token' => false,
    'masked_token' => '',
    'site_domain' => base_url(),
    'account' => null,
    'account_error' => '',
    'themes' => [],
    'error' => '',
    'wechat_pay' => null,
];
$appStoreThemes = is_array($appStore['themes'] ?? null) ? $appStore['themes'] : [];
$validThemeCount = count(array_filter($themes, static fn(array $theme): bool => $theme['is_valid']));
$invalidThemeCount = count($themes) - $validThemeCount;
?>

<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-sky-600 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">网站模版</h1>
                    <p class="mt-1 text-indigo-100">管理本地 `/themes` 目录主题，也可以从 ShopAGG App Store 下载 B2B 网站主题。</p>
                </div>
                <div class="inline-flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 text-sm text-white">
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

            <?php if (!empty($appStore['wechat_pay'])): ?>
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="font-semibold">微信支付订单已创建：<?= h($appStore['wechat_pay']['resource_name'] ?? 'B2B 网站主题') ?></p>
                            <p class="mt-1 text-emerald-700">订单号：<?= h($appStore['wechat_pay']['order_id'] ?? '') ?>。支付完成后刷新此页面，再点击下载安装。</p>
                        </div>
                        <a href="<?= h($appStore['wechat_pay']['code_url'] ?? '#') ?>" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 font-semibold text-white transition hover:bg-emerald-700">
                            <i class="fas fa-qrcode"></i>
                            打开微信支付链接
                        </a>
                    </div>
                    <p class="mt-3 break-all rounded-xl bg-white/70 px-3 py-2 font-mono text-xs text-emerald-900"><?= h($appStore['wechat_pay']['code_url'] ?? '') ?></p>
                </div>
            <?php endif; ?>

            <div class="grid gap-6 xl:grid-cols-12">
                <div class="xl:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                                <i class="fas fa-upload"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">主题上传</h2>
                                <p class="text-sm text-slate-500">上传后自动校验文件内容并解压到 `/themes`目录。</p>
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

                <div class="xl:col-span-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                                <i class="fas fa-store"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">ShopAGG 账户绑定</h2>
                                <p class="text-sm text-slate-500">输入 App Store API Token 后，当前站点即可使用该账户的主题授权。</p>
                            </div>
                        </div>

                        <?php if ($appStore['has_token']): ?>
                            <?php $account = is_array($appStore['account'] ?? null) ? $appStore['account'] : null; ?>
                            <div class="space-y-4">
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                    <div class="flex items-start gap-3">
                                        <?php if (!empty($account['avatar'])): ?>
                                            <img src="<?= h($account['avatar']) ?>" alt="" class="h-12 w-12 rounded-full border border-white object-cover shadow-sm">
                                        <?php else: ?>
                                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">
                                                <?= h(mb_substr((string)($account['name'] ?? $account['email'] ?? 'S'), 0, 1)) ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">当前站点已绑定</p>
                                            <?php if ($account): ?>
                                                <p class="mt-1 truncate text-base font-bold text-slate-900"><?= h((string)($account['name'] ?? 'ShopAGG 用户')) ?></p>
                                                <p class="mt-0.5 truncate text-sm text-slate-600"><?= h((string)($account['email'] ?? '')) ?></p>
                                            <?php else: ?>
                                                <p class="mt-1 text-base font-bold text-slate-900">ShopAGG 账户</p>
                                                <p class="mt-0.5 text-sm text-amber-700">账号信息获取失败：<?= h((string)($appStore['account_error'] ?? 'Token 无法验证')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-4 rounded-xl bg-white/70 px-3 py-2 text-xs leading-6 text-slate-600">
                                        <p>当前站点：<span class="font-semibold text-slate-800"><?= h($appStore['site_domain'] ?? base_url()) ?></span></p>
                                        <p>Token：<span class="font-mono"><?= h($appStore['masked_token'] ?? '') ?></span></p>
                                    </div>
                                </div>

                                <form action="<?= url('/admin/appearance/themes/app-store/settings') ?>" method="post" onsubmit="return confirm('确认解除当前站点与 ShopAGG 账户的绑定吗？解除后将无法下载需要授权的主题。')">
                                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="clear_token" value="1">
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 font-semibold text-rose-700 transition hover:bg-rose-100">
                                        <i class="fas fa-link-slash"></i>
                                        解除绑定
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form action="<?= url('/admin/appearance/themes/app-store/settings') ?>" method="post" class="space-y-4">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-slate-700">API Token</span>
                                    <input type="password" name="api_token" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="粘贴 ShopAGG App Store Token" autocomplete="off">
                                </label>
                                <div class="rounded-xl bg-slate-50 px-3 py-2 text-xs leading-6 text-slate-500">
                                    当前站点会自动使用 <span class="font-semibold text-slate-700"><?= h($appStore['site_domain'] ?? base_url()) ?></span> 进行主题授权绑定。
                                </div>
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-5 py-3 font-semibold text-white transition hover:bg-sky-700">
                                    <i class="fas fa-link"></i>
                                    绑定 ShopAGG 账户
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="xl:col-span-4">
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

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-sky-50 p-4">
                                <p class="text-sm text-sky-700">App Store 主题</p>
                                <p class="mt-2 text-3xl font-bold text-sky-800"><?= count($appStoreThemes) ?></p>
                            </div>
                            <div class="rounded-2xl <?= $appStore['has_token'] ? 'bg-emerald-50' : 'bg-rose-50' ?> p-4">
                                <p class="text-sm <?= $appStore['has_token'] ? 'text-emerald-700' : 'text-rose-700' ?>">授权 Token</p>
                                <p class="mt-2 text-base font-bold <?= $appStore['has_token'] ? 'text-emerald-800' : 'text-rose-800' ?>"><?= $appStore['has_token'] ? '已配置' : '未配置' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">本地已安装主题</h2>
            <p class="mt-1 text-sm text-slate-500">这些主题已经存在于当前项目 `/themes` 目录中。</p>
        </div>
        <span class="text-sm font-medium text-slate-500"><?= count($themes) ?> 个本地主题</span>
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
                    <div class="aspect-[4/3] overflow-hidden border-b border-slate-200 bg-slate-100">
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

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">App Store B2B 网站主题</h2>
            <p class="mt-1 text-sm text-slate-500">来自 SHOPAGG App Store 的 B2B 网站主题，下载时会按 Token 和域名执行授权校验。</p>
        </div>
        <span class="text-sm font-medium text-slate-500"><?= count($appStoreThemes) ?> 个商店主题</span>
    </div>

    <?php if (!empty($appStore['error'])): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-amber-700"><i class="fas fa-circle-info"></i></span>
                <div>
                    <p class="font-semibold">暂时无法获取 App Store 主题</p>
                    <p class="mt-1"><?= h($appStore['error']) ?></p>
                </div>
            </div>
        </div>
    <?php elseif (empty($appStoreThemes)): ?>
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-sky-50 text-sky-500">
                <i class="fas fa-store text-2xl"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-900">App Store 暂无 B2B 网站主题</h2>
            <p class="mt-2 text-sm text-slate-500">请先在 Laravel 后台 App Store 资源管理中发布类型为“B2B 网站主题”的资源。</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($appStoreThemes as $storeTheme): ?>
                <?php
                    $storeThemeId = (int)($storeTheme['id'] ?? 0);
                    $storeName = (string)($storeTheme['name'] ?? '未命名主题');
                    $storeSlug = (string)($storeTheme['slug'] ?? '');
                    $storeVersion = (string)($storeTheme['version'] ?? '');
                    $storeDescription = trim((string)($storeTheme['description'] ?? $storeTheme['short_description'] ?? ''));
                    $storePreview = (string)($storeTheme['cover_image'] ?? $storeTheme['banner_image'] ?? '');
                    $isFree = !empty($storeTheme['is_free']);
                    $hasLicense = !empty($storeTheme['has_license']);
                    $canDownload = !empty($storeTheme['can_download']);
                    $licenseRequired = !empty($storeTheme['license_required']);
                    $isInstalled = !empty($storeTheme['_installed']);
                    $localTheme = is_array($storeTheme['_local_theme'] ?? null) ? $storeTheme['_local_theme'] : null;
                    $installedSlug = (string)($storeTheme['_installed_slug'] ?? $storeSlug);
                    $installedVersion = (string)($storeTheme['_installed_version'] ?? '');
                    $needsUpdate = !empty($storeTheme['_needs_update']);
                    $isActiveStoreTheme = $localTheme && !empty($localTheme['is_active']);
                    $priceText = $isFree ? '免费' : '$' . number_format((float)($storeTheme['price'] ?? 0), 2);
                ?>
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="aspect-[4/3] overflow-hidden border-b border-slate-200 bg-slate-100">
                        <?php if ($storePreview !== ''): ?>
                            <img src="<?= h($storePreview) ?>" alt="<?= h($storeName) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-sky-50 to-slate-100 text-sm font-medium text-slate-500">
                                App Store B2B Theme
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-bold text-slate-900"><?= h($storeName) ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= h($storeSlug) ?></p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2">
                                <span class="rounded-full <?= $isFree ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-700' ?> px-3 py-1 text-xs font-semibold"><?= h($priceText) ?></span>
                                <?php if ($isInstalled): ?>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"><?= $needsUpdate ? '可更新' : '已安装' ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="mt-4 min-h-[48px] text-sm leading-6 text-slate-600">
                            <?= $storeDescription !== '' ? h($storeDescription) : '暂无主题描述' ?>
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">商店版本</p>
                                <p class="mt-1 font-medium text-slate-700"><?= h($storeVersion !== '' ? $storeVersion : '未填写') ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">本地状态</p>
                                <p class="mt-1 font-medium text-slate-700"><?= $isInstalled ? h($installedSlug) : '未安装' ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">授权</p>
                                <p class="mt-1 font-medium <?= (!$licenseRequired || $hasLicense) ? 'text-emerald-700' : 'text-amber-700' ?>"><?= !$licenseRequired ? '免费授权' : ($hasLicense ? '已授权' : '需购买') ?></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="text-xs text-slate-400">绑定域名</p>
                                <p class="mt-1 truncate font-medium text-slate-700" title="<?= h((string)($storeTheme['bound_domain'] ?? $appStore['site_domain'] ?? '')) ?>"><?= h((string)($storeTheme['bound_domain'] ?? $appStore['site_domain'] ?? '-')) ?></p>
                            </div>
                        </div>

                        <?php if ($isInstalled && $installedVersion !== ''): ?>
                            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                                本地安装版本：<span class="font-semibold text-slate-800"><?= h($installedVersion) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-3 border-t border-slate-200 bg-slate-50 px-5 py-4">
                        <?php if (!$appStore['has_token']): ?>
                            <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-200 px-4 py-3 font-semibold text-slate-500">
                                <i class="fas fa-lock"></i>
                                配置 Token 后安装
                            </button>
                        <?php elseif ($licenseRequired && !$hasLicense && !$canDownload): ?>
                            <form action="<?= url('/admin/appearance/themes/app-store/purchase') ?>" method="post" class="space-y-3">
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="resource_id" value="<?= $storeThemeId ?>">
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
                                <input type="hidden" name="resource_id" value="<?= $storeThemeId ?>">
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-3 font-semibold text-white transition hover:bg-sky-700">
                                    <i class="fas fa-download"></i>
                                    <?= $isInstalled ? ($needsUpdate ? '更新安装' : '重新安装') : '下载安装' ?>
                                </button>
                            </form>

                            <?php if ($isInstalled && !$isActiveStoreTheme && $localTheme && !empty($localTheme['is_valid'])): ?>
                                <form action="<?= url('/admin/appearance/themes/activate') ?>" method="post">
                                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="theme" value="<?= h($installedSlug) ?>">
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800">
                                        <i class="fas fa-bolt"></i>
                                        启用此主题
                                    </button>
                                </form>
                            <?php elseif ($isActiveStoreTheme): ?>
                                <div class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600">
                                    <i class="fas fa-circle-check"></i>
                                    当前前台正在使用这个主题
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
