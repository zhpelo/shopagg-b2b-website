<div class="level">
    <div class="level-left">
        <h1 class="title is-4">员工管理</h1>
    </div>
    <div class="level-right">
        <a href="/admin/staff/create" class="button is-link">新增员工</a>
    </div>
</div>

<div class="box admin-card">
    <div class="table-container">
        <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>显示名称</th>
                    <th>角色</th>
                    <th>权限</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= h($user['username']) ?></td>
                    <td><?= h($user['display_name']) ?></td>
                    <td>
                        <span class="tag <?= $user['role'] === 'admin' ? 'is-danger' : 'is-info' ?>">
                            <?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>
                        </span>
                    </td>
                    <td>
                        <div class="tags">
                            <?php 
                            $perms = explode(',', $user['permissions'] ?? '');
                            foreach ($perms as $p): 
                                if (empty($p)) continue;
                                $label = match($p) {
                                    'products' => '产品',
                                    'cases' => '案例',
                                    'blog' => '博客',
                                    'inbox' => '询单',
                                    'settings' => '设置',
                                    'staff' => '员工',
                                    default => $p
                                };
                            ?>
                                <span class="tag is-light is-small"><?= $label ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="is-size-7"><?= h($user['created_at']) ?></td>
                    <td>
                        <div class="buttons are-small">
                            <a href="/admin/staff/edit?id=<?= $user['id'] ?>" class="button is-link is-light">编辑</a>
                            <?php if ($user['id'] !== (int)$_SESSION['admin_user_id']): ?>
                                <a href="/admin/staff/delete?id=<?= $user['id'] ?>" class="button is-danger is-light" onclick="return confirm('确定要删除吗？')">删除</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

