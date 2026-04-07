<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <div class="icon-wrap">
                <i class="fas fa-rocket"></i>
            </div>
            <h1>SHOPAGG Admin</h1>
            <p>欢迎回来，请登录您的账户</p>
        </div>

        <form method="post" action="<?= url('/admin/login'); ?>" class="login-form">
            <div class="space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">用户名</label>
                    <div class="relative">
                        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pl-11 text-sm text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100" name="username" placeholder="请输入用户名" required>
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">密码</label>
                    <div class="relative">
                        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pl-11 text-sm text-slate-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100" type="password" name="password" placeholder="请输入密码" required>
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                </div>
            </div>
            <button class="login-btn mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:-translate-y-0.5 hover:shadow-indigo-500/40" type="submit">
                <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-sign-in-alt"></i></span>
                登录
            </button>
        </form>

        <div class="login-footer">
            <a href="<?= url('/') ?>">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
                返回网站首页
            </a>
        </div>
    </div>
</div>