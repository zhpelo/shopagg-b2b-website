<?php
$tabs = [
    'general' => ['基础设置', 'cog'],
    'company' => ['公司简介', 'building'],
    'trade' => ['贸易能力', 'globe'],
    'media' => ['公司展示', 'images'],
    'contact' => ['联系方式', 'phone'],
    'translate' => ['翻译设置', 'language'],
    'custom' => ['自定义代码', 'code']
];

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
<div class="page-header animate-in" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); box-shadow: 0 10px 40px rgba(108, 117, 125, 0.3);">
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

<!-- 选项卡 -->
<div class="animate-in delay-1 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
    <?php foreach ($tabs as $key => $info): ?>
        <a
            href="<?= url('/admin/settings?tab=' . urlencode($key)) ?>"
            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition <?= $tab === $key ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"
        >
            <i class="fas fa-<?= $info[1] ?> text-xs"></i>
            <span><?= $info[0] ?></span>
        </a>
    <?php endforeach; ?>
</div>

<form method="post" action="<?= url('/admin/settings') ?>" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="tab" value="<?= h($tab) ?>">

    <?php if ($tab === 'general'): ?>
    <div class="columns">
        <div class="column is-8">
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-globe"></i></span>
                    网站基础设置
                </div>
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">网站名称</label>
                            <div class="control has-icons-left">
                                <input class="input" name="site_name" value="<?= h($settings['site_name'] ?? '') ?>" placeholder="我的B2B网站">
                                <span class="icon is-left has-text-grey-light"><i class="fas fa-heading"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">标语</label>
                            <div class="control has-icons-left">
                                <input class="input" name="site_tagline" value="<?= h($settings['site_tagline'] ?? '') ?>" placeholder="专业的B2B服务">
                                <span class="icon is-left has-text-grey-light"><i class="fas fa-quote-right"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">启用主题</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="theme">
                                        <?php foreach ($available_themes as $t): ?>
                                            <option value="<?= h($t) ?>" <?= ($settings['theme'] ?? 'default') === $t ? 'selected' : '' ?>>
                                                <?= h($t) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">禁用设置</label>
                            <div class="control">
                                <p class="help">多语言功能已移除，默认使用英语</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">网站 Logo</label>
                            <input type="hidden" name="site_logo" id="site_logo" value="<?= h($settings['site_logo'] ?? '') ?>">
                            <div class="logo-preview-box" onclick="openMediaLibrary(url => { document.getElementById('site_logo').value = url; this.innerHTML = '<img src=\'' + url + '\'>'; })" style="width: 200px; height: 80px; border: 2px dashed #e5e7eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fafafa; cursor: pointer; transition: all 0.2s; overflow: hidden;">
                                <?php if (!empty($settings['site_logo'])): ?>
                                    <img src="<?= asset_url(h($settings['site_logo'])) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                <?php else: ?>
                                    <div class="has-text-centered has-text-grey-light">
                                        <span class="icon is-large"><i class="fas fa-image fa-2x"></i></span>
                                        <p class="is-size-7 mt-1">点击选择 Logo</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="help">推荐尺寸: 200×60 像素，支持 PNG/SVG</p>
                        </div>
                    </div>
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">网站 Favicon</label>
                            <input type="hidden" name="site_favicon" id="site_favicon" value="<?= h($settings['site_favicon'] ?? '') ?>">
                            <div class="favicon-preview-box" onclick="openMediaLibrary(url => { document.getElementById('site_favicon').value = url; this.innerHTML = '<img src=\'' + url + '\'>'; })" style="width: 80px; height: 80px; border: 2px dashed #e5e7eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fafafa; cursor: pointer; transition: all 0.2s; overflow: hidden;">
                                <?php if (!empty($settings['site_favicon'])): ?>
                                    <img src="<?= asset_url(h($settings['site_favicon'])) ?>" style="max-width: 48px; max-height: 48px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="has-text-centered has-text-grey-light">
                                        <span class="icon"><i class="fas fa-star fa-lg"></i></span>
                                        <p class="is-size-7 mt-1">Favicon</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="help">推荐尺寸: 32×32 或 64×64 像素</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card animate-in delay-3" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box success"><i class="fas fa-search"></i></span>
                    SEO 设置
                </div>
                <div class="field">
                    <label class="label">SEO 标题 (Title)</label>
                    <div class="control has-icons-left">
                        <input class="input" name="seo_title" value="<?= h($settings['seo_title'] ?? '') ?>" placeholder="网站标题 | 公司名称">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-heading"></i></span>
                    </div>
                </div>
                <div class="field">
                    <label class="label">SEO 关键词 (Keywords)</label>
                    <div class="control has-icons-left">
                        <input class="input" name="seo_keywords" value="<?= h($settings['seo_keywords'] ?? '') ?>" placeholder="关键词1, 关键词2, 关键词3">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-tags"></i></span>
                    </div>
                </div>
                <div class="field">
                    <label class="label">SEO 描述 (Description)</label>
                    <div class="control">
                        <textarea class="textarea" name="seo_description" rows="3" placeholder="网站描述内容..."><?= h($settings['seo_description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">OG Image (社交分享图)</label>
                    <input type="hidden" name="og_image" id="og_image" value="<?= h($settings['og_image'] ?? '') ?>">
                    <div class="og-image-preview-box" onclick="openMediaLibrary(url => { document.getElementById('og_image').value = url; this.innerHTML = '<img src=\'' + url + '\'>'; })" style="width: 200px; height: 105px; border: 2px dashed #e5e7eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: #fafafa; cursor: pointer; transition: all 0.2s; overflow: hidden;">
                        <?php if (!empty($settings['og_image'])): ?>
                            <img src="<?= asset_url(h($settings['og_image'])) ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="has-text-centered has-text-grey-light">
                                <span class="icon is-large"><i class="fas fa-share-alt fa-2x"></i></span>
                                <p class="is-size-7 mt-1">点击选择图片</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="help">推荐尺寸: 1200×630 像素，用于社交媒体分享</p>
                </div>
            </div>
        </div>
        
        <div class="column is-4">
            <div class="admin-card animate-in delay-2" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box info"><i class="fas fa-info"></i></span>
                    设置说明
                </div>
                <div class="content is-size-7 has-text-grey">
                    <p><strong>网站名称</strong>：显示在浏览器标签和网站头部</p>
                    <p><strong>标语</strong>：简短描述网站或公司特点</p>
                    <p><strong>SEO 设置</strong>：用于搜索引擎优化，帮助提升网站排名</p>
                    <p><strong>OG Image</strong>：在社交媒体分享时显示的图片</p>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($tab === 'company'): ?>
    <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
        <div class="section-title">
            <span class="icon-box primary"><i class="fas fa-building"></i></span>
            公司简介 (Profile)
        </div>
        <div class="field">
            <label class="label">详细介绍</label>
            <div class="control">
                <textarea class="textarea" name="company_bio" rows="6" placeholder="介绍公司背景、历史和优势..."><?= h($settings['company_bio'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="columns is-multiline">
            <div class="column is-6">
                <div class="field">
                    <label class="label">业务类型</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_business_type" value="<?= h($settings['company_business_type'] ?? '') ?>" placeholder="制造商 / 贸易商">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-industry"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">主营产品</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_main_products" value="<?= h($settings['company_main_products'] ?? '') ?>" placeholder="产品类目">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-box"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">成立年份</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_year_established" value="<?= h($settings['company_year_established'] ?? '') ?>" placeholder="2000">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-calendar"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">员工人数</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_employees" value="<?= h($settings['company_employees'] ?? '') ?>" placeholder="50-100">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-users"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">厂房面积</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_plant_area" value="<?= h($settings['company_plant_area'] ?? '') ?>" placeholder="5000㎡">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-warehouse"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">注册资本</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_registered_capital" value="<?= h($settings['company_registered_capital'] ?? '') ?>" placeholder="1000万">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-dollar-sign"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-card animate-in delay-3" style="padding: 2rem;">
        <div class="section-title">
            <span class="icon-box warning"><i class="fas fa-star"></i></span>
            资质认证
        </div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">SGS 报告编号</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_sgs_report" value="<?= h($settings['company_sgs_report'] ?? '') ?>" placeholder="报告编号">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-certificate"></i></span>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">评分</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_rating" value="<?= h($settings['company_rating'] ?? '5.0/5') ?>" placeholder="5.0/5">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-star"></i></span>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">平均响应时间</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_response_time" value="<?= h($settings['company_response_time'] ?? '≤24h') ?>" placeholder="≤24h">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-clock"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($tab === 'trade'): ?>
    <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
        <div class="section-title">
            <span class="icon-box info"><i class="fas fa-globe"></i></span>
            贸易能力 (Trade Capacity)
        </div>
        <div class="field">
            <label class="label">主要市场</label>
            <div class="control has-icons-left">
                <input class="input" name="company_main_markets" value="<?= h($settings['company_main_markets'] ?? '') ?>" placeholder="北美、欧洲、东南亚">
                <span class="icon is-left has-text-grey-light"><i class="fas fa-map-marker-alt"></i></span>
            </div>
        </div>
        <div class="columns is-multiline">
            <div class="column is-6">
                <div class="field">
                    <label class="label">外贸人数</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_trade_staff" value="<?= h($settings['company_trade_staff'] ?? '') ?>" placeholder="10">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-user-tie"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">贸易条款 (Incoterms)</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_incoterms" value="<?= h($settings['company_incoterms'] ?? '') ?>" placeholder="FOB, CIF, EXW">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-file-contract"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">支付方式</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_payment_terms" value="<?= h($settings['company_payment_terms'] ?? '') ?>" placeholder="T/T, L/C, PayPal">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-credit-card"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6">
                <div class="field">
                    <label class="label">平均交期</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_lead_time" value="<?= h($settings['company_lead_time'] ?? '') ?>" placeholder="15-30天">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-shipping-fast"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">海外分支</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_overseas_agent" value="<?= h($settings['company_overseas_agent'] ?? 'No') ?>" placeholder="有/无">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-globe-americas"></i></span>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">出口开始年份</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_export_year" value="<?= h($settings['company_export_year'] ?? '') ?>" placeholder="2005">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">最近港口</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_nearest_port" value="<?= h($settings['company_nearest_port'] ?? '') ?>" placeholder="上海港">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-anchor"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-card animate-in delay-3" style="padding: 2rem;">
        <div class="section-title">
            <span class="icon-box success"><i class="fas fa-flask"></i></span>
            研发能力 (R&D)
        </div>
        <div class="columns">
            <div class="column is-4">
                <div class="field">
                    <label class="label">研发工程师人数</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_rd_engineers" value="<?= h($settings['company_rd_engineers'] ?? '') ?>" placeholder="5">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-user-graduate"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($tab === 'media'): ?>
    <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
        <div class="section-title">
            <span class="icon-box primary"><i class="fas fa-images"></i></span>
            公司展示 (Show)
        </div>
        <div id="company-show-container">
            <?php 
            $showItems = json_decode($settings['company_show_json'] ?? '[]', true);
            foreach ($showItems as $item): ?>
                <div class="box mb-3 media-item-row" style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div class="columns is-vcentered">
                        <div class="column is-2">
                            <div class="media-preview-wrap">
                                <input type="hidden" name="show_img[]" value="<?= h($item['img']) ?>">
                                <div class="media-preview <?= empty($item['img']) ? 'is-empty' : '' ?>" onclick="selectMediaPreview(this)">
                                    <?php if (!empty($item['img'])): ?>
                                        <img src="<?= asset_url(h($item['img'])) ?>" alt="">
                                    <?php else: ?>
                                        <span class="icon has-text-grey-light"><i class="fas fa-image fa-2x"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field mb-0">
                                <label class="label is-size-7">图片标题</label>
                                <input class="input" name="show_title[]" value="<?= h($item['title']) ?>" placeholder="输入图片标题">
                            </div>
                        </div>
                        <div class="column is-narrow">
                            <button type="button" class="button is-danger is-light" onclick="this.closest('.media-item-row').remove()" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-trash-alt"></i></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button is-primary is-light" onclick="addMediaRow('company-show-container', 'show_img', 'show_title')" style="border-radius: 8px;">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>新增项目</span>
        </button>
    </div>

    <div class="admin-card animate-in delay-3" style="padding: 2rem;">
        <div class="section-title">
            <span class="icon-box warning"><i class="fas fa-certificate"></i></span>
            资质证书 (Certificates)
        </div>
        <div id="certificates-container">
            <?php 
            $certItems = json_decode($settings['company_certificates_json'] ?? '[]', true);
            foreach ($certItems as $item): ?>
                <div class="box mb-3 media-item-row" style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div class="columns is-vcentered">
                        <div class="column is-2">
                            <div class="media-preview-wrap">
                                <input type="hidden" name="cert_img[]" value="<?= h($item['img']) ?>">
                                <div class="media-preview <?= empty($item['img']) ? 'is-empty' : '' ?>" onclick="selectMediaPreview(this)">
                                    <?php if (!empty($item['img'])): ?>
                                        <img src="<?= asset_url(h($item['img'])) ?>" alt="">
                                    <?php else: ?>
                                        <span class="icon has-text-grey-light"><i class="fas fa-image fa-2x"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field mb-0">
                                <label class="label is-size-7">证书名称</label>
                                <input class="input" name="cert_title[]" value="<?= h($item['title']) ?>" placeholder="输入证书名称">
                            </div>
                        </div>
                        <div class="column is-narrow">
                            <button type="button" class="button is-danger is-light" onclick="this.closest('.media-item-row').remove()" style="border-radius: 8px;">
                                <span class="icon"><i class="fas fa-trash-alt"></i></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button is-warning is-light" onclick="addMediaRow('certificates-container', 'cert_img', 'cert_title')" style="border-radius: 8px;">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>新增证书</span>
        </button>
    </div>

    <style>
    .media-preview-wrap { position: relative; }
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
        div.className = 'box mb-3 media-item-row';
        div.style.cssText = 'background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px;';
        const labelText = imgName.includes('cert') ? '证书名称' : '图片标题';
        const placeholderText = imgName.includes('cert') ? '输入证书名称' : '输入图片标题';
        div.innerHTML = `
            <div class="columns is-vcentered">
                <div class="column is-2">
                    <div class="media-preview-wrap">
                        <input type="hidden" name="${imgName}[]" value="">
                        <div class="media-preview is-empty" onclick="selectMediaPreview(this)">
                            <span class="icon has-text-grey-light"><i class="fas fa-image fa-2x"></i></span>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field mb-0">
                        <label class="label is-size-7">${labelText}</label>
                        <input class="input" name="${titleName}[]" value="" placeholder="${placeholderText}">
                    </div>
                </div>
                <div class="column is-narrow">
                    <button type="button" class="button is-danger is-light" onclick="this.closest('.media-item-row').remove()" style="border-radius: 8px;">
                        <span class="icon"><i class="fas fa-trash-alt"></i></span>
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
    <div class="columns">
        <div class="column is-8">
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-phone"></i></span>
                    联系信息
                </div>
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">邮箱</label>
                            <div class="control has-icons-left">
                                <input class="input" name="company_email" value="<?= h($settings['company_email'] ?? '') ?>" placeholder="contact@example.com">
                                <span class="icon is-left has-text-grey-light"><i class="fas fa-envelope"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">电话</label>
                            <div class="control has-icons-left">
                                <input class="input" name="company_phone" value="<?= h($settings['company_phone'] ?? '') ?>" placeholder="+86 123 4567 8900">
                                <span class="icon is-left has-text-grey-light"><i class="fas fa-phone"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">WhatsApp</label>
                            <div class="control has-icons-left">
                                <input class="input" name="whatsapp" value="<?= h($settings['whatsapp'] ?? '') ?>" placeholder="+86 123 4567 8900">
                                <span class="icon is-left has-text-grey-light"><i class="fab fa-whatsapp"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">公司地址</label>
                    <div class="control has-icons-left">
                        <input class="input" name="company_address" value="<?= h($settings['company_address'] ?? '') ?>" placeholder="详细地址">
                        <span class="icon is-left has-text-grey-light"><i class="fas fa-map-marker-alt"></i></span>
                    </div>
                </div>
            </div>

            <div class="admin-card animate-in delay-3" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box info"><i class="fas fa-share-alt"></i></span>
                    社交媒体
                </div>
                <div class="columns is-multiline">
                    <?php 
                    $social_icons = [
                        'facebook' => ['Facebook', 'fab fa-facebook-f', '#1877f2'],
                        'instagram' => ['Instagram', 'fab fa-instagram', '#e4405f'],
                        'twitter' => ['Twitter', 'fab fa-twitter', '#1da1f2'],
                        'linkedin' => ['LinkedIn', 'fab fa-linkedin-in', '#0a66c2'],
                        'youtube' => ['YouTube', 'fab fa-youtube', '#ff0000']
                    ];
                    foreach ($social_icons as $key => $info): ?>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label"><?= $info[0] ?></label>
                                <div class="control has-icons-left">
                                    <input class="input" name="<?= $key ?>" value="<?= h($settings[$key] ?? '') ?>" placeholder="<?= $info[0] ?> URL">
                                    <span class="icon is-left" style="color: <?= $info[2] ?>;"><i class="<?= $info[1] ?>"></i></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($tab === 'translate'): ?>
    <div class="columns">
        <div class="column is-8">
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-language"></i></span>
                    前台翻译功能
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">启用网页翻译</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="translate_enabled">
                                        <option value="1" <?= ($settings['translate_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>启用</option>
                                        <option value="0" <?= ($settings['translate_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>禁用</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">根据浏览器语言自动翻译</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="translate_auto_browser">
                                        <option value="0" <?= ($settings['translate_auto_browser'] ?? '0') === '0' ? 'selected' : '' ?>>关闭</option>
                                        <option value="1" <?= ($settings['translate_auto_browser'] ?? '0') === '1' ? 'selected' : '' ?>>开启</option>
                                    </select>
                                </div>
                            </div>
                            <p class="help">仅在用户尚未手动选择语言时生效</p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">前台可选翻译语种</label>
                    <input type="hidden" name="translate_languages[]" value="en">
                    <div class="columns is-multiline">
                        <?php foreach ($translateLanguageOptions as $langCode => $langLabel): ?>
                            <div class="column is-4">
                                <label class="checkbox" style="display: inline-flex; align-items: center; gap: .5rem;">
                                    <input type="checkbox" name="translate_languages[]" value="<?= h($langCode) ?>" <?= in_array($langCode, $selectedTranslateLanguages, true) ? 'checked' : '' ?> <?= $langCode === 'en' ? 'disabled' : '' ?>>
                                    <span><?= h($langLabel) ?> <?= $langCode === 'en' ? '(默认原文)' : '' ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="help">English 固定保留，用于“英文不翻译（恢复原文）”。</p>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <div class="admin-card animate-in delay-2" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box info"><i class="fas fa-info"></i></span>
                    设置说明
                </div>
                <div class="content is-size-7 has-text-grey">
                    <p><strong>启用网页翻译</strong>：控制前台是否显示翻译下拉框。</p>
                    <p><strong>自动翻译</strong>：按浏览器语言自动切换（若不在可选语种内则保持英文）。</p>
                    <p><strong>可选语种</strong>：控制前台下拉框中的语言列表。</p>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($tab === 'custom'): ?>
    <div class="columns">
        <div class="column is-8">
            <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box primary"><i class="fas fa-code"></i></span>
                    自定义代码
                </div>
                <div class="field">
                    <label class="label">Head 自定义代码</label>
                    <div class="control">
                        <textarea class="textarea" name="head_code" rows="6" placeholder="例如：统计代码、验证代码、全站样式..."><?= h($settings['head_code'] ?? '') ?></textarea>
                    </div>
                    <p class="help">会插入到 &lt;head&gt; 末尾，请确保代码安全。</p>
                </div>
                <div class="field">
                    <label class="label">Footer 自定义代码</label>
                    <div class="control">
                        <textarea class="textarea" name="footer_code" rows="6" placeholder="例如：脚本、像素追踪代码..."><?= h($settings['footer_code'] ?? '') ?></textarea>
                    </div>
                    <p class="help">会插入到 &lt;/body&gt; 前。</p>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <div class="admin-card animate-in delay-2" style="padding: 1.5rem;">
                <div class="section-title" style="font-size: 1rem;">
                    <span class="icon-box info"><i class="fas fa-info"></i></span>
                    设置说明
                </div>
                <div class="content is-size-7 has-text-grey">
                    <p><strong>Head</strong>：用于统计/验证/全站样式等。</p>
                    <p><strong>Footer</strong>：用于脚本或追踪代码。</p>
                    <p>保存后会在前台模板中输出。</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-6 animate-in delay-3">
        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800" type="submit">
            <i class="fas fa-save text-xs"></i>
            <span>保存设置</span>
        </button>
    </div>
</form>
