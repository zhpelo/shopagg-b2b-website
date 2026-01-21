<?php
$tabs = [
    'general' => ['基础设置', 'cog'],
    'company' => ['公司简介', 'building'],
    'trade' => ['贸易能力', 'globe'],
    'media' => ['公司展示', 'images'],
    'contact' => ['联系方式', 'phone'],
    'translations' => ['语言翻译', 'language']
];
?>

<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); box-shadow: 0 10px 40px rgba(108, 117, 125, 0.3);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-cog"></i></span>
                    系统设置
                </h1>
                <p class="subtitle is-6">管理网站配置和公司信息</p>
            </div>
        </div>
    </div>
</div>

<!-- 选项卡 -->
<div class="modern-tabs animate-in delay-1" style="display: flex; flex-wrap: wrap;">
    <?php foreach ($tabs as $key => $info): ?>
        <a href="/admin/settings?tab=<?= $key ?>" class="<?= $tab === $key ? 'is-active' : '' ?>">
            <span class="icon is-small mr-1"><i class="fas fa-<?= $info[1] ?>"></i></span>
            <?= $info[0] ?>
        </a>
    <?php endforeach; ?>
</div>

<form method="post" action="/admin/settings" class="modern-form">
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
                            <label class="label">默认语言</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="default_lang">
                                        <?php foreach ($available_langs as $l): ?>
                                            <option value="<?= h($l) ?>" <?= ($settings['default_lang'] ?? 'en') === $l ? 'selected' : '' ?>>
                                                <?= $l === 'zh' ? '中文 (zh)' : ($l === 'en' ? 'English (en)' : strtoupper($l)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
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
                    <label class="label">OG Image</label>
                    <div class="field has-addons">
                        <div class="control is-expanded">
                            <input class="input" name="og_image" id="og_image" value="<?= h($settings['og_image'] ?? '') ?>" placeholder="选择社交分享图片">
                        </div>
                        <div class="control">
                            <button type="button" class="button is-info" onclick="openMediaLibrary(url => document.getElementById('og_image').value = url)">
                                <span class="icon"><i class="fas fa-image"></i></span>
                                <span>选择</span>
                            </button>
                        </div>
                    </div>
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
                        <div class="column is-3">
                            <div class="field mb-0">
                                <input class="input" name="show_img[]" value="<?= h($item['img']) ?>" placeholder="图片URL">
                                <button type="button" class="button is-small is-info is-fullwidth mt-2" onclick="selectMedia(this)" style="border-radius: 8px;">
                                    <span class="icon"><i class="fas fa-image"></i></span>
                                    <span>选择</span>
                                </button>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field mb-0">
                                <input class="input" name="show_title[]" value="<?= h($item['title']) ?>" placeholder="图片标题">
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
                        <div class="column is-3">
                            <div class="field mb-0">
                                <input class="input" name="cert_img[]" value="<?= h($item['img']) ?>" placeholder="图片URL">
                                <button type="button" class="button is-small is-info is-fullwidth mt-2" onclick="selectMedia(this)" style="border-radius: 8px;">
                                    <span class="icon"><i class="fas fa-image"></i></span>
                                    <span>选择</span>
                                </button>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field mb-0">
                                <input class="input" name="cert_title[]" value="<?= h($item['title']) ?>" placeholder="证书名称">
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

    <script>
    function addMediaRow(containerId, imgName, titleName) {
        const container = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'box mb-3 media-item-row';
        div.style.cssText = 'background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px;';
        div.innerHTML = `
            <div class="columns is-vcentered">
                <div class="column is-3">
                    <div class="field mb-0">
                        <input class="input" name="${imgName}[]" value="" placeholder="图片URL">
                        <button type="button" class="button is-small is-info is-fullwidth mt-2" onclick="selectMedia(this)" style="border-radius: 8px;">
                            <span class="icon"><i class="fas fa-image"></i></span>
                            <span>选择</span>
                        </button>
                    </div>
                </div>
                <div class="column">
                    <div class="field mb-0">
                        <input class="input" name="${titleName}[]" value="" placeholder="标题/名称">
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
    function selectMedia(btn) {
        const input = btn.closest('.field').querySelector('input');
        openMediaLibrary(function(url) {
            input.value = url;
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

    <?php elseif ($tab === 'translations'): ?>
    <div class="admin-card animate-in delay-2" style="padding: 2rem;">
        <div class="level mb-4">
            <div class="level-left">
                <div class="section-title mb-0">
                    <span class="icon-box primary"><i class="fas fa-language"></i></span>
                    编辑语言包: <?= h($current_edit_lang) ?>
                </div>
            </div>
            <div class="level-right">
                <div class="modern-tabs" style="margin-bottom: 0;">
                    <?php foreach ($available_langs as $l): ?>
                        <a href="/admin/settings?tab=translations&lang=<?= h($l) ?>" class="<?= $current_edit_lang === $l ? 'is-active' : '' ?>">
                            <?= $l === 'zh' ? '中文' : ($l === 'en' ? 'English' : strtoupper($l)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="edit_lang" value="<?= h($current_edit_lang) ?>">
        
        <div class="modern-table">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th style="width: 30%;">键名 (Key)</th>
                        <th>翻译内容 (Value)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($translations as $key => $val): ?>
                        <tr>
                            <td>
                                <code style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8125rem;"><?= h($key) ?></code>
                            </td>
                            <td>
                                <input class="input" name="t[<?= h($key) ?>]" value="<?= h($val) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-5 animate-in delay-3">
        <button class="button is-primary is-medium" type="submit">
            <span class="icon"><i class="fas fa-save"></i></span>
            <span>保存设置</span>
        </button>
    </div>
</form>
