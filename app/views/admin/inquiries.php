<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="icon mr-2"><i class="fas fa-file-invoice"></i></span>
                    询单管理
                </h1>
                <p class="mt-1 text-sm text-white/80">共收到 <?= count($inquiries) ?> 条询单</p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= url('/admin/inquiries/export') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>导出 CSV</span>
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="animate-in relative rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
    <button class="delete" type="button" onclick="this.parentElement.remove()"></button>
    <?= h($_GET['success']) ?>
</div>
<?php endif; ?>

<!-- 状态筛选 -->
<div class="modern-tabs animate-in delay-1">
    <a href="<?= url('/admin/inquiries') ?>" class="<?= empty($current_status) ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-list"></i></span>全部
    </a>
    <a href="<?= url('/admin/inquiries?status=pending') ?>" class="<?= $current_status === 'pending' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-clock"></i></span>待处理
    </a>
    <a href="<?= url('/admin/inquiries?status=contacted') ?>" class="<?= $current_status === 'contacted' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-phone"></i></span>已联系
    </a>
    <a href="<?= url('/admin/inquiries?status=quoted') ?>" class="<?= $current_status === 'quoted' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-file-invoice-dollar"></i></span>已报价
    </a>
    <a href="<?= url('/admin/inquiries?status=closed') ?>" class="<?= $current_status === 'closed' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-check-circle"></i></span>已关闭
    </a>
</div>

<?php if (empty($inquiries)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-2">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-file-invoice"></i></span>
        <p>暂无相关询单记录</p>
    </div>
</div>
<?php else: ?>
<!-- 询单列表 -->
<div class="modern-table animate-in delay-2">
    <div class="table-container">
        <table class="min-w-full text-sm text-slate-700">
            <thead>
                <tr>
                    <th>客户信息</th>
                    <th>需求产品</th>
                    <th>询单内容</th>
                    <th>状态</th>
                    <th>来源</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inquiries as $row): ?>
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="icon-box mr-3" style="width: 44px; height: 44px; background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0;">
                                <?= strtoupper(mb_substr($row['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= h($row['name']) ?></strong>
                                <p class="text-xs text-slate-500"><?= h($row['email']) ?></p>
                                <p class="text-xs text-slate-600"><?= h($row['company']) ?><?= !empty($row['phone']) ? ' / ' . h($row['phone']) : '' ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700"><?= h($row['product_title'] ?? '通用咨询') ?></span>
                        <p class="mt-1 text-xs text-slate-500">
                            <span class="icon is-small"><i class="fas fa-cubes"></i></span>
                            数量: <?= h($row['quantity'] ?: '未指定') ?>
                        </p>
                    </td>
                    <td>
                        <div class="max-w-[280px] whitespace-normal text-xs leading-6 text-slate-600">
                            <?= nl2br(h($row['message'])) ?>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            <span class="icon is-small"><i class="far fa-clock"></i></span>
                            <?= format_date($row['created_at']) ?>
                        </p>
                    </td>
                    <td>
                        <?php 
                        $status_styles = [
                            'pending' => ['bg' => 'rgba(255, 193, 7, 0.15)', 'color' => '#d39e00', 'icon' => 'clock'],
                            'contacted' => ['bg' => 'rgba(23, 162, 184, 0.15)', 'color' => '#17a2b8', 'icon' => 'phone'],
                            'quoted' => ['bg' => 'rgba(40, 167, 69, 0.15)', 'color' => '#28a745', 'icon' => 'file-invoice-dollar'],
                            'closed' => ['bg' => 'rgba(108, 117, 125, 0.15)', 'color' => '#6c757d', 'icon' => 'check-circle']
                        ];
                        $status_labels = [
                            'pending' => '待处理',
                            'contacted' => '已联系',
                            'quoted' => '已报价',
                            'closed' => '已关闭'
                        ];
                        $style = $status_styles[$row['status']] ?? $status_styles['pending'];
                        ?>
                        <span class="tag" style="background: <?= $style['bg'] ?>; color: <?= $style['color'] ?>;">
                            <span class="icon is-small mr-1"><i class="fas fa-<?= $style['icon'] ?>"></i></span>
                            <?= $status_labels[$row['status']] ?? $row['status'] ?>
                        </span>
                    </td>
                    <td>
                        <p class="text-xs text-slate-500">
                            <span class="icon is-small"><i class="fas fa-map-marker-alt"></i></span>
                            IP: <?= h($row['ip']) ?>
                        </p>
                        <?php if (!empty($row['source_url'])): ?>
                        <a href="<?= h($row['source_url']) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                            <span class="icon is-small"><i class="fas fa-external-link-alt"></i></span>
                            查看来源
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            <a href="<?= url('/admin/inquiries/detail?id=' . (int)$row['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 transition hover:bg-cyan-100" title="查看详情">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                        <div class="dropdown relative">
                            <div class="dropdown-trigger">
                                <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900" aria-haspopup="true">
                                        <span class="icon is-small"><i class="fas fa-ellipsis-v"></i></span>
                                </button>
                            </div>
                            <div class="dropdown-menu">
                                <div class="dropdown-content">
                                        <a href="<?= url('/admin/inquiries/detail?id=' . (int)$row['id']) ?>" class="dropdown-item rounded-xl px-3 py-2.5 text-sm">
                                            <span class="icon is-small has-text-info mr-2"><i class="fas fa-eye"></i></span>
                                            查看详情
                                        </a>
                                        <hr class="dropdown-divider">
                                    <a href="<?= url('/admin/inquiries/status?id=' . (int)$row['id'] . '&status=contacted') ?>" class="dropdown-item rounded-xl px-3 py-2.5 text-sm">
                                        <span class="icon is-small has-text-info mr-2"><i class="fas fa-phone"></i></span>
                                        标记为已联系
                                    </a>
                                    <a href="<?= url('/admin/inquiries/status?id=' . (int)$row['id'] . '&status=quoted') ?>" class="dropdown-item rounded-xl px-3 py-2.5 text-sm">
                                        <span class="icon is-small has-text-success mr-2"><i class="fas fa-file-invoice-dollar"></i></span>
                                        标记为已报价
                                    </a>
                                    <a href="<?= url('/admin/inquiries/status?id=' . (int)$row['id'] . '&status=closed') ?>" class="dropdown-item rounded-xl px-3 py-2.5 text-sm">
                                        <span class="icon is-small has-text-grey mr-2"><i class="fas fa-check-circle"></i></span>
                                        标记为已关闭
                                    </a>
                                    <hr class="dropdown-divider">
                                        <a href="<?= url('/admin/inquiries/delete?id=' . (int)$row['id']) ?>" class="dropdown-item rounded-xl px-3 py-2.5 text-sm text-rose-600" onclick="return confirm('确定要删除此询单吗？')">
                                            <span class="icon is-small mr-2"><i class="fas fa-trash"></i></span>
                                            删除询单
                                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
