<?php
/**
 * 程序更新页面
 * @var array $checkResult 更新检查结果
 * @var array $releases GitHub 上的版本列表
 * @var array $history 本地更新历史
 * @var array $backups 备份列表
 * @var array $migrationStatus 迁移状态
 */
$hasUpdate = $checkResult['has_update'] ?? false;
$currentVersion = $checkResult['current_version'] ?? '1.0.0';
$latestVersion = $checkResult['latest_version'] ?? $currentVersion;
$releaseInfo = $checkResult['release_info'] ?? null;

// 获取迁移状态
$migrationStatus = $migrationStatus ?? ['status' => ['total' => 0, 'executed' => 0, 'pending' => 0], 'pending' => [], 'executed' => []];
$pendingMigrations = $migrationStatus['pending'] ?? [];
$executedMigrations = $migrationStatus['executed'] ?? [];
$migrationStats = $migrationStatus['status'] ?? ['total' => 0, 'executed' => 0, 'pending' => 0];
?>

<!-- 页面头部 -->
<div class="page-header bg-gradient-to-br from-emerald-500 to-teal-600 shadow-[0_10px_40px_rgba(16,185,129,0.3)]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/16 text-white">
                    <i class="fas fa-sync-alt"></i>
                </span>
                <span>程序更新</span>
            </h1>
            <p class="mt-2 text-sm text-white/80">检查更新、下载并安装最新版本，查看更新历史。</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/settings-updater') ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                <i class="fas fa-refresh"></i>
                <span>刷新状态</span>
            </a>
        </div>
    </div>
</div>

<!-- 当前版本状态卡片 -->
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <i class="fas fa-code-branch text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">当前版本</p>
                <p class="text-lg font-bold text-slate-900"><?= h($currentVersion) ?></p>
            </div>
        </div>
    </div>
    
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl <?= $hasUpdate ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' ?>">
                <i class="fas <?= $hasUpdate ? 'fa-arrow-up' : 'fa-check' ?> text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">最新版本</p>
                <p class="text-lg font-bold text-slate-900"><?= h($latestVersion) ?></p>
            </div>
        </div>
    </div>
    
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i class="fab fa-github text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">GitHub 仓库</p>
                <a href="https://github.com/zhpelo/shopagg-b2b-website" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline">
                    查看仓库 <i class="fas fa-external-link-alt text-xs"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl <?= $migrationStats['pending'] > 0 ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600' ?>">
                <i class="fas fa-database text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">数据库迁移</p>
                <p class="text-lg font-bold text-slate-900">
                    <?= $migrationStats['executed'] ?> / <?= $migrationStats['total'] ?>
                    <?php if ($migrationStats['pending'] > 0): ?>
                    <span class="ml-1 text-xs font-normal text-amber-600">(<?= $migrationStats['pending'] ?> 个待执行)</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- 更新提示 -->
<?php if ($hasUpdate && $releaseInfo): ?>
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
    <div class="flex items-start gap-4">
        <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
            <i class="fas fa-bell"></i>
        </div>
        <div class="flex-1">
            <h3 class="text-base font-bold text-amber-900">发现新版本：<?= h($releaseInfo['name']) ?></h3>
            <p class="mt-1 text-sm text-amber-700">
                发布于 <?= h(date('Y-m-d H:i', strtotime($releaseInfo['published_at']))) ?>
            </p>
            <?php if ($releaseInfo['body']): ?>
            <div class="mt-3 rounded-xl bg-white/60 p-3 text-sm text-amber-800">
                <div class="prose prose-sm max-w-none prose-amber">
                    <?= nl2br(h($releaseInfo['body'])) ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition hover:from-amber-600 hover:to-orange-600" onclick="installUpdate('<?= h($latestVersion) ?>')">
                    <i class="fas fa-download"></i>
                    <span>立即更新</span>
                </button>
                <a href="<?= h($releaseInfo['html_url']) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50">
                    <i class="fas fa-external-link-alt"></i>
                    <span>查看详情</span>
                </a>
            </div>
        </div>
    </div>
</div>
<?php elseif (!$checkResult['success']): ?>
<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
    <div class="flex items-center gap-3">
        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div>
            <h3 class="font-semibold text-red-900">检查更新失败</h3>
            <p class="text-sm text-red-700"><?= h($checkResult['message']) ?></p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
    <div class="flex items-center gap-3">
        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <h3 class="font-semibold text-emerald-900">已是最新版本</h3>
            <p class="text-sm text-emerald-700">当前运行的 <?= h($currentVersion) ?> 是最新版本。</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 标签页导航 -->
