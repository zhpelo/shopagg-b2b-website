<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); box-shadow: 0 10px 40px rgba(23, 162, 184, 0.3);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-photo-video"></i></span>
                    媒体库管理
                </h1>
                <p class="subtitle is-6">管理网站所有上传的图片和媒体文件</p>
            </div>
        </div>
        <div class="level-right header-actions">
            <div class="file is-white">
                <label class="file-label mb-0">
                    <input class="file-input" type="file" id="directUploadInput" multiple accept="image/*">
                    <span class="file-cta" style="border-radius: 8px; background: white; color: #17a2b8; border: none;">
                        <span class="file-icon"><i class="fas fa-upload"></i></span>
                        <span class="file-label">上传图片</span>
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>

<!-- 存储统计 -->
<div class="columns animate-in delay-1">
    <div class="column is-4">
        <div class="admin-card" style="padding: 1.5rem;">
            <div class="stat-mini">
                <div class="icon-box" style="background: var(--info-gradient);">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-info">
                    <div class="value"><?= number_format($total_count) ?></div>
                    <div class="label">图片总数</div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-4">
        <div class="admin-card" style="padding: 1.5rem;">
            <div class="stat-mini">
                <div class="icon-box" style="background: var(--success-gradient);">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-info">
                    <div class="value"><?= h($total_size_formatted) ?></div>
                    <div class="label">存储空间占用</div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-4">
        <div class="admin-card" style="padding: 1.5rem;">
            <div class="stat-mini">
                <div class="icon-box" style="background: var(--primary-gradient);">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-info">
                    <div class="value">/uploads/</div>
                    <div class="label">存储目录</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="notification is-success is-light animate-in">
    <button class="delete" onclick="this.parentElement.remove()"></button>
    <?= h($_GET['success']) ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="notification is-danger is-light animate-in">
    <button class="delete" onclick="this.parentElement.remove()"></button>
    <?= h($_GET['error']) ?>
</div>
<?php endif; ?>

<!-- 搜索和筛选 -->
<div class="admin-card mb-5 animate-in delay-2" style="padding: 1rem 1.5rem;">
    <div class="level">
        <div class="level-left">
            <div class="field has-addons mb-0">
                <div class="control has-icons-left">
                    <input class="input" type="text" id="searchInput" placeholder="搜索文件名..." style="width: 250px;">
                    <span class="icon is-left"><i class="fas fa-search"></i></span>
                </div>
            </div>
        </div>
        <div class="level-right">
            <div class="buttons mb-0">
                <button class="button is-light is-small view-btn is-active" data-view="grid" title="网格视图">
                    <span class="icon"><i class="fas fa-th"></i></span>
                </button>
                <button class="button is-light is-small view-btn" data-view="list" title="列表视图">
                    <span class="icon"><i class="fas fa-list"></i></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 图片列表 -->
<?php if (empty($files)): ?>
<div class="admin-card animate-in delay-3" style="padding: 4rem 2rem;">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-images"></i></span>
        <p>暂无上传的图片</p>
        <button type="button" class="button is-info mt-4" onclick="openMediaLibrary(function(urls){ location.reload(); }, true)">
            <span class="icon"><i class="fas fa-upload"></i></span>
            <span>上传第一张图片</span>
        </button>
    </div>
</div>
<?php else: ?>

