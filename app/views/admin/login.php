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
            <div class="field">
                <label class="label">用户名</label>
                <div class="control has-icons-left">
                    <input class="input" name="username" placeholder="请输入用户名" required>
                    <span class="icon is-left">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
            </div>
            <div class="field">
                <label class="label">密码</label>
                <div class="control has-icons-left">
                    <input class="input" type="password" name="password" placeholder="请输入密码" required>
                    <span class="icon is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
            </div>
            <button class="login-btn" type="submit">
                <span class="icon mr-2"><i class="fas fa-sign-in-alt"></i></span>
                登录
            </button>
        </form>
        
        <div class="login-footer">
            <a href="<?= url('/') ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                返回网站首页
            </a>
        </div>
    </div>
</div>