<div class="mb-6 border-b border-slate-200">
    <nav class="flex gap-1" aria-label="Tabs">
        <button type="button" class="tab-btn active inline-flex items-center gap-2 border-b-2 border-indigo-500 px-4 py-3 text-sm font-semibold text-indigo-600" data-tab="releases">
            <i class="fas fa-history"></i>
            <span>版本历史</span>
        </button>
        <button type="button" class="tab-btn inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:border-slate-200 hover:text-slate-900" data-tab="history">
            <i class="fas fa-list-ul"></i>
            <span>更新记录</span>
        </button>
        <button type="button" class="tab-btn inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:border-slate-200 hover:text-slate-900" data-tab="backups">
            <i class="fas fa-archive"></i>
            <span>备份管理</span>
        </button>
        <button type="button" class="tab-btn inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:border-slate-200 hover:text-slate-900" data-tab="migrations">
            <i class="fas fa-database"></i>
            <span>数据库迁移</span>
            <?php if ($migrationStats['pending'] > 0): ?>
            <span class="ml-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-xs font-bold text-white"><?= $migrationStats['pending'] ?></span>
            <?php endif; ?>
        </button>
    </nav>
</div>

<!-- 版本历史 -->
<div id="tab-releases" class="tab-content">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <i class="fab fa-github text-slate-400"></i>
                GitHub 版本发布记录
            </h2>
        </div>
        <?php if (empty($releases)): ?>
        <div class="p-8 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i class="fas fa-inbox text-2xl"></i>
            </div>
            <p class="mt-3 text-slate-500">暂无版本记录</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-slate-100">
            <?php foreach ($releases as $release): ?>
            <div class="p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900"><?= h($release['name']) ?></h3>
                            <?php if ($release['version'] === $currentVersion): ?>
                            <span class="inline-flex items-center rounded-lg bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">当前</span>
                            <?php endif; ?>
                            <?php if ($release['is_prerelease']): ?>
                            <span class="inline-flex items-center rounded-lg bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">预发布</span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            <i class="fas fa-user mr-1"></i><?= h($release['author']) ?>
                            <span class="mx-2">·</span>
                            <i class="fas fa-calendar mr-1"></i><?= h(date('Y-m-d', strtotime($release['published_at']))) ?>
                            <span class="mx-2">·</span>
                            <i class="fas fa-file-archive mr-1"></i><?= $release['assets_count'] ?> 个资源
                        </p>
                        <?php if ($release['body']): ?>
                        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                            <?= nl2br(h($release['body'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <?php if (version_compare($release['version'], $currentVersion, '>')): ?>
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700" onclick="installUpdate('<?= h($release['version']) ?>')">
                            <i class="fas fa-download"></i>
                            更新
                        </button>
                        <?php endif; ?>
                        <a href="<?= h($release['html_url']) ?>" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                            <i class="fas fa-external-link-alt"></i>
                            查看
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 本地更新记录 -->
<div id="tab-history" class="tab-content hidden">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <i class="fas fa-history text-slate-400"></i>
                本地更新记录
            </h2>
        </div>
        <?php if (empty($history)): ?>
        <div class="p-8 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i class="fas fa-inbox text-2xl"></i>
            </div>
            <p class="mt-3 text-slate-500">暂无更新记录</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">时间</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">版本</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">状态</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">备注</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($history as $log): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-slate-600"><?= h($log['timestamp']) ?></td>
                        <td class="px-5 py-3 font-medium text-slate-900"><?= h($log['version']) ?></td>
                        <td class="px-5 py-3">
                            <?php if ($log['status'] === 'success'): ?>
                            <span class="inline-flex items-center rounded-lg bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                <i class="fas fa-check mr-1"></i>成功
                            </span>
                            <?php elseif ($log['status'] === 'failed'): ?>
                            <span class="inline-flex items-center rounded-lg bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                <i class="fas fa-times mr-1"></i>失败
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                <?= h($log['status']) ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-slate-600"><?= h($log['message']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 备份管理 -->
<div id="tab-backups" class="tab-content hidden">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                    <i class="fas fa-archive text-slate-400"></i>
                    备份文件
                </h2>
                <p class="text-xs text-slate-500">更新前会自动创建备份，可用于回滚</p>
            </div>
        </div>
        <?php if (empty($backups)): ?>
        <div class="p-8 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i class="fas fa-inbox text-2xl"></i>
            </div>
            <p class="mt-3 text-slate-500">暂无备份文件</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">文件名</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">大小</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700">创建时间</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-700">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($backups as $backup): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file-archive text-slate-400"></i>
                                <span class="font-medium text-slate-900"><?= h($backup['filename']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-600"><?= h($backup['size']) ?></td>
                        <td class="px-5 py-3 text-slate-600"><?= h($backup['created_at']) ?></td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100" onclick="deleteBackup('<?= h($backup['filename']) ?>')">
                                <i class="fas fa-trash"></i>
                                删除
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 数据库迁移 -->
<div id="tab-migrations" class="tab-content hidden">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                    <i class="fas fa-database text-slate-400"></i>
                    数据库迁移管理
                </h2>
                <div class="flex items-center gap-2">
                    <?php if ($migrationStats['pending'] > 0): ?>
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700" onclick="runMigrations()">
                        <i class="fas fa-play"></i>
                        执行迁移
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 待执行迁移 -->
        <?php if (!empty($pendingMigrations)): ?>
        <div class="border-b border-slate-200 bg-amber-50/50 p-4">
            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-amber-900">
                <i class="fas fa-clock text-amber-500"></i>
                待执行迁移 (<?= count($pendingMigrations) ?>)
            </h3>
            <div class="space-y-2">
                <?php foreach ($pendingMigrations as $migration): ?>
                <div class="flex items-center justify-between rounded-lg border border-amber-200 bg-white p-3">
                    <div>
                        <p class="font-medium text-slate-900"><?= h($migration['name']) ?></p>
                        <p class="text-xs text-slate-500">版本: <?= h($migration['version']) ?></p>
                    </div>
                    <span class="inline-flex items-center rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">待执行</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 已执行迁移 -->
        <div class="p-4">
            <h3 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-700">
                <i class="fas fa-check-circle text-emerald-500"></i>
                已执行迁移 (<?= count($executedMigrations) ?>)
            </h3>
            <?php if (empty($executedMigrations)): ?>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                暂无已执行的迁移
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-slate-700">版本</th>
                            <th class="px-4 py-2 text-left font-semibold text-slate-700">名称</th>
                            <th class="px-4 py-2 text-left font-semibold text-slate-700">执行时间</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-700">耗时</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach (array_reverse($executedMigrations, true) as $version => $migration): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 font-mono text-xs text-slate-600"><?= h($version) ?></td>
                            <td class="px-4 py-2 text-slate-900"><?= h($migration['name']) ?></td>
                            <td class="px-4 py-2 text-slate-600"><?= h($migration['executed_at']) ?></td>
                            <td class="px-4 py-2 text-right text-slate-600"><?= $migration['execution_time'] ?>ms</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 更新进度模态框 -->
<div id="update-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="text-center">
            <div id="update-icon" class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                <i class="fas fa-download text-2xl"></i>
            </div>
            <h3 id="update-title" class="text-lg font-bold text-slate-900">正在更新</h3>
            <p id="update-message" class="mt-2 text-sm text-slate-600">正在准备更新...</p>
            
            <div id="update-progress-container" class="mt-4">
                <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                    <span id="update-status-text">处理中...</span>
                    <span id="update-percent">0%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                    <div id="update-progress-bar" class="h-full w-0 rounded-full bg-indigo-600 transition-all duration-300"></div>
                </div>
            </div>
            
            <div id="update-actions" class="mt-6 hidden">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700" onclick="closeUpdateModal()">
                    确定
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// 标签页切换
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        
        // 切换按钮状态
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
            b.classList.add('border-transparent', 'text-slate-500');
        });
        btn.classList.add('active', 'border-indigo-500', 'text-indigo-600');
        btn.classList.remove('border-transparent', 'text-slate-500');
        
        // 切换内容
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
    });
});

