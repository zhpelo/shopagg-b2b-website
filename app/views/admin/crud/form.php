<h1 class="title is-3"><?= isset($item) ? '编辑' : '新建' ?><?= h($label) ?></h1>
<div class="box admin-card">
    <form method="post" action="<?= h($action) ?>">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">标题</label>
                    <div class="control"><input class="input" name="title" value="<?= h($item['title'] ?? '') ?>" required></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">别名</label>
                    <div class="control"><input class="input" name="slug" value="<?= h($item['slug'] ?? '') ?>"></div>
                    <p class="help">留空自动生成</p>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">摘要</label>
            <div class="control">
                <textarea class="textarea" name="summary" rows="3"><?= h($item['summary'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="field">
            <label class="label">内容</label>
            <div class="control">
                <textarea id="content-input" name="content" style="display:none"><?= h($item['content'] ?? '') ?></textarea>
                <div id="quill-editor" style="height:400px; background:#fff"></div>
            </div>
        </div>
        <button class="button is-link" type="submit">保存</button>
    </form>
</div>

