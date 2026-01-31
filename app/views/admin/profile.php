<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-user-circle"></i></span>
                    个人资料
                </h1>
                <p class="subtitle is-6">管理您的账户信息</p>
            </div>
        </div>
    </div>
</div>

<div class="columns">
    <!-- 左侧：个人信息卡片 -->
    <div class="column is-4 animate-in delay-1">
        <div class="admin-card" style="padding: 2rem; text-align: center;">
            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; margin-bottom: 1.5rem; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                <?= strtoupper(substr($user['display_name'] ?? $user['username'], 0, 1)) ?>
            </div>
            <h3 class="title is-5 mb-1"><?= h($user['display_name'] ?? $user['username']) ?></h3>
            <p class="has-text-grey mb-3">@<?= h($user['username']) ?></p>
            <span class="tag <?= $user['role'] === 'admin' ? 'is-danger' : 'is-info' ?>" style="font-size: 0.875rem;">
                <span class="icon is-small mr-1"><i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?>"></i></span>
                <?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>
            </span>
            
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
                <div class="is-flex is-justify-content-center is-align-items-center">
                    <span class="icon has-text-grey-light mr-2"><i class="far fa-calendar-alt"></i></span>
                    <span class="is-size-7 has-text-grey">创建于 <?= format_date($user['created_at'] ?? '') ?: '未知' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 右侧：编辑表单 -->
    <div class="column is-8 animate-in delay-2">
        <form method="post" action="<?= url('/admin/profile/update') ?>" class="modern-form">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            
            <div class="admin-card" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-edit"></i></span>
                    编辑资料
                </div>
                
                <div class="field">
                    <label class="label">用户名</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" value="<?= h($user['username']) ?>" readonly disabled style="background: #f8fafc;">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">用户名创建后不可修改</p>
                </div>

                <div class="field">
                    <label class="label">显示名称</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="display_name" value="<?= h($user['display_name']) ?>" required placeholder="输入您的显示名称">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-id-badge"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">此名称将在后台界面中显示</p>
                </div>

                <div class="field">
                    <label class="label">修改密码</label>
                    <div class="control has-icons-left">
                        <input class="input" type="password" name="password" placeholder="输入新密码（留空则不修改）">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">如需修改密码请输入新密码，否则留空</p>
                </div>

                <div class="field">
                    <label class="label">账户角色</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" value="<?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>" readonly disabled style="background: #f8fafc;">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">角色权限由管理员分配</p>
                </div>

                <hr style="margin: 1.5rem 0;">
                
                <div class="buttons">
                    <button type="submit" class="button is-primary">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>保存修改</span>
                    </button>
                    <a href="/admin" class="button is-light">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>返回仪表盘</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
