<h1 class="title is-3">设置</h1>

<form method="post" action="/admin/settings">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

    <div class="box admin-card">
        <h2 class="title is-4">网站设置</h2>
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
        <h2 class="title is-4">SEO 设置</h2>
        <div class="field">
            <label class="label">SEO 标题 (Title)</label>
            <div class="control">
                <input class="input" name="seo_title" value="<?= h($settings['seo_title'] ?? '') ?>" placeholder="留空则显示网站名称">
            </div>
            <p class="help">建议 50-60 个字符</p>
        </div>
        <div class="field">
            <label class="label">SEO 关键词 (Keywords)</label>
            <div class="control">
                <input class="input" name="seo_keywords" value="<?= h($settings['seo_keywords'] ?? '') ?>" placeholder="关键词以英文逗号分隔">
            </div>
        </div>
        <div class="field">
            <label class="label">SEO 描述 (Description)</label>
            <div class="control">
                <textarea class="textarea" name="seo_description" rows="3" placeholder="建议 150-160 个字符"><?= h($settings['seo_description'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="field">
            <label class="label">OG Image (社交分享预览图)</label>
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input class="input" name="og_image" id="og_image" value="<?= h($settings['og_image'] ?? '') ?>" placeholder="输入图片 URL 或从媒体库选择">
                </div>
                <div class="control">
                    <button type="button" class="button is-info" onclick="openMediaLibrary('og_image')">
                        <span class="icon"><i class="fas fa-images"></i></span>
                        <span>选择图片</span>
                    </button>
                </div>
            </div>
            <p class="help">建议尺寸: 1200x630px</p>
        </div>
    </div>

    <div class="box admin-card">
        <h2 class="title is-4">公司信息</h2>
        <div class="field">
            <label class="label">公司简介</label>
            <div class="control">
                <textarea class="textarea" name="company_about" rows="4"><?= h($settings['company_about'] ?? '') ?></textarea>
            </div>
        </div>


        <div class="field">
            <label class="label">公司地址</label>
            <div class="control">
            <input class="input" name="company_address" value="<?= h($settings['company_address'] ?? '') ?>">
            </div>
        </div>
    </div>
    <div class="box admin-card">

        <h2 class="title is-4">联系方式</h2>

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
    </div>

    <div class="box admin-card">

        <h2 class="title is-4">社交媒体</h2>

        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">Facebook</label>
                    <div class="control"><input class="input" name="facebook" value="<?= h($settings['facebook'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Instagram</label>
                    <div class="control"><input class="input" name="instagram" value="<?= h($settings['instagram'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Twitter</label>
                    <div class="control"><input class="input" name="twitter" value="<?= h($settings['twitter'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">LinkedIn</label>
                    <div class="control"><input class="input" name="linkedin" value="<?= h($settings['linkedin'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">YouTube</label>
                    <div class="control"><input class="input" name="youtube" value="<?= h($settings['youtube'] ?? '') ?>"></div>
                </div>
            </div>
        </div>
    </div>

    <button class="button is-link" type="submit">保存设置</button>
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