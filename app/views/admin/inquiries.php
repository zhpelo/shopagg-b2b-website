<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4">
                    <span class="icon mr-2"><i class="fas fa-file-invoice"></i></span>
                    询单管理
                </h1>
                <p class="subtitle is-6">共收到 <?= count($inquiries) ?> 条询单</p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/inquiries/export" class="button is-white">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>导出 CSV</span>
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="notification is-success is-light animate-in">
    <button class="delete" onclick="this.parentElement.remove()"></button>
    <?= h($_GET['success']) ?>
</div>
<?php endif; ?>

<!-- 状态筛选 -->
<div class="modern-tabs animate-in delay-1">
    <a href="/admin/inquiries" class="<?= empty($current_status) ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-list"></i></span>全部
    </a>
    <a href="/admin/inquiries?status=pending" class="<?= $current_status === 'pending' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-clock"></i></span>待处理
    </a>
    <a href="/admin/inquiries?status=contacted" class="<?= $current_status === 'contacted' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-phone"></i></span>已联系
    </a>
    <a href="/admin/inquiries?status=quoted" class="<?= $current_status === 'quoted' ? 'is-active' : '' ?>">
        <span class="icon is-small mr-1"><i class="fas fa-file-invoice-dollar"></i></span>已报价
    </a>
    <a href="/admin/inquiries?status=closed" class="<?= $current_status === 'closed' ? 'is-active' : '' ?>">
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
        <table class="table is-fullwidth">
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
                        <div class="is-flex is-align-items-center">
                            <div class="icon-box mr-3" style="width: 44px; height: 44px; background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0;">
                                <?= strtoupper(mb_substr($row['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= h($row['name']) ?></strong>
                                <p class="is-size-7 has-text-grey"><?= h($row['email']) ?></p>
                                <p class="is-size-7"><?= h($row['company']) ?><?= !empty($row['phone']) ? ' / ' . h($row['phone']) : '' ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="tag is-info is-light"><?= h($row['product_title'] ?? '通用咨询') ?></span>
                        <p class="is-size-7 has-text-grey mt-1">
                            <span class="icon is-small"><i class="fas fa-cubes"></i></span>
                            数量: <?= h($row['quantity'] ?: '未指定') ?>
                        </p>
                    </td>
                    <td>
                        <div class="is-size-7" style="max-width: 280px; white-space: normal; line-height: 1.6;">
                            <?= nl2br(h($row['message'])) ?>
                        </div>
                        <p class="is-size-7 has-text-grey mt-2">
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
                        <p class="is-size-7 has-text-grey">
                            <span class="icon is-small"><i class="fas fa-map-marker-alt"></i></span>
                            IP: <?= h($row['ip']) ?>
                        </p>
                        <?php if (!empty($row['source_url'])): ?>
                        <a href="<?= h($row['source_url']) ?>" target="_blank" class="is-size-7" style="color: #667eea;">
                            <span class="icon is-small"><i class="fas fa-external-link-alt"></i></span>
                            查看来源
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="buttons are-small">
                            <a href="/admin/inquiries/detail?id=<?= $row['id'] ?>" class="button is-info is-light" title="查看详情">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                            <div class="dropdown is-right is-hoverable">
                                <div class="dropdown-trigger">
                                    <button class="button is-small is-light" aria-haspopup="true" style="border-radius: 8px;">
                                        <span class="icon is-small"><i class="fas fa-ellipsis-v"></i></span>
                                    </button>
                                </div>
                                <div class="dropdown-menu">
                                    <div class="dropdown-content">
                                        <a href="/admin/inquiries/detail?id=<?= $row['id'] ?>" class="dropdown-item">
                                            <span class="icon is-small has-text-info mr-2"><i class="fas fa-eye"></i></span>
                                            查看详情
                                        </a>
                                        <hr class="dropdown-divider">
                                        <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=contacted" class="dropdown-item">
                                            <span class="icon is-small has-text-info mr-2"><i class="fas fa-phone"></i></span>
                                            标记为已联系
                                        </a>
                                        <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=quoted" class="dropdown-item">
                                            <span class="icon is-small has-text-success mr-2"><i class="fas fa-file-invoice-dollar"></i></span>
                                            标记为已报价
                                        </a>
                                        <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=closed" class="dropdown-item">
                                            <span class="icon is-small has-text-grey mr-2"><i class="fas fa-check-circle"></i></span>
                                            标记为已关闭
                                        </a>
                                        <hr class="dropdown-divider">
                                        <a href="/admin/inquiries/delete?id=<?= $row['id'] ?>" class="dropdown-item has-text-danger" onclick="return confirm('确定要删除此询单吗？')">
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
