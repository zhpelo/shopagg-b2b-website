<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= isset($product) ? 'edit' : 'plus' ?>"></i></span>
                    <?= isset($product) ? '编辑产品' : '添加产品' ?>
                </h1>
                <p class="subtitle is-6"><?= isset($product) ? '修改产品信息' : '创建新的产品' ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="<?= url('/admin/products') ?>" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= h(url($action)) ?>" enctype="multipart/form-data" id="product-form" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    
    <div class="columns">
        <!-- 左侧栏 -->
        <div class="column is-8">
            <!-- 标题和描述 -->
            <div class="admin-card mb-5 animate-in delay-1" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-info-circle"></i></span>
                    基本信息
                </div>
                <div class="field">
                    <label class="label">产品标题</label>
                    <div class="control">
                        <input class="input" name="title" value="<?= h($product['title'] ?? '') ?>" placeholder="输入产品标题" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">产品描述</label>
                    <div class="control">
                        <textarea id="content-input" name="content" style="display:none"><?= h(process_rich_text($product['content'] ?? '')) ?></textarea>
                        <div id="quill-editor" style="height:300px; background:#fff; border-radius: 0 0 10px 10px;"></div>
                    </div>
                </div>
            </div>

            <!-- 媒体文件 -->
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-images"></i></span>
                    媒体文件
                </div>
                
                <?php $images = $product['images'] ?? []; ?>
                <div id="media-grid-wrap" class="<?= empty($images) ? 'is-hidden' : '' ?>">
                    <div id="media-container" class="mb-4">
                        <?php foreach ($images as $img): ?>
                            <div class="media-item" data-url="<?= h($img) ?>">
                                <img src="<?= h(url($img)) ?>">
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

                <!-- 空占位区域 -->
                <div id="media-empty-placeholder" class="media-placeholder <?= !empty($images) ? 'is-hidden' : '' ?>">
                    <div class="buttons">
                        <div class="file is-centered">
                            <label class="file-label">
                                <input class="file-input" type="file" name="new_images[]" multiple accept="image/*" id="file-upload-input">
                                <span class="file-cta button is-light" style="padding: 10px 20px; border-radius: 10px;background-color: #fff;">
                                    <span class="file-icon"><i class="fas fa-upload"></i></span>
                                    <span class="file-label has-text-weight-semibold">上传新文件</span>
                                </span>
                            </label>
                        </div>
                        <button type="button" class="button is-light open-media-library-btn has-text-weight-semibold ml-2" style="padding: 10px 20px; border-radius: 10px; background-color: #fff;">
                            <span class="icon"><i class="fas fa-photo-video"></i></span>
                            <span>选择现有文件</span>
                        </button>
                    </div>
                    <p class="is-size-7 has-text-grey mt-3">支持 JPG、PNG、GIF、WebP 格式图片</p>
                </div>
            </div>

            <!-- 横幅图片 -->
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-image"></i></span>
                    横幅图片
                </div>
                <p class="is-size-7 has-text-grey mb-4">选择一张图片作为商品页面的横幅展示（可选）</p>
                
                <div id="banner-preview" class="mb-4" style="<?= empty($product['banner_image']) ? 'display: none;' : '' ?>">
                    <div class="banner-image-container" style="position: relative; display: inline-block;">
                        <img id="banner-image" src="<?= h(url($product['banner_image'] ?? '')) ?>" alt="横幅图片" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        <button type="button" class="delete is-small" id="remove-banner" style="position: absolute; top: 8px; right: 8px;"></button>
                    </div>
                </div>
                
                <input type="hidden" name="banner_image" id="banner-input" value="<?= h($product['banner_image'] ?? '') ?>">
                
                <div id="banner-placeholder" class="media-placeholder" style="<?= !empty($product['banner_image']) ? 'display: none;' : '' ?>">
                    <button type="button" class="button is-light open-banner-library-btn" style="padding: 10px 20px; border-radius: 10px;  background-color: #fff;">
                        <span class="icon is-large"><i class="fas fa-plus"></i></span>
                        <p class="has-text-weight-semibold">选择横幅图片</p>
                    </button>
                </div>
            </div>

            <!-- 价格 -->
            <div class="admin-card mb-5 animate-in delay-3" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-dollar-sign"></i></span>
                    阶梯价格
                </div>
                
                <input type="hidden" name="price_tiers_enabled" value="1">
                <div id="price-tier-wrap">
                    <?php 
                    $tierData = !empty($prices) ? $prices : [['min_qty'=>'', 'max_qty'=>'', 'price'=>'', 'currency'=>'USD']];
                    foreach ($tierData as $tier): 
                    ?>
                    <div class="columns price-tier-row is-vcentered" style="background: #f8fafc; border-radius: 10px; padding: 0.75rem; margin-bottom: 0.5rem;">
                        <div class="column is-2">
                            <div class="field mb-0">
                                <label class="label is-size-7">最小数量</label>
                                <div class="control">
                                    <input class="input is-small" name="price_min[]" type="number" min="1" value="<?= h((string)$tier['min_qty']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="column is-2">
                            <div class="field mb-0">
                                <label class="label is-size-7">最大数量</label>
                                <div class="control">
                                    <input class="input is-small" name="price_max[]" type="number" min="1" placeholder="可空" value="<?= h((string)($tier['max_qty'] ?? '')) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="field mb-0">
                                <label class="label is-size-7">单价</label>
                                <div class="control">
                                    <input class="input is-small" name="price_value[]" type="number" min="0" step="0.01" value="<?= h((string)($tier['price'] ?? '')) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="column is-2">
                            <div class="field mb-0">
                                <label class="label is-size-7">货币</label>
                                <div class="control">
                                    <input class="input is-small" name="price_currency[]" value="<?= h($tier['currency'] ?? 'USD') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="column is-narrow">
                            <div class="field mb-0">
                                <label class="label is-size-7">&nbsp;</label>
                                <div class="control">
                                    <button type="button" class="button is-danger is-light is-small remove-price-tier" style="border-radius: 8px;">
                                        <span class="icon"><i class="fas fa-trash-alt"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button is-success is-light is-small mt-3" id="add-price-tier" style="border-radius: 8px;">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>新增阶梯价格</span>
                </button>
            </div>
        </div>

        <!-- 右侧栏 -->
        <div class="column is-4">
            <!-- 状态 -->
            <div class="admin-card mb-5 animate-in delay-1" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box warning"><i class="fas fa-toggle-on"></i></span>
                    发布状态
                </div>
                <div class="field">
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="status">
                                <option value="draft" <?= ($product['status'] ?? '') === 'draft' ? 'selected' : '' ?>>📝 草稿</option>
                                <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>✅ 已上架</option>
                                <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' || ($product['status'] ?? '') === 'archived' ? 'selected' : '' ?>>⬇️ 已下架</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 类别和摘要 -->
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box primary"><i class="fas fa-cog"></i></span>
                    产品设置
                </div>
                
                <div class="field">
                    <label class="label">别名 (Slug)</label>
                    <div class="control">
                        <input class="input" name="slug" value="<?= h($product['slug'] ?? '') ?>" placeholder="product-slug">
                    </div>
                    <p class="help">留空自动生成</p>
                </div>
                
                <div class="field">
                    <label class="label">产品摘要</label>
                    <div class="control">
                        <textarea class="textarea" name="summary" rows="2" placeholder="简短描述产品特点"><?= h($product['summary'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="field">
                    <label class="label">产品分类</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="category_id">
                                <option value="0">选择分类</option>
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

            <!-- 产品组织 -->
            <div class="admin-card mb-5 animate-in delay-3" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box info"><i class="fas fa-sitemap"></i></span>
                    产品组织
                </div>
                
                <div class="field">
                    <label class="label is-size-7">产品类型</label>
                    <div class="control">
                        <input class="input" name="product_type" value="<?= h($product['product_type'] ?? '') ?>" placeholder="如：服装、电子产品">
                    </div>
                </div>
                <div class="field">
                    <label class="label is-size-7">供应商/厂商</label>
                    <div class="control">
                        <input class="input" name="vendor" value="<?= h($product['vendor'] ?? '') ?>" placeholder="厂商名称">
                    </div>
                </div>
                <div class="field">
                    <label class="label is-size-7">标签</label>
                    <div class="control">
                        <input class="input" name="tags" value="<?= h($product['tags'] ?? '') ?>" placeholder="用逗号分隔多个标签">
                    </div>
                </div>
            </div>

            <!-- SEO 设置 -->
            <div class="admin-card mb-5 animate-in delay-3" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box success"><i class="fas fa-search"></i></span>
                    SEO 设置
                </div>
                <p class="is-size-7 has-text-grey mb-3">留空则使用产品标题和摘要作为默认值</p>
                
                <div class="field">
                    <label class="label is-size-7">SEO 标题</label>
                    <div class="control">
                        <input class="input" name="seo_title" value="<?= h($product['seo_title'] ?? '') ?>" placeholder="页面标题 (留空使用产品标题)">
                    </div>
                </div>
                <div class="field">
                    <label class="label is-size-7">SEO 关键词</label>
                    <div class="control">
                        <input class="input" name="seo_keywords" value="<?= h($product['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2">
                    </div>
                </div>
                <div class="field">
                    <label class="label is-size-7">SEO 描述</label>
                    <div class="control">
                        <textarea class="textarea" name="seo_description" rows="2" placeholder="页面描述 (留空使用产品摘要)"><?= h($product['seo_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="animate-in delay-3">
                <button type="submit" class="button is-primary is-fullwidth is-medium">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($product) ? '保存修改' : '发布产品' ?></span>
                </button>
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
            urls.forEach(url => addMediaItem(url));
            checkMediaState();
        }, true);
    }));

    function addMediaItem(url) {
        const exists = [].some.call(mediaContainer.querySelectorAll('input[name="images[]"]'), function(inp) { return inp.value === url; });
        if (exists) return;
        const basePath = window.APP_BASE_PATH || '';
        const imgSrc = basePath + url;
        const safeUrl = url.replace(/"/g, '&quot;');
        const safeImgSrc = imgSrc.replace(/"/g, '&quot;');
        const div = document.createElement('div');
        div.className = 'media-item';
        div.dataset.url = url;
        div.innerHTML = `
            <img src="${safeImgSrc}">
            <input type="hidden" name="images[]" value="${safeUrl}">
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

    // 4. 横幅图片选择
    const bannerInput = document.getElementById('banner-input');
    const bannerPreview = document.getElementById('banner-preview');
    const bannerImage = document.getElementById('banner-image');
    const bannerPlaceholder = document.getElementById('banner-placeholder');
    const removeBannerBtn = document.getElementById('remove-banner');
    const openBannerBtns = document.querySelectorAll('.open-banner-library-btn');

    openBannerBtns.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        openMediaLibrary(function(url) {
            setBannerImage(url);
        }, false);
    }));

    function setBannerImage(url) {
        const basePath = window.APP_BASE_PATH || '';
        const imgSrc = basePath + url;
        bannerInput.value = url;
        bannerImage.src = imgSrc;
        bannerPreview.style.display = 'block';
        bannerPlaceholder.style.display = 'none';
    }

    if (removeBannerBtn) {
        removeBannerBtn.addEventListener('click', () => {
            bannerInput.value = '';
            bannerPreview.style.display = 'none';
            bannerPlaceholder.style.display = 'block';
        });
    }

    // 3. 本地上传预览
    const fileInput = document.getElementById('file-upload-input');
    fileInput.addEventListener('change', async function() {
        if (this.files.length > 0) {
            const files = Array.from(this.files);
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

            const uploadTasks = files.map(file => {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('csrf', '<?= csrf_token() ?>');
                return fetch((window.APP_BASE_PATH || '') + '/admin/upload-image', { method: 'POST', body: formData }).then(r => r.json());
            });

            try {
                const results = await Promise.all(uploadTasks);
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
