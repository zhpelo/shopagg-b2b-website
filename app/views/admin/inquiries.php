<div class="level">
    <div class="level-left">
        <div>
            <h1 class="title is-4">询单管理</h1>
            <p class="subtitle is-7">共收到 <?= count($inquiries) ?> 条询单</p>
        </div>
    </div>
    <div class="level-right">
        <a href="/admin/inquiries/export" class="button is-light is-small">
            <span class="icon"><i class="fas fa-download"></i></span>
            <span>导出 CSV</span>
        </a>
    </div>
</div>

<div class="tabs is-toggle is-small mb-4">
    <ul>
        <li class="<?= empty($current_status) ? 'is-active' : '' ?>">
            <a href="/admin/inquiries">全部</a>
        </li>
        <li class="<?= $current_status === 'pending' ? 'is-active' : '' ?>">
            <a href="/admin/inquiries?status=pending">待处理</a>
        </li>
        <li class="<?= $current_status === 'contacted' ? 'is-active' : '' ?>">
            <a href="/admin/inquiries?status=contacted">已联系</a>
        </li>
        <li class="<?= $current_status === 'quoted' ? 'is-active' : '' ?>">
            <a href="/admin/inquiries?status=quoted">已报价</a>
        </li>
        <li class="<?= $current_status === 'closed' ? 'is-active' : '' ?>">
            <a href="/admin/inquiries?status=closed">已关闭</a>
        </li>
    </ul>
</div>

<div class="box admin-card p-0">
    <div class="table-container">
        <table class="table is-fullwidth is-hoverable">
        <thead>
                <tr style="background: #f8fafc;">
                    <th class="py-3 pl-4">客户信息</th>
                    <th class="py-3">需求产品</th>
                    <th class="py-3">询单内容</th>
                    <th class="py-3">状态</th>
                    <th class="py-3">来源信息</th>
                    <th class="py-3 pr-4">操作</th>
            </tr>
        </thead>
        <tbody>
                <?php if (empty($inquiries)): ?>
                    <tr><td colspan="6" class="has-text-centered py-6 has-text-grey">暂无相关询单记录</td></tr>
                <?php endif; ?>
            <?php foreach ($inquiries as $row): ?>
            <tr>
                    <td class="pl-4">
                        <strong><?= h($row['name']) ?></strong><br>
                        <span class="is-size-7 has-text-grey"><?= h($row['email']) ?></span><br>
                        <span class="is-size-7"><?= h($row['company']) ?> / <?= h($row['phone']) ?></span>
                    </td>
                    <td>
                        <span class="tag is-light"><?= h($row['product_title'] ?? '通用咨询') ?></span><br>
                        <span class="is-size-7 has-text-grey">数量: <?= h($row['quantity'] ?: '未指定') ?></span>
                    </td>
                    <td>
                        <div class="is-size-7" style="max-width: 300px; white-space: normal;"><?= nl2br(h($row['message'])) ?></div>
                        <p class="is-size-7 has-text-grey mt-1"><?= h($row['created_at']) ?></p>
                    </td>
                    <td>
                        <?php 
                        $status_colors = [
                            'pending' => 'is-warning',
                            'contacted' => 'is-info',
                            'quoted' => 'is-success',
                            'closed' => 'is-light'
                        ];
                        $status_labels = [
                            'pending' => '待处理',
                            'contacted' => '已联系',
                            'quoted' => '已报价',
                            'closed' => '已关闭'
                        ];
                        ?>
                        <span class="tag <?= $status_colors[$row['status']] ?? 'is-light' ?> is-light">
                            <?= $status_labels[$row['status']] ?? $row['status'] ?>
                        </span>
                    </td>
                    <td>
                        <p class="is-size-7">IP: <?= h($row['ip']) ?></p>
                        <a href="<?= h($row['source_url']) ?>" target="_blank" class="is-size-7 has-text-link" title="<?= h($row['source_url']) ?>">查看来源页面</a>
                    </td>
                    <td class="pr-4">
                        <div class="dropdown is-right is-hoverable">
                            <div class="dropdown-trigger">
                                <button class="button is-small is-white" aria-haspopup="true">
                                    <span class="icon is-small"><i class="fas fa-ellipsis-v"></i></span>
                                </button>
                            </div>
                            <div class="dropdown-menu">
                                <div class="dropdown-content">
                                    <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=contacted" class="dropdown-item is-size-7">标记为已联系</a>
                                    <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=quoted" class="dropdown-item is-size-7">标记为已报价</a>
                                    <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=closed" class="dropdown-item is-size-7">标记为已关闭</a>
                                    <hr class="dropdown-divider">
                                    <a href="/admin/inquiries/status?id=<?= $row['id'] ?>&status=pending" class="dropdown-item is-size-7">重置为待处理</a>
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
