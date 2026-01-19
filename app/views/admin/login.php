<div class="columns is-centered">
    <div class="column is-4">
        <div class="box admin-card">
            <h1 class="title is-4">后台登录</h1>
            <form method="post" action="/admin/login">
                <div class="field">
                    <label class="label">用户名</label>
                    <div class="control"><input class="input" name="username" required></div>
                </div>
                <div class="field">
                    <label class="label">密码</label>
                    <div class="control"><input class="input" type="password" name="password" required></div>
                </div>
                <button class="button is-link is-fullwidth" type="submit">登录</button>
            </form>
        </div>
    </div>
</div>

