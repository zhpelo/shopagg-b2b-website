<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-users"></i></span>
                    员工管理
                </h1>
                <p class="subtitle is-6">管理后台用户和权限</p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/staff/create" class="button is-white">
                <span class="icon"><i class="fas fa-user-plus"></i></span>
                <span>新增员工</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($users)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-1">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-users"></i></span>
        <p>暂无员工记录</p>
    </div>
</div>
<?php else: ?>
<!-- 员工列表 -->
<div class="modern-table animate-in delay-1">
    <div class="table-container">
        <table class="table is-fullwidth">
            <thead>
                <tr>
                    <th>员工信息</th>
                    <th>角色</th>
                    <th>访问权限</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="is-flex is-align-items-center">
                            <div style="width: 44px; height: 44px; background: <?= $user['role'] === 'admin' ? 'linear-gradient(135deg, #dc3545 0%, #e83e8c 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 1rem; flex-shrink: 0;">
                                <?= strtoupper(substr($user['display_name'] ?? $user['username'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= h($user['display_name'] ?? $user['username']) ?></strong>
                                <p class="is-size-7 has-text-grey">@<?= h($user['username']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="tag <?= $user['role'] === 'admin' ? 'is-danger' : 'is-info' ?>">
                            <span class="icon is-small mr-1"><i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?>"></i></span>
                            <?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>
                        </span>
                    </td>
                    <td>
                        <div class="tags" style="gap: 0.25rem;">
                            <?php 
                            $perms = array_filter(explode(',', $user['permissions'] ?? ''));
                            if (empty($perms) && $user['role'] !== 'admin'): ?>
                                <span class="has-text-grey-light is-size-7">无特殊权限</span>
                            <?php elseif ($user['role'] === 'admin'): ?>
                                <span class="tag is-light is-small" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">全部权限</span>
                            <?php else:
                            foreach ($perms as $p): 
                                $label = match($p) {
                                    'products' => ['产品', 'box'],
                                    'cases' => ['案例', 'briefcase'],
                                    'blog' => ['博客', 'pen-nib'],
                                    'inbox' => ['询单', 'envelope'],
                                    'settings' => ['设置', 'cog'],
                                    'staff' => ['员工', 'users'],
                                    default => [$p, 'check']
                                };
                            ?>
                                <span class="tag is-light is-small">
                                    <span class="icon is-small mr-1"><i class="fas fa-<?= $label[1] ?>"></i></span>
                                    <?= $label[0] ?>
                                </span>
                            <?php endforeach; endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="is-size-7 has-text-grey">
                            <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
                            <?= format_date($user['created_at']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="buttons are-small" style="gap: 0.5rem;">
                            <a href="/admin/staff/edit?id=<?= $user['id'] ?>" class="button is-light" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-edit"></i></span>
                                <span>编辑</span>
                            </a>
                            <?php if ($user['id'] !== (int)$_SESSION['admin_user_id']): ?>
                            <a href="/admin/staff/delete?id=<?= $user['id'] ?>" class="button is-danger is-light" style="border-radius: 8px;" onclick="return confirm('确定要删除该员工吗？此操作不可恢复。')">
                                <span class="icon"><i class="fas fa-trash-alt"></i></span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