// 安装更新
async function installUpdate(version) {
    if (!confirm('确定要更新到版本 ' + version + ' 吗？\n\n更新前系统会自动创建备份。')) {
        return;
    }
    
    showUpdateModal('正在更新', '正在准备下载更新包...');
    
    try {
        // 步骤1：下载
        updateProgress(10, '正在下载更新包...');
        const downloadRes = await fetch('<?= url('/admin/settings-updater/download') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf=<?= h(csrf_token()) ?>&version=' + encodeURIComponent(version)
        });
        
        const downloadData = await downloadRes.json();
        
        if (!downloadData.success) {
            throw new Error(downloadData.message || '下载失败');
        }
        
        updateProgress(50, '下载完成，正在安装...');
        
        // 步骤2：安装
        const installRes = await fetch('<?= url('/admin/settings-updater/install') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf=<?= h(csrf_token()) ?>&version=' + encodeURIComponent(version) + '&filepath=' + encodeURIComponent(downloadData.filepath)
        });
        
        const installData = await installRes.json();
        
        if (!installData.success) {
            throw new Error(installData.message || '安装失败');
        }
        
        updateProgress(100, '更新完成！');
        showUpdateSuccess('更新成功', '已成功安装版本 ' + version + '，共更新 ' + (installData.files_updated || 0) + ' 个文件。');
        
    } catch (error) {
        showUpdateError('更新失败', error.message);
    }
}