<!-- 网格视图 -->
<div class="admin-card animate-in delay-3" style="padding: 1.5rem;" id="gridView">
    <div class="media-grid">
        <?php foreach ($files as $file): ?>
        <div class="media-grid-item" data-name="<?= h(strtolower($file['name'])) ?>">
            <div class="media-thumb" onclick="showMediaDetail(<?= htmlspecialchars(json_encode($file), ENT_QUOTES, 'UTF-8') ?>)">
                <img src="<?= h($file['path']) ?>" alt="<?= h($file['name']) ?>" loading="lazy">
                <div class="media-overlay">
                    <span class="icon"><i class="fas fa-search-plus"></i></span>
                </div>
            </div>
            <div class="media-info">
                <span class="media-name" title="<?= h($file['name']) ?>"><?= h($file['name']) ?></span>
                <span class="media-size"><?= h($file['size_formatted']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 列表视图 -->
<div class="admin-card animate-in delay-3" style="padding: 0; display: none;" id="listView">
    <div class="modern-table">
        <table class="table is-fullwidth is-hoverable">
            <thead>
                <tr>
                    <th style="width: 60px;">预览</th>
                    <th>文件名</th>
                    <th style="width: 100px;">尺寸</th>
                    <th style="width: 100px;">大小</th>
                    <th style="width: 150px;">上传时间</th>
                    <th style="width: 120px;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr data-name="<?= h(strtolower($file['name'])) ?>">
                    <td>
                        <figure class="image is-48x48" style="cursor: pointer;" onclick="showMediaDetail(<?= htmlspecialchars(json_encode($file), ENT_QUOTES, 'UTF-8') ?>)">
                            <img src="<?= h($file['path']) ?>" alt="<?= h($file['name']) ?>" style="object-fit: cover; border-radius: 6px;">
                        </figure>
                    </td>
                    <td>
                        <span class="has-text-weight-medium"><?= h($file['name']) ?></span>
                    </td>
                    <td>
                        <?php if ($file['width'] && $file['height']): ?>
                        <span class="tag is-light"><?= $file['width'] ?>×<?= $file['height'] ?></span>
                        <?php else: ?>
                        <span class="has-text-grey">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($file['size_formatted']) ?></td>
                    <td><?= h($file['date']) ?></td>
                    <td>
                        <div class="buttons are-small">
                            <button class="button is-info is-light" onclick="showMediaDetail(<?= htmlspecialchars(json_encode($file), ENT_QUOTES, 'UTF-8') ?>)" title="查看详情">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </button>
                            <button class="button is-primary is-light" onclick="copyToClipboard('<?= h($file['path']) ?>')" title="复制链接">
                                <span class="icon"><i class="fas fa-copy"></i></span>
                            </button>
                            <a class="button is-danger is-light" href="/admin/media/delete?path=<?= urlencode($file['path']) ?>" onclick="return confirm('确定要删除此图片吗？')" title="删除">
                                <span class="icon"><i class="fas fa-trash"></i></span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<!-- 图片详情弹窗 -->
<div class="modal" id="mediaDetailModal">
    <div class="modal-background" onclick="closeMediaDetail()"></div>
    <div class="modal-card" style="max-width: 800px; width: 95%;">
        <header class="modal-card-head">
            <p class="modal-card-title">
                <span class="icon mr-2"><i class="fas fa-image"></i></span>
                图片详情
            </p>
            <button class="delete" aria-label="close" onclick="closeMediaDetail()"></button>
        </header>
        <section class="modal-card-body" style="padding: 0;">
            <div class="columns is-gapless" style="margin: 0;">
                <div class="column is-7" style="display: flex; align-items: center; justify-content: center; min-height: 300px;">
                    <img id="detailImage" src="" alt="" style="max-width: 100%; max-height: 400px; object-fit: contain;">
                </div>
                <div class="column is-5" style="padding: 10px !important;">
                    <div class="mb-4">
                        <label class="label is-small has-text-grey">文件名</label>
                        <p id="detailName" class="has-text-weight-semibold"></p>
                    </div>
                    <div class="mb-4">
                        <label class="label is-small has-text-grey">图片尺寸</label>
                        <p id="detailDimensions"></p>
                    </div>
                    <div class="mb-4">
                        <label class="label is-small has-text-grey">文件大小</label>
                        <p id="detailSize"></p>
                    </div>
                    <div class="mb-4">
                        <label class="label is-small has-text-grey">上传时间</label>
                        <p id="detailDate"></p>
                    </div>
                    <div class="mb-4">
                        <label class="label is-small has-text-grey">图片路径</label>
                        <div class="field has-addons">
                            <div class="control is-expanded">
                                <input class="input is-small" type="text" id="detailPath" readonly>
                            </div>
                            <div class="control">
                                <button class="button is-info is-small" onclick="copyToClipboard(document.getElementById('detailPath').value)">
                                    <span class="icon"><i class="fas fa-copy"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <footer class="modal-card-foot" style="justify-content: space-between;">
            <a id="detailDeleteBtn" class="button is-danger" href="#" onclick="return confirm('确定要删除此图片吗？')">
                <span class="icon"><i class="fas fa-trash"></i></span>
                <span>删除图片</span>
            </a>
            <button class="button" onclick="closeMediaDetail()">关闭</button>
        </footer>
    </div>
</div>

<style>
/* 媒体网格样式 */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
}

