<!-- 页面头部 -->
<div class="page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-user-circle"></i></span>
                    个人资料
                </h1>
                <p class="mt-1 text-sm text-white/80">管理您的账户信息</p>
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-12">
    <!-- 左侧：个人信息卡片 -->
    <div class="xl:col-span-4">
        <div class="card p-8 text-center">
            <div class="size-[100px] bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full inline-flex items-center justify-center text-4xl text-white mb-6 shadow-[0_10px_30px_rgba(102,126,234,0.3)]">
                <?= strtoupper(substr($user['display_name'] ?? $user['username'], 0, 1)) ?>
            </div>
            <h3 class="mb-1 text-xl font-bold text-slate-900"><?= h($user['display_name'] ?? $user['username']) ?></h3>
            <p class="mb-3 text-sm text-slate-500">@<?= h($user['username']) ?></p>
            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-semibold <?= $user['role'] === 'admin' ? 'bg-rose-50 text-rose-700' : 'bg-cyan-50 text-cyan-700' ?>">
                <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?>"></i></span>
                <?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>
            </span>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-center">
                    <span class="mr-2 inline-flex h-4 w-4 items-center justify-center text-slate-400"><i class="far fa-calendar-alt"></i></span>
                    <span class="text-xs text-slate-500">创建于 <?= format_date($user['created_at'] ?? '') ?: '未知' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 右侧：编辑表单 -->
    <div class="xl:col-span-8">
        <form method="post" action="<?= url('/admin/profile/update') ?>">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

            <div class="card p-8">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-edit"></i></span>
                    编辑资料
                </div>

                <div class="space-y-5">
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">用户名</span>
                        <span class="relative block">
                            <i class="fas fa-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" name="username" value="<?= h($user['username']) ?>" required placeholder="输入用户名">
                        </span>
                        <span class="text-xs text-slate-500">用于登录的账户名，仅支持字母、数字和下划线</span>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">显示名称</span>
                        <span class="relative block">
                            <i class="fas fa-id-badge pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" name="display_name" value="<?= h($user['display_name']) ?>" required placeholder="输入您的显示名称">
                        </span>
                        <span class="text-xs text-slate-500">此名称将在后台界面中显示</span>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">修改密码</span>
                        <span class="relative block">
                            <i class="fas fa-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="password" name="password" placeholder="输入新密码（留空则不修改）">
                        </span>
                        <span class="text-xs text-slate-500">如需修改密码请输入新密码，否则留空</span>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">账户角色</span>
                        <span class="relative block">
                            <i class="fas fa-shield-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-700 outline-none" type="text" value="<?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>" readonly disabled>
                        </span>
                        <span class="text-xs text-slate-500">角色权限由管理员分配</span>
                    </label>
                </div>

                <hr class="my-6">

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-save"></i></span>
                        <span>保存修改</span>
                    </button>
                    <a href="<?= url('/admin') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
                        <span>返回仪表盘</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>