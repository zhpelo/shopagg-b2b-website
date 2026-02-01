<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #00d1b2 0%, #48c774 100%); box-shadow: 0 10px 40px rgba(0, 209, 178, 0.3);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-newspaper"></i></span>
                    博客管理
                </h1>
                <p class="subtitle is-6">共有 <?= count($items) ?> 篇文章</p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= url('/admin/post-categories') ?>" class="button is-white is-outlined mr-2">
                <span class="icon"><i class="fas fa-folder"></i></span>
                <span>管理分类</span>
            </a>
            <a href="<?= url('/admin/posts/create') ?>" class="button is-white">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>新建文章</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($items)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-1">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-file-alt"></i></span>
        <p>暂无博客文章</p>
        <a href="<?= url('/admin/posts/create') ?>" class="button is-success mt-4">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>创建第一篇文章</span>
        </a>
    </div>
</div>
<?php else: ?>
<!-- 文章列表 -->
<div class="modern-table animate-in delay-1">
    <div class="table-container">
        <table class="table is-fullwidth">
            <thead>
                <tr>
                    <th>文章信息</th>
                    <th>分类</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                <tr>
                    <td>
                        <div class="is-flex is-align-items-center">
                            <?php $cover = $row['cover'] ?? ''; ?>
                            <div style="width: 56px; height: 56px; background: <?= $cover ? 'url(' . asset_url($cover) . ') center/cover' : 'linear-gradient(135deg, #00d1b2 0%, #48c774 100%)' ?>; border-radius: 10px; margin-right: 1rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <?php if (!$cover): ?>
                                <span class="icon has-text-white"><i class="fas fa-file-alt"></i></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong><?= h($row['title']) ?></strong>
                                <p class="is-size-7 has-text-grey">/blog/<?= h($row['slug']) ?></p>
                                <?php if (!empty($row['summary'])): ?>
                                <p class="is-size-7 has-text-grey-light mt-1" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= h(mb_substr($row['summary'], 0, 60)) ?><?= mb_strlen($row['summary']) > 60 ? '...' : '' ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($row['category_name'])): ?>
                        <span class="tag is-success is-light" style="border-radius: 6px;">
                            <span class="icon is-small"><i class="fas fa-folder"></i></span>
                            <span><?= h($row['category_name']) ?></span>
                        </span>
                        <?php else: ?>
                        <span class="has-text-grey-light is-size-7">未分类</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $status = $row['status'] ?? 'active';
                        $status_map = [
                            'draft' => ['草稿', 'warning', 'edit'],
                            'active' => ['已发布', 'success', 'check-circle'],
                            'inactive' => ['已下架', 'grey', 'arrow-down']
                        ];
                        $s = $status_map[$status] ?? $status_map['active'];
                        ?>
                        <span class="tag is-<?= $s[1] ?> is-light" style="border-radius: 20px; padding: 0 12px;">
                            <span class="icon is-small"><i class="fas fa-<?= $s[2] ?>"></i></span>
                            <span><?= $s[0] ?></span>
                        </span>
                    </td>
                    <td>
                        <span class="is-size-7 has-text-grey">
                            <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
                            <?= format_date($row['created_at']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="buttons are-small" style="gap: 0.5rem;">
                            <a href="<?= url('/blog/' . h($row['slug'])) ?>" target="_blank" class="button is-info is-light" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                            <a href="<?= url('/admin/posts/edit?id=' . (int)$row['id']) ?>" class="button is-light" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-edit"></i></span>
                                <span>编辑</span>
                            </a>
                            <a href="<?= url('/admin/posts/delete?id=' . (int)$row['id']) ?>" class="button is-danger is-light" style="border-radius: 8px;" onclick="return confirm('确定要删除该文章吗？此操作不可恢复。')">
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

