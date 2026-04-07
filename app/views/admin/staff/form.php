<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="icon mr-2"><i class="fas fa-<?= isset($user) ? 'user-edit' : 'user-plus' ?>"></i></span>
                    <?= isset($user) ? '编辑员工' : '新增员工' ?>
                </h1>
                <p class="mt-1 text-sm text-white/80"><?= isset($user) ? '修改员工信息和权限' : '添加新的后台用户' ?></p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= url('/admin/staff') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-12">
    <div class="animate-in delay-1 xl:col-span-8">
        <form method="post" action="<?= h(url($action)) ?>" class="modern-form">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            
            <!-- 基本信息 -->
            <div class="admin-card mb-5" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-user"></i></span>
                    基本信息
                </div>
                
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
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
                    <div>
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
                        <div class="mt-2 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
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
                            <div>
                                <label class="flex cursor-pointer items-center rounded-2xl border-2 px-4 py-3 transition <?= in_array($key, $user_perms) ? 'border-indigo-500 bg-indigo-50' : 'border-transparent bg-slate-50 hover:border-slate-200 hover:bg-slate-100' ?>">
                                    <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key, $user_perms) ? 'checked' : '' ?> class="mr-3 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
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
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($user) ? '保存修改' : '创建员工' ?></span>
                </button>
                <a href="<?= url('/admin/staff') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <span class="icon"><i class="fas fa-times"></i></span>
                    <span>取消</span>
                </a>
            </div>
        </form>
    </div>

    <!-- 右侧提示 -->
    <div class="animate-in delay-2 xl:col-span-4">
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
