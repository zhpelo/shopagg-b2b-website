<?php


$translateLanguageOptions = [
    'en' => 'English',
    'zh-CN' => '简体中文',
    'zh-TW' => '繁體中文',
    'ja' => '日本語',
    'ko' => '한국어',
    'es' => 'Español',
    'fr' => 'Français',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'pt' => 'Português',
    'ru' => 'Русский',
    'ar' => 'العربية',
];

$selectedTranslateLanguages = json_decode($settings['translate_languages'] ?? '[]', true);
if (!is_array($selectedTranslateLanguages) || empty($selectedTranslateLanguages)) {
    $selectedTranslateLanguages = ['en', 'zh-CN', 'zh-TW', 'ja', 'ko', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ar'];
}
if (!in_array('en', $selectedTranslateLanguages, true)) {
    array_unshift($selectedTranslateLanguages, 'en');
}
?>

<!-- 页面头部 -->
<div class="page-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); box-shadow: 0 10px 40px rgba(108, 117, 125, 0.3);">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/16 text-white">
                    <i class="fas fa-cog"></i>
                </span>
                <span>系统设置</span>
            </h1>
            <p class="mt-2 text-sm text-white/80">管理网站配置、公司资料、媒体展示和自定义代码。</p>
        </div>
    </div>
</div>


<form method="post" action="<?= url('/admin/settings') ?>" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="tab" value="<?= h($tab) ?>">

    <?php if ($tab === 'general'): ?>
        <div class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
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
                        <div class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">禁用设置</span>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                多语言功能已移除，默认使用英语
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 md:grid-cols-2">
                        <div class="space-y-3">
                            <label class="text-sm font-medium text-slate-700">网站 Logo</label>
                            <input type="hidden" name="site_logo" id="site_logo" value="<?= h($settings['site_logo'] ?? '') ?>">
                            <div class="logo-preview-box flex h-24 w-[200px] cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-indigo-300 hover:bg-indigo-50/40" onclick="openMediaLibrary(url => { document.getElementById('site_logo').value = url; this.innerHTML = '<img src=\'' + url + '\'>'; })">
                                <?php if (!empty($settings['site_logo'])): ?>
                                    <img src="<?= asset_url(h($settings['site_logo'])) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
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
                            <div class="favicon-preview-box flex h-20 w-20 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-indigo-300 hover:bg-indigo-50/40" onclick="openMediaLibrary(url => { document.getElementById('site_favicon').value = url; this.innerHTML = '<img src=\'' + url + '\'>'; })">
                                <?php if (!empty($settings['site_favicon'])): ?>
                                    <img src="<?= asset_url(h($settings['site_favicon'])) ?>" style="max-width: 48px; max-height: 48px; object-fit: contain;">
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

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
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
                            <div class="og-image-preview-box flex h-[105px] w-[200px] cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-emerald-300 hover:bg-emerald-50/40" onclick="openMediaLibrary(url => { document.getElementById('og_image').value = url; this.innerHTML = '<img src=\'' + url + '\'>'; })">
                                <?php if (!empty($settings['og_image'])): ?>
                                    <img src="<?= asset_url(h($settings['og_image'])) ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
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
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
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

    <?php elseif ($tab === 'company'): ?>
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-building"></i></span>
                    公司简介 (Profile)
                </div>

                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">详细介绍</span>
                    <textarea class="min-h-[150px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_bio" rows="6" placeholder="介绍公司背景、历史和优势..."><?= h($settings['company_bio'] ?? '') ?></textarea>
                </label>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">业务类型</span>
                        <span class="relative block">
                            <i class="fas fa-industry pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_business_type" value="<?= h($settings['company_business_type'] ?? '') ?>" placeholder="制造商 / 贸易商">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">主营产品</span>
                        <span class="relative block">
                            <i class="fas fa-box pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_main_products" value="<?= h($settings['company_main_products'] ?? '') ?>" placeholder="产品类目">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">成立年份</span>
                        <span class="relative block">
                            <i class="fas fa-calendar pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_year_established" value="<?= h($settings['company_year_established'] ?? '') ?>" placeholder="2000">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">员工人数</span>
                        <span class="relative block">
                            <i class="fas fa-users pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_employees" value="<?= h($settings['company_employees'] ?? '') ?>" placeholder="50-100">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">厂房面积</span>
                        <span class="relative block">
                            <i class="fas fa-warehouse pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_plant_area" value="<?= h($settings['company_plant_area'] ?? '') ?>" placeholder="5000㎡">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">注册资本</span>
                        <span class="relative block">
                            <i class="fas fa-dollar-sign pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_registered_capital" value="<?= h($settings['company_registered_capital'] ?? '') ?>" placeholder="1000万">
                        </span>
                    </label>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                <div class="section-title">
                    <span class="icon-box warning"><i class="fas fa-star"></i></span>
                    资质认证
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">SGS 报告编号</span>
                        <span class="relative block">
                            <i class="fas fa-certificate pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="company_sgs_report" value="<?= h($settings['company_sgs_report'] ?? '') ?>" placeholder="报告编号">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">评分</span>
                        <span class="relative block">
                            <i class="fas fa-star pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="company_rating" value="<?= h($settings['company_rating'] ?? '5.0/5') ?>" placeholder="5.0/5">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">平均响应时间</span>
                        <span class="relative block">
                            <i class="fas fa-clock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="company_response_time" value="<?= h($settings['company_response_time'] ?? '≤24h') ?>" placeholder="≤24h">
                        </span>
                    </label>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'trade'): ?>
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-globe"></i></span>
                    贸易能力 (Trade Capacity)
                </div>

                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">主要市场</span>
                    <span class="relative block">
                        <i class="fas fa-map-marker-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_main_markets" value="<?= h($settings['company_main_markets'] ?? '') ?>" placeholder="北美、欧洲、东南亚">
                    </span>
                </label>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">外贸人数</span>
                        <span class="relative block">
                            <i class="fas fa-user-tie pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_trade_staff" value="<?= h($settings['company_trade_staff'] ?? '') ?>" placeholder="10">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">贸易条款 (Incoterms)</span>
                        <span class="relative block">
                            <i class="fas fa-file-contract pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_incoterms" value="<?= h($settings['company_incoterms'] ?? '') ?>" placeholder="FOB, CIF, EXW">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">支付方式</span>
                        <span class="relative block">
                            <i class="fas fa-credit-card pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_payment_terms" value="<?= h($settings['company_payment_terms'] ?? '') ?>" placeholder="T/T, L/C, PayPal">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">平均交期</span>
                        <span class="relative block">
                            <i class="fas fa-shipping-fast pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_lead_time" value="<?= h($settings['company_lead_time'] ?? '') ?>" placeholder="15-30天">
                        </span>
                    </label>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">海外分支</span>
                        <span class="relative block">
                            <i class="fas fa-globe-americas pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_overseas_agent" value="<?= h($settings['company_overseas_agent'] ?? 'No') ?>" placeholder="有/无">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">出口开始年份</span>
                        <span class="relative block">
                            <i class="fas fa-calendar-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_export_year" value="<?= h($settings['company_export_year'] ?? '') ?>" placeholder="2005">
                        </span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">最近港口</span>
                        <span class="relative block">
                            <i class="fas fa-anchor pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_nearest_port" value="<?= h($settings['company_nearest_port'] ?? '') ?>" placeholder="上海港">
                        </span>
                    </label>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-flask"></i></span>
                    研发能力 (R&D)
                </div>

                <div class="max-w-sm">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700">研发工程师人数</span>
                        <span class="relative block">
                            <i class="fas fa-user-graduate pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="company_rd_engineers" value="<?= h($settings['company_rd_engineers'] ?? '') ?>" placeholder="5">
                        </span>
                    </label>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'media'): ?>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-images"></i></span>
                公司展示 (Show)
            </div>
            <div id="company-show-container">
                <?php
                $showItems = json_decode($settings['company_show_json'] ?? '[]', true);
                foreach ($showItems as $item): ?>
                    <div class="media-item-row mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="grid gap-4 md:grid-cols-[120px_minmax(0,1fr)_56px] md:items-center">
                            <div>
                                <div class="media-preview-wrap">
                                    <input type="hidden" name="show_img[]" value="<?= h($item['img']) ?>">
                                    <div class="media-preview <?= empty($item['img']) ? 'is-empty' : '' ?>" onclick="selectMediaPreview(this)">
                                        <?php if (!empty($item['img'])): ?>
                                            <img src="<?= asset_url(h($item['img'])) ?>" alt="">
                                        <?php else: ?>
                                            <span class="text-slate-400"><i class="fas fa-image fa-2x"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">图片标题</label>
                                <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="show_title[]" value="<?= h($item['title']) ?>" placeholder="输入图片标题">
                            </div>
                            <div>
                                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" onclick="this.closest('.media-item-row').remove()">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100" onclick="addMediaRow('company-show-container', 'show_img', 'show_title')">
                <i class="fas fa-plus text-xs"></i>
                <span>新增项目</span>
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
            <div class="section-title">
                <span class="icon-box warning"><i class="fas fa-certificate"></i></span>
                资质证书 (Certificates)
            </div>
            <div id="certificates-container">
                <?php
                $certItems = json_decode($settings['company_certificates_json'] ?? '[]', true);
                foreach ($certItems as $item): ?>
                    <div class="media-item-row mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="grid gap-4 md:grid-cols-[120px_minmax(0,1fr)_56px] md:items-center">
                            <div>
                                <div class="media-preview-wrap">
                                    <input type="hidden" name="cert_img[]" value="<?= h($item['img']) ?>">
                                    <div class="media-preview <?= empty($item['img']) ? 'is-empty' : '' ?>" onclick="selectMediaPreview(this)">
                                        <?php if (!empty($item['img'])): ?>
                                            <img src="<?= asset_url(h($item['img'])) ?>" alt="">
                                        <?php else: ?>
                                            <span class="text-slate-400"><i class="fas fa-image fa-2x"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">证书名称</label>
                                <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="cert_title[]" value="<?= h($item['title']) ?>" placeholder="输入证书名称">
                            </div>
                            <div>
                                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" onclick="this.closest('.media-item-row').remove()">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:bg-amber-100" onclick="addMediaRow('certificates-container', 'cert_img', 'cert_title')">
                <i class="fas fa-plus text-xs"></i>
                <span>新增证书</span>
            </button>
        </div>

        <style>
            .media-preview-wrap {
                position: relative;
            }

            .media-preview {
                width: 100%;
                aspect-ratio: 1/1;
                border: 2px dashed #d1d5db;
                border-radius: 12px;
                overflow: hidden;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fafafa;
                transition: all 0.2s;
            }

            .media-preview:hover {
                border-color: #667eea;
                background: rgba(102, 126, 234, 0.05);
            }

            .media-preview:not(.is-empty) {
                border-style: solid;
                border-color: #e5e7eb;
            }

            .media-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        </style>

        <script>
            function addMediaRow(containerId, imgName, titleName) {
                const container = document.getElementById(containerId);
                const div = document.createElement('div');
                div.className = 'media-item-row mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-4';
                const labelText = imgName.includes('cert') ? '证书名称' : '图片标题';
                const placeholderText = imgName.includes('cert') ? '输入证书名称' : '输入图片标题';
                const inputAccent = imgName.includes('cert') ?
                    'focus:border-amber-400 focus:ring-2 focus:ring-amber-100' :
                    'focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100';
                div.innerHTML = `
 <div class="grid gap-4 md:grid-cols-[120px_minmax(0,1fr)_56px] md:items-center">
 <div>
 <div class="media-preview-wrap">
 <input type="hidden" name="${imgName}[]" value="">
 <div class="media-preview is-empty" onclick="selectMediaPreview(this)">
 <span class="text-slate-400"><i class="fas fa-image fa-2x"></i></span>
 </div>
 </div>
 </div>
 <div class="space-y-2">
 <label class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">${labelText}</label>
 <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 ${inputAccent}" name="${titleName}[]" value="" placeholder="${placeholderText}">
 </div>
 <div>
 <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" onclick="this.closest('.media-item-row').remove()">
 <i class="fas fa-trash-alt text-sm"></i>
 </button>
 </div>
 </div>
 `;
                container.appendChild(div);
            }

            function selectMediaPreview(previewEl) {
                const wrap = previewEl.closest('.media-preview-wrap');
                const input = wrap.querySelector('input[type="hidden"]');
                openMediaLibrary(function(url) {
                    input.value = url;
                    previewEl.innerHTML = `<img src="${url}" alt="">`;
                    previewEl.classList.remove('is-empty');
                });
            }
        </script>

    <?php elseif ($tab === 'contact'): ?>
        <div class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                    <div class="section-title">
                        <span class="icon-box primary"><i class="fas fa-phone"></i></span>
                        联系信息
                    </div>

                    <div class="grid gap-5 md:grid-cols-3">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">邮箱</span>
                            <span class="relative block">
                                <i class="fas fa-envelope pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_email" value="<?= h($settings['company_email'] ?? '') ?>" placeholder="contact@example.com">
                            </span>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">电话</span>
                            <span class="relative block">
                                <i class="fas fa-phone pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_phone" value="<?= h($settings['company_phone'] ?? '') ?>" placeholder="+86 123 4567 8900">
                            </span>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">WhatsApp</span>
                            <span class="relative block">
                                <i class="fab fa-whatsapp pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="whatsapp" value="<?= h($settings['whatsapp'] ?? '') ?>" placeholder="+86 123 4567 8900">
                            </span>
                        </label>
                    </div>

                    <label class="mt-6 block space-y-2">
                        <span class="text-sm font-medium text-slate-700">公司地址</span>
                        <span class="relative block">
                            <i class="fas fa-map-marker-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_address" value="<?= h($settings['company_address'] ?? '') ?>" placeholder="详细地址">
                        </span>
                    </label>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                    <div class="section-title">
                        <span class="icon-box info"><i class="fas fa-share-alt"></i></span>
                        社交媒体
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <?php
                        $social_icons = [
                            'facebook' => ['Facebook', 'fab fa-facebook-f', '#1877f2'],
                            'instagram' => ['Instagram', 'fab fa-instagram', '#e4405f'],
                            'twitter' => ['Twitter', 'fab fa-twitter', '#1da1f2'],
                            'linkedin' => ['LinkedIn', 'fab fa-linkedin-in', '#0a66c2'],
                            'youtube' => ['YouTube', 'fab fa-youtube', '#ff0000']
                        ];
                        foreach ($social_icons as $key => $info): ?>
                            <label class="space-y-2">
                                <span class="text-sm font-medium text-slate-700"><?= $info[0] ?></span>
                                <span class="relative block">
                                    <i class="<?= $info[1] ?> pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color: <?= $info[2] ?>;"></i>
                                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="<?= $key ?>" value="<?= h($settings[$key] ?? '') ?>" placeholder="<?= $info[0] ?> URL">
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                    <div class="section-title">
                        <span class="icon-box info"><i class="fas fa-info"></i></span>
                        设置说明
                    </div>
                    <div class="space-y-3 text-sm leading-6 text-slate-600">
                        <p><strong>邮箱 / 电话 / WhatsApp</strong>：用于页脚、联系模块和询盘转化入口。</p>
                        <p><strong>公司地址</strong>：建议填写完整地址，便于搜索引擎和客户识别。</p>
                        <p><strong>社交媒体</strong>：前台会按已填写的链接显示对应图标。</p>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'translate'): ?>
        <div class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                    <div class="section-title">
                        <span class="icon-box primary"><i class="fas fa-language"></i></span>
                        前台翻译功能
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">启用网页翻译</span>
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="translate_enabled">
                                <option value="1" <?= ($settings['translate_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>启用</option>
                                <option value="0" <?= ($settings['translate_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>禁用</option>
                            </select>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-slate-700">根据浏览器语言自动翻译</span>
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="translate_auto_browser">
                                <option value="0" <?= ($settings['translate_auto_browser'] ?? '0') === '0' ? 'selected' : '' ?>>关闭</option>
                                <option value="1" <?= ($settings['translate_auto_browser'] ?? '0') === '1' ? 'selected' : '' ?>>开启</option>
                            </select>
                            <p class="text-xs text-slate-500">仅在用户尚未手动选择语言时生效</p>
                        </label>
                    </div>

                    <div class="mt-6 space-y-3">
                        <label class="text-sm font-medium text-slate-700">前台可选翻译语种</label>
                        <input type="hidden" name="translate_languages[]" value="en">
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            <?php foreach ($translateLanguageOptions as $langCode => $langLabel): ?>
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <input class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" type="checkbox" name="translate_languages[]" value="<?= h($langCode) ?>" <?= in_array($langCode, $selectedTranslateLanguages, true) ? 'checked' : '' ?> <?= $langCode === 'en' ? 'disabled' : '' ?>>
                                    <span><?= h($langLabel) ?> <?= $langCode === 'en' ? '(默认原文)' : '' ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-slate-500">English 固定保留，用于“英文不翻译（恢复原文）”。</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                    <div class="section-title">
                        <span class="icon-box info"><i class="fas fa-info"></i></span>
                        设置说明
                    </div>
                    <div class="space-y-3 text-sm leading-6 text-slate-600">
                        <p><strong>启用网页翻译</strong>：控制前台是否显示翻译下拉框。</p>
                        <p><strong>自动翻译</strong>：按浏览器语言自动切换（若不在可选语种内则保持英文）。</p>
                        <p><strong>可选语种</strong>：控制前台下拉框中的语言列表。</p>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($tab === 'custom'): ?>
        <div class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
                    <div class="section-title">
                        <span class="icon-box primary"><i class="fas fa-code"></i></span>
                        自定义代码
                    </div>

                    <div class="space-y-5">
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-slate-700">Head 自定义代码</span>
                            <textarea class="min-h-[150px] w-full rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 font-mono text-sm text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="head_code" rows="6" placeholder="例如：统计代码、验证代码、全站样式..."><?= h($settings['head_code'] ?? '') ?></textarea>
                            <p class="text-xs text-slate-500">会插入到 &lt;head&gt; 末尾，请确保代码安全。</p>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-slate-700">Footer 自定义代码</span>
                            <textarea class="min-h-[150px] w-full rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 font-mono text-sm text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="footer_code" rows="6" placeholder="例如：脚本、像素追踪代码..."><?= h($settings['footer_code'] ?? '') ?></textarea>
                            <p class="text-xs text-slate-500">会插入到 &lt;/body&gt; 前。</p>
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                    <div class="section-title">
                        <span class="icon-box info"><i class="fas fa-info"></i></span>
                        设置说明
                    </div>
                    <div class="space-y-3 text-sm leading-6 text-slate-600">
                        <p><strong>Head</strong>：用于统计/验证/全站样式等。</p>
                        <p><strong>Footer</strong>：用于脚本或追踪代码。</p>
                        <p>保存后会在前台模板中输出。</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-6">
        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800" type="submit">
            <i class="fas fa-save text-xs"></i>
            <span>保存设置</span>
        </button>
    </div>
</form>
