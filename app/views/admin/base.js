window.tailwind = window.tailwind || {};
window.tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', '"PingFang SC"', '"Hiragino Sans GB"', '"Microsoft YaHei"', '"Helvetica Neue"', 'sans-serif']
            },
            colors: {
                admin: {
                    primary: '#667eea',
                    primaryDark: '#4c51bf',
                    slate: '#0f172a'
                }
            },
            boxShadow: {
                admin: '0 12px 40px rgba(15, 23, 42, 0.12)'
            }
        }
    }
};

(function () {
    const root = document.documentElement;
    window.APP_BASE_PATH = root.dataset.appBasePath || '';
    window.ADMIN_CSRF_TOKEN = root.dataset.adminCsrf || '';

    const mediaLibraryState = {
        callback: null,
        multi: false,
        options: {
            type: 'image',
            returnObjects: false
        },
        directory: '',
        search: '',
        type: 'image',
        sort: 'date_desc',
        activeItem: null,
        currentStats: {
            item_count: 0,
            total_size_formatted: '0 B'
        },
        selectedItems: new Map(),
        view: 'list',
        expandedDirectories: new Set(['']),
        fetchTimer: null,
        lastPayload: null,
        history: [],
        historyIndex: -1
    };

    let currentExplorerFile = null;

    function escapeHtmlAttr(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function normalizeMediaSelectionUrl(url) {
        if (!url) {
            return '';
        }
        if (/^(https?:)?\/\//i.test(url) || url.startsWith('data:')) {
            return url;
        }
        return `${window.APP_BASE_PATH || ''}${url}`;
    }

    function rememberExpandedDirectory(directory) {
        mediaLibraryState.expandedDirectories.add('');
        if (!directory) {
            return;
        }

        const parts = String(directory).split('/').filter(Boolean);
        let current = '';
        parts.forEach((part) => {
            current = current ? `${current}/${part}` : part;
            mediaLibraryState.expandedDirectories.add(current);
        });
    }

    function mediaLibraryApiUrl() {
        const params = new URLSearchParams();
        if (mediaLibraryState.directory) params.set('dir', mediaLibraryState.directory);
        if (mediaLibraryState.search) params.set('search', mediaLibraryState.search);
        if (mediaLibraryState.type) params.set('type', mediaLibraryState.type);
        if (mediaLibraryState.sort) params.set('sort', mediaLibraryState.sort);
        return `${window.APP_BASE_PATH || ''}/admin/media-library?${params.toString()}`;
    }

    function renderLibraryStatus(message = '', type = 'info') {
        const status = document.getElementById('media-library-status');
        if (!status) {
            return;
        }

        if (!message) {
            status.className = 'media-library-status hidden';
            status.textContent = '';
            return;
        }

        status.className = `media-library-status is-${type}`;
        status.textContent = message;
    }

    function updateMediaDetails(item) {
        const emptyPanel = document.getElementById('media-library-details-empty');
        const detailsPanel = document.getElementById('media-library-details-panel');
        const preview = document.getElementById('media-library-details-preview');
        if (!emptyPanel || !detailsPanel || !preview) {
            return;
        }

        if (!item) {
            emptyPanel.classList.remove('hidden');
            detailsPanel.classList.add('hidden');
            preview.innerHTML = '';
            return;
        }

        emptyPanel.classList.add('hidden');
        detailsPanel.classList.remove('hidden');

        const itemUrl = normalizeMediaSelectionUrl(item.public_path || item.url || '');
        if (item.type === 'video') {
            preview.innerHTML = `<video controls playsinline preload="metadata"><source src="${escapeHtmlAttr(itemUrl)}"></video>`;
        } else {
            preview.innerHTML = `<img src="${escapeHtmlAttr(itemUrl)}" alt="${escapeHtmlAttr(item.name || '')}">`;
        }

        document.getElementById('media-details-title').textContent = item.title || item.name || '';
        document.getElementById('media-details-original').textContent = item.original_name || item.name || '';
        document.getElementById('media-details-storage').textContent = item.storage_name || '';
        document.getElementById('media-details-type').textContent = item.type === 'video' ? '视频' : (item.type === 'image' ? '图片' : '文件');
        document.getElementById('media-details-directory').textContent = item.directory ? `/uploads/${item.directory}` : '/uploads';
        document.getElementById('media-details-meta').textContent = [item.dimensions || '', item.size_formatted || item.size || ''].filter(Boolean).join(' · ') || '-';
        document.getElementById('media-details-date').textContent = item.date || '';
        document.getElementById('media-details-path').value = item.public_path || '';
    }

    function resolveMediaSelectionPayload(items) {
        if (mediaLibraryState.options.returnObjects) {
            return mediaLibraryState.multi ? items : (items[0] || null);
        }

        const urls = items.map((item) => item.public_path || item.url || '');
        return mediaLibraryState.multi ? urls : (urls[0] || '');
    }

    function updateMediaLibraryNavButtons() {
        const backButton = document.getElementById('media-library-back-btn');
        const forwardButton = document.getElementById('media-library-forward-btn');
        const upButton = document.getElementById('media-library-up-btn');

        if (backButton) {
            backButton.disabled = mediaLibraryState.historyIndex <= 0;
        }
        if (forwardButton) {
            forwardButton.disabled = mediaLibraryState.historyIndex >= mediaLibraryState.history.length - 1;
        }
        if (upButton) {
            upButton.disabled = !mediaLibraryState.directory;
        }
    }

    function updateMediaLibraryViewMode() {
        const listBtn = document.getElementById('media-library-view-list-btn');
        const tilesBtn = document.getElementById('media-library-view-tiles-btn');
        const list = document.getElementById('media-library-list');
        const header = document.getElementById('media-library-file-header');
        const label = document.getElementById('media-library-current-mode-label');

        if (listBtn) {
            listBtn.classList.toggle('bg-indigo-50', mediaLibraryState.view === 'list');
            listBtn.classList.toggle('text-indigo-600', mediaLibraryState.view === 'list');
            listBtn.classList.toggle('text-slate-500', mediaLibraryState.view !== 'list');
            listBtn.classList.toggle('hover:bg-white', mediaLibraryState.view !== 'list');
            listBtn.classList.toggle('hover:text-slate-700', mediaLibraryState.view !== 'list');
        }
        if (tilesBtn) {
            tilesBtn.classList.toggle('bg-indigo-50', mediaLibraryState.view === 'tiles');
            tilesBtn.classList.toggle('text-indigo-600', mediaLibraryState.view === 'tiles');
            tilesBtn.classList.toggle('text-slate-500', mediaLibraryState.view !== 'tiles');
            tilesBtn.classList.toggle('hover:bg-white', mediaLibraryState.view !== 'tiles');
            tilesBtn.classList.toggle('hover:text-slate-700', mediaLibraryState.view !== 'tiles');
        }
        if (list) list.classList.toggle('is-tiles', mediaLibraryState.view === 'tiles');
        if (header) header.classList.toggle('hidden', mediaLibraryState.view === 'tiles');
        if (label) label.textContent = mediaLibraryState.view === 'tiles' ? '缩略图视图' : '列表视图';
    }

    function updateMediaLibraryBatchControls() {
        const count = mediaLibraryState.selectedItems.size;
        const deleteBtn = document.getElementById('media-library-bulk-delete-btn');
        const deleteLabel = document.getElementById('media-library-bulk-delete-label');
        const confirmBtn = document.getElementById('confirm-media-selection');
        const currentStats = mediaLibraryState.currentStats || {};

        if (deleteBtn && deleteLabel) {
            deleteBtn.disabled = count === 0;
            deleteLabel.textContent = count > 0 ? `删除 (${count})` : '删除';
        }

        const selectedCount = document.getElementById('media-library-selected-count');
        if (selectedCount) {
            selectedCount.textContent = `已选择 ${count} 个文件`;
        }

        const currentCount = document.getElementById('media-library-current-count');
        if (currentCount) {
            currentCount.textContent = `${currentStats.folder_count || 0} 个文件夹，${currentStats.file_count || 0} 个文件`;
        }

        const currentSize = document.getElementById('media-library-current-size');
        if (currentSize) {
            currentSize.textContent = `文件总大小 ${currentStats.total_size_formatted || '0 B'}`;
        }

        if (confirmBtn) {
            confirmBtn.disabled = count === 0;
            confirmBtn.textContent = mediaLibraryState.multi ? (count > 0 ? `插入所选 (${count})` : '插入所选') : '插入';
        }

        const toggleAll = document.getElementById('media-library-toggle-all');
        if (toggleAll) {
            const files = Array.isArray(mediaLibraryState.lastPayload?.files) ? mediaLibraryState.lastPayload.files : [];
            toggleAll.disabled = !mediaLibraryState.multi;
            toggleAll.checked = mediaLibraryState.multi && files.length > 0 && count === files.length;
            toggleAll.indeterminate = mediaLibraryState.multi && count > 0 && files.length > 0 && count < files.length;
        }
    }

    function renderMediaLibraryTree(nodes, currentDirectory) {
        const renderNodes = (items) => {
            if (!items.length) {
                return '';
            }

            return `
<ul class="media-library-tree-list">
${items.map((node) => `
<li class="media-library-tree-node ${node.is_current ? 'is-current' : ''} ${node.is_ancestor ? 'is-ancestor' : ''}">
<div class="media-library-tree-row">
<button type="button" class="media-library-tree-toggle ${node.children && node.children.length ? '' : 'is-empty'} ${mediaLibraryState.expandedDirectories.has(node.directory || '') ? 'is-expanded' : ''}" data-directory="${escapeHtmlAttr(node.directory || '')}">
<span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-caret-right"></i></span>
</button>
<a href="#" class="js-media-library-tree-link media-library-tree-link" data-directory="${escapeHtmlAttr(node.directory || '')}">
<span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-folder"></i></span>
<span>${escapeHtmlAttr(node.name || '')}</span>
</a>
</div>
<div class="media-library-tree-children ${mediaLibraryState.expandedDirectories.has(node.directory || '') ? 'is-open' : 'is-collapsed'}">
${renderNodes(node.children || [])}
</div>
</li>
`).join('')}
</ul>
`;
        };

        return `
<a href="#" class="js-media-library-tree-link media-library-tree-root ${currentDirectory === '' ? 'is-current' : ''}" data-directory="">
<span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-hdd"></i></span>
<span>我的媒体</span>
</a>
${renderNodes(nodes || [])}
`;
    }

    function renderMediaLibrarySelection() {
        document.querySelectorAll('.js-media-library-item').forEach((element) => {
            const path = element.dataset.path || '';
            element.classList.toggle('is-selected', mediaLibraryState.selectedItems.has(path));
            element.classList.toggle('is-active', !!(mediaLibraryState.activeItem && mediaLibraryState.activeItem.public_path === path));
            const checkbox = element.querySelector('.js-media-library-selector');
            if (checkbox) {
                checkbox.checked = mediaLibraryState.selectedItems.has(path);
            }
        });
        updateMediaLibraryBatchControls();
    }

    function selectMediaItem(item, immediate = false) {
        mediaLibraryState.activeItem = item;
        updateMediaDetails(item);

        if (mediaLibraryState.multi) {
            if (mediaLibraryState.selectedItems.has(item.public_path)) {
                mediaLibraryState.selectedItems.delete(item.public_path);
            } else {
                mediaLibraryState.selectedItems.set(item.public_path, item);
            }
            renderMediaLibrarySelection();
            return;
        }

        mediaLibraryState.selectedItems = new Map([[item.public_path, item]]);
        renderMediaLibrarySelection();

        if (immediate && typeof mediaLibraryState.callback === 'function') {
            mediaLibraryState.callback(resolveMediaSelectionPayload([item]));
            closeMediaLibraryModal();
        }
    }

    function activateMediaItem(item) {
        if (!item) {
            return;
        }

        mediaLibraryState.selectedItems = new Map([[item.public_path, item]]);
        renderMediaLibrarySelection();

        if (typeof mediaLibraryState.callback === 'function') {
            mediaLibraryState.callback(resolveMediaSelectionPayload([item]));
            closeMediaLibraryModal();
        }
    }

    function renderMediaLibrary(payload) {
        const container = document.getElementById('media-library-list');
        const breadcrumbsContainer = document.getElementById('media-library-breadcrumbs');
        const directoryLabel = document.getElementById('media-library-directory-label');
        const treeContainer = document.getElementById('media-library-tree');

        if (!container || !breadcrumbsContainer || !directoryLabel || !treeContainer) {
            return;
        }

        mediaLibraryState.lastPayload = payload;
        mediaLibraryState.currentStats = payload.current_stats || {
            item_count: 0,
            total_size_formatted: '0 B'
        };
        directoryLabel.textContent = payload.current_directory_label || '/uploads';
        breadcrumbsContainer.innerHTML = (payload.breadcrumbs || []).map((crumb, index, arr) => {
            const active = index === arr.length - 1;
            const label = escapeHtmlAttr(crumb.name || '');
            const separator = index > 0 ? '<span class="text-slate-300">/</span>' : '';
            if (active) {
                return `${separator}<span class="font-semibold text-slate-900" aria-current="page">${label}</span>`;
            }
            return `${separator}<a href="#" data-directory="${escapeHtmlAttr(crumb.directory || '')}" class="js-media-breadcrumb transition hover:text-slate-700">${label}</a>`;
        }).join('');

        const folders = payload.folders || [];
        const files = payload.files || [];
        container.innerHTML = '';
        container.classList.toggle('is-tiles', mediaLibraryState.view === 'tiles');
        treeContainer.innerHTML = renderMediaLibraryTree(payload.folder_tree || [], payload.current_directory || '');
        mediaLibraryState.selectedItems = new Map(Array.from(mediaLibraryState.selectedItems.entries()).filter(([path]) => files.some((item) => item.public_path === path)));

        if (mediaLibraryState.activeItem && !files.some((item) => item.public_path === mediaLibraryState.activeItem.public_path)) {
            mediaLibraryState.activeItem = null;
            updateMediaDetails(null);
        }

        updateMediaLibraryViewMode();

        if (!folders.length && !files.length) {
            container.innerHTML = `
<div class="media-library-empty">
<span class="inline-flex h-8 w-8 items-center justify-center text-2xl"><i class="fas fa-photo-video"></i></span>
<p class="mt-3">这个目录下还没有符合条件的媒体文件。</p>
</div>
`;
            updateMediaDetails(null);
            updateMediaLibraryBatchControls();
            return;
        }

        if (mediaLibraryState.view === 'tiles') {
            folders.forEach((folder) => {
                const tile = document.createElement('button');
                tile.type = 'button';
                tile.className = 'media-library-folder-tile media-library-folder-tile-explorer';
                tile.innerHTML = `
<div class="media-library-folder-tile-icon">
<span class="inline-flex h-8 w-8 items-center justify-center text-2xl"><i class="fas fa-folder"></i></span>
</div>
<div class="media-library-tile-body">
<strong title="${escapeHtmlAttr(folder.name || '')}">${escapeHtmlAttr(folder.name || '')}</strong>
<span>${Number(folder.item_count || 0)} 个项目</span>
<span>${escapeHtmlAttr(folder.directory ? `/uploads/${folder.directory}` : '/uploads')}</span>
</div>
`;
                tile.addEventListener('click', () => {
                    mediaLibraryState.selectedItems.clear();
                    mediaLibraryState.activeItem = null;
                    updateMediaDetails(null);
                    renderMediaLibrarySelection();
                });
                tile.addEventListener('dblclick', () => {
                    setMediaLibraryDirectory(folder.directory || '');
                });
                container.appendChild(tile);
            });

            files.forEach((file) => {
                const tile = document.createElement('button');
                tile.type = 'button';
                tile.className = 'media-library-tile js-media-library-item media-library-file-tile';
                tile.dataset.path = file.public_path || '';

                const itemUrl = normalizeMediaSelectionUrl(file.public_path || file.url || '');
                const thumbHtml = file.type === 'video'
                    ? `<div class="media-library-thumb"><video preload="metadata"><source src="${escapeHtmlAttr(itemUrl)}"></video><span class="media-library-video-badge"><i class="fas fa-play"></i></span></div>`
                    : `<div class="media-library-thumb"><img src="${escapeHtmlAttr(itemUrl)}" alt="${escapeHtmlAttr(file.name || '')}" loading="lazy"></div>`;

                tile.innerHTML = `
<label class="media-library-select-toggle">
<input type="checkbox" class="js-media-library-selector" value="${escapeHtmlAttr(file.public_path || '')}">
<span>选择</span>
</label>
${thumbHtml}
<div class="media-library-tile-body">
<strong title="${escapeHtmlAttr(file.original_name || file.name || '')}">${escapeHtmlAttr(file.original_name || file.name || '')}</strong>
<span>${file.type === 'video' ? '视频' : '图片'} · ${escapeHtmlAttr(file.size_formatted || '')}</span>
<span title="${escapeHtmlAttr(file.directory ? `/uploads/${file.directory}` : '/uploads')}">${escapeHtmlAttr(file.directory ? `/uploads/${file.directory}` : '/uploads')}</span>
</div>
`;

                tile.addEventListener('click', () => {
                    selectMediaItem(file, false);
                });
                tile.addEventListener('dblclick', () => {
                    activateMediaItem(file);
                });

                const selector = tile.querySelector('.js-media-library-selector');
                if (selector) {
                    selector.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                    selector.addEventListener('change', (event) => {
                        event.stopPropagation();
                        mediaLibraryState.activeItem = file;
                        updateMediaDetails(file);
                        if (!mediaLibraryState.multi) {
                            mediaLibraryState.selectedItems = selector.checked ? new Map([[file.public_path, file]]) : new Map();
                        } else if (selector.checked) {
                            mediaLibraryState.selectedItems.set(file.public_path, file);
                        } else {
                            mediaLibraryState.selectedItems.delete(file.public_path);
                        }
                        renderMediaLibrarySelection();
                    });
                }

                container.appendChild(tile);
            });

            renderMediaLibrarySelection();
            return;
        }

        folders.forEach((folder) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'media-library-folder-row';
            row.innerHTML = `
<div class="media-library-file-col check"></div>
<div class="media-library-file-col name">
<span class="media-library-row-icon folder"><i class="fas fa-folder"></i></span>
<div class="media-library-row-name">
<strong>${escapeHtmlAttr(folder.name || '')}</strong>
<span>${Number(folder.item_count || 0)} 个项目</span>
</div>
</div>
<div class="media-library-file-col type">文件夹</div>
<div class="media-library-file-col size">-</div>
<div class="media-library-file-col date">${Number(folder.item_count || 0)} 项</div>
`;
            row.addEventListener('click', () => {
                mediaLibraryState.selectedItems.clear();
                mediaLibraryState.activeItem = null;
                updateMediaDetails(null);
                renderMediaLibrarySelection();
            });
            row.addEventListener('dblclick', () => {
                setMediaLibraryDirectory(folder.directory || '');
            });
            container.appendChild(row);
        });

        files.forEach((file) => {
            const element = document.createElement('button');
            element.type = 'button';
            element.className = 'media-library-file-row js-media-library-item';
            element.dataset.path = file.public_path || '';

            const itemUrl = normalizeMediaSelectionUrl(file.public_path || file.url || '');
            const iconHtml = file.type === 'video'
                ? `<span class="media-library-row-icon video"><i class="fas fa-video"></i></span>`
                : `<span class="media-library-row-icon image"><img src="${escapeHtmlAttr(itemUrl)}" alt="${escapeHtmlAttr(file.name || '')}" loading="lazy"></span>`;

            element.innerHTML = `
<div class="media-library-file-col check">
<label class="media-library-select-toggle">
<input type="checkbox" class="js-media-library-selector" value="${escapeHtmlAttr(file.public_path || '')}">
</label>
</div>
<div class="media-library-file-col name">
${iconHtml}
<div class="media-library-row-name">
<strong title="${escapeHtmlAttr(file.original_name || file.name || '')}">${escapeHtmlAttr(file.original_name || file.name || '')}</strong>
<span>存储名：${escapeHtmlAttr(file.storage_name || '')}</span>
</div>
</div>
<div class="media-library-file-col type">${file.type === 'video' ? '视频' : '图片'}</div>
<div class="media-library-file-col size">${escapeHtmlAttr(file.size_formatted || '')}</div>
<div class="media-library-file-col date">${escapeHtmlAttr(file.date || '')}</div>
`;

            element.addEventListener('click', () => {
                selectMediaItem(file, false);
            });
            element.addEventListener('dblclick', () => {
                activateMediaItem(file);
            });

            const selector = element.querySelector('.js-media-library-selector');
            if (selector) {
                selector.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
                selector.addEventListener('change', (event) => {
                    event.stopPropagation();
                    mediaLibraryState.activeItem = file;
                    updateMediaDetails(file);
                    if (!mediaLibraryState.multi) {
                        mediaLibraryState.selectedItems = selector.checked ? new Map([[file.public_path, file]]) : new Map();
                    } else if (selector.checked) {
                        mediaLibraryState.selectedItems.set(file.public_path, file);
                    } else {
                        mediaLibraryState.selectedItems.delete(file.public_path);
                    }
                    renderMediaLibrarySelection();
                });
            }

            container.appendChild(element);
        });

        breadcrumbsContainer.querySelectorAll('.js-media-breadcrumb').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                setMediaLibraryDirectory(link.dataset.directory || '');
            });
        });

        treeContainer.querySelectorAll('.js-media-library-tree-link').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                setMediaLibraryDirectory(link.dataset.directory || '');
            });
        });

        treeContainer.querySelectorAll('.media-library-tree-toggle').forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const directory = toggle.dataset.directory || '';
                if (toggle.classList.contains('is-empty')) {
                    return;
                }
                if (mediaLibraryState.expandedDirectories.has(directory)) {
                    mediaLibraryState.expandedDirectories.delete(directory);
                } else {
                    rememberExpandedDirectory(directory);
                }
                renderMediaLibrary(mediaLibraryState.lastPayload || payload);
            });
        });

        renderMediaLibrarySelection();
    }

    async function fetchMediaLibrary() {
        const container = document.getElementById('media-library-list');
        if (!container) {
            return;
        }

        renderLibraryStatus('');
        container.innerHTML = `
<div class="media-library-empty">
<span class="inline-flex h-8 w-8 items-center justify-center text-2xl"><i class="fas fa-spinner fa-pulse"></i></span>
<p class="mt-3">正在加载媒体库...</p>
</div>
`;

        try {
            const res = await fetch(mediaLibraryApiUrl(), {
                headers: {
                    Accept: 'application/json'
                }
            });
            const payload = await res.json();
            if (!res.ok || payload.success === false) {
                throw new Error(payload?.data?.messages?.[0] || '媒体库加载失败');
            }
            renderMediaLibrary(payload);
        } catch (err) {
            container.innerHTML = `
<div class="media-library-empty text-rose-600">
<span class="inline-flex h-8 w-8 items-center justify-center text-2xl"><i class="fas fa-exclamation-circle"></i></span>
<p class="mt-3">${escapeHtmlAttr(err.message || '加载失败')}</p>
</div>
`;
            updateMediaDetails(null);
        }
    }

    function setMediaLibraryDirectory(directory, options = {}) {
        const normalized = directory || '';
        const pushHistory = options.pushHistory !== false;

        mediaLibraryState.directory = normalized;
        rememberExpandedDirectory(normalized);
        if (pushHistory) {
            mediaLibraryState.history = mediaLibraryState.history.slice(0, mediaLibraryState.historyIndex + 1);
            mediaLibraryState.history.push(normalized);
            mediaLibraryState.historyIndex = mediaLibraryState.history.length - 1;
        }

        updateMediaLibraryNavButtons();
        fetchMediaLibrary();
    }

    function closeMediaLibraryModal() {
        const modal = document.getElementById('media-library-modal');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        const scrollbarWidth = document.body.dataset.mediaLibraryScrollbarWidth || '';
        document.documentElement.classList.remove('media-library-modal-open');
        document.body.classList.remove('media-library-modal-open');
        document.body.style.paddingRight = '';

        if (scrollbarWidth !== '') {
            delete document.body.dataset.mediaLibraryScrollbarWidth;
        }
    }

    function openMediaLibrary(callback, multi = false, options = {}) {
        mediaLibraryState.callback = callback;
        mediaLibraryState.multi = multi;
        mediaLibraryState.options = {
            type: options.type || 'image',
            returnObjects: !!options.returnObjects
        };
        mediaLibraryState.directory = options.directory || '';
        mediaLibraryState.search = '';
        mediaLibraryState.type = options.type || 'image';
        mediaLibraryState.sort = options.sort || 'date_desc';
        mediaLibraryState.activeItem = null;
        mediaLibraryState.selectedItems = new Map();
        mediaLibraryState.currentStats = {
            item_count: 0,
            total_size_formatted: '0 B'
        };
        mediaLibraryState.view = options.view || 'list';
        mediaLibraryState.expandedDirectories = new Set(['']);
        mediaLibraryState.lastPayload = null;
        rememberExpandedDirectory(mediaLibraryState.directory);
        mediaLibraryState.history = [mediaLibraryState.directory];
        mediaLibraryState.historyIndex = 0;

        const modal = document.getElementById('media-library-modal');
        const searchInput = document.getElementById('media-library-search');
        const typeFilter = document.getElementById('media-library-type-filter');
        const sortFilter = document.getElementById('media-library-sort-filter');
        const confirmBtn = document.getElementById('confirm-media-selection');

        if (!modal) {
            return;
        }

        if (searchInput) {
            searchInput.value = '';
        }
        if (typeFilter) {
            typeFilter.value = mediaLibraryState.type;
        }
        if (sortFilter) {
            sortFilter.value = mediaLibraryState.sort;
        }
        if (confirmBtn) {
            confirmBtn.classList.remove('hidden');
            confirmBtn.classList.add('inline-flex');
            confirmBtn.disabled = true;
            confirmBtn.textContent = multi ? '插入所选' : '插入';
        }

        const scrollbarWidth = Math.max(0, window.innerWidth - document.documentElement.clientWidth);
        document.body.dataset.mediaLibraryScrollbarWidth = String(scrollbarWidth);
        document.documentElement.classList.add('media-library-modal-open');
        document.body.classList.add('media-library-modal-open');
        document.body.style.paddingRight = scrollbarWidth > 0 ? `${scrollbarWidth}px` : '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        updateMediaLibraryNavButtons();
        updateMediaLibraryViewMode();
        fetchMediaLibrary();
    }

    function insertMediaIntoEditor(editor, selection) {
        const items = Array.isArray(selection) ? selection : [selection];
        items.filter(Boolean).forEach((item) => {
            const url = normalizeMediaSelectionUrl(item.public_path || item.url || '');
            if (!url) {
                return;
            }

            if (item.type === 'video') {
                editor.s.insertHTML(`<p><video controls playsinline src="${escapeHtmlAttr(url)}"></video></p>`);
                return;
            }

            if (item.type === 'image') {
                editor.s.insertHTML(`<p><img src="${escapeHtmlAttr(url)}" alt="${escapeHtmlAttr(item.title || item.original_name || '')}"></p>`);
                return;
            }

            editor.s.insertHTML(`<p><a href="${escapeHtmlAttr(url)}" target="_blank" rel="noopener">${escapeHtmlAttr(item.title || item.original_name || url)}</a></p>`);
        });
    }


    function initGlobalActions() {
        document.addEventListener('click', (event) => {
            const dismissButton = event.target.closest('[data-dismiss-parent]');
            if (dismissButton) {
                dismissButton.parentElement?.remove();
                return;
            }

            const historyButton = event.target.closest('[data-history-nav]');
            if (historyButton) {
                if (historyButton.dataset.historyNav === 'back') {
                    window.history.back();
                } else if (historyButton.dataset.historyNav === 'forward') {
                    window.history.forward();
                }
                return;
            }

            const copyPathButton = event.target.closest('[data-copy-path]');
            if (copyPathButton) {
                copyMediaPath(copyPathButton.dataset.copyPath || '');
                return;
            }

            const copyTargetButton = event.target.closest('[data-copy-target]');
            if (copyTargetButton) {
                const target = document.querySelector(copyTargetButton.dataset.copyTarget || '');
                copyMediaPath(target?.value || '');
                return;
            }

            const deletePathButton = event.target.closest('[data-delete-path]');
            if (deletePathButton) {
                submitSingleMediaDelete(deletePathButton.dataset.deletePath || '');
            }
        });

        document.addEventListener('click', (event) => {
            const confirmTarget = event.target.closest('[data-confirm-message]');
            if (!confirmTarget) {
                return;
            }
            if (!window.confirm(confirmTarget.dataset.confirmMessage || '确定继续吗？')) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);
    }

    function initNavToggle() {
        document.querySelectorAll('[data-nav-toggle]').forEach((el) => {
            el.addEventListener('click', () => {
                const target = el.dataset.target;
                const targetEl = document.getElementById(target);
                if (!targetEl) {
                    return;
                }
                const isHidden = targetEl.classList.contains('hidden');
                el.classList.toggle('is-active');
                el.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                targetEl.classList.toggle('hidden', !isHidden);
                if (window.innerWidth < 1024) {
                    targetEl.classList.toggle('flex', isHidden);
                }
            });
        });
    }

    function initMediaLibraryModal() {
        const modal = document.getElementById('media-library-modal');
        const confirmBtn = document.getElementById('confirm-media-selection');
        if (!modal || !confirmBtn) {
            return;
        }

        modal.querySelectorAll('[data-media-modal-close]').forEach((btn) => {
            btn.addEventListener('click', () => closeMediaLibraryModal());
        });

        confirmBtn.addEventListener('click', () => {
            const selected = Array.from(mediaLibraryState.selectedItems.values());
            if (!selected.length) {
                return;
            }
            if (typeof mediaLibraryState.callback === 'function') {
                mediaLibraryState.callback(resolveMediaSelectionPayload(selected));
            }
            closeMediaLibraryModal();
        });

        document.getElementById('media-library-view-list-btn')?.addEventListener('click', () => {
            mediaLibraryState.view = 'list';
            updateMediaLibraryViewMode();
            if (mediaLibraryState.lastPayload) {
                renderMediaLibrary(mediaLibraryState.lastPayload);
            }
        });

        document.getElementById('media-library-view-tiles-btn')?.addEventListener('click', () => {
            mediaLibraryState.view = 'tiles';
            updateMediaLibraryViewMode();
            if (mediaLibraryState.lastPayload) {
                renderMediaLibrary(mediaLibraryState.lastPayload);
            }
        });

        document.getElementById('media-library-toggle-all')?.addEventListener('change', function () {
            if (!mediaLibraryState.multi) {
                this.checked = false;
                return;
            }
            const files = Array.isArray(mediaLibraryState.lastPayload?.files) ? mediaLibraryState.lastPayload.files : [];
            if (this.checked) {
                mediaLibraryState.selectedItems = new Map(files.map((item) => [item.public_path, item]));
                if (!mediaLibraryState.activeItem && files[0]) {
                    mediaLibraryState.activeItem = files[0];
                    updateMediaDetails(files[0]);
                }
            } else {
                mediaLibraryState.selectedItems.clear();
                mediaLibraryState.activeItem = null;
                updateMediaDetails(null);
            }
            renderMediaLibrarySelection();
        });

        document.getElementById('media-details-copy-btn')?.addEventListener('click', () => {
            const path = document.getElementById('media-details-path')?.value || '';
            if (!path) {
                return;
            }
            const fullUrl = toFullUrl(path);
            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText(fullUrl).then(() => {
                    renderLibraryStatus('路径已复制到剪贴板', 'success');
                    window.setTimeout(() => renderLibraryStatus(''), 1600);
                }).catch(() => {
                    fallbackCopyText(fullUrl);
                });
            } else {
                fallbackCopyText(fullUrl);
            }
        });

        document.getElementById('media-library-search')?.addEventListener('input', function () {
            mediaLibraryState.search = this.value.trim();
            window.clearTimeout(mediaLibraryState.fetchTimer);
            mediaLibraryState.fetchTimer = window.setTimeout(fetchMediaLibrary, 250);
        });

        document.getElementById('media-library-type-filter')?.addEventListener('change', function () {
            mediaLibraryState.type = this.value;
            fetchMediaLibrary();
        });

        document.getElementById('media-library-sort-filter')?.addEventListener('change', function () {
            mediaLibraryState.sort = this.value;
            fetchMediaLibrary();
        });

        document.getElementById('media-library-back-btn')?.addEventListener('click', () => {
            if (mediaLibraryState.historyIndex <= 0) {
                return;
            }
            mediaLibraryState.historyIndex -= 1;
            mediaLibraryState.directory = mediaLibraryState.history[mediaLibraryState.historyIndex] || '';
            updateMediaLibraryNavButtons();
            fetchMediaLibrary();
        });

        document.getElementById('media-library-forward-btn')?.addEventListener('click', () => {
            if (mediaLibraryState.historyIndex >= mediaLibraryState.history.length - 1) {
                return;
            }
            mediaLibraryState.historyIndex += 1;
            mediaLibraryState.directory = mediaLibraryState.history[mediaLibraryState.historyIndex] || '';
            updateMediaLibraryNavButtons();
            fetchMediaLibrary();
        });

        document.getElementById('media-library-up-btn')?.addEventListener('click', () => {
            if (!mediaLibraryState.directory) {
                return;
            }
            const parts = mediaLibraryState.directory.split('/').filter(Boolean);
            parts.pop();
            setMediaLibraryDirectory(parts.join('/'));
        });

        document.getElementById('media-library-refresh-btn')?.addEventListener('click', () => {
            fetchMediaLibrary();
        });

        document.getElementById('media-library-new-folder-btn')?.addEventListener('click', async () => {
            const name = window.prompt('请输入新目录名称');
            if (!name) {
                return;
            }

            const formData = new FormData();
            formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
            formData.append('dir', mediaLibraryState.directory || '');
            formData.append('folder_name', name.trim());
            formData.append('response_format', 'json');

            try {
                const response = await fetch((window.APP_BASE_PATH || '') + '/admin/media/folder/create', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const payload = await response.json();
                if (!response.ok || payload.success === false) {
                    throw new Error(payload?.data?.messages?.[0] || '创建目录失败');
                }
                renderLibraryStatus('目录已创建', 'success');
                fetchMediaLibrary();
            } catch (error) {
                renderLibraryStatus(error.message || '创建目录失败', 'danger');
            }
        });

        document.getElementById('media-upload-input')?.addEventListener('change', async function () {
            const files = Array.from(this.files || []);
            if (files.length === 0) {
                return;
            }
            const progressContainer = document.getElementById('upload-progress-container');
            const overallProgress = document.getElementById('overall-progress');
            const progressPercent = document.getElementById('upload-progress-percent');
            const statusText = document.getElementById('upload-status-text');

            progressContainer?.classList.remove('hidden');
            if (overallProgress) {
                overallProgress.value = 0;
            }
            if (progressPercent) {
                progressPercent.innerText = '0%';
            }

            try {
                const formData = new FormData();
                files.forEach((file) => formData.append('media_files[]', file));
                formData.append('dir', mediaLibraryState.directory || '');
                formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
                formData.append('response_format', 'json');

                await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', (window.APP_BASE_PATH || '') + '/admin/media/upload', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.upload.onprogress = (event) => {
                        if (event.lengthComputable) {
                            const percent = Math.round((event.loaded / event.total) * 100);
                            if (overallProgress) {
                                overallProgress.value = percent;
                            }
                            if (progressPercent) {
                                progressPercent.innerText = `${percent}%`;
                            }
                            if (statusText) {
                                statusText.innerText = `正在上传 ${files.length} 个文件`;
                            }
                        }
                    };
                    xhr.onload = () => {
                        try {
                            const payload = JSON.parse(xhr.responseText || '{}');
                            if (xhr.status >= 200 && xhr.status < 300 && payload.success !== false) {
                                resolve(payload);
                                return;
                            }
                            reject(new Error(payload?.data?.messages?.[0] || '上传失败'));
                        } catch (error) {
                            reject(new Error('上传响应无效'));
                        }
                    };
                    xhr.onerror = () => reject(new Error('上传失败'));
                    xhr.send(formData);
                });

                progressContainer?.classList.add('hidden');
                renderLibraryStatus('媒体上传成功', 'success');
                fetchMediaLibrary();
            } catch (error) {
                progressContainer?.classList.add('hidden');
                renderLibraryStatus(error.message || '上传失败', 'danger');
            }
            this.value = '';
        });

        document.getElementById('media-library-bulk-delete-btn')?.addEventListener('click', async () => {
            const selected = Array.from(mediaLibraryState.selectedItems.values());
            if (!selected.length) {
                return;
            }
            if (!window.confirm(`确定删除选中的 ${selected.length} 个文件吗？此操作无法撤销。`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
                formData.append('dir', mediaLibraryState.directory || '');
                formData.append('response_format', 'json');
                selected.forEach((item) => formData.append('paths[]', item.public_path || ''));

                const response = await fetch((window.APP_BASE_PATH || '') + '/admin/media/delete', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const payload = await response.json();
                if (!response.ok || payload.success === false) {
                    throw new Error(payload?.data?.messages?.[0] || '删除失败');
                }
                mediaLibraryState.selectedItems.clear();
                mediaLibraryState.activeItem = null;
                updateMediaDetails(null);
                renderLibraryStatus(payload?.data?.messages?.[0] || '删除成功', 'success');
                fetchMediaLibrary();
            } catch (error) {
                renderLibraryStatus(error.message || '删除失败', 'danger');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (modal.classList.contains('hidden')) {
                return;
            }
            const activeTag = document.activeElement?.tagName?.toLowerCase() || '';
            const isTypingContext = ['input', 'textarea', 'select'].includes(activeTag);

            if (event.key === 'Escape') {
                closeMediaLibraryModal();
                return;
            }
            if (event.key === 'Enter' && !isTypingContext && mediaLibraryState.selectedItems.size > 0) {
                event.preventDefault();
                confirmBtn.click();
            }
        });
    }

    function initJoditEditors() {
        const editorInputs = document.querySelectorAll('.js-rich-editor');
        if (!editorInputs.length || !window.Jodit) {
            return;
        }

        if (window.Jodit.defaultOptions && window.Jodit.defaultOptions.controls) {
            window.Jodit.defaultOptions.controls.mediaImageLibrary = {
                name: 'mediaImageLibrary',
                icon: 'image',
                tooltip: '从媒体库插入图片',
                exec(editor) {
                    openMediaLibrary((selection) => {
                        insertMediaIntoEditor(editor, selection);
                    }, true, {
                        type: 'image',
                        returnObjects: true
                    });
                }
            };

            window.Jodit.defaultOptions.controls.mediaAssetLibrary = {
                name: 'mediaAssetLibrary',
                icon: 'folder',
                tooltip: '从媒体库插入图片或视频',
                exec(editor) {
                    openMediaLibrary((selection) => {
                        insertMediaIntoEditor(editor, selection);
                    }, false, {
                        type: 'all',
                        returnObjects: true
                    });
                }
            };
        }

        editorInputs.forEach((input) => {
            const editorHeight = parseInt(input.dataset.editorHeight || '', 10);
            const editor = window.Jodit.make(input, {
                language: 'zh_cn',
                height: Number.isFinite(editorHeight) && editorHeight > 0 ? editorHeight : 400,
                minHeight: 260,
                globalFullSize: false,
                editorClassName: 'rich-content',
                toolbarAdaptive: false,
                toolbarSticky: false,
                askBeforePasteHTML: false,
                askBeforePasteFromWord: false,
                showCharsCounter: false,
                showWordsCounter: false,
                showXPathInStatusbar: false,
                beautifyHTML: false,
                imageDefaultWidth: null,
                buttons: [
                    'source', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'ul', 'ol', 'outdent', 'indent', '|',
                    'font', 'fontsize', 'brush', 'paragraph', '|',
                    'mediaImageLibrary', 'mediaAssetLibrary', 'link', 'table', '|',
                    'align', 'undo', 'redo', '|',
                    'hr', 'eraser', 'fullsize'
                ]
            });

            const fullscreenHostElements = [];
            let current = input.parentElement;
            while (current && current !== document.body) {
                fullscreenHostElements.push(current);
                current = current.parentElement;
            }

            editor.e.on('toggleFullSize', (isFullSize) => {
                document.documentElement.classList.toggle('editor-fullscreen-active', isFullSize);
                document.body.classList.toggle('editor-fullscreen-active', isFullSize);
                fullscreenHostElements.forEach((element) => {
                    element.classList.toggle('editor-fullscreen-host', isFullSize);
                });
            });

            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    editor.synchronizeValues();
                });
            }
        });
    }

    function initPriceTierManager() {
        const addTierBtn = document.getElementById('add-price-tier');
        const tierWrap = document.getElementById('price-tier-wrap');
        if (!addTierBtn || !tierWrap) {
            return;
        }

        addTierBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'price-tier-row mb-3 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_110px_56px] md:items-end';
            row.innerHTML = `
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">最小数量</span>
<input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_min[]" type="number" min="1" required>
</label>
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">最大数量</span>
<input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_max[]" type="number" min="1" placeholder="可空">
</label>
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">单价</span>
<input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_value[]" type="number" min="0" step="0.01" required>
</label>
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">货币</span>
<input class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="price_currency[]" value="USD" required>
</label>
<div class="flex md:justify-end">
<button type="button" class="remove-price-tier inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" aria-label="删除阶梯价格">
<i class="fas fa-trash-alt text-sm"></i>
</button>
</div>
`;
            tierWrap.appendChild(row);
        });

        tierWrap.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-price-tier');
            if (removeButton) {
                removeButton.closest('.price-tier-row')?.remove();
            }
        });
    }

    function initPostForm() {
        const coverInput = document.getElementById('cover-input');
        const coverPreview = document.getElementById('cover-preview');
        const coverPreviewWrap = document.getElementById('cover-preview-wrap');
        const coverSelectBtn = document.getElementById('post-cover-select-btn');
        const coverClearBtn = document.getElementById('cover-clear-btn');
        if (!coverInput || !coverSelectBtn) {
            return;
        }

        coverSelectBtn.addEventListener('click', () => {
            openMediaLibrary((url) => {
                coverInput.value = url;
                if (coverPreview) coverPreview.src = url;
                coverPreviewWrap?.classList.remove('hidden');
            }, false);
        });

        coverClearBtn?.addEventListener('click', () => {
            coverInput.value = '';
            if (coverPreview) coverPreview.src = '';
            coverPreviewWrap?.classList.add('hidden');
        });
    }

    function initProductForm() {
        const mediaContainer = document.getElementById('media-container');
        const gridWrap = document.getElementById('media-grid-wrap');
        const emptyPlaceholder = document.getElementById('media-empty-placeholder');
        const gridAddBtn = document.getElementById('grid-add-btn');
        if (!mediaContainer || !gridWrap || !emptyPlaceholder || !gridAddBtn) {
            return;
        }

        if (window.Sortable) {
            new window.Sortable(mediaContainer, {
                animation: 150,
                draggable: '.media-item',
                ghostClass: 'sortable-ghost',
                onEnd: checkMediaState
            });
        }

        function checkMediaState() {
            const hasImages = mediaContainer.querySelectorAll('.media-item').length > 0;
            gridWrap.classList.toggle('hidden', !hasImages);
            emptyPlaceholder.classList.toggle('hidden', hasImages);
        }

        function addMediaItem(url) {
            const exists = Array.from(mediaContainer.querySelectorAll('input[name="images[]"]')).some((input) => input.value === url);
            if (exists) {
                return;
            }
            const imgSrc = `${window.APP_BASE_PATH || ''}${url}`;
            const div = document.createElement('div');
            div.className = 'media-item';
            div.dataset.url = url;
            div.innerHTML = `
<img src="${escapeHtmlAttr(imgSrc)}">
<input type="hidden" name="images[]" value="${escapeHtmlAttr(url)}">
<button type="button" class="remove-media inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900/70 text-white transition hover:bg-rose-500" aria-label="移除图片">
<i class="fas fa-times text-xs"></i>
</button>
`;
            mediaContainer.insertBefore(div, gridAddBtn);
        }

        document.querySelectorAll('.open-media-library-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                openMediaLibrary((urls) => {
                    urls.forEach((url) => addMediaItem(url));
                    checkMediaState();
                }, true);
            });
        });

        mediaContainer.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-media');
            if (removeButton) {
                removeButton.closest('.media-item')?.remove();
                checkMediaState();
            }
        });

        const bannerInput = document.getElementById('banner-input');
        const bannerPreview = document.getElementById('banner-preview');
        const bannerImage = document.getElementById('banner-image');
        const bannerPlaceholder = document.getElementById('banner-placeholder');
        const removeBannerBtn = document.getElementById('remove-banner');
        document.querySelectorAll('.open-banner-library-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                openMediaLibrary((url) => {
                    const imgSrc = `${window.APP_BASE_PATH || ''}${url}`;
                    if (bannerInput) bannerInput.value = url;
                    if (bannerImage) bannerImage.src = imgSrc;
                    bannerPreview?.style.setProperty('display', 'block');
                    bannerPlaceholder?.style.setProperty('display', 'none');
                }, false);
            });
        });

        removeBannerBtn?.addEventListener('click', () => {
            if (bannerInput) bannerInput.value = '';
            bannerPreview?.style.setProperty('display', 'none');
            bannerPlaceholder?.style.setProperty('display', 'block');
        });

        document.getElementById('file-upload-input')?.addEventListener('change', async function () {
            const files = Array.from(this.files || []);
            if (!files.length) {
                return;
            }

            files.forEach((file) => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const div = document.createElement('div');
                    div.className = 'media-item is-uploading';
                    div.style.opacity = '0.6';
                    div.innerHTML = `
<img src="${escapeHtmlAttr(event.target?.result || '')}">
<span class="absolute inset-x-0 bottom-0 rounded-b-2xl bg-slate-900/80 px-3 py-1.5 text-center text-[10px] font-semibold tracking-[0.12em] text-white">上传中...</span>
`;
                    mediaContainer.insertBefore(div, gridAddBtn);
                };
                reader.readAsDataURL(file);
            });
            checkMediaState();

            const uploadTasks = files.map((file) => {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('csrf', window.ADMIN_CSRF_TOKEN || '');
                return fetch((window.APP_BASE_PATH || '') + '/admin/upload-image', {
                    method: 'POST',
                    body: formData
                }).then((response) => response.json());
            });

            try {
                const results = await Promise.all(uploadTasks);
                mediaContainer.querySelectorAll('.media-item.is-uploading').forEach((item) => item.remove());
                results.forEach((result) => {
                    if (result.url) {
                        addMediaItem(result.url);
                    }
                });
                checkMediaState();
            } catch (error) {
                window.alert('上传失败');
            }

            this.value = '';
        });
    }

    function renderSettingsPickerPreview(container, url, fit) {
        const objectClass = fit === 'cover' ? 'h-full w-full object-cover' : 'max-h-full max-w-full object-contain';
        container.innerHTML = `<img src="${escapeHtmlAttr(url)}" alt="" class="${objectClass}">`;
    }

    function initSettingsGeneralPickers() {
        document.querySelectorAll('[data-media-picker-target]').forEach((picker) => {
            picker.addEventListener('click', () => {
                const targetId = picker.dataset.mediaPickerTarget || '';
                const targetInput = document.getElementById(targetId);
                if (!targetInput) {
                    return;
                }
                openMediaLibrary((url) => {
                    targetInput.value = url;
                    renderSettingsPickerPreview(picker, url, picker.dataset.mediaPickerFit || 'contain');
                });
            });
        });
    }

    function addMediaRow(containerId, imgName, titleName) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }
        const div = document.createElement('div');
        div.className = 'media-item-row mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-4';
        const labelText = imgName.includes('cert') ? '证书名称' : '图片标题';
        const placeholderText = imgName.includes('cert') ? '输入证书名称' : '输入图片标题';
        const inputAccent = imgName.includes('cert') ? 'focus:border-amber-400 focus:ring-2 focus:ring-amber-100' : 'focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100';
        div.innerHTML = `
<div class="grid gap-4 md:grid-cols-[120px_minmax(0,1fr)_56px] md:items-center">
<div>
<div class="media-preview-wrap">
<input type="hidden" name="${imgName}[]" value="">
<div class="media-preview is-empty" data-settings-media-preview>
<span class="text-slate-400"><i class="fas fa-image fa-2x"></i></span>
</div>
</div>
</div>
<div class="space-y-2">
<label class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">${labelText}</label>
<input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 ${inputAccent}" name="${titleName}[]" value="" placeholder="${placeholderText}">
</div>
<div>
<button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" data-media-row-remove>
<i class="fas fa-trash-alt text-sm"></i>
</button>
</div>
</div>
`;
        container.appendChild(div);
    }

    function selectMediaPreview(previewEl) {
        const wrap = previewEl.closest('.media-preview-wrap');
        const input = wrap?.querySelector('input[type="hidden"]');
        if (!wrap || !input) {
            return;
        }
        openMediaLibrary((url) => {
            input.value = url;
            previewEl.innerHTML = `<img src="${escapeHtmlAttr(url)}" alt="">`;
            previewEl.classList.remove('is-empty');
        });
    }

    function initSettingsMediaHelpers() {
        document.querySelectorAll('[data-media-row-add]').forEach((button) => {
            button.addEventListener('click', () => {
                addMediaRow(button.dataset.containerId || '', button.dataset.imageName || '', button.dataset.titleName || '');
            });
        });

        document.addEventListener('click', (event) => {
            const preview = event.target.closest('[data-settings-media-preview]');
            if (preview) {
                selectMediaPreview(preview);
                return;
            }
            const removeButton = event.target.closest('[data-media-row-remove]');
            if (removeButton) {
                removeButton.closest('.media-item-row')?.remove();
            }
        });
    }

    function toFullUrl(path) {
        if (!path || /^(https?:)?\/\//i.test(path) || path.startsWith('data:')) {
            return path || '';
        }
        return window.location.origin + (window.APP_BASE_PATH || '') + path;
    }

    function copyMediaPath(path) {
        if (!path) {
            return;
        }
        const fullUrl = toFullUrl(path);
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(fullUrl).then(() => {
                window.alert(`已复制：${fullUrl}`);
            }).catch(() => {
                fallbackCopyText(fullUrl);
            });
        } else {
            fallbackCopyText(fullUrl);
        }
    }

    function fallbackCopyText(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px;opacity:0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            window.alert(`已复制：${text}`);
        } catch (e) {
            window.alert('复制失败，请手动复制');
        }
        document.body.removeChild(textarea);
    }

    function setExplorerSelectedCount() {
        const selected = Array.from(document.querySelectorAll('.js-page-media-checkbox:checked'));
        const label = document.getElementById('page-media-selected-count');
        const deleteButton = document.getElementById('page-media-delete-btn');
        const deleteLabel = document.getElementById('page-media-delete-label');
        const toggleAll = document.getElementById('page-media-toggle-all');
        const allItems = document.querySelectorAll('.js-page-media-checkbox');

        if (label) {
            label.textContent = `已选择 ${selected.length} 个文件`;
        }
        if (deleteButton) {
            deleteButton.disabled = selected.length === 0;
        }
        if (deleteLabel) {
            deleteLabel.textContent = selected.length > 0 ? `删除 (${selected.length})` : '删除';
        }
        if (toggleAll) {
            toggleAll.checked = allItems.length > 0 && selected.length === allItems.length;
        }
    }

    function selectExplorerFile(file) {
        currentExplorerFile = file || null;
        document.querySelectorAll('.explorer-file-row').forEach((row) => {
            row.classList.toggle('is-active', row.dataset.filePath === (file?.path || ''));
        });

        const empty = document.getElementById('explorerPreviewEmpty');
        const panel = document.getElementById('explorerPreviewPanel');
        const box = document.getElementById('explorerPreviewBox');
        if (!empty || !panel || !box || !file) {
            return;
        }

        empty.classList.add('hidden');
        panel.classList.remove('hidden');

        if (file.type === 'video') {
            box.innerHTML = `<video controls playsinline preload="metadata"><source src="${escapeHtmlAttr(file.url || '')}"></video>`;
        } else {
            box.innerHTML = `<img src="${escapeHtmlAttr(file.url || '')}" alt="">`;
        }

        document.getElementById('explorerPreviewName').textContent = file.name || '';
        document.getElementById('explorerPreviewStorage').textContent = file.storage_name || '';
        document.getElementById('explorerPreviewType').textContent = file.type === 'video' ? '视频' : (file.type === 'image' ? '图片' : '文件');
        document.getElementById('explorerPreviewDirectory').textContent = file.directory ? `/uploads/${file.directory}` : '/uploads';
        document.getElementById('explorerPreviewSize').textContent = [file.size || '', file.dimensions || ''].filter(Boolean).join(' · ') || '-';
        document.getElementById('explorerPreviewDate').textContent = file.date || '';
        document.getElementById('explorerPreviewPath').value = file.path || '';
    }

    function submitSingleMediaDelete(path) {
        if (!window.confirm('确定删除这个文件吗？')) {
            return;
        }
        const form = document.getElementById('single-media-delete-form');
        const input = document.getElementById('single-media-delete-path');
        if (!form || !input) {
            return;
        }
        input.value = path || '';
        form.submit();
    }

    function submitPageBulkDelete() {
        const selected = Array.from(document.querySelectorAll('.js-page-media-checkbox:checked'));
        if (!selected.length) {
            return;
        }
        if (!window.confirm(`确定删除选中的 ${selected.length} 个文件吗？此操作无法撤销。`)) {
            return;
        }
        document.getElementById('media-bulk-delete-form')?.submit();
    }

    function initMediaExplorerPage() {
        if (!document.getElementById('page-media-file-list')) {
            return;
        }

        document.querySelectorAll('.explorer-file-row[data-explorer-file]').forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('.explorer-check-cell') || event.target.closest('.explorer-actions')) {
                    return;
                }
                try {
                    selectExplorerFile(JSON.parse(row.dataset.explorerFile || '{}'));
                } catch (error) {
                    console.warn('Invalid explorer file payload', error);
                }
            });
        });

        document.querySelectorAll('.js-page-media-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', (event) => {
                event.stopPropagation();
                setExplorerSelectedCount();
            });
        });

        document.getElementById('page-media-toggle-all')?.addEventListener('change', function () {
            document.querySelectorAll('.js-page-media-checkbox').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
            setExplorerSelectedCount();
        });

        document.getElementById('page-media-delete-btn')?.addEventListener('click', submitPageBulkDelete);
        document.getElementById('page-media-upload-btn')?.addEventListener('click', () => {
            document.getElementById('page-media-upload-input')?.click();
        });
        document.getElementById('page-media-upload-input')?.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                document.getElementById('page-media-upload-form')?.submit();
            }
        });
        document.getElementById('page-media-new-folder-btn')?.addEventListener('click', () => {
            const name = window.prompt('请输入新文件夹名称');
            if (!name) {
                return;
            }
            const folderInput = document.getElementById('page-media-create-folder-name');
            if (!folderInput) {
                return;
            }
            folderInput.value = name.trim();
            document.getElementById('page-media-create-folder-form')?.submit();
        });

        document.querySelectorAll('.js-folder-delete-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const folderDir = btn.dataset.folderDir || '';
                const folderName = btn.dataset.folderName || '';
                const folderCount = parseInt(btn.dataset.folderCount || '0', 10);
                if (folderCount > 0) {
                    window.alert(`文件夹「${folderName}」内有 ${folderCount} 个项目，请先清空后再删除。`);
                    return;
                }
                if (!window.confirm(`确定删除文件夹「${folderName}」吗？`)) {
                    return;
                }
                const form = document.getElementById('page-media-folder-delete-form');
                const input = document.getElementById('page-media-folder-delete-dir');
                if (!form || !input) {
                    return;
                }
                input.value = folderDir;
                form.submit();
            });
        });

        setExplorerSelectedCount();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initGlobalActions();
        initNavToggle();
        initMediaLibraryModal();
        initJoditEditors();
        initPriceTierManager();
        initPostForm();
        initProductForm();
        initSettingsGeneralPickers();
        initSettingsMediaHelpers();
        initMediaExplorerPage();
    });

    window.openMediaLibrary = openMediaLibrary;
    window.copyMediaPath = copyMediaPath;
    window.submitSingleMediaDelete = submitSingleMediaDelete;
    window.submitPageBulkDelete = submitPageBulkDelete;
    window.addMediaRow = addMediaRow;
    window.selectMediaPreview = selectMediaPreview;
    window.selectExplorerFile = selectExplorerFile;
})();
