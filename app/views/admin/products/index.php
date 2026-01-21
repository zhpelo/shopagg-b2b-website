<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-box"></i></span>
                    产品管理
                </h1>
                <p class="subtitle is-6">共有 <?= count($products) ?> 个产品</p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/products/create" class="button is-white">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>添加产品</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($products)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-1">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-box-open"></i></span>
        <p>暂无产品记录</p>
        <a href="/admin/products/create" class="button is-primary mt-4">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>添加第一个产品</span>
        </a>
    </div>
</div>
<?php else: ?>
<!-- 产品列表 -->
<div class="modern-table animate-in delay-1">
    <div class="table-container">
        <table class="table is-fullwidth">
            <thead>
                <tr>
                    <th>产品信息</th>
                    <th>分类</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $row): ?>
                <tr>
                    <td>
                        <div class="is-flex is-align-items-center">
                            <?php 
                            $images = json_decode($row['images_json'] ?? '[]', true);
                            $cover = !empty($images) ? $images[0] : null;
                            ?>
                            <div style="width: 56px; height: 56px; background: <?= $cover ? 'url(' . h($cover) . ') center/cover' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>; border-radius: 10px; margin-right: 1rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <?php if (!$cover): ?>
                                <span class="icon has-text-white"><i class="fas fa-box"></i></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong><?= h($row['title']) ?></strong>
                                <p class="is-size-7 has-text-grey">/product/<?= h($row['slug']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($row['category_name'])): ?>
                        <span class="tag is-light">
                            <span class="icon is-small mr-1"><i class="fas fa-folder"></i></span>
                            <?= h($row['category_name']) ?>
                        </span>
                        <?php else: ?>
                        <span class="has-text-grey-light is-size-7">未分类</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $status_map = [
                            'active' => ['已上架', 'success', 'check-circle'],
                            'draft' => ['草稿', 'warning', 'edit'],
                            'archived' => ['已归档', 'grey', 'archive']
                        ];
                        $s = $status_map[$row['status'] ?? 'active'] ?? $status_map['active'];
                        ?>
                        <span class="tag is-<?= $s[1] ?>">
                            <span class="icon is-small mr-1"><i class="fas fa-<?= $s[2] ?>"></i></span>
                            <?= $s[0] ?>
                        </span>
                    </td>
                    <td>
                        <span class="is-size-7 has-text-grey">
                            <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
                            <?= h($row['created_at']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="buttons are-small" style="gap: 0.5rem;">
                            <a href="/product/<?= h($row['slug']) ?>" target="_blank" class="button is-light" style="border-radius: 8px;" title="预览">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                            <a href="/admin/products/edit?id=<?= (int)$row['id'] ?>" class="button is-light" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-edit"></i></span>
                                <span>编辑</span>
                            </a>
                            <a href="/admin/products/delete?id=<?= (int)$row['id'] ?>" class="button is-danger is-light" style="border-radius: 8px;" onclick="return confirm('确定要删除该产品吗？此操作不可恢复。')">
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
