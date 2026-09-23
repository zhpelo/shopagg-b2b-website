<?php
$appStore = is_array($appStore ?? null) ? $appStore : [];
$hasToken = !empty($appStore['has_token']);
$maskedToken = (string)($appStore['masked_token'] ?? '');
$siteDomain = (string)($appStore['site_domain'] ?? base_url());
$tokenUrl = (string)($appStore['token_url'] ?? 'https://www.shopagg.com/dashboard/api-tokens');
?>
<div class="space-y-6">
    <header class="rounded-xl border border-slate-200 bg-white px-5 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">SHOPAGG App Store</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">账户与 API Token</h1>
                <p class="mt-1 text-sm text-slate-500">连接 SHOPAGG 账户，用于购买、下载和更新需要授权的应用与主题。</p>
            </div>
            <a class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="<?= url('/admin/app-store') ?>">
                <i class="fas fa-arrow-left text-slate-400" aria-hidden="true"></i>返回应用商店
            </a>
        </div>
    </header>

    <?php if (isset($_GET['success'])): ?>
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <i class="fas fa-circle-check mt-0.5" aria-hidden="true"></i><span><?= h($_GET['success']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <i class="fas fa-triangle-exclamation mt-0.5" aria-hidden="true"></i><span><?= h($_GET['error']) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-7">
            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full <?= $hasToken ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' ?>">
                        <i class="fas <?= $hasToken ? 'fa-check' : 'fa-link' ?>" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">连接状态</h2>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $hasToken ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>"><?= $hasToken ? '已连接' : '尚未连接' ?></span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600"><?= $hasToken ? '此站点已保存可用的 App Store API Token。' : '您仍可浏览免费资源；安装付费或需要授权的资源前，请先完成连接。' ?></p>
                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="rounded-lg bg-slate-50 px-4 py-3"><dt class="text-xs text-slate-400">当前站点</dt><dd class="mt-1 break-all font-semibold text-slate-800"><?= h($siteDomain) ?></dd></div>
                            <div class="rounded-lg bg-slate-50 px-4 py-3"><dt class="text-xs text-slate-400">已保存 Token</dt><dd class="mt-1 break-all font-mono text-xs font-semibold text-slate-800"><?= h($hasToken ? $maskedToken : '未配置') ?></dd></div>
                        </dl>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">新手引导</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">如何获取 App Store API Token</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">整个过程通常只需几分钟。Token 相当于此站点访问 App Store 的授权凭证。</p>
                </div>

                <ol class="mt-6 space-y-5">
                    <li class="flex gap-4">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">1</span>
                        <div class="min-w-0 pt-1"><h3 class="font-bold text-slate-900">登录 SHOPAGG 账户</h3><p class="mt-1 text-sm leading-6 text-slate-600">使用购买应用或主题的 SHOPAGG 账户登录；没有账户时，可先在 SHOPAGG 完成注册。</p></div>
                    </li>
                    <li class="flex gap-4">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">2</span>
                        <div class="min-w-0 pt-1"><h3 class="font-bold text-slate-900">打开 API Tokens 页面并创建 Token</h3><p class="mt-1 text-sm leading-6 text-slate-600">进入账户后台的 API Tokens 页面，创建一个新 Token。建议使用当前站点域名作为名称，方便以后辨认。</p><p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">建议名称：<?= h((string)(parse_url($siteDomain, PHP_URL_HOST) ?: $siteDomain)) ?></p></div>
                    </li>
                    <li class="flex gap-4">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">3</span>
                        <div class="min-w-0 pt-1"><h3 class="font-bold text-slate-900">复制并粘贴到本站</h3><p class="mt-1 text-sm leading-6 text-slate-600">复制生成的完整 Token，粘贴到右侧输入框并保存。本站会立即向 SHOPAGG 验证，验证成功后才会保存。</p></div>
                    </li>
                </ol>

                <a class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" href="<?= h($tokenUrl) ?>" target="_blank" rel="noreferrer">
                    打开 SHOPAGG API Tokens 页面<i class="fas fa-arrow-up-right-from-square text-xs" aria-hidden="true"></i>
                </a>
            </section>

            <section class="rounded-xl border border-sky-200 bg-sky-50 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-bold text-sky-900"><i class="fas fa-shield-halved" aria-hidden="true"></i>Token 安全提示</h2>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-sky-900">
                    <li>• Token 只会保存在当前网站服务器中，页面再次打开时仅显示脱敏内容。</li>
                    <li>• 请勿把 Token 发给他人，也不要放入前台页面、截图或公开代码仓库。</li>
                    <li>• 建议每个网站使用独立 Token；怀疑泄露时，请在 SHOPAGG 后台撤销并重新生成。</li>
                </ul>
            </section>
        </div>

        <aside class="xl:col-span-5">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:sticky xl:top-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><i class="fas fa-key" aria-hidden="true"></i></span>
                    <div><h2 class="font-bold text-slate-900"><?= $hasToken ? '更换 API Token' : '连接 SHOPAGG 账户' ?></h2><p class="mt-0.5 text-xs text-slate-500">保存时会自动验证有效性</p></div>
                </div>

                <form class="mt-6 space-y-4" action="<?= url('/admin/app-store/settings') ?>" method="post">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <label class="block">
                        <span class="text-sm font-semibold text-slate-700">App Store API Token</span>
                        <input class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-3 font-mono text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="password" name="api_token" placeholder="粘贴完整 Token" autocomplete="off" spellcheck="false" required>
                    </label>
                    <p class="text-xs leading-5 text-slate-500">粘贴时请确保 Token 前后没有空格。更换 Token 不会删除已安装的应用或主题。</p>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700" type="submit"><i class="fas fa-circle-check" aria-hidden="true"></i>验证并保存 Token</button>
                </form>

                <?php if ($hasToken): ?>
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <h3 class="text-sm font-bold text-slate-900">解除当前连接</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500">解除后，已安装内容不会被删除，但授权资源将无法继续下载或更新。</p>
                        <form class="mt-3" action="<?= url('/admin/app-store/settings') ?>" method="post" onsubmit="return confirm('确认解除当前站点的 SHOPAGG 账户绑定吗？')">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="clear_token" value="1">
                            <button class="w-full rounded-lg border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50" type="submit">解除绑定</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="mt-6 rounded-lg bg-slate-50 p-4 text-xs leading-5 text-slate-500">
                    <p class="font-semibold text-slate-700">验证失败怎么办？</p>
                    <p class="mt-1">请确认 Token 已完整复制且未被撤销。仍无法连接时，可重新生成 Token 后再试。</p>
                </div>
            </section>
        </aside>
    </div>
</div>
