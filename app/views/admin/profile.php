<div class="level">
    <div class="level-left">
        <h1 class="title is-4">个人资料</h1>
    </div>
</div>

<div class="columns">
    <div class="column is-6">
        <form method="post" action="/admin/profile/update">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <div class="box admin-card">
                <div class="field">
                    <label class="label">用户名</label>
                    <div class="control">
                        <input class="input" type="text" value="<?= h($user['username']) ?>" readonly disabled>
                    </div>
                </div>

                <div class="field">
                    <label class="label">显示名称</label>
                    <div class="control">
                        <input class="input" type="text" name="display_name" value="<?= h($user['display_name']) ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">修改密码</label>
                    <div class="control">
                        <input class="input" type="password" name="password" placeholder="留空则不修改">
                    </div>
                </div>

                <div class="field">
                    <label class="label">角色</label>
                    <div class="control">
                        <input class="input" type="text" value="<?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>" readonly disabled>
                    </div>
                </div>

                <hr>
                <div class="buttons">
                    <button type="submit" class="button is-link">保存修改</button>
                </div>
            </div>
        </form>
    </div>
</div>

