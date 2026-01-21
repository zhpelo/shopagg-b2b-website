<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); box-shadow: 0 10px 40px rgba(255, 193, 7, 0.3);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-folder"></i></span>
                    产品分类
                </h1>
                <p class="subtitle is-6">共有 <?= count($categories) ?> 个分类</p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/categories/create" class="button is-white">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>新建分类</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($categories)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-1">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-folder-open"></i></span>
        <p>暂无分类记录</p>
        <a href="/admin/categories/create" class="button is-warning mt-4">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>创建第一个分类</span>
        </a>
    </div>
</div>
<?php else: ?>
<!-- 分类列表 -->
<div class="modern-table animate-in delay-1">
    <div class="table-container">
        <table class="table is-fullwidth">
            <thead>
                <tr>
                    <th>分类名称</th>
                    <th>别名 (Slug)</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $row): ?>
                <tr>
                    <td>
                        <div class="is-flex is-align-items-center">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border-radius: 10px; margin-right: 1rem; display: flex; align-items: center; justify-content: center;">
                                <span class="icon has-text-white"><i class="fas fa-folder"></i></span>
                            </div>
                            <strong><?= h($row['name']) ?></strong>
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
                            <a href="/admin/categories/edit?id=<?= (int)$row['id'] ?>" class="button is-light" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-edit"></i></span>
                                <span>编辑</span>
                            </a>
                            <a href="/admin/categories/delete?id=<?= (int)$row['id'] ?>" class="button is-danger is-light" style="border-radius: 8px;" onclick="return confirm('确定要删除该分类吗？此操作不可恢复。')">
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
