<div class="level">
    <div class="level-left">
        <h1 class="title is-4"><?= isset($user) ? '编辑' : '新增' ?>员工</h1>
    </div>
</div>

<div class="columns">
    <div class="column is-6">
        <form method="post" action="<?= h($action) ?>">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <div class="box admin-card">
                <div class="field">
                    <label class="label">用户名</label>
                    <div class="control">
                        <input class="input" type="text" name="username" value="<?= h($user['username'] ?? '') ?>" required <?= isset($user) ? 'readonly' : '' ?>>
                    </div>
                    <?php if (isset($user)): ?>
                        <p class="help">用户名不可修改</p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label class="label">显示名称</label>
                    <div class="control">
                        <input class="input" type="text" name="display_name" value="<?= h($user['display_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">密码</label>
                    <div class="control">
                        <input class="input" type="password" name="password" <?= isset($user) ? '' : 'required' ?>>
                    </div>
                    <?php if (isset($user)): ?>
                        <p class="help">留空则不修改密码</p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label class="label">角色</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="role">
                                <option value="staff" <?= ($user['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>普通员工</option>
                                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>管理员</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">访问权限</label>
                    <div class="control">
                        <?php 
                        $available_perms = [
                            'products' => '产品管理',
                            'cases' => '案例管理',
                            'blog' => '内容管理',
                            'inbox' => '询单/留言',
                            'settings' => '系统设置',
                            'staff' => '员工管理'
                        ];
                        $user_perms = $user['permissions'] ?? [];
                        foreach ($available_perms as $key => $label): 
                        ?>
                            <label class="checkbox mr-4">
                                <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key, $user_perms) ? 'checked' : '' ?>>
                                <?= $label ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="help">管理员角色默认拥有所有权限</p>
                </div>

                <hr>
                <div class="buttons">
                    <button type="submit" class="button is-link">保存</button>
                    <a href="/admin/staff" class="button is-light">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>