// 删除备份
async function deleteBackup(filename) {
    if (!confirm('确定要删除备份 ' + filename + ' 吗？此操作不可恢复。')) {
        return;
    }
    
    try {
        const res = await fetch('<?= url('/admin/settings-updater/delete-backup') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf=<?= h(csrf_token()) ?>&filename=' + encodeURIComponent(filename)
        });
        
        const data = await res.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || '删除失败');
        }
    } catch (error) {
        alert('删除失败：' + error.message);
    }
}

// 显示更新模态框
function showUpdateModal(title, message) {
    document.getElementById('update-title').textContent = title;
    document.getElementById('update-message').textContent = message;
    document.getElementById('update-icon').className = 'mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600';
    document.getElementById('update-icon').innerHTML = '<i class="fas fa-download text-2xl"></i>';
    document.getElementById('update-progress-container').classList.remove('hidden');
    document.getElementById('update-actions').classList.add('hidden');
    document.getElementById('update-modal').classList.remove('hidden');
    document.getElementById('update-modal').classList.add('flex');
    updateProgress(0, '准备中...');
}

// 更新进度
function updateProgress(percent, text) {
    document.getElementById('update-progress-bar').style.width = percent + '%';
    document.getElementById('update-percent').textContent = percent + '%';
    if (text) {
        document.getElementById('update-status-text').textContent = text;
    }
}

// 显示更新成功
function showUpdateSuccess(title, message) {
    document.getElementById('update-title').textContent = title;
    document.getElementById('update-message').textContent = message;
    document.getElementById('update-icon').className = 'mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600';
    document.getElementById('update-icon').innerHTML = '<i class="fas fa-check text-2xl"></i>';
    document.getElementById('update-progress-container').classList.add('hidden');
    document.getElementById('update-actions').classList.remove('hidden');
}

// 显示更新错误
function showUpdateError(title, message) {
    document.getElementById('update-title').textContent = title;
    document.getElementById('update-message').textContent = message;
    document.getElementById('update-icon').className = 'mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-red-600';
    document.getElementById('update-icon').innerHTML = '<i class="fas fa-exclamation-triangle text-2xl"></i>';
    document.getElementById('update-progress-container').classList.add('hidden');
    document.getElementById('update-actions').classList.remove('hidden');
}

// 关闭更新模态框
function closeUpdateModal() {
    document.getElementById('update-modal').classList.add('hidden');
    document.getElementById('update-modal').classList.remove('flex');
    window.location.reload();
}

// 执行数据库迁移
async function runMigrations() {
    if (!confirm('确定要执行数据库迁移吗？\n\n此操作将修改数据库结构，请确保已备份重要数据。')) {
        return;
    }
    
    showUpdateModal('正在执行迁移', '正在准备执行数据库迁移...');
    updateProgress(30, '正在执行迁移...');
    
    try {
        const res = await fetch('<?= url('/admin/settings-updater/migrations/run') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf=<?= h(csrf_token()) ?>'
        });
        
        const data = await res.json();
        
        if (data.success) {
            updateProgress(100, '迁移完成！');
            const count = data.executed ? data.executed.length : 0;
            showUpdateSuccess('迁移成功', '成功执行 ' + count + ' 个数据库迁移。');
        } else {
            throw new Error(data.message || '迁移失败');
        }
    } catch (error) {
        showUpdateError('迁移失败', error.message);
    }
}
</script>
