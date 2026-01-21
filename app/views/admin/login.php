<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        min-height: 100vh;
    }
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .login-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 25px 80px rgba(0,0,0,0.3);
        padding: 3rem;
        width: 100%;
        max-width: 420px;
        animation: slideUp 0.5s ease;
    }
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .login-logo {
        text-align: center;
        margin-bottom: 2rem;
    }
    .login-logo .icon-wrap {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin-bottom: 1rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    .login-logo h1 {
        font-size: 1.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.25rem;
    }
    .login-logo p {
        color: #94a3b8;
        font-size: 0.875rem;
    }
    .login-form .field {
        margin-bottom: 1.25rem;
    }
    .login-form .label {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    .login-form .input {
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        transition: all 0.2s;
        height: auto;
    }
    .login-form .input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .login-form .input::placeholder {
        color: #9ca3af;
    }
    .login-form .control.has-icons-left .icon {
        color: #9ca3af;
        height: 100%;
    }
    .login-form .control.has-icons-left .input {
        padding-left: 2.75rem;
    }
    .login-btn {
        width: 100%;
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 0.5rem;
    }
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    .login-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
    }
    .login-footer a {
        color: #667eea;
        font-size: 0.875rem;
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <div class="icon-wrap">
                <i class="fas fa-rocket"></i>
            </div>
            <h1>SHOPAGG Admin</h1>
            <p>欢迎回来，请登录您的账户</p>
        </div>
        
        <form method="post" action="/admin/login" class="login-form">
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
            <a href="/">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                返回网站首页
            </a>
        </div>
    </div>
</div>
