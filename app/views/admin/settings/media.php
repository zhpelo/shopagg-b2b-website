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
