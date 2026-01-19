<?php
$tabs = [
    'general' => '基础设置',
    'company' => '公司简介',
    'trade' => '贸易能力',
    'media' => '公司展示',
    'contact' => '联系方式'
];
?>

<div class="level">
    <div class="level-left">
        <h1 class="title is-4">系统设置</h1>
    </div>
</div>

<div class="tabs is-boxed mb-5">
    <ul>
        <?php foreach ($tabs as $key => $label): ?>
            <li class="<?= $tab === $key ? 'is-active' : '' ?>">
                <a href="/admin/settings?tab=<?= $key ?>"><?= $label ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<form method="post" action="/admin/settings">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="tab" value="<?= h($tab) ?>">

    <?php if ($tab === 'general'): ?>
    <div class="box admin-card">
            <h2 class="title is-5">网站基础设置</h2>
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
    </div>

    <div class="box admin-card">
            <h2 class="title is-5">SEO 设置</h2>
            <div class="field">
                <label class="label">SEO 标题 (Title)</label>
                <div class="control"><input class="input" name="seo_title" value="<?= h($settings['seo_title'] ?? '') ?>"></div>
            </div>
            <div class="field">
                <label class="label">SEO 关键词 (Keywords)</label>
                <div class="control"><input class="input" name="seo_keywords" value="<?= h($settings['seo_keywords'] ?? '') ?>"></div>
            </div>
            <div class="field">
                <label class="label">SEO 描述 (Description)</label>
                <div class="control"><textarea class="textarea" name="seo_description" rows="3"><?= h($settings['seo_description'] ?? '') ?></textarea></div>
            </div>
        <div class="field">
                <label class="label">OG Image</label>
                <div class="field has-addons">
                    <div class="control is-expanded"><input class="input" name="og_image" id="og_image" value="<?= h($settings['og_image'] ?? '') ?>"></div>
                    <div class="control"><button type="button" class="button is-info" onclick="openMediaLibrary('og_image')">选择</button></div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'company'): ?>
        <div class="box admin-card">
            <h2 class="title is-5">公司简介 (Profile)</h2>
            <div class="field">
                <label class="label">详细介绍</label>
                <div class="control"><textarea class="textarea" name="company_bio" rows="6"><?= h($settings['company_bio'] ?? '') ?></textarea></div>
            </div>
            <div class="columns is-multiline">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">业务类型</label>
                        <div class="control"><input class="input" name="company_business_type" value="<?= h($settings['company_business_type'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">主营产品</label>
                        <div class="control"><input class="input" name="company_main_products" value="<?= h($settings['company_main_products'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">成立年份</label>
                        <div class="control"><input class="input" name="company_year_established" value="<?= h($settings['company_year_established'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">员工人数</label>
                        <div class="control"><input class="input" name="company_employees" value="<?= h($settings['company_employees'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">厂房面积</label>
                        <div class="control"><input class="input" name="company_plant_area" value="<?= h($settings['company_plant_area'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">注册资本</label>
                        <div class="control"><input class="input" name="company_registered_capital" value="<?= h($settings['company_registered_capital'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">SGS 报告编号</label>
                        <div class="control"><input class="input" name="company_sgs_report" value="<?= h($settings['company_sgs_report'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">评分 (如 5.0/5)</label>
                        <div class="control"><input class="input" name="company_rating" value="<?= h($settings['company_rating'] ?? '5.0/5') ?>"></div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">平均响应时间</label>
                        <div class="control"><input class="input" name="company_response_time" value="<?= h($settings['company_response_time'] ?? '≤24h') ?>"></div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($tab === 'trade'): ?>
        <div class="box admin-card">
            <h2 class="title is-5">贸易能力 (Trade Capacity)</h2>
            <div class="field">
                <label class="label">主要市场</label>
                <div class="control"><input class="input" name="company_main_markets" value="<?= h($settings['company_main_markets'] ?? '') ?>"></div>
            </div>
            <div class="columns is-multiline">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">外贸人数</label>
                        <div class="control"><input class="input" name="company_trade_staff" value="<?= h($settings['company_trade_staff'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">贸易条款 (Incoterms)</label>
                        <div class="control"><input class="input" name="company_incoterms" value="<?= h($settings['company_incoterms'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">支付方式</label>
                        <div class="control"><input class="input" name="company_payment_terms" value="<?= h($settings['company_payment_terms'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label">平均交期</label>
                        <div class="control"><input class="input" name="company_lead_time" value="<?= h($settings['company_lead_time'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">海外分支</label>
                        <div class="control"><input class="input" name="company_overseas_agent" value="<?= h($settings['company_overseas_agent'] ?? 'No') ?>"></div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">出口开始年份</label>
                        <div class="control"><input class="input" name="company_export_year" value="<?= h($settings['company_export_year'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">最近港口</label>
                        <div class="control"><input class="input" name="company_nearest_port" value="<?= h($settings['company_nearest_port'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
            <hr>
            <h2 class="title is-5">研发能力 (R&D)</h2>
        <div class="field">
                <label class="label">研发工程师人数</label>
                <div class="control"><input class="input" name="company_rd_engineers" value="<?= h($settings['company_rd_engineers'] ?? '') ?>"></div>
            </div>
        </div>

    <?php elseif ($tab === 'media'): ?>
        <div class="box admin-card">
            <h2 class="title is-5">公司展示 (Show)</h2>
            <div id="company-show-container">
                <?php 
                $showItems = json_decode($settings['company_show_json'] ?? '[]', true);
                foreach ($showItems as $item): ?>
                    <div class="box mb-3 media-item-row">
                        <div class="columns">
                            <div class="column is-3">
                                <div class="field">
                                    <div class="control">
                                        <input class="input mb-2" name="show_img[]" value="<?= h($item['img']) ?>">
                                        <button type="button" class="button is-small is-info is-fullwidth" onclick="selectMedia(this)">选择图片</button>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-8">
                                <div class="field">
                                    <div class="control"><input class="input" name="show_title[]" value="<?= h($item['title']) ?>" placeholder="图片标题"></div>
                                </div>
                            </div>
                            <div class="column is-1">
                                <button type="button" class="button is-danger is-small" onclick="this.closest('.media-item-row').remove()">删除</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button is-link is-light is-small" onclick="addMediaRow('company-show-container', 'show_img', 'show_title')">新增项目</button>
    </div>

    <div class="box admin-card">
            <h2 class="title is-5">资质证书 (Certificates)</h2>
            <div id="certificates-container">
                <?php 
                $certItems = json_decode($settings['company_certificates_json'] ?? '[]', true);
                foreach ($certItems as $item): ?>
                    <div class="box mb-3 media-item-row">
                        <div class="columns">
                            <div class="column is-3">
                                <div class="field">
                                    <div class="control">
                                        <input class="input mb-2" name="cert_img[]" value="<?= h($item['img']) ?>">
                                        <button type="button" class="button is-small is-info is-fullwidth" onclick="selectMedia(this)">选择图片</button>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-8">
                                <div class="field">
                                    <div class="control"><input class="input" name="cert_title[]" value="<?= h($item['title']) ?>" placeholder="证书名称"></div>
                                </div>
                            </div>
                            <div class="column is-1">
                                <button type="button" class="button is-danger is-small" onclick="this.closest('.media-item-row').remove()">删除</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button is-link is-light is-small" onclick="addMediaRow('certificates-container', 'cert_img', 'cert_title')">新增证书</button>
        </div>

        <script>
        function addMediaRow(containerId, imgName, titleName) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'box mb-3 media-item-row';
            div.innerHTML = `
                <div class="columns">
                    <div class="column is-3">
                        <div class="field">
                            <div class="control">
                                <input class="input mb-2" name="${imgName}[]" value="">
                                <button type="button" class="button is-small is-info is-fullwidth" onclick="selectMedia(this)">选择图片</button>
                            </div>
                        </div>
                    </div>
                    <div class="column is-8">
                        <div class="field">
                            <div class="control"><input class="input" name="${titleName}[]" value="" placeholder="标题/名称"></div>
                        </div>
                    </div>
                    <div class="column is-1">
                        <button type="button" class="button is-danger is-small" onclick="this.closest('.media-item-row').remove()">删除</button>
                    </div>
                </div>
            `;
            container.appendChild(div);
        }
        function selectMedia(btn) {
            const input = btn.previousElementSibling;
            const id = 'temp_' + Math.random().toString(36).substr(2, 9);
            input.id = id;
            openMediaLibrary(id);
        }
        </script>

    <?php elseif ($tab === 'contact'): ?>
        <div class="box admin-card">
            <h2 class="title is-5">联系信息</h2>
        <div class="columns">
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
            <div class="column">
                <div class="field">
                    <label class="label">WhatsApp</label>
                    <div class="control"><input class="input" name="whatsapp" value="<?= h($settings['whatsapp'] ?? '') ?>"></div>
                </div>
            </div>
        </div>
            <div class="field">
                <label class="label">公司地址</label>
                <div class="control"><input class="input" name="company_address" value="<?= h($settings['company_address'] ?? '') ?>"></div>
            </div>
    </div>

    <div class="box admin-card">
            <h2 class="title is-5">社交媒体</h2>
            <div class="columns is-multiline">
                <?php foreach (['facebook', 'instagram', 'twitter', 'linkedin', 'youtube'] as $sm): ?>
                    <div class="column is-4">
                <div class="field">
                            <label class="label is-capitalized"><?= $sm ?></label>
                            <div class="control"><input class="input" name="<?= $sm ?>" value="<?= h($settings[$sm] ?? '') ?>"></div>
                        </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-5">
        <button class="button is-link is-large" type="submit">保存当前标签页设置</button>
    </div>
