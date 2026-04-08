<!-- 页面头部 -->
<div class="page-header">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-4">
 <div>
 <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
 <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-<?= isset($user) ? 'user-edit' : 'user-plus' ?>"></i></span>
 <?= isset($user) ? '编辑员工' : '新增员工' ?>
 </h1>
 <p class="mt-1 text-sm text-white/80"><?= isset($user) ? '修改员工信息和权限' : '添加新的后台用户' ?></p>
 </div>
 </div>
 <div class="header-actions flex items-center gap-3">
 <a href="<?= url('/admin/staff') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
 <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
 <span>返回列表</span>
 </a>
 </div>
 </div>
</div>

<div class="grid gap-6 xl:grid-cols-12">
 <div class="xl:col-span-8">
 <form method="post" action="<?= h(url($action)) ?>">
 <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
 
 <!-- 基本信息 -->
 <div class="card mb-5 p-8">
 <div class="section-title">
 <span class="icon-box primary"><i class="fas fa-user"></i></span>
 基本信息
 </div>

 <div class="grid gap-4 md:grid-cols-2">
 <label class="block space-y-2">
 <span class="text-sm font-medium text-slate-700">用户名</span>
 <span class="relative block">
 <i class="fas fa-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
 <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" name="username" value="<?= h($user['username'] ?? '') ?>" required <?= isset($user) ? 'readonly class="!bg-slate-50"' : 'placeholder="输入登录用户名"' ?>>
 </span>
 <?php if (isset($user)): ?>
 <span class="text-xs text-slate-500">用户名创建后不可修改</span>
 <?php else: ?>
 <span class="text-xs text-slate-500">用于登录后台系统</span>
 <?php endif; ?>
 </label>

 <label class="block space-y-2">
 <span class="text-sm font-medium text-slate-700">显示名称</span>
 <span class="relative block">
 <i class="fas fa-id-badge pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
 <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="text" name="display_name" value="<?= h($user['display_name'] ?? '') ?>" required placeholder="输入显示名称">
 </span>
 <span class="text-xs text-slate-500">在后台界面中显示的名称</span>
 </label>
 </div>

 <label class="mt-4 block space-y-2">
 <span class="text-sm font-medium text-slate-700">密码</span>
 <span class="relative block">
 <i class="fas fa-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
 <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="password" name="password" <?= isset($user) ? '' : 'required' ?> placeholder="<?= isset($user) ? '留空则不修改密码' : '输入登录密码' ?>">
 </span>
 <?php if (isset($user)): ?>
 <span class="text-xs text-slate-500">如需修改密码请输入新密码，否则留空</span>
 <?php endif; ?>
 </label>
 </div>

 <!-- 角色和权限 -->
 <div class="card mb-5 p-8">
 <div class="section-title">
 <span class="icon-box warning"><i class="fas fa-shield-alt"></i></span>
 角色和权限
 </div>

 <div class="space-y-5">
 <label class="block space-y-2">
 <span class="text-sm font-medium text-slate-700">账户角色</span>
 <select name="role" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
 <option value="staff" <?= ($user['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>普通员工</option>
 <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>管理员</option>
 </select>
 <span class="text-xs text-slate-500">管理员拥有所有权限</span>
 </label>

 <div class="space-y-2">
 <div class="text-sm font-medium text-slate-700">访问权限</div>
 <div class="mt-2 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
 <?php 
 $available_perms = [
 'products' => ['产品管理', 'box', 'text-indigo-600'],
 'cases' => ['案例管理', 'briefcase', 'text-sky-600'],
 'blog' => ['内容管理', 'pen-nib', 'text-emerald-600'],
 'inbox' => ['询单/留言', 'envelope', 'text-amber-600'],
 'settings' => ['系统设置', 'cog', 'text-rose-600'],
 'staff' => ['员工管理', 'users', 'text-slate-700']
 ];
 $user_perms = $user['permissions'] ?? [];
 foreach ($available_perms as $key => $info): 
 ?>
 <div>
 <label class="flex cursor-pointer items-center rounded-2xl border-2 px-4 py-3 transition <?= in_array($key, $user_perms) ? 'border-indigo-500 bg-indigo-50' : 'border-transparent bg-slate-50 hover:border-slate-200 hover:bg-slate-100' ?>">
 <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key, $user_perms) ? 'checked' : '' ?> class="mr-3 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
 <span class="inline-flex h-4 w-4 items-center justify-center mr-2 <?= $info[2] ?>"><i class="fas fa-<?= $info[1] ?>"></i></span>
 <?= $info[0] ?>
 </label>
 </div>
 <?php endforeach; ?>
 </div>
 <p class="mt-3 text-xs text-slate-500">选择该员工可以访问的后台功能模块（管理员角色自动拥有所有权限）</p>
 </div>
 </div>
 </div>

 <!-- 提交按钮 -->
 <div class="flex flex-wrap gap-3">
 <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
 <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-save"></i></span>
 <span><?= isset($user) ? '保存修改' : '创建员工' ?></span>
 </button>
 <a href="<?= url('/admin/staff') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
 <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-times"></i></span>
 <span>取消</span>
 </a>
 </div>
 </form>
 </div>

 <!-- 右侧提示 -->
 <div class="xl:col-span-4">
 <div class="card p-6">
 <div class="section-title text-base">
 <span class="icon-box info"><i class="fas fa-info"></i></span>
 权限说明
 </div>
 <div class="text-xs leading-6 text-slate-500">
 <ul class="list-disc space-y-1 pl-5">
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
