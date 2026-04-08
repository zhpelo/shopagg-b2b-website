<div class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-globe"></i></span>
                网站基础设置
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">网站名称</span>
                    <span class="relative block">
                        <i class="fas fa-heading pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="site_name" value="<?= h($settings['site_name'] ?? '') ?>" placeholder="我的B2B网站">
                    </span>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">标语</span>
                    <span class="relative block">
                        <i class="fas fa-quote-right pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="site_tagline" value="<?= h($settings['site_tagline'] ?? '') ?>" placeholder="专业的B2B服务">
                    </span>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">启用主题</span>
                    <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="theme">
                        <?php foreach ($available_themes as $t): ?>
                            <option value="<?= h($t) ?>" <?= ($settings['theme'] ?? 'default') === $t ? 'selected' : '' ?>>
                                <?= h($t) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <label class="text-sm font-medium text-slate-700">网站 Logo</label>
                    <input type="hidden" name="site_logo" id="site_logo" value="<?= h($settings['site_logo'] ?? '') ?>">
                    <div class="logo-preview-box flex h-24 w-[200px] cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-indigo-300 hover:bg-indigo-50/40" data-media-picker-target="site_logo" data-media-picker-fit="contain">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="<?= asset_url(h($settings['site_logo'])) ?>" class="max-w-full max-h-full object-contain">
                        <?php else: ?>
                            <div class="text-center text-slate-400">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white"><i class="fas fa-image text-lg"></i></span>
                                <p class="mt-2 text-xs">点击选择 Logo</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500">推荐尺寸: 200×60 像素，支持 PNG/SVG</p>
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-medium text-slate-700">网站 Favicon</label>
                    <input type="hidden" name="site_favicon" id="site_favicon" value="<?= h($settings['site_favicon'] ?? '') ?>">
                    <div class="favicon-preview-box flex h-20 w-20 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-indigo-300 hover:bg-indigo-50/40" data-media-picker-target="site_favicon" data-media-picker-fit="contain">
                        <?php if (!empty($settings['site_favicon'])): ?>
                            <img src="<?= asset_url(h($settings['site_favicon'])) ?>" class="max-w-12 max-h-12 object-contain">
                        <?php else: ?>
                            <div class="text-center text-slate-400">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white"><i class="fas fa-star"></i></span>
                                <p class="mt-1 text-[11px]">Favicon</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500">推荐尺寸: 32×32 或 64×64 像素</p>
                </div>
            </div>
        </div>

        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box success"><i class="fas fa-search"></i></span>
                SEO 设置
            </div>

            <div class="space-y-5">
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">SEO 标题 (Title)</span>
                    <span class="relative block">
                        <i class="fas fa-heading pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="seo_title" value="<?= h($settings['seo_title'] ?? '') ?>" placeholder="网站标题 | 公司名称">
                    </span>
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">SEO 关键词 (Keywords)</span>
                    <span class="relative block">
                        <i class="fas fa-tags pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="seo_keywords" value="<?= h($settings['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2, 关键词3">
                    </span>
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">SEO 描述 (Description)</span>
                    <textarea class="min-h-[110px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="seo_description" rows="3" placeholder="网站描述内容..."><?= h($settings['seo_description'] ?? '') ?></textarea>
                </label>
                <div class="space-y-3">
                    <label class="text-sm font-medium text-slate-700">OG Image (社交分享图)</label>
                    <input type="hidden" name="og_image" id="og_image" value="<?= h($settings['og_image'] ?? '') ?>">
                    <div class="og-image-preview-box flex h-[105px] w-[200px] cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-emerald-300 hover:bg-emerald-50/40" data-media-picker-target="og_image" data-media-picker-fit="cover">
                        <?php if (!empty($settings['og_image'])): ?>
                            <img src="<?= asset_url(h($settings['og_image'])) ?>" class="max-w-full max-h-full object-cover">
                        <?php else: ?>
                            <div class="text-center text-slate-400">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white"><i class="fas fa-share-alt text-lg"></i></span>
                                <p class="mt-2 text-xs">点击选择图片</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500">推荐尺寸: 1200×630 像素，用于社交媒体分享</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 xl:col-span-4">
        <div class="card p-6">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-info"></i></span>
                设置说明
            </div>
            <div class="space-y-3 text-sm leading-6 text-slate-600">
                <p><strong>网站名称</strong>：显示在浏览器标签和网站头部</p>
                <p><strong>标语</strong>：简短描述网站或公司特点</p>
                <p><strong>SEO 设置</strong>：用于搜索引擎优化，帮助提升网站排名</p>
                <p><strong>OG Image</strong>：在社交媒体分享时显示的图片</p>
            </div>
        </div>
    </div>
</div>
