<h1 class="title is-3"><?= isset($category) ? '编辑' : '新建' ?>分类</h1>
<div class="box admin-card">
    <form method="post" action="<?= h($action) ?>">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">分类名称</label>
                    <div class="control"><input class="input" name="name" value="<?= h($category['name'] ?? '') ?>" required></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">别名</label>
                    <div class="control"><input class="input" name="slug" value="<?= h($category['slug'] ?? '') ?>"></div>
                    <p class="help">留空自动生成</p>
                </div>
            </div>
        </div>
        <button class="button is-link" type="submit">保存</button>
    </form>
</div>