</form>

<!-- 媒体库模态框 -->
<div class="modal" id="media-library-modal">
    <div class="modal-background"></div>
    <div class="modal-card" style="width: 90%; max-width: 1000px;">
        <header class="modal-card-head">
            <p class="modal-card-title">选择媒体文件</p>
            <div class="field mb-0 mr-3">
                <div class="control">
                    <div class="file is-info is-small">
                        <label class="file-label">
                            <input class="file-input" type="file" id="media-upload-input" multiple accept="image/*">
                            <span class="file-cta">
                                <span class="file-icon"><i class="fas fa-upload"></i></span>
                                <span class="file-label">上传图片</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="delete close-modal" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <div id="upload-progress-container" class="mb-4 is-hidden">
                <div class="is-size-7 mb-1 is-flex is-justify-content-between">
                    <span id="upload-status-text">正在上传...</span>
                    <span id="upload-progress-percent">0%</span>
                </div>
                <progress id="overall-progress" class="progress is-info is-small" value="0" max="100">0%</progress>
            </div>
            <div id="media-library-list" class="columns is-multiline is-mobile">
                <!-- 动态加载图片列表 -->
            </div>
        </section>
        <footer class="modal-card-head" style="justify-content: flex-end;">
            <button type="button" class="button close-modal">取消</button>
        </footer>
                </div>
            </div>

