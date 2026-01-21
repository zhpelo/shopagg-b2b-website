<?php
// 根据label设置不同的颜色主题
$theme_colors = [
    '案例' => ['gradient' => 'linear-gradient(135deg, #17a2b8 0%, #20c997 100%)', 'shadow' => 'rgba(23, 162, 184, 0.3)', 'icon' => 'briefcase'],
    '博客' => ['gradient' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)', 'shadow' => 'rgba(40, 167, 69, 0.3)', 'icon' => 'pen-nib'],
];
$theme = $theme_colors[$label] ?? ['gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'shadow' => 'rgba(102, 126, 234, 0.3)', 'icon' => 'file'];
?>

<!-- 页面头部 -->
<div class="page-header animate-in" style="background: <?= $theme['gradient'] ?>; box-shadow: 0 10px 40px <?= $theme['shadow'] ?>;">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= $theme['icon'] ?>"></i></span>
                    <?= h($label) ?>管理
                </h1>
                <p class="subtitle is-6">共有 <?= count($items) ?> 个<?= h($label) ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= h($base) ?>/create" class="button is-white">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>新建<?= h($label) ?></span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($items)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-1">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-<?= $theme['icon'] ?>"></i></span>
        <p>暂无<?= h($label) ?>记录</p>
        <a href="<?= h($base) ?>/create" class="button is-primary mt-4">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>创建第一个<?= h($label) ?></span>
        </a>
    </div>
</div>
<?php else: ?>
<!-- 列表 -->
<div class="modern-table animate-in delay-1">
    <div class="table-container">
        <table class="table is-fullwidth">
            <thead>
                <tr>
                    <th>标题</th>
                    <th>别名 (Slug)</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                <tr>
                    <td>
                        <div class="is-flex is-align-items-center">
                            <div style="width: 44px; height: 44px; background: <?= $theme['gradient'] ?>; border-radius: 10px; margin-right: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <span class="icon has-text-white"><i class="fas fa-<?= $theme['icon'] ?>"></i></span>
                            </div>
                            <div>
                                <strong><?= h($row['title']) ?></strong>
                                <?php if (!empty($row['summary'])): ?>
                                <p class="is-size-7 has-text-grey" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= h($row['summary']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8125rem;"><?= h($row['slug']) ?></code>
                    </td>
                    <td>
                        <span class="is-size-7 has-text-grey">
                            <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
                            <?= h($row['created_at']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="buttons are-small" style="gap: 0.5rem;">
                            <a href="<?= h($base) ?>/edit?id=<?= (int)$row['id'] ?>" class="button is-light" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-edit"></i></span>
                                <span>编辑</span>
                            </a>
                            <a href="<?= h($base) ?>/delete?id=<?= (int)$row['id'] ?>" class="button is-danger is-light" style="border-radius: 8px;" onclick="return confirm('确定要删除该<?= h($label) ?>吗？此操作不可恢复。')">
                                <span class="icon"><i class="fas fa-trash-alt"></i></span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
