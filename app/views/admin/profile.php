<!-- 页面头部 -->
<div class="page-header">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-4">
 <div>
 <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
 <span class="icon mr-2"><i class="fas fa-user-circle"></i></span>
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
 <div class="admin-card" style="padding: 2rem; text-align: center;">
 <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; margin-bottom: 1.5rem; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
 <?= strtoupper(substr($user['display_name'] ?? $user['username'], 0, 1)) ?>
 </div>
 <h3 class="mb-1 text-xl font-bold text-slate-900"><?= h($user['display_name'] ?? $user['username']) ?></h3>
 <p class="mb-3 text-sm text-slate-500">@<?= h($user['username']) ?></p>
 <span class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-semibold <?= $user['role'] === 'admin' ? 'bg-rose-50 text-rose-700' : 'bg-cyan-50 text-cyan-700' ?>">
 <span class="icon is-small mr-1"><i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?>"></i></span>
 <?= $user['role'] === 'admin' ? '管理员' : '普通员工' ?>
 </span>
 
 <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
 <div class="flex items-center justify-center">
 <span class="icon has-text-grey-light mr-2"><i class="far fa-calendar-alt"></i></span>
 <span class="text-xs text-slate-500">创建于 <?= format_date($user['created_at'] ?? '') ?: '未知' ?></span>
 </div>
 </div>
 </div>
 </div>

 <!-- 右侧：编辑表单 -->
 <div class="xl:col-span-8">
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
 
 <div class="flex flex-wrap gap-3">
 <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
 <span class="icon"><i class="fas fa-save"></i></span>
 <span>保存修改</span>
 </button>
 <a href="<?= url('/admin') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
 <span class="icon"><i class="fas fa-arrow-left"></i></span>
 <span>返回仪表盘</span>
 </a>
 </div>
 </div>
 </form>
 </div>
</div>
