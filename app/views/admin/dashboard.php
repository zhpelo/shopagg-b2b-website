<h1 class="title is-3">仪表盘</h1>
<div class="columns is-multiline">
    <div class="column is-3">
        <div class="box admin-card">
            <p class="heading">产品</p>
            <p class="title is-4"><?= $counts['products'] ?></p>
            <a href="/admin/products" class="button is-small is-link is-light">管理</a>
        </div>
    </div>
    <div class="column is-3">
        <div class="box admin-card">
            <p class="heading">案例</p>
            <p class="title is-4"><?= $counts['cases'] ?></p>
            <a href="/admin/cases" class="button is-small is-link is-light">管理</a>
        </div>
    </div>
    <div class="column is-3">
        <div class="box admin-card">
            <p class="heading">博客</p>
            <p class="title is-4"><?= $counts['posts'] ?></p>
            <a href="/admin/posts" class="button is-small is-link is-light">管理</a>
        </div>
    </div>
    <div class="column is-3">
        <div class="box admin-card">
            <p class="heading">留言</p>
            <p class="title is-4"><?= $counts['messages'] ?></p>
            <a href="/admin/messages" class="button is-small is-link is-light">查看</a>
        </div>
    </div>
    <div class="column is-3">
        <div class="box admin-card">
            <p class="heading">询单</p>
            <p class="title is-4"><?= $counts['inquiries'] ?></p>
            <a href="/admin/inquiries" class="button is-small is-link is-light">查看</a>
        </div>
    </div>
</div>

