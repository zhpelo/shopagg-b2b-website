<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= isset($user) ? 'user-edit' : 'user-plus' ?>"></i></span>
                    <?= isset($user) ? '编辑员工' : '新增员工' ?>
                </h1>
                <p class="subtitle is-6"><?= isset($user) ? '修改员工信息和权限' : '添加新的后台用户' ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= url('/admin/staff') ?>" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-8 animate-in delay-1">
        <form method="post" action="<?= h(url($action)) ?>" class="modern-form">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            
            <!-- 基本信息 -->
            <div class="admin-card mb-5" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-user"></i></span>
                    基本信息
                </div>
                
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">用户名</label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" name="username" value="<?= h($user['username'] ?? '') ?>" required <?= isset($user) ? 'readonly style="background: #f8fafc;"' : 'placeholder="输入登录用户名"' ?>>
                                <span class="icon is-left has-text-grey-light">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                            <?php if (isset($user)): ?>
                                <p class="help has-text-grey">用户名创建后不可修改</p>
                            <?php else: ?>
                                <p class="help has-text-grey">用于登录后台系统</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">显示名称</label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" name="display_name" value="<?= h($user['display_name'] ?? '') ?>" required placeholder="输入显示名称">
                                <span class="icon is-left has-text-grey-light">
                                    <i class="fas fa-id-badge"></i>
                                </span>
                            </div>
                            <p class="help has-text-grey">在后台界面中显示的名称</p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">密码</label>
                    <div class="control has-icons-left">
                        <input class="input" type="password" name="password" <?= isset($user) ? '' : 'required' ?> placeholder="<?= isset($user) ? '留空则不修改密码' : '输入登录密码' ?>">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                    <?php if (isset($user)): ?>
                        <p class="help has-text-grey">如需修改密码请输入新密码，否则留空</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 角色和权限 -->
            <div class="admin-card mb-5" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box warning"><i class="fas fa-shield-alt"></i></span>
                    角色和权限
                </div>

                <div class="field">
                    <label class="label">账户角色</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="role">
                                <option value="staff" <?= ($user['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>普通员工</option>
                                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>管理员</option>
                            </select>
                        </div>
                    </div>
                    <p class="help has-text-grey">管理员拥有所有权限</p>
                </div>

                <div class="field">
                    <label class="label">访问权限</label>
                    <div class="control">
                        <div class="columns is-multiline" style="margin-top: 0.5rem;">
                            <?php 
                            $available_perms = [
                                'products' => ['产品管理', 'box', 'primary'],
                                'cases' => ['案例管理', 'briefcase', 'info'],
                                'blog' => ['内容管理', 'pen-nib', 'success'],
                                'inbox' => ['询单/留言', 'envelope', 'warning'],
                                'settings' => ['系统设置', 'cog', 'danger'],
                                'staff' => ['员工管理', 'users', 'dark']
                            ];
                            $user_perms = $user['permissions'] ?? [];
                            foreach ($available_perms as $key => $info): 
                            ?>
                            <div class="column is-4">
                                <label class="checkbox" style="display: flex; align-items: center; padding: 0.75rem 1rem; background: <?= in_array($key, $user_perms) ? 'rgba(102, 126, 234, 0.1)' : '#f8fafc' ?>; border-radius: 10px; cursor: pointer; transition: all 0.2s; border: 2px solid <?= in_array($key, $user_perms) ? '#667eea' : 'transparent' ?>;">
                                    <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key, $user_perms) ? 'checked' : '' ?> style="margin-right: 0.75rem;">
                                    <span class="icon is-small mr-2 has-text-<?= $info[2] ?>"><i class="fas fa-<?= $info[1] ?>"></i></span>
                                    <?= $info[0] ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <p class="help has-text-grey mt-3">选择该员工可以访问的后台功能模块（管理员角色自动拥有所有权限）</p>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="buttons">
                <button type="submit" class="button is-primary is-medium">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($user) ? '保存修改' : '创建员工' ?></span>
                </button>
                <a href="<?= url('/admin/staff') ?>" class="button is-light is-medium">
                    <span class="icon"><i class="fas fa-times"></i></span>
                    <span>取消</span>
                </a>
            </div>
        </form>
    </div>

    <!-- 右侧提示 -->
    <div class="column is-4 animate-in delay-2">
        <div class="admin-card" style="padding: 1.5rem;">
            <div class="section-title" style="font-size: 1rem;">
                <span class="icon-box info"><i class="fas fa-info"></i></span>
                权限说明
            </div>
            <div class="content is-size-7">
                <ul style="margin-left: 0;">
                    <li><strong>产品管理</strong>：添加、编辑、删除产品和分类</li>
                    <li><strong>案例管理</strong>：添加、编辑、删除客户案例</li>
                    <li><strong>内容管理</strong>：发布和管理博客文章</li>
                    <li><strong>询单/留言</strong>：查看和处理客户询单和留言</li>
                    <li><strong>系统设置</strong>：修改网站配置和SEO设置</li>
                    <li><strong>员工管理</strong>：管理后台用户账户</li>
                </ul>
            </div>
        </div>
    </div>
</div>
