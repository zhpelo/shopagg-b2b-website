<h1 class="title is-3">设置</h1>
<div class="box admin-card">
    <form method="post" action="/admin/settings">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">网站名称</label>
                    <div class="control"><input class="input" name="site_name" value="<?= h($settings['site_name'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">标语</label>
                    <div class="control"><input class="input" name="site_tagline" value="<?= h($settings['site_tagline'] ?? '') ?>"></div>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">公司简介</label>
            <div class="control">
                <textarea class="textarea" name="company_about" rows="4"><?= h($settings['company_about'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">地址</label>
                    <div class="control"><input class="input" name="company_address" value="<?= h($settings['company_address'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">邮箱</label>
                    <div class="control"><input class="input" name="company_email" value="<?= h($settings['company_email'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">电话</label>
                    <div class="control"><input class="input" name="company_phone" value="<?= h($settings['company_phone'] ?? '') ?>"></div>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">主题</label>
                    <div class="control"><input class="input" name="theme" value="<?= h($settings['theme'] ?? 'default') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">默认语言</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="default_lang">
                                <option value="en" <?= ($settings['default_lang'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                                <option value="zh" <?= ($settings['default_lang'] ?? '') === 'zh' ? 'selected' : '' ?>>中文</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">WhatsApp 账号</label>
            <div class="control"><input class="input" name="whatsapp" value="<?= h($settings['whatsapp'] ?? '') ?>"></div>
        </div>
        <button class="button is-link" type="submit">保存设置</button>
    </form>
</div>

