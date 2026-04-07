<!-- 页面头部 -->
<div class="page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-<?= isset($product) ? 'edit' : 'plus' ?>"></i></span>
                    <?= isset($product) ? '编辑产品' : '添加产品' ?>
                </h1>
                <p class="mt-1 text-sm text-white/80"><?= isset($product) ? '修改产品信息' : '创建新的产品' ?></p>
            </div>
        </div>
        <div class="header-actions flex items-center gap-3">
            <a href="<?= url('/admin/products') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-slate-50">
                <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<form method="post" action="<?= h(url($action)) ?>" enctype="multipart/form-data" id="product-form" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

    <div class="grid gap-6 xl:grid-cols-12">
        <!-- 左侧栏 -->
        <div class="xl:col-span-8">
            <!-- 标题和描述 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-8">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-info-circle"></i></span>
                    基本信息
                </div>
                <div class="space-y-5">
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">产品标题</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="title" value="<?= h($product['title'] ?? '') ?>" placeholder="输入产品标题" required>
                    </label>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="content-input">产品描述</label>
                        <div id="editor-wrapper">
                            <textarea id="content-input" name="content" class="js-rich-editor" data-editor-height="300"><?= h(process_rich_text($product['content'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 媒体文件 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-8">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-images"></i></span>
                    媒体文件
                </div>

                <?php $images = $product['images'] ?? []; ?>
                <div id="media-grid-wrap" class="<?= empty($images) ? 'hidden' : '' ?>">
                    <div id="media-container" class="mb-4">
                        <?php foreach ($images as $img): ?>
                            <div class="media-item" data-url="<?= h($img) ?>">
                                <img src="<?= h(url($img)) ?>">
                                <input type="hidden" name="images[]" value="<?= h($img) ?>">
                                <button type="button" class="remove-media inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900/70 text-white transition hover:bg-rose-500" aria-label="移除图片">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>

                        <!-- 网格末尾的添加按钮 -->
                        <div class="media-add-btn open-media-library-btn flex items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 text-slate-400 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-500" id="grid-add-btn">
                            <span class="text-3xl font-light">+</span>
                        </div>
                    </div>
                </div>

                <!-- 空占位区域 -->
                <div id="media-empty-placeholder" class="media-placeholder <?= !empty($images) ? 'hidden' : '' ?>">
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                            <input class="hidden" type="file" name="new_images[]" multiple accept="image/*" id="file-upload-input">
                            <i class="fas fa-upload text-xs"></i>
                            <span>上传新文件</span>
                        </label>
                        <button type="button" class="open-media-library-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                            <i class="fas fa-photo-video text-xs"></i>
                            <span>选择现有文件</span>
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">支持 JPG、PNG、GIF、WebP 格式图片</p>
                </div>
            </div>

            <!-- 横幅图片 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-8">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-image"></i></span>
                    横幅图片
                </div>
                <p class="mb-4 text-xs text-slate-500">选择一张图片作为商品页面的横幅展示（可选）</p>

                <div id="banner-preview" class="mb-4" style="<?= empty($product['banner_image']) ? 'display: none;' : '' ?>">
                    <div class="banner-image-container relative inline-block">
                        <img id="banner-image" src="<?= h(url($product['banner_image'] ?? '')) ?>" alt="横幅图片" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900/70 text-white transition hover:bg-rose-500" id="remove-banner" style="position: absolute; top: 8px; right: 8px;" aria-label="移除横幅">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="banner_image" id="banner-input" value="<?= h($product['banner_image'] ?? '') ?>">

                <div id="banner-placeholder" class="media-placeholder" style="<?= !empty($product['banner_image']) ? 'display: none;' : '' ?>">
                    <button type="button" class="open-banner-library-btn inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                        <i class="fas fa-plus text-xs"></i>
                        <span>选择横幅图片</span>
                    </button>
                </div>
            </div>

            <!-- 价格 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-8">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-dollar-sign"></i></span>
                    阶梯价格
                </div>

                <input type="hidden" name="price_tiers_enabled" value="1">
                <div id="price-tier-wrap">
                    <?php
                    $tierData = !empty($prices) ? $prices : [['min_qty' => '', 'max_qty' => '', 'price' => '', 'currency' => 'USD']];
                    foreach ($tierData as $tier):
                    ?>
                        <div class="price-tier-row mb-3 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_110px_56px] md:items-end">
                            <label class="space-y-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">最小数量</span>
                                <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_min[]" type="number" min="1" value="<?= h((string)$tier['min_qty']) ?>" required>
                            </label>
                            <label class="space-y-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">最大数量</span>
                                <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_max[]" type="number" min="1" placeholder="可空" value="<?= h((string)($tier['max_qty'] ?? '')) ?>">
                            </label>
                            <label class="space-y-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">单价</span>
                                <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_value[]" type="number" min="0" step="0.01" value="<?= h((string)($tier['price'] ?? '')) ?>" required>
                            </label>
                            <label class="space-y-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">货币</span>
                                <input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_currency[]" value="<?= h($tier['currency'] ?? 'USD') ?>" required>
                            </label>
                            <div class="flex md:justify-end">
                                <button type="button" class="remove-price-tier inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" aria-label="删除阶梯价格">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="mt-3 inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100" id="add-price-tier">
                    <i class="fas fa-plus text-xs"></i>
                    <span>新增阶梯价格</span>
                </button>
            </div>
        </div>

        <!-- 右侧栏 -->
        <div class="xl:col-span-4">
            <!-- 状态 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-6">
                <div class="section-title">
                    <span class="icon-box warning"><i class="fas fa-toggle-on"></i></span>
                    发布状态
                </div>
                <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="status">
                    <option value="draft" <?= ($product['status'] ?? '') === 'draft' ? 'selected' : '' ?>>📝 草稿</option>
                    <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>✅ 已上架</option>
                    <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' || ($product['status'] ?? '') === 'archived' ? 'selected' : '' ?>>⬇️ 已下架</option>
                </select>
            </div>

            <!-- 类别和摘要 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-6">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-cog"></i></span>
                    产品设置
                </div>

                <div class="space-y-5">
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">别名 (Slug)</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="slug" value="<?= h($product['slug'] ?? '') ?>" placeholder="product-slug">
                        <p class="text-xs text-slate-500">留空自动生成</p>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">产品摘要</span>
                        <textarea class="min-h-[96px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="summary" rows="2" placeholder="简短描述产品特点"><?= h($product['summary'] ?? '') ?></textarea>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-slate-700">产品分类</span>
                        <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="category_id">
                            <option value="0">选择分类</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= h($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </div>

            <!-- 产品组织 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-6">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-sitemap"></i></span>
                    产品组织
                </div>

                <div class="space-y-4">
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">产品类型</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="product_type" value="<?= h($product['product_type'] ?? '') ?>" placeholder="如：服装、电子产品">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">供应商/厂商</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="vendor" value="<?= h($product['vendor'] ?? '') ?>" placeholder="厂商名称">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">标签</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="tags" value="<?= h($product['tags'] ?? '') ?>" placeholder="用逗号分隔多个标签">
                    </label>
                </div>
            </div>

            <!-- SEO 设置 -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-6">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-search"></i></span>
                    SEO 设置
                </div>
                <p class="mb-3 text-xs text-slate-500">留空则使用产品标题和摘要作为默认值</p>

                <div class="space-y-4">
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 标题</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="seo_title" value="<?= h($product['seo_title'] ?? '') ?>" placeholder="页面标题 (留空使用产品标题)">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 关键词</span>
                        <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="seo_keywords" value="<?= h($product['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SEO 描述</span>
                        <textarea class="min-h-[96px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="seo_description" rows="2" placeholder="页面描述 (留空使用产品摘要)"><?= h($product['seo_description'] ?? '') ?></textarea>
                    </label>
                </div>
            </div>

            <!-- 提交按钮 -->
            <div class="">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:-translate-y-0.5">
                    <i class="fas fa-save text-xs"></i>
                    <span><?= isset($product) ? '保存修改' : '发布产品' ?></span>
                </button>
            </div>
        </div>
    </div>
</form>

