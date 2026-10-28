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
            returnObjects: false,
            lockType: false
        },
        directory: '',
        search: '',
        type: 'image',
        sort: 'date_desc',
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

    const productSelectorState = {
        callback: null,
        multiple: true,
        max: 0,
        selectedItems: new Map(),
        page: 1,
        perPage: 12,
        totalPages: 1,
        total: 0,
        q: '',
        categoryId: '',
        status: 'active',
        fetchTimer: null,
        products: []
    };

    let pageMediaPreviewReturnFocus = null;

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

    function submitPostAction(target) {
        const action = target.dataset.postAction || '';
        if (!action) {
            return;
        }

        let payload = {};
        try {
            payload = JSON.parse(target.dataset.postPayload || '{}');
        } catch (error) {
            console.error('Invalid POST action payload', error);
            return;
        }

        const form = document.createElement('form');
        form.method = 'post';
        form.action = action;
        form.hidden = true;

        Object.entries({ ...payload, csrf: window.ADMIN_CSRF_TOKEN || '' }).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = String(value ?? '');
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function parseProductSelectorIds(value) {
        const ids = [];
        String(value || '').split(/[,\s]+/).forEach((item) => {
            const id = Number.parseInt(item, 10);
            if (id > 0 && !ids.includes(id)) {
                ids.push(id);
            }
        });
        return ids;
    }

    function productSelectorSearchUrl() {
        const params = new URLSearchParams();
        if (productSelectorState.q) params.set('q', productSelectorState.q);
        if (productSelectorState.categoryId) params.set('category_id', productSelectorState.categoryId);
        params.set('status', productSelectorState.status);
        params.set('page', String(productSelectorState.page));
        params.set('per_page', String(productSelectorState.perPage));
        return `${window.APP_BASE_PATH || ''}/admin/products/selector?${params.toString()}`;
    }

    function productSelectorIdsUrl(ids) {
        const params = new URLSearchParams();
        params.set('ids', parseProductSelectorIds(ids).join(','));
        return `${window.APP_BASE_PATH || ''}/admin/products/selector?${params.toString()}`;
    }

    function productStatusLabel(status) {
        if (status === 'active') return '已上架';
        if (status === 'draft') return '草稿';
        if (status === 'inactive' || status === 'archived') return '未上架';
        return status || '未知';
    }

    function productSelectorCover(product) {
        return normalizeMediaSelectionUrl(product?.cover || '');
    }

    function productSelectorThumb(product, sizeClass = 'h-14 w-16') {
        const cover = productSelectorCover(product);
        if (cover) {
            return `<img src="${escapeHtmlAttr(cover)}" alt="" class="${sizeClass} shrink-0 rounded-lg border border-slate-200 object-cover">`;
        }
        return `<span class="${sizeClass} inline-flex shrink-0 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-slate-300"><i class="fas fa-box"></i></span>`;
    }

    function setProductSelectorMessage(message = '', type = 'info') {
        const box = document.getElementById('product-selector-message');
        if (!box) {
            return;
        }
        if (!message) {
            box.className = 'hidden rounded-xl border px-4 py-3 text-sm';
            box.textContent = '';
            return;
        }
        const classes = type === 'error'
            ? 'border-rose-200 bg-rose-50 text-rose-700'
            : type === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-sky-200 bg-sky-50 text-sky-700';
        box.className = `rounded-xl border px-4 py-3 text-sm ${classes}`;
        box.textContent = message;
    }

    function renderProductSelectorCategories(categories = []) {
        const select = document.getElementById('product-selector-category');
        if (!select) {
            return;
        }
        const current = select.value || productSelectorState.categoryId;
        select.innerHTML = '<option value="">全部分类</option>' + categories.map((category) => (
            `<option value="${escapeHtmlAttr(category.id)}">${escapeHtmlAttr(category.name)}</option>`
        )).join('');
        select.value = current;
    }

    function findProductSelectorItem(id) {
        return productSelectorState.products.find((item) => Number(item.id) === Number(id))
            || productSelectorState.selectedItems.get(Number(id))
            || null;
    }

    function renderProductSelectorPagination() {
        const count = document.getElementById('product-selector-count');
        const page = document.getElementById('product-selector-page');
        const prev = document.getElementById('product-selector-prev');
        const next = document.getElementById('product-selector-next');
        const currentPage = productSelectorState.page;
        const totalPages = Math.max(1, productSelectorState.totalPages);

        if (count) {
            count.textContent = `商品列表（共 ${productSelectorState.total} 个）`;
        }
        if (page) {
            page.textContent = `${currentPage} / ${totalPages}`;
        }
        if (prev) {
            prev.disabled = currentPage <= 1;
        }
        if (next) {
            next.disabled = currentPage >= totalPages;
        }
    }

    function renderProductSelectorList() {
        const list = document.getElementById('product-selector-list');
        if (!list) {
            return;
        }
        const products = productSelectorState.products;
        if (!products.length) {
            list.innerHTML = `
<div class="flex h-48 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-center text-slate-400">
<i class="fas fa-search mb-3 text-2xl"></i>
<p class="text-sm font-semibold text-slate-500">没有找到商品</p>
<p class="mt-1 text-xs">请调整关键词、分类或状态筛选。</p>
</div>`;
            return;
        }

        list.innerHTML = products.map((product) => {
            const id = Number(product.id);
            const selected = productSelectorState.selectedItems.has(id);
            const selectedClasses = selected
                ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-100'
                : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-slate-50';
            return `
<button type="button" class="mb-3 flex w-full items-start gap-3 rounded-2xl border p-3 text-left transition ${selectedClasses}" data-product-selector-id="${id}" aria-pressed="${selected ? 'true' : 'false'}">
<span class="mt-6 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border ${selected ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-transparent'}">
<i class="fas fa-check text-[10px]"></i>
</span>
${productSelectorThumb(product)}
<span class="min-w-0 flex-1">
<span class="block truncate text-sm font-semibold text-slate-900">${escapeHtmlAttr(product.title || `#${id}`)}</span>
<span class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
<span>#${id}</span>
${product.category_name ? `<span>${escapeHtmlAttr(product.category_name)}</span>` : ''}
<span>${escapeHtmlAttr(productStatusLabel(product.status))}</span>
</span>
${product.summary ? `<span class="mt-2 block line-clamp-2 text-xs leading-5 text-slate-500">${escapeHtmlAttr(product.summary)}</span>` : ''}
</span>
</button>`;
        }).join('');
    }

    function renderProductSelectorSelected() {
        const selected = document.getElementById('product-selector-selected');
        const count = document.getElementById('product-selector-selected-count');
        const items = Array.from(productSelectorState.selectedItems.values());

        if (count) {
            const limitText = productSelectorState.max > 0 ? `，最多 ${productSelectorState.max} 个` : '';
            count.textContent = `${items.length} 个商品${limitText}`;
        }
        if (!selected) {
            return;
        }
        if (!items.length) {
            selected.innerHTML = `
<div class="flex h-40 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white text-center text-slate-400">
<i class="fas fa-check-square mb-3 text-2xl"></i>
<p class="text-sm font-semibold text-slate-500">暂未选择商品</p>
</div>`;
            return;
        }

        selected.innerHTML = items.map((product) => {
            const id = Number(product.id);
            return `
<div class="mb-3 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3">
${productSelectorThumb(product, 'h-12 w-14')}
<div class="min-w-0 flex-1">
<p class="truncate text-sm font-semibold text-slate-900">${escapeHtmlAttr(product.title || `#${id}`)}</p>
<p class="mt-1 text-xs text-slate-500">#${id}${product.category_name ? ` · ${escapeHtmlAttr(product.category_name)}` : ''}</p>
</div>
<button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-500 transition hover:bg-rose-100" data-product-selector-remove="${id}" aria-label="移除商品">
<i class="fas fa-times text-xs"></i>
</button>
</div>`;
        }).join('');
    }

    function renderProductSelector() {
        renderProductSelectorList();
        renderProductSelectorSelected();
        renderProductSelectorPagination();
    }

    function toggleProductSelectorItem(product) {
        if (!product) {
            return;
        }
        const id = Number(product.id);
        if (productSelectorState.selectedItems.has(id)) {
            productSelectorState.selectedItems.delete(id);
            setProductSelectorMessage('');
            renderProductSelector();
            return;
        }

        if (!productSelectorState.multiple) {
            productSelectorState.selectedItems.clear();
        } else if (productSelectorState.max > 0 && productSelectorState.selectedItems.size >= productSelectorState.max) {
            setProductSelectorMessage(`最多只能选择 ${productSelectorState.max} 个商品。`, 'error');
            return;
        }

        productSelectorState.selectedItems.set(id, product);
        setProductSelectorMessage('');
        renderProductSelector();
    }

    async function fetchProductSelectorProducts() {
        const list = document.getElementById('product-selector-list');
        if (list) {
            list.innerHTML = '<div class="flex h-48 items-center justify-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>正在加载商品...</div>';
        }

        try {
            const response = await fetch(productSelectorSearchUrl(), {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || '商品加载失败');
            }

            productSelectorState.products = Array.isArray(payload.products) ? payload.products : [];
            productSelectorState.total = Number(payload.pagination?.total || 0);
            productSelectorState.totalPages = Number(payload.pagination?.total_pages || 1);
            productSelectorState.page = Number(payload.pagination?.page || productSelectorState.page);
            renderProductSelectorCategories(payload.categories || []);
            renderProductSelector();
            setProductSelectorMessage('');
        } catch (error) {
            productSelectorState.products = [];
            renderProductSelector();
            setProductSelectorMessage(error.message || '商品加载失败', 'error');
        }
    }

    async function fetchProductsByIds(ids) {
        const normalized = parseProductSelectorIds(ids);
        if (!normalized.length) {
            return [];
        }

        const response = await fetch(productSelectorIdsUrl(normalized), {
            headers: { 'Accept': 'application/json' }
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            throw new Error(payload.message || '商品加载失败');
        }
        return Array.isArray(payload.products) ? payload.products : [];
    }

    function openProductSelector(callback, options = {}) {
        const modal = document.getElementById('product-selector-modal');
        if (!modal) {
            return;
        }

        productSelectorState.callback = callback;
        productSelectorState.multiple = options.multiple !== false;
        productSelectorState.max = Math.max(0, Number.parseInt(options.max || '0', 10) || 0);
        productSelectorState.selectedItems = new Map();
        productSelectorState.page = 1;
        productSelectorState.perPage = Math.max(1, Math.min(60, Number.parseInt(options.perPage || '12', 10) || 12));
        productSelectorState.totalPages = 1;
        productSelectorState.total = 0;
        productSelectorState.q = '';
        productSelectorState.categoryId = '';
        productSelectorState.status = options.status || 'active';
        productSelectorState.products = [];

        const search = document.getElementById('product-selector-search');
        const category = document.getElementById('product-selector-category');
        const status = document.getElementById('product-selector-status');
        if (search) search.value = '';
        if (category) category.value = '';
        if (status) status.value = productSelectorState.status;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        setProductSelectorMessage('');
        renderProductSelector();
        fetchProductSelectorProducts();

        const selectedIds = parseProductSelectorIds(options.selectedIds || '');
        if (selectedIds.length) {
            fetchProductsByIds(selectedIds)
                .then((products) => {
                    products.forEach((product) => {
                        const id = Number(product.id);
                        if (id > 0) {
                            productSelectorState.selectedItems.set(id, product);
                        }
                    });
                    renderProductSelector();
                })
                .catch((error) => setProductSelectorMessage(error.message || '已选商品加载失败', 'error'));
        }
    }

    function closeProductSelectorModal() {
        const modal = document.getElementById('product-selector-modal');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
        document.body.classList.remove('overflow-hidden');
    }

    function renderProductPickerPreview(field, products) {
        const preview = field.querySelector('[data-product-picker-preview]');
        if (!preview) {
            return;
        }
        if (!products.length) {
            preview.innerHTML = '<div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-400">未选择商品</div>';
            return;
        }

        preview.innerHTML = products.map((product) => {
            const id = Number(product.id);
            return `
<div class="mb-2 flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">
${productSelectorThumb(product, 'h-11 w-12')}
<div class="min-w-0 flex-1">
<p class="truncate text-sm font-semibold text-slate-900">${escapeHtmlAttr(product.title || `#${id}`)}</p>
<p class="mt-1 text-xs text-slate-500">#${id}${product.category_name ? ` · ${escapeHtmlAttr(product.category_name)}` : ''}</p>
</div>
</div>`;
        }).join('');
    }

    function hydrateProductPickerField(field) {
        const input = field.querySelector('[data-product-picker-input]');
        const preview = field.querySelector('[data-product-picker-preview]');
        if (!input || !preview) {
            return;
        }

        const ids = parseProductSelectorIds(input.value);
        if (!ids.length) {
            renderProductPickerPreview(field, []);
            return;
        }

        preview.innerHTML = '<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>正在加载已选商品...</div>';
        fetchProductsByIds(ids)
            .then((products) => renderProductPickerPreview(field, products))
            .catch(() => {
                preview.innerHTML = '<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-600">已选商品加载失败，请检查商品 ID。</div>';
            });
    }

    function initProductPickerFields() {
        document.querySelectorAll('[data-product-picker-field]').forEach((field) => {
            const input = field.querySelector('[data-product-picker-input]');
            const openButton = field.querySelector('[data-product-picker-open]');
            const clearButton = field.querySelector('[data-product-picker-clear]');
            if (!input || !openButton) {
                return;
            }

            openButton.addEventListener('click', () => {
                openProductSelector((selection) => {
                    const products = Array.isArray(selection) ? selection : [selection].filter(Boolean);
                    input.value = products.map((product) => product.id).join(',');
                    renderProductPickerPreview(field, products);
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }, {
                    multiple: openButton.dataset.productPickerMultiple !== 'false',
                    max: openButton.dataset.productPickerMax || '0',
                    selectedIds: input.value,
                    status: openButton.dataset.productPickerStatus || 'active'
                });
            });

            if (clearButton) {
                clearButton.addEventListener('click', () => {
                    input.value = '';
                    renderProductPickerPreview(field, []);
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            input.addEventListener('change', () => hydrateProductPickerField(field));
            hydrateProductPickerField(field);
        });
    }

    function initProductSelectorModal() {
        const modal = document.getElementById('product-selector-modal');
        if (!modal) {
            return;
        }

        modal.querySelectorAll('[data-product-selector-close]').forEach((button) => {
            button.addEventListener('click', closeProductSelectorModal);
        });

        const search = document.getElementById('product-selector-search');
        if (search) {
            search.addEventListener('input', () => {
                window.clearTimeout(productSelectorState.fetchTimer);
                productSelectorState.fetchTimer = window.setTimeout(() => {
                    productSelectorState.q = search.value.trim();
                    productSelectorState.page = 1;
                    fetchProductSelectorProducts();
                }, 250);
            });
        }

        const category = document.getElementById('product-selector-category');
        if (category) {
            category.addEventListener('change', () => {
                productSelectorState.categoryId = category.value;
                productSelectorState.page = 1;
                fetchProductSelectorProducts();
            });
        }

        const status = document.getElementById('product-selector-status');
        if (status) {
            status.addEventListener('change', () => {
                productSelectorState.status = status.value;
                productSelectorState.page = 1;
                fetchProductSelectorProducts();
            });
        }

        document.getElementById('product-selector-refresh')?.addEventListener('click', fetchProductSelectorProducts);
        document.getElementById('product-selector-prev')?.addEventListener('click', () => {
            if (productSelectorState.page > 1) {
                productSelectorState.page -= 1;
                fetchProductSelectorProducts();
            }
        });
        document.getElementById('product-selector-next')?.addEventListener('click', () => {
            if (productSelectorState.page < productSelectorState.totalPages) {
                productSelectorState.page += 1;
                fetchProductSelectorProducts();
            }
        });

        document.getElementById('product-selector-list')?.addEventListener('click', (event) => {
            const row = event.target.closest('[data-product-selector-id]');
            if (!row) {
                return;
            }
            toggleProductSelectorItem(findProductSelectorItem(row.dataset.productSelectorId));
        });

        document.getElementById('product-selector-selected')?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-product-selector-remove]');
            if (!button) {
                return;
            }
            productSelectorState.selectedItems.delete(Number(button.dataset.productSelectorRemove));
            renderProductSelector();
        });

        document.getElementById('product-selector-confirm')?.addEventListener('click', () => {
            const selection = Array.from(productSelectorState.selectedItems.values());
            if (typeof productSelectorState.callback === 'function') {
                productSelectorState.callback(productSelectorState.multiple ? selection : (selection[0] || null));
            }
            closeProductSelectorModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeProductSelectorModal();
            }
        });
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
        updateMediaLibrarySortHeaders();
    }

    function updateMediaLibrarySortHeaders() {
        const labels = {
            name: '名称',
            date: '修改时间',
            size: '大小',
            type: '类型'
        };

        document.querySelectorAll('[data-media-library-sort]').forEach((button) => {
            const field = button.dataset.mediaLibrarySort || '';
            const ascending = `${field}_asc`;
            const descending = `${field}_desc`;
            const active = mediaLibraryState.sort === ascending || mediaLibraryState.sort === descending;
            const direction = mediaLibraryState.sort === ascending ? 'asc' : 'desc';
            const nextDirection = active
                ? (direction === 'asc' ? 'desc' : 'asc')
                : (field === 'date' ? 'desc' : 'asc');
            const icon = button.querySelector('[data-sort-icon]');
            const column = button.closest('[role="columnheader"]');

            button.classList.toggle('is-active', active);
            button.title = `按${labels[field] || field}${nextDirection === 'asc' ? '升序' : '降序'}排列`;
            button.setAttribute('aria-label', button.title);
            column?.setAttribute('aria-sort', active ? (direction === 'asc' ? 'ascending' : 'descending') : 'none');

            if (icon) {
                icon.classList.remove('fa-sort', 'fa-chevron-up', 'fa-chevron-down');
                icon.classList.add(active ? (direction === 'asc' ? 'fa-chevron-up' : 'fa-chevron-down') : 'fa-sort');
            }
        });
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
            const checkbox = element.querySelector('.js-media-library-selector');
            if (checkbox) {
                checkbox.checked = mediaLibraryState.selectedItems.has(path);
            }
        });
        updateMediaLibraryBatchControls();
    }

    function selectMediaItem(item, immediate = false) {
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
        if (payload.filters?.sort) {
            mediaLibraryState.sort = payload.filters.sort;
        }
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

        updateMediaLibraryViewMode();

        if (!folders.length && !files.length) {
            container.innerHTML = `
<div class="media-library-empty">
<span class="inline-flex h-8 w-8 items-center justify-center text-2xl"><i class="fas fa-photo-video"></i></span>
<p class="mt-3">这个目录下还没有符合条件的媒体文件。</p>
</div>
`;
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
<div class="media-library-file-col date">-</div>
<div class="media-library-file-col size">-</div>
<div class="media-library-file-col type">文件夹</div>
`;
            row.addEventListener('click', () => {
                mediaLibraryState.selectedItems.clear();
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
</div>
</div>
<div class="media-library-file-col date">${escapeHtmlAttr(file.date || '')}</div>
<div class="media-library-file-col size">${escapeHtmlAttr(file.size_formatted || '')}</div>
<div class="media-library-file-col type">${file.type === 'video' ? '视频' : (file.type === 'image' ? '图片' : '文件')}</div>
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
            returnObjects: !!options.returnObjects,
            lockType: !!options.lockType
        };
        mediaLibraryState.directory = options.directory || '';
        mediaLibraryState.search = '';
        mediaLibraryState.type = options.type || 'image';
        mediaLibraryState.sort = options.sort || 'date_desc';
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
        const confirmBtn = document.getElementById('confirm-media-selection');
        const uploadInput = document.getElementById('media-upload-input');

        if (!modal) {
            return;
        }

        if (searchInput) {
            searchInput.value = '';
        }
        if (typeFilter) {
            typeFilter.value = mediaLibraryState.type;
            typeFilter.disabled = !!mediaLibraryState.options.lockType;
            typeFilter.classList.toggle('cursor-not-allowed', !!mediaLibraryState.options.lockType);
            typeFilter.classList.toggle('opacity-60', !!mediaLibraryState.options.lockType);
        }
        if (uploadInput) {
            uploadInput.accept = mediaLibraryState.type === 'image'
                ? 'image/*'
                : mediaLibraryState.type === 'video'
                    ? 'video/mp4,video/webm,video/ogg,video/quicktime'
                    : 'image/*,video/mp4,video/webm,video/ogg,video/quicktime';
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
                return;
            }

            const postActionButton = event.target.closest('[data-post-action]');
            if (postActionButton) {
                event.preventDefault();
                submitPostAction(postActionButton);
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
        const sidebar = document.querySelector('[data-admin-sidebar]');
        const toggles = Array.from(document.querySelectorAll('[data-sidebar-toggle]'));
        const dismissButtons = Array.from(document.querySelectorAll('[data-sidebar-dismiss]'));
        const profileMenu = document.querySelector('[data-profile-menu]');
        const profileToggle = document.querySelector('[data-profile-menu-toggle]');
        const groups = Array.from(document.querySelectorAll('[data-nav-group]'));
        const desktopQuery = window.matchMedia('(min-width: 1024px)');
        const collapseKey = 'shopagg.admin.sidebar.collapsed';
        const groupsKey = 'shopagg.admin.sidebar.groups';
        let lastTrigger = null;

        if (!sidebar) {
            return;
        }

        const storage = {
            get(key) {
                try { return window.localStorage.getItem(key); } catch (_) { return null; }
            },
            set(key, value) {
                try { window.localStorage.setItem(key, value); } catch (_) {}
            },
        };

        const closeProfileMenu = () => {
            profileMenu?.classList.remove('is-open');
            profileToggle?.setAttribute('aria-expanded', 'false');
        };

        const syncToggleState = () => {
            const expanded = desktopQuery.matches
                ? !document.documentElement.classList.contains('admin-sidebar-collapsed')
                : document.body.classList.contains('admin-sidebar-open');
            toggles.forEach((toggle) => {
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                toggle.setAttribute('aria-label', expanded ? '收起导航栏' : '展开导航栏');
            });
        };

        const closeNav = (restoreFocus = false) => {
            document.body.classList.remove('admin-sidebar-open');
            closeProfileMenu();
            syncToggleState();
            if (restoreFocus) (lastTrigger || toggles[0])?.focus();
        };

        const openMobileNav = (trigger) => {
            lastTrigger = trigger || toggles[0] || null;
            document.body.classList.add('admin-sidebar-open');
            syncToggleState();
            sidebar.querySelector('[data-sidebar-dismiss]')?.focus();
        };

        const setDesktopCollapsed = (collapsed, remember = true) => {
            document.documentElement.classList.toggle('admin-sidebar-collapsed', collapsed);
            if (remember) storage.set(collapseKey, collapsed ? '1' : '0');
            syncToggleState();
        };

        toggles.forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                if (desktopQuery.matches) {
                    setDesktopCollapsed(!document.documentElement.classList.contains('admin-sidebar-collapsed'));
                    return;
                }
                document.body.classList.contains('admin-sidebar-open') ? closeNav(true) : openMobileNav(toggle);
            });
        });

        dismissButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                closeNav(true);
            });
        });

        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (!desktopQuery.matches) closeNav();
            });
        });

        const savedGroupsValue = storage.get(groupsKey);
        let savedGroups = [];
        let hasSavedGroupState = savedGroupsValue !== null;
        if (hasSavedGroupState) {
            try {
                const parsedGroups = JSON.parse(savedGroupsValue);
                if (Array.isArray(parsedGroups)) {
                    savedGroups = parsedGroups;
                } else {
                    hasSavedGroupState = false;
                }
            } catch (_) {
                hasSavedGroupState = false;
            }
        }

        const groupIds = new Set(groups.map((group) => group.dataset.navGroup || '').filter(Boolean));
        savedGroups = savedGroups.filter((id) => typeof id === 'string' && groupIds.has(id));
        const activeGroup = groups.find((group) => group.dataset.active === 'true');
        const activeGroupId = activeGroup?.dataset.navGroup || '';
        const initialOpenGroupId = hasSavedGroupState
            ? (savedGroups.includes(activeGroupId) ? activeGroupId : (savedGroups[0] || ''))
            : activeGroupId;

        const syncGroupAria = (group) => {
            group.querySelector('summary')?.setAttribute('aria-expanded', group.open ? 'true' : 'false');
        };

        const persistOpenGroup = () => {
            const openGroup = groups.find((group) => group.open);
            const openGroupId = openGroup?.dataset.navGroup || '';
            storage.set(groupsKey, JSON.stringify(openGroupId ? [openGroupId] : []));
        };

        groups.forEach((group) => {
            const id = group.dataset.navGroup || '';
            group.open = id !== '' && id === initialOpenGroupId;
            const summary = group.querySelector('summary');
            syncGroupAria(group);

            summary?.addEventListener('click', (event) => {
                if (desktopQuery.matches && document.documentElement.classList.contains('admin-sidebar-collapsed')) {
                    event.preventDefault();
                    setDesktopCollapsed(false);
                    group.open = true;
                    syncGroupAria(group);
                }
            });
            group.addEventListener('toggle', () => {
                if (group.open) {
                    groups.forEach((otherGroup) => {
                        if (otherGroup !== group && otherGroup.open) {
                            otherGroup.open = false;
                            syncGroupAria(otherGroup);
                        }
                    });
                }
                syncGroupAria(group);
                persistOpenGroup();
            });
        });

        profileToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = !profileMenu?.classList.contains('is-open');
            profileMenu?.classList.toggle('is-open', willOpen);
            profileToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (event) => {
            if (profileMenu && !profileMenu.contains(event.target)) {
                closeProfileMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                if (event.key !== 'Tab' || !document.body.classList.contains('admin-sidebar-open')) return;
                const focusable = Array.from(sidebar.querySelectorAll('a[href], button:not([disabled]), summary'));
                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
                return;
            }
            if (document.body.classList.contains('admin-sidebar-open')) {
                closeNav(true);
            } else {
                closeProfileMenu();
            }
        });

        const onViewportChange = () => {
            if (desktopQuery.matches && document.body.classList.contains('admin-sidebar-open')) {
                closeNav();
            }
            syncToggleState();
        };
        desktopQuery.addEventListener?.('change', onViewportChange);
        syncToggleState();
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
            } else {
                mediaLibraryState.selectedItems.clear();
            }
            renderMediaLibrarySelection();
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

        document.querySelectorAll('[data-media-library-sort]').forEach((button) => {
            button.addEventListener('click', () => {
                const field = button.dataset.mediaLibrarySort || '';
                if (!['name', 'date', 'size', 'type'].includes(field)) {
                    return;
                }

                const ascending = `${field}_asc`;
                const descending = `${field}_desc`;
                mediaLibraryState.sort = mediaLibraryState.sort === ascending
                    ? descending
                    : (mediaLibraryState.sort === descending ? ascending : (field === 'date' ? descending : ascending));
                updateMediaLibrarySortHeaders();
                fetchMediaLibrary();
            });
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

                const uploadPayload = await new Promise((resolve, reject) => {
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
                await fetchMediaLibrary();
                renderLibraryStatus((uploadPayload?.data?.messages || ['媒体上传成功']).join('；'), 'info');
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
                toolbarAdaptive: window.matchMedia('(max-width: 768px)').matches,
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
        const skuWrap = document.getElementById('sku-price-wrap');
        const addSkuBtn = document.getElementById('add-sku-price');
        const applySkuBulkBtn = document.getElementById('apply-sku-bulk');
        const modeInputs = document.querySelectorAll('input[name="price_mode"]');
        const modePanels = document.querySelectorAll('[data-price-mode-panel]');

        if (!modeInputs.length || !modePanels.length) {
            return;
        }

        function setPriceMode(mode) {
            modePanels.forEach((panel) => {
                const active = panel.dataset.priceModePanel === mode;
                panel.classList.toggle('hidden', !active);
                panel.querySelectorAll('input, select, textarea, button').forEach((control) => {
                    control.disabled = !active;
                });
            });

            document.querySelectorAll('[data-price-mode-option]').forEach((option) => {
                const active = option.dataset.priceModeOption === mode;
                option.classList.toggle('bg-white', active);
                option.classList.toggle('text-emerald-700', active);
                option.classList.toggle('shadow-sm', active);
                option.classList.toggle('text-slate-500', !active);
                option.classList.toggle('hover:text-slate-700', !active);
            });
        }

        const checkedMode = document.querySelector('input[name="price_mode"]:checked')?.value || 'tier';
        setPriceMode(checkedMode);
        modeInputs.forEach((input) => {
            input.addEventListener('change', () => setPriceMode(input.value));
        });

        addTierBtn?.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'price-tier-row mb-3 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_56px] md:items-end';
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
<div class="flex md:justify-end">
<button type="button" class="remove-price-tier inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" aria-label="删除阶梯价格">
<i class="fas fa-trash-alt text-sm"></i>
</button>
</div>
`;
            tierWrap.appendChild(row);
        });

        tierWrap?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-price-tier');
            if (removeButton) {
                removeButton.closest('.price-tier-row')?.remove();
            }
        });

        addSkuBtn?.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'sku-price-row mb-3 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[44px_minmax(0,1.4fr)_minmax(0,0.9fr)_minmax(0,1fr)_56px] md:items-end';
            row.innerHTML = `
<input type="hidden" name="sku_id[]" value="0">
<label class="flex h-11 items-center justify-center md:mb-0">
<input type="checkbox" class="sku-select h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-200">
</label>
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">SKU名称</span>
<input class="sku-name-input w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="sku_name[]" placeholder="如：Red / XL" required>
</label>
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">起订量</span>
<input class="sku-min-input w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="sku_min_qty[]" type="number" min="1" required>
</label>
<label class="space-y-2">
<span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">单价</span>
<input class="sku-price-input w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="sku_price[]" type="number" min="0" step="0.01" required>
</label>
<div class="flex md:justify-end">
<button type="button" class="remove-sku-price inline-flex h-11 w-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-500 transition hover:bg-rose-100" aria-label="删除SKU价格">
<i class="fas fa-trash-alt text-sm"></i>
</button>
</div>
`;
            skuWrap?.appendChild(row);
        });

        skuWrap?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-sku-price');
            if (removeButton) {
                removeButton.closest('.sku-price-row')?.remove();
            }
        });

        applySkuBulkBtn?.addEventListener('click', () => {
            const panel = document.getElementById('sku-price-panel');
            const priceValue = panel?.querySelector('[data-sku-bulk-price]')?.value.trim() || '';
            const minValue = panel?.querySelector('[data-sku-bulk-min]')?.value.trim() || '';
            if (priceValue === '' && minValue === '') {
                return;
            }

            const selectedRows = Array.from(document.querySelectorAll('.sku-price-row')).filter((row) => {
                const checkbox = row.querySelector('.sku-select');
                return checkbox && checkbox.checked && !checkbox.disabled;
            });

            if (!selectedRows.length) {
                window.alert('请选择要修改的SKU');
                return;
            }

            selectedRows.forEach((row) => {
                if (priceValue !== '') {
                    const priceInput = row.querySelector('.sku-price-input');
                    if (priceInput) priceInput.value = priceValue;
                }
                if (minValue !== '') {
                    const minInput = row.querySelector('.sku-min-input');
                    if (minInput) minInput.value = minValue;
                }
            });
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
<button type="button" class="media-remove-btn remove-media" aria-label="移除图片">
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

    function initCollapsibleSections() {
        document.querySelectorAll('[data-collapsible-trigger]').forEach((trigger) => {
            const panelId = trigger.getAttribute('aria-controls') || '';
            const panel = panelId ? document.getElementById(panelId) : null;
            if (!panel) {
                return;
            }

            const icon = trigger.querySelector('[data-collapsible-icon]');
            const setExpanded = (expanded) => {
                trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                panel.classList.toggle('hidden', !expanded);
                if (icon) {
                    icon.style.transform = expanded ? 'rotate(180deg)' : '';
                }
            };

            setExpanded(trigger.getAttribute('aria-expanded') === 'true');
            trigger.addEventListener('click', () => {
                setExpanded(trigger.getAttribute('aria-expanded') !== 'true');
            });
        });
    }

    function initBulkSelectionControls() {
        document.querySelectorAll('[data-bulk-select-all]').forEach((toggle) => {
            const form = toggle.closest('form');
            if (!form) {
                return;
            }

            const getItems = () => Array.from(form.querySelectorAll('[data-bulk-item]'));
            const actions = Array.from(form.querySelectorAll('[data-bulk-action]'));
            const updateState = () => {
                const items = getItems();
                const checked = items.filter((item) => item.checked);
                toggle.checked = items.length > 0 && checked.length === items.length;
                toggle.indeterminate = checked.length > 0 && checked.length < items.length;
                actions.forEach((action) => {
                    action.disabled = checked.length === 0;
                });
            };

            toggle.addEventListener('change', () => {
                getItems().forEach((item) => {
                    item.checked = toggle.checked;
                });
                updateState();
            });

            getItems().forEach((item) => {
                item.addEventListener('change', updateState);
            });
            updateState();
        });
    }

    function renderSettingsPickerPreview(container, url, fit) {
        const objectClass = fit === 'cover' ? 'h-full w-full object-cover' : 'max-h-full max-w-full object-contain';
        container.innerHTML = `<img src="${escapeHtmlAttr(normalizeMediaSelectionUrl(url))}" alt="" class="${objectClass}">`;
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
                    const clearButton = picker.parentElement?.querySelector('[data-media-picker-clear-target]');
                    clearButton?.classList.remove('hidden');
                }, false, {
                    type: 'image',
                    lockType: picker.dataset.mediaPickerImageOnly === 'true'
                });
            });
        });

        document.querySelectorAll('[data-media-picker-clear-target]').forEach((clearButton) => {
            clearButton.addEventListener('click', () => {
                const targetId = clearButton.dataset.mediaPickerClearTarget || '';
                const targetInput = document.getElementById(targetId);
                const picker = Array.from(document.querySelectorAll('[data-media-picker-target]'))
                    .find((item) => item.dataset.mediaPickerTarget === targetId);
                if (!targetInput || !picker) return;

                targetInput.value = '';
                picker.innerHTML = '<span class="text-center text-slate-400"><i class="fas fa-image text-2xl"></i><span class="mt-2 block text-xs">点击选择图片</span></span>';
                clearButton.classList.add('hidden');
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

    function sanitizeSlugValue(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9-]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function normalizeSlugInputValue(input) {
        if (!input) {
            return;
        }

        const normalized = sanitizeSlugValue(input.value);
        if (normalized !== input.value) {
            input.value = normalized;
        }
    }

    function initSlugFieldHelpers() {
        document.querySelectorAll('form').forEach((form) => {
            const sourceInput = form.querySelector('[data-slug-source]');
            const slugInput = form.querySelector('[data-slug-input]');
            if (!sourceInput || !slugInput) {
                return;
            }

            normalizeSlugInputValue(slugInput);
            let autoSyncSlug = String(slugInput.value || '').trim() === '';

            const syncSlugFromSource = () => {
                if (!autoSyncSlug && String(slugInput.value || '').trim() !== '') {
                    return;
                }

                autoSyncSlug = true;
                const normalized = sanitizeSlugValue(sourceInput.value);
                slugInput.value = normalized;
            };

            syncSlugFromSource();

            sourceInput.addEventListener('input', syncSlugFromSource);
            sourceInput.addEventListener('blur', syncSlugFromSource);
            sourceInput.addEventListener('change', syncSlugFromSource);

            slugInput.addEventListener('input', () => {
                normalizeSlugInputValue(slugInput);
                autoSyncSlug = String(slugInput.value || '').trim() === '';
            });

            slugInput.addEventListener('blur', () => {
                normalizeSlugInputValue(slugInput);
                syncSlugFromSource();
            });
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

    function setPageMediaPreviewText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || '-';
        }
    }

    function closePageMediaPreviewModal(restoreFocus = true) {
        const modal = document.getElementById('page-media-preview-modal');
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }

        modal.querySelector('video')?.pause();
        const box = document.getElementById('page-media-preview-box');
        if (box) {
            box.replaceChildren();
        }
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('media-library-modal-open');
        document.body.classList.remove('media-library-modal-open');
        document.querySelectorAll('.explorer-file-row').forEach((row) => {
            row.classList.remove('is-active');
        });

        const returnFocus = pageMediaPreviewReturnFocus;
        pageMediaPreviewReturnFocus = null;
        if (restoreFocus && returnFocus instanceof HTMLElement && returnFocus.isConnected) {
            returnFocus.focus();
        }
    }

    function openPageMediaPreviewModal(file, trigger = null) {
        const modal = document.getElementById('page-media-preview-modal');
        const box = document.getElementById('page-media-preview-box');
        const pathInput = document.getElementById('page-media-preview-path');
        if (!modal || !box || !pathInput || !file) {
            return;
        }

        closePageMediaEditModal();
        pageMediaPreviewReturnFocus = trigger instanceof HTMLElement
            ? trigger
            : (document.activeElement instanceof HTMLElement ? document.activeElement : null);
        document.querySelectorAll('.explorer-file-row').forEach((row) => {
            row.classList.toggle('is-active', row.dataset.filePath === (file.path || ''));
        });

        if (file.type === 'video') {
            box.innerHTML = `<video controls playsinline preload="metadata"><source src="${escapeHtmlAttr(file.url || '')}"></video>`;
        } else if (file.type === 'image') {
            box.innerHTML = `<img src="${escapeHtmlAttr(file.url || '')}" alt="${escapeHtmlAttr(file.title || file.name || '')}">`;
        } else {
            box.innerHTML = '<span class="page-media-preview-file-icon" aria-hidden="true"><i class="fas fa-file"></i></span>';
        }

        setPageMediaPreviewText('page-media-preview-subtitle', file.title || file.name || '媒体文件');
        setPageMediaPreviewText('page-media-preview-title', file.title || '-');
        setPageMediaPreviewText('page-media-preview-name', file.name || '-');
        setPageMediaPreviewText('page-media-preview-storage', file.storage_name || '-');
        setPageMediaPreviewText('page-media-preview-type', file.type === 'video' ? '视频' : (file.type === 'image' ? '图片' : '文件'));
        setPageMediaPreviewText('page-media-preview-directory', file.directory ? `/uploads/${file.directory}` : '/uploads');
        setPageMediaPreviewText('page-media-preview-size', [file.size || '', file.dimensions || ''].filter(Boolean).join(' · ') || '-');
        setPageMediaPreviewText('page-media-preview-date', file.date || '-');
        pathInput.value = file.path || '';

        document.documentElement.classList.add('media-library-modal-open');
        document.body.classList.add('media-library-modal-open');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        window.requestAnimationFrame(() => modal.querySelector('[data-media-preview-close]')?.focus());
    }

    function parseExplorerFileRow(row) {
        try {
            return JSON.parse(row.dataset.explorerFile || '{}');
        } catch (error) {
            console.warn('Invalid explorer file payload', error);
            return null;
        }
    }

    function trapFocusInsideModal(event, modal) {
        if (event.key !== 'Tab') {
            return;
        }
        const focusable = Array.from(modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter((element) => element instanceof HTMLElement && element.offsetParent !== null);
        if (focusable.length === 0) {
            event.preventDefault();
            modal.focus();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function closePageMediaEditModal() {
        const modal = document.getElementById('page-media-edit-modal');
        if (!modal) {
            return;
        }

        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.documentElement.classList.remove('media-library-modal-open');
        document.body.classList.remove('media-library-modal-open');
    }

    function openPageMediaEditModal(button) {
        const modal = document.getElementById('page-media-edit-modal');
        const pathInput = document.getElementById('page-media-edit-path');
        const originalNameInput = document.getElementById('page-media-edit-original-name');
        const titleInput = document.getElementById('page-media-edit-title');
        const storageName = document.getElementById('page-media-edit-storage-name');
        const publicPath = document.getElementById('page-media-edit-public-path');
        if (!modal || !pathInput || !originalNameInput || !titleInput || !storageName || !publicPath) {
            return;
        }

        closePageMediaPreviewModal(false);
        pathInput.value = button.dataset.editPath || '';
        originalNameInput.value = button.dataset.editOriginalName || '';
        titleInput.value = button.dataset.editTitle || '';
        storageName.textContent = button.dataset.editStorageName || '-';
        publicPath.textContent = button.dataset.editPath || '-';

        document.documentElement.classList.add('media-library-modal-open');
        document.body.classList.add('media-library-modal-open');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        originalNameInput.focus();
        originalNameInput.select();
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

        const previewModal = document.getElementById('page-media-preview-modal');
        const editModal = document.getElementById('page-media-edit-modal');
        [previewModal, editModal].forEach((modal) => {
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        document.querySelectorAll('.explorer-file-row[data-explorer-file]').forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('.explorer-check-cell') || event.target.closest('.explorer-actions')) {
                    return;
                }
                const file = parseExplorerFileRow(row);
                if (file) {
                    openPageMediaPreviewModal(file, row.querySelector('.js-page-media-preview-btn'));
                }
            });

            row.querySelector('.js-page-media-preview-btn')?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const file = parseExplorerFileRow(row);
                if (file) {
                    openPageMediaPreviewModal(file, event.currentTarget);
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

        document.querySelectorAll('.js-page-media-edit-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                openPageMediaEditModal(btn);
            });
        });

        if (previewModal) {
            previewModal.querySelectorAll('[data-media-preview-close]').forEach((btn) => {
                btn.addEventListener('click', closePageMediaPreviewModal);
            });
            previewModal.addEventListener('click', (event) => {
                if (event.target === previewModal) {
                    closePageMediaPreviewModal();
                }
            });
        }

        if (editModal) {
            editModal.querySelectorAll('[data-media-edit-close]').forEach((btn) => {
                btn.addEventListener('click', closePageMediaEditModal);
            });
            editModal.addEventListener('click', (event) => {
                if (event.target === editModal) {
                    closePageMediaEditModal();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (previewModal && !previewModal.classList.contains('hidden')) {
                if (event.key === 'Escape') {
                    closePageMediaPreviewModal();
                    return;
                }
                trapFocusInsideModal(event, previewModal);
                return;
            }
            if (event.key === 'Escape' && editModal && !editModal.classList.contains('hidden')) {
                closePageMediaEditModal();
            }
        });

        setExplorerSelectedCount();
    }

    function initResponsiveAdminTables() {
        document.querySelectorAll('.admin-table table').forEach((table) => {
            const headers = Array.from(table.querySelectorAll('thead th'));
            if (headers.length < 3) {
                return;
            }

            const labels = headers.map((header) => header.textContent.trim());
            table.classList.add('responsive-admin-table');

            table.querySelectorAll('tbody tr').forEach((row) => {
                Array.from(row.children).forEach((cell, index) => {
                    if (!(cell instanceof HTMLTableCellElement)) {
                        return;
                    }
                    if (cell.hasAttribute('colspan')) {
                        cell.classList.add('mobile-full-cell');
                        return;
                    }

                    const label = labels[index] || '';
                    cell.dataset.mobileLabel = label;
                    if (!label) {
                        cell.classList.add('mobile-selection-cell');
                    } else if (/^(操作|actions?)$/i.test(label)) {
                        cell.classList.add('mobile-action-cell');
                    }
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initGlobalActions();
        initNavToggle();
        initResponsiveAdminTables();
        initMediaLibraryModal();
        initJoditEditors();
        initPriceTierManager();
        initPostForm();
        initProductForm();
        initCollapsibleSections();
        initBulkSelectionControls();
        initSettingsGeneralPickers();
        initSettingsMediaHelpers();
        initProductSelectorModal();
        initProductPickerFields();
        initSlugFieldHelpers();
        initMediaExplorerPage();
    });

    window.openMediaLibrary = openMediaLibrary;
    window.openProductSelector = openProductSelector;
    window.copyMediaPath = copyMediaPath;
})();
