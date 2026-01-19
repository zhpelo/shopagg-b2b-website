<div class="level">
    <div class="level-left">
        <h1 class="title is-4"><?= isset($product) ? '编辑' : '添加' ?>产品</h1>
    </div>
</div>

<form method="post" action="<?= h($action) ?>" enctype="multipart/form-data" id="product-form">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    
        <div class="columns">
        <!-- 左侧栏 -->
        <div class="column is-8">
            <!-- 标题和描述 -->
            <div class="card mb-5 admin-card">
                <div class="card-content">
                <div class="field">
                    <label class="label">标题</label>
                        <div class="control">
                            <input class="input" name="title" value="<?= h($product['title'] ?? '') ?>" placeholder="短袖 T 恤" required>
            </div>
        </div>
        <div class="field">
                        <label class="label">描述</label>
            <div class="control">
                            <textarea id="content-input" name="content" style="display:none"><?= h($product['content'] ?? '') ?></textarea>
                            <div id="quill-editor" style="height:300px; background:#fff"></div>
            </div>
        </div>
                </div>
            </div>

            <!-- 媒体文件 -->
            <div class="card mb-5 admin-card">
                <div class="card-header">
                    <p class="card-header-title">媒体文件</p>
                </div>
                <div class="card-content">
                    <!-- 状态 1：已选图片网格 (图片 2 UI) -->
                    <?php $images = $product['images'] ?? []; ?>
                    <div id="media-grid-wrap" class="<?= empty($images) ? 'is-hidden' : '' ?>">
                        <div id="media-container" class="mb-4">
                            <?php foreach ($images as $img): ?>
                                <div class="media-item" data-url="<?= h($img) ?>">
                                    <img src="<?= h($img) ?>">
                                    <input type="hidden" name="images[]" value="<?= h($img) ?>">
                                    <button type="button" class="delete is-small remove-media"></button>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- 网格末尾的添加按钮 -->
                            <div class="media-add-btn open-media-library-btn" id="grid-add-btn">
                                <span class="is-size-4 has-text-grey-light">+</span>
        </div>
            </div>
        </div>

                    <!-- 状态 2：空占位区域 (图片 1 UI) -->
                    <div id="media-empty-placeholder" class="media-placeholder <?= !empty($images) ? 'is-hidden' : '' ?>">
                        <div class="buttons">
                            <div class="file is-centered">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="new_images[]" multiple accept="image/*" id="file-upload-input">
                                    <span class="file-cta button is-white border" style="height: auto; padding: 8px 20px;">
                                        <span class="file-label has-text-weight-bold">上传新文件</span>
                                    </span>
                                </label>
                            </div>
                            <button type="button" class="button is-white border open-media-library-btn has-text-weight-bold ml-2" style="height: auto; padding: 8px 20px;">选择现有文件</button>
                        </div>
                        <p class="is-size-7 has-text-grey mt-2">支持图片、视频或 3D 模型</p>
                    </div>
                </div>
        </div>

          
            <!-- 价格 -->
            <div class="card mb-5 admin-card">
                <div class="card-header"><p class="card-header-title">价格 (阶梯价格)</p></div>
                <div class="card-content">
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
                    <button type="button" class="button is-link is-light is-small" id="add-price-tier">新增阶梯价格</button>
                </div>
            </div>
        </div>

        <!-- 右侧栏 -->
        <div class="column is-4">
            <!-- 状态 -->
            <div class="card mb-5 admin-card">
                <div class="card-header"><p class="card-header-title">状态</p></div>
                <div class="card-content">
                    <div class="field">
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>有效</option>
                                    <option value="draft" <?= ($product['status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option>
                                    <option value="archived" <?= ($product['status'] ?? '') === 'archived' ? 'selected' : '' ?>>归档</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

  <!-- 类别和摘要 -->
  <div class="card mb-5 admin-card">
                <div class="card-content">
                    <div class="field">
                        <label class="label">别名 (Slug)</label>
                        <div class="control">
                            <input class="input" name="slug" value="<?= h($product['slug'] ?? '') ?>" placeholder="t-shirt-slug">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">产品摘要</label>
                        <div class="control">
                            <textarea class="textarea" name="summary" rows="2"><?= h($product['summary'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">产品分类</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="category_id">
                                    <option value="0">选择产品类别</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int)$cat['id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                                            <?= h($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 产品组织 -->
            <div class="card mb-5 admin-card">
                <div class="card-header"><p class="card-header-title">产品组织</p></div>
                <div class="card-content">
                    <div class="field">
                        <label class="label is-size-7">类型</label>
                        <div class="control">
                            <input class="input" name="product_type" value="<?= h($product['product_type'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label is-size-7">厂商</label>
                        <div class="control">
                            <input class="input" name="vendor" value="<?= h($product['vendor'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label is-size-7">标签</label>
                        <div class="control">
                            <input class="input" name="tags" value="<?= h($product['tags'] ?? '') ?>" placeholder="用逗号分隔">
                        </div>
                    </div>
                </div>
            </div>

            <div class="buttons mt-5">
                <button type="submit" class="button is-link is-fullwidth is-medium">保存产品</button>
            </div>
        </div>
    </div>
    </form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. 初始化拖拽排序
    const mediaContainer = document.getElementById('media-container');
    const gridWrap = document.getElementById('media-grid-wrap');
    const emptyPlaceholder = document.getElementById('media-empty-placeholder');
    const gridAddBtn = document.getElementById('grid-add-btn');

    if (mediaContainer) {
        new Sortable(mediaContainer, {
            animation: 150,
            draggable: ".media-item",
            ghostClass: "sortable-ghost",
            onEnd: checkMediaState 
        });
    }

    function checkMediaState() {
        const hasImages = mediaContainer.querySelectorAll('.media-item').length > 0;
        if (hasImages) {
            gridWrap.classList.remove('is-hidden');
            emptyPlaceholder.classList.add('is-hidden');
        } else {
            gridWrap.classList.add('is-hidden');
            emptyPlaceholder.classList.remove('is-hidden');
        }
    }

    // 2. 调用全局媒体库
    const openBtns = document.querySelectorAll('.open-media-library-btn');
    openBtns.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        openMediaLibrary(function(urls) {
            // urls 是数组，因为开了多选
            urls.forEach(url => addMediaItem(url));
            checkMediaState();
        }, true); // true 表示多选模式
    }));

    function addMediaItem(url) {
        if (mediaContainer.querySelector(`input[value="${url}"]`)) return;
        const div = document.createElement('div');
        div.className = 'media-item';
        div.dataset.url = url;
        div.innerHTML = `
            <img src="${url}">
            <input type="hidden" name="images[]" value="${url}">
            <button type="button" class="delete is-small remove-media"></button>
        `;
        mediaContainer.insertBefore(div, gridAddBtn);
    }

    mediaContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-media')) {
            e.target.closest('.media-item').remove();
            checkMediaState();
        }
    });

    // 3. 本地上传预览 (保持原有逻辑，但上传后刷新媒体库)
    const fileInput = document.getElementById('file-upload-input');
    fileInput.addEventListener('change', async function() {
        if (this.files.length > 0) {
            const files = Array.from(this.files);
            // 立即显示预览
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement('div');
                    div.className = 'media-item';
                    div.style.opacity = '0.6';
                    div.innerHTML = `<img src="${e.target.result}"><span class="tag is-dark" style="position:absolute; bottom:0; width:100%; font-size:10px; border-radius:0;">上传中...</span>`;
                    mediaContainer.insertBefore(div, gridAddBtn);
                };
                reader.readAsDataURL(file);
            });
            checkMediaState();

            // 执行实际上传
            const uploadTasks = files.map(file => {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('csrf', '<?= csrf_token() ?>');
                return fetch('/admin/upload-image', { method: 'POST', body: formData }).then(r => r.json());
            });

            try {
                const results = await Promise.all(uploadTasks);
                // 上传完成后，替换预览为正式项目
                mediaContainer.querySelectorAll('.media-item[style*="opacity: 0.6"]').forEach(el => el.remove());
                results.forEach(res => {
                    if (res.url) addMediaItem(res.url);
                });
                checkMediaState();
            } catch (err) { alert('上传失败'); }
            this.value = '';
        }
    });
});
</script>