.media-grid-item {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    background: white;
    transition: all 0.2s;
}

.media-grid-item:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.media-thumb {
    aspect-ratio: 1;
    background: #f5f5f5;
    position: relative;
    cursor: pointer;
    overflow: hidden;
}

.media-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.media-thumb:hover img {
    transform: scale(1.05);
}

.media-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.media-thumb:hover .media-overlay {
    opacity: 1;
}

.media-overlay .icon {
    color: white;
    font-size: 1.5rem;
}

.media-info {
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.media-name {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-size {
    font-size: 0.75rem;
    color: #9ca3af;
}

/* 视图切换按钮 */
.view-btn.is-active {
    background: var(--primary-gradient);
    color: white !important;
    border: none;
}

/* 响应式 */
@media screen and (max-width: 768px) {
    .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    #mediaDetailModal .columns {
        flex-direction: column;
    }
    
    #mediaDetailModal .column.is-7,
    #mediaDetailModal .column.is-5 {
        width: 100%;
    }
}
</style>

<script>
// 搜索功能
document.getElementById('searchInput').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('[data-name]').forEach(item => {
        const name = item.getAttribute('data-name');
        item.style.display = name.includes(query) ? '' : 'none';
    });
});

// 视图切换
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('is-active'));
        this.classList.add('is-active');
        
        const view = this.getAttribute('data-view');
        document.getElementById('gridView').style.display = view === 'grid' ? '' : 'none';
        document.getElementById('listView').style.display = view === 'list' ? '' : 'none';
    });
});

// 显示图片详情
function showMediaDetail(file) {
    document.getElementById('detailImage').src = file.path;
    document.getElementById('detailName').textContent = file.name;
    document.getElementById('detailDimensions').textContent = file.width && file.height ? file.width + ' × ' + file.height + ' 像素' : '未知';
    document.getElementById('detailSize').textContent = file.size_formatted;
    document.getElementById('detailDate').textContent = file.date;
    document.getElementById('detailPath').value = file.path;
    document.getElementById('detailDeleteBtn').href = '/admin/media/delete?path=' + encodeURIComponent(file.path);
    document.getElementById('mediaDetailModal').classList.add('is-active');
}

function closeMediaDetail() {
    document.getElementById('mediaDetailModal').classList.remove('is-active');
}

// 复制到剪贴板
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // 简单提示
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="icon"><i class="fas fa-check"></i></span>';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
        }, 1500);
    });
}

// ESC关闭弹窗
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMediaDetail();
});

// 直接上传图片
document.getElementById('directUploadInput').addEventListener('change', async function(e) {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;
    
    // 显示上传进度提示
    const uploadBtn = this.closest('.file');
    const originalHTML = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<span class="button is-white is-loading" style="border-radius: 8px;">上传中...</span>';
    
    let uploadedCount = 0;
    let failedCount = 0;
    
    for (const file of files) {
        try {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('csrf', '<?= csrf_token() ?>');
            
            const response = await fetch('/admin/upload-image', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                uploadedCount++;
            } else {
                failedCount++;
            }
        } catch (err) {
            failedCount++;
        }
    }
    
    // 恢复按钮
    uploadBtn.innerHTML = originalHTML;
    
    // 刷新页面显示新上传的图片
    if (uploadedCount > 0) {
        location.href = '/admin/media?success=' + encodeURIComponent('成功上传 ' + uploadedCount + ' 张图片' + (failedCount > 0 ? '，' + failedCount + ' 张失败' : ''));
    } else if (failedCount > 0) {
        location.href = '/admin/media?error=' + encodeURIComponent('上传失败');
    }
});
</script>