<script>
let currentTargetInputId = '';

function openMediaLibrary(inputId) {
    currentTargetInputId = inputId;
    document.getElementById('media-library-modal').classList.add('is-active');
    fetchMediaLibrary();
}

async function fetchMediaLibrary() {
    const container = document.getElementById('media-library-list');
    container.innerHTML = '<div class="column is-12 has-text-centered p-6"><p>正在加载...</p></div>';
    try {
        const res = await fetch('/admin/media-library');
        const files = await res.json();
        container.innerHTML = '';
        files.forEach(file => {
            const col = document.createElement('div');
            col.className = 'column is-2-desktop is-3-tablet is-4-mobile';
            col.innerHTML = `
                <div class="card media-select-item" style="cursor: pointer; border: 2px solid transparent; border-radius: 6px; overflow:hidden;">
                    <div class="card-image">
                        <figure class="image is-1by1">
                            <img src="${file}" style="object-fit: cover;">
                        </figure>
        </div>
    </div>
            `;
            col.querySelector('.media-select-item').addEventListener('click', function() {
                document.getElementById(currentTargetInputId).value = file;
                document.getElementById('media-library-modal').classList.remove('is-active');
            });
            container.appendChild(col);
        });
    } catch (err) {
        container.innerHTML = '<div class="column is-12 has-text-centered p-6"><p class="has-text-danger">加载失败</p></div>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.close-modal, .modal-background').forEach(el => {
        el.addEventListener('click', () => {
            document.getElementById('media-library-modal').classList.remove('is-active');
        });
    });

    const uploadInput = document.getElementById('media-upload-input');
    const progressContainer = document.getElementById('upload-progress-container');
    const overallProgress = document.getElementById('overall-progress');
    const progressPercent = document.getElementById('upload-progress-percent');
    const statusText = document.getElementById('upload-status-text');

    if (uploadInput) {
        uploadInput.addEventListener('change', async function() {
            const files = Array.from(this.files);
            if (files.length === 0) return;
            if (files.length > 20) {
                alert('单次最多只能上传20张图片');
                this.value = '';
                return;
            }

            progressContainer.classList.remove('is-hidden');
            overallProgress.value = 0;
            progressPercent.innerText = '0%';
            statusText.innerText = `正在上传 ${files.length} 个文件...`;

            const fileProgresses = new Array(files.length).fill(0);

            const uploadTasks = files.map((file, index) => {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/admin/upload-image', true);
                    
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            fileProgresses[index] = e.loaded / e.total;
                            const totalProgress = fileProgresses.reduce((a, b) => a + b, 0) / files.length;
                            const percent = Math.round(totalProgress * 100);
                            overallProgress.value = percent;
                            progressPercent.innerText = percent + '%';
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status === 200) resolve();
                        else reject();
                    };

                    xhr.onerror = () => reject();

                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('csrf', '<?= csrf_token() ?>');
                    xhr.send(formData);
                });
            });

            try {
                await Promise.all(uploadTasks);
                statusText.innerText = '上传成功！';
                overallProgress.classList.remove('is-info');
                overallProgress.classList.add('is-success');
                setTimeout(() => {
                    progressContainer.classList.add('is-hidden');
                    overallProgress.classList.remove('is-success');
                    overallProgress.classList.add('is-info');
                    fetchMediaLibrary();
                }, 1000);
            } catch (err) {
                statusText.innerText = '部分文件上传失败';
                overallProgress.classList.remove('is-info');
                overallProgress.classList.add('is-danger');
                setTimeout(() => {
                    progressContainer.classList.add('is-hidden');
                    overallProgress.classList.remove('is-danger');
                    overallProgress.classList.add('is-info');
                    fetchMediaLibrary();
                }, 2000);
            }
            this.value = '';
        });
    }
});
</script>