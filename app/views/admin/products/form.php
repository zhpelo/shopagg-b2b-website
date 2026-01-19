<h1 class="title is-3"><?= isset($product) ? '编辑' : '新建' ?>产品</h1>
<div class="box admin-card">
    <form method="post" action="<?= h($action) ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">标题</label>
                    <div class="control"><input class="input" name="title" value="<?= h($product['title'] ?? '') ?>" required></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">别名</label>
                    <div class="control"><input class="input" name="slug" value="<?= h($product['slug'] ?? '') ?>"></div>
                    <p class="help">留空自动生成</p>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">摘要</label>
            <div class="control">
                <textarea class="textarea" name="summary" rows="3"><?= h($product['summary'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="field">
            <label class="label">产品分类</label>
            <div class="control">
                <div class="select is-fullwidth">
                    <select name="category_id">
                        <option value="0">未分类</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= h($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">内容</label>
            <div class="control">
                <textarea id="content-input" name="content" style="display:none"><?= h($product['content'] ?? '') ?></textarea>
                <div id="quill-editor" style="height:400px; background:#fff"></div>
            </div>
        </div>

        <div class="field">
            <label class="label">产品图片（1-6 张）</label>
            <div class="control"><input class="input" type="file" name="images[]" accept="image/*" multiple></div>
            <div class="columns is-multiline" id="product-image-preview" style="margin-top:8px"></div>
            <?php if (!empty($images)): ?>
                <p class="help">已上传图片，勾选可删除：</p>
                <div class="columns is-multiline">
                    <?php foreach ($images as $img): ?>
                        <div class="column is-3">
                            <div class="box">
                                <figure class="image"><img src="<?= h($img['url']) ?>"></figure>
                                <label class="checkbox">
                                    <input type="checkbox" name="remove_images[]" value="<?= (int)$img['id'] ?>"> 删除
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label class="label">阶梯价格</label>
            <input type="hidden" name="price_tiers_enabled" value="1">
            <div id="price-tier-wrap">
                <?php 
                $tierData = !empty($prices) ? $prices : [['min_qty'=>'', 'max_qty'=>'', 'price'=>'', 'currency'=>'USD']];
                foreach ($tierData as $tier): 
                ?>
                <div class="columns price-tier-row">
                    <div class="column"><div class="field"><label class="label is-size-7">最小数量</label><div class="control"><input class="input" name="price_min[]" type="number" min="1" value="<?= h((string)$tier['min_qty']) ?>" required></div></div></div>
                    <div class="column"><div class="field"><label class="label is-size-7">最大数量</label><div class="control"><input class="input" name="price_max[]" type="number" min="1" placeholder="可空" value="<?= h((string)($tier['max_qty'] ?? '')) ?>"></div></div></div>
                    <div class="column"><div class="field"><label class="label is-size-7">单价</label><div class="control"><input class="input" name="price_value[]" type="number" min="0" step="0.01" value="<?= h((string)($tier['price'] ?? '')) ?>" required></div></div></div>
                    <div class="column"><div class="field"><label class="label is-size-7">货币</label><div class="control"><input class="input" name="price_currency[]" value="<?= h($tier['currency'] ?? 'USD') ?>" required></div></div></div>
                    <div class="column is-narrow"><div class="field"><label class="label is-size-7">操作</label><div class="control"><button type="button" class="button is-light remove-price-tier">删除</button></div></div></div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button is-link is-light" id="add-price-tier">新增阶梯价格</button>
        </div>

        <button class="button is-link" type="submit">保存产品</button>
    </form>
</div>

