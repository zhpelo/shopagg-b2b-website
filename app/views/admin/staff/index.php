<!-- 页面头部 -->
<div class="page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-users"></i></span>
                    员工管理
                </h1>
                <p class="mt-1 text-sm text-white/80">管理后台用户和权限</p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= url('/admin/staff/create') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-user-plus"></i></span>
                <span>新增员工</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($users)): ?>
    <!-- 空状态 -->
    <div class="card">
        <div class="empty-state">
            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-users"></i></span>
            <p>暂无员工记录</p>
        </div>
    </div>
<?php else: ?>
    <!-- 员工列表 -->
    <div class="admin-table">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-slate-700">
                <thead class="bg-gradient-to-b from-white to-slate-50">
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
                                <div class="flex items-center">
                                    <div class="size-11 rounded-[10px] flex items-center justify-center text-white font-semibold mr-4 shrink-0" style="background: <?= $user['role'] === 'admin' ? 'linear-gradient(135deg, #dc3545 0%, #e83e8c 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>">
                                        <?= strtoupper(substr($user['display_name'] ?? $user['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= h($user['display_name'] ?? $user['username']) ?></strong>
                                        <p class="text-xs text-slate-500">@<?= h($user['username']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $user['role'] === 'admin' ? 'bg-rose-50 text-rose-700' : 'bg-cyan-50 text-cyan-700' ?>">
                                    <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?>"></i></span>
                                    <?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <?php
                                    $perms = array_filter(explode(',', $user['permissions'] ?? ''));
                                    if (empty($perms) && $user['role'] !== 'admin'): ?>
                                        <span class="text-xs text-slate-400">无特殊权限</span>
                                    <?php elseif ($user['role'] === 'admin'): ?>
                                        <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600">全部权限</span>
                                        <?php else:
                                        foreach ($perms as $p):
                                            $label = match ($p) {
                                                'products' => ['产品', 'box'],
                                                'cases' => ['案例', 'briefcase'],
                                                'blog' => ['博客', 'pen-nib'],
                                                'inbox' => ['询单', 'envelope'],
                                                'settings' => ['设置', 'cog'],
                                                'staff' => ['员工', 'users'],
                                                default => [$p, 'check']
                                            };
                                        ?>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                                <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="fas fa-<?= $label[1] ?>"></i></span>
                                                <?= $label[0] ?>
                                            </span>
                                    <?php endforeach;
                                    endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-slate-500">
                                    <span class="inline-flex h-4 w-4 items-center justify-center"><i class="far fa-calendar-alt"></i></span>
                                    <?= format_date($user['created_at']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= url('/admin/staff/edit?id=' . (int)$user['id']) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-edit"></i></span>
                                        <span>编辑</span>
                                    </a>
                                    <?php if ($user['id'] !== (int)$_SESSION['admin_user_id']): ?>
                                        <a href="<?= url('/admin/staff/delete?id=' . (int)$user['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" data-confirm-message="确定要删除该员工吗？此操作不可恢复。">
                                            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash-alt"></i></span>
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