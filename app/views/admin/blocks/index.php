<?php
/**
 * 后台 - 模板区块管理
 * @var array $definitions 区块定义（来自 blocks.php）
 * @var array $userValues  用户自定义值
 * @var string $theme      当前主题
 */

$resolveGroupMeta = static function (string $blockKey, array $block): array {
    $groupKey = trim((string)($block['group'] ?? ''));
    $groupLabel = trim((string)($block['group_label'] ?? ''));

    if ($groupKey !== '') {
        return [$groupKey, $groupLabel !== '' ? $groupLabel : $groupKey];
    }

    return match (true) {
        str_starts_with($blockKey, 'home_') => ['home', '首页'],
        $blockKey === 'page_about' => ['about', '关于我们页面'],
        $blockKey === 'page_contact' => ['contact', '联系我们页面'],
        in_array($blockKey, ['header', 'footer', 'float_contact'], true) => ['global', '全站通用'],
        $blockKey === 'brand_colors' => ['brand', '品牌与样式'],
        default => ['other', '未分组'],
    };
};

$groupedDefinitions = [];
foreach ($definitions as $blockKey => $block) {
    [$groupKey, $groupLabel] = $resolveGroupMeta($blockKey, $block);
    if (!isset($groupedDefinitions[$groupKey])) {
        $groupedDefinitions[$groupKey] = [
            'label' => $groupLabel,
            'blocks' => [],
        ];
    }

    $block['__group_key'] = $groupKey;
    $block['__group_label'] = $groupLabel;
    $groupedDefinitions[$groupKey]['blocks'][$blockKey] = $block;
}

$firstGroupKey = array_key_first($groupedDefinitions) ?? '';
$firstBlockKey = $firstGroupKey !== '' ? (array_key_first($groupedDefinitions[$firstGroupKey]['blocks']) ?? '') : '';
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-sky-600 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">模板区块配置</h1>
                <p class="text-indigo-100 mt-1">按页面分组管理模板区块内容，支持文字、图片、颜色和图标配置</p>
            </div>
            <span class="px-3 py-1 bg-white/20 text-white text-sm rounded-lg">主题：<?= h($theme) ?></span>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="m-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span><?= h($_GET['success']) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= url('/admin/appearance/blocks/save') ?>" method="post" class="p-6" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="theme" value="<?= h($theme) ?>">

        <?php if (empty($groupedDefinitions)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center text-slate-500">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                    <i class="fas fa-puzzle-piece text-2xl"></i>
                </div>
                <p class="text-lg font-semibold text-slate-700">当前主题还没有定义可配置区块</p>
                <p class="mt-2 text-sm">请在主题目录中的 `blocks.php` 里先添加区块定义。</p>
            </div>
        <?php else: ?>
            <div class="mb-8 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="mb-4">
                    <p class="text-sm font-semibold text-slate-900">按所属页面查看区块</p>
                    <p class="mt-1 text-sm text-slate-500">先选择页面分组，再编辑该页面下的具体区块。</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <?php foreach ($groupedDefinitions as $groupKey => $group): ?>
                        <button type="button"
                                data-block-group-tab="<?= h($groupKey) ?>"
                                class="block-group-tab px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $groupKey === $firstGroupKey ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
                                onclick="switchBlockGroup('<?= h($groupKey) ?>')">
                            <?= h($group['label']) ?>
                            <span class="ml-2 text-xs opacity-75">(<?= count($group['blocks']) ?>)</span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($groupedDefinitions as $groupKey => $group): ?>
                    <div class="mt-5 <?= $groupKey === $firstGroupKey ? '' : 'hidden' ?>" data-block-group-nav="<?= h($groupKey) ?>">
                        <div class="mb-3 flex items-center gap-2 text-sm text-slate-500">
                            <i class="fas fa-folder-open text-slate-400"></i>
                            <span>当前分组：<?= h($group['label']) ?></span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($group['blocks'] as $blockKey => $block): ?>
                                <button type="button"
                                        data-block-tab="<?= h($blockKey) ?>"
                                        data-block-group="<?= h($groupKey) ?>"
                                        class="block-tab px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $blockKey === $firstBlockKey ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
                                        onclick="switchBlockTab('<?= h($blockKey) ?>')">
                                    <?= h($block['label']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php foreach ($groupedDefinitions as $groupKey => $group): ?>
                <?php foreach ($group['blocks'] as $blockKey => $block): ?>
                    <div class="block-panel <?= $blockKey === $firstBlockKey ? '' : 'hidden' ?>" data-block-panel="<?= h($blockKey) ?>" data-block-group="<?= h($groupKey) ?>">
                        <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 mb-6">
                            <div class="mb-6">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                                        <i class="fas fa-layer-group"></i>
                                        <?= h($block['__group_label']) ?>
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 border border-slate-200">
                                        <i class="fas fa-code-branch"></i>
                                        <?= h($blockKey) ?>
                                    </span>
                                </div>

                                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fas fa-puzzle-piece text-indigo-500"></i>
                                    <?= h($block['label']) ?>
                                </h2>
                                <?php if (!empty($block['description'])): ?>
                                    <p class="text-sm text-slate-500 mt-1"><?= h($block['description']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="space-y-5">
                                <?php foreach ($block['fields'] as $fieldKey => $field):
                                    $fieldName = "blocks[{$blockKey}][{$fieldKey}]";
                                    $fieldInputId = 'block-field-' . $blockKey . '-' . $fieldKey;
                                    $fieldPreviewId = $fieldInputId . '-preview';
                                    $defaultVal = $field['default'] ?? '';
                                    $userVal = $userValues[$blockKey][$fieldKey] ?? '';
                                    $currentVal = $userVal !== '' ? $userVal : $defaultVal;
                                    $fieldType = $field['type'] ?? 'text';
                                    $previewUrl = $currentVal !== '' ? (str_starts_with($currentVal, 'http') ? $currentVal : asset_url($currentVal)) : '';
                                ?>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                        <div class="md:col-span-3">
                                            <label class="block text-sm font-semibold text-slate-700" for="<?= h($fieldInputId) ?>">
                                                <?= h($field['label']) ?>
                                            </label>
                                            <span class="text-xs text-slate-400"><?= h($fieldType) ?></span>
                                        </div>
                                        <div class="md:col-span-9">
                                            <?php if ($fieldType === 'textarea'): ?>
                                                <textarea id="<?= h($fieldInputId) ?>" name="<?= h($fieldName) ?>" rows="3"
                                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none resize-y"
                                                          placeholder="<?= h($defaultVal) ?>"><?= h($currentVal) ?></textarea>

                                            <?php elseif ($fieldType === 'color'): ?>
                                                <div class="flex items-center gap-3">
                                                    <input id="<?= h($fieldInputId) ?>" type="color" name="<?= h($fieldName) ?>"
                                                           value="<?= h($currentVal) ?>"
                                                           class="w-12 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                                    <input type="text"
                                                           value="<?= h($currentVal) ?>"
                                                           class="w-32 px-3 py-2 rounded-lg border border-slate-300 text-sm font-mono"
                                                           oninput="this.previousElementSibling.value = this.value"
                                                           onchange="this.previousElementSibling.value = this.value">
                                                    <?php if ($userVal !== '' && $userVal !== $defaultVal): ?>
                                                        <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">默认: <?= h($defaultVal) ?></span>
                                                    <?php endif; ?>
                                                </div>

                                            <?php elseif ($fieldType === 'image'): ?>
                                                <div class="space-y-3">
                                                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                                                        <input id="<?= h($fieldInputId) ?>" name="<?= h($fieldName) ?>"
                                                               value="<?= h($currentVal) ?>"
                                                               data-block-image-input
                                                               data-preview-id="<?= h($fieldPreviewId) ?>"
                                                               class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                               placeholder="图片路径或 URL">
                                                        <button type="button"
                                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors text-sm"
                                                                data-block-image-picker
                                                                data-input-id="<?= h($fieldInputId) ?>"
                                                                data-preview-id="<?= h($fieldPreviewId) ?>">
                                                            <i class="fas fa-image"></i>
                                                            选择图片
                                                        </button>
                                                    </div>
                                                    <div id="<?= h($fieldPreviewId) ?>" class="rounded-xl border border-slate-200 bg-white p-3">
                                                        <?php if ($previewUrl !== ''): ?>
                                                            <img src="<?= h($previewUrl) ?>"
                                                                 alt="预览" class="h-24 rounded-lg border border-slate-200 object-cover">
                                                        <?php else: ?>
                                                            <div class="flex h-24 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400">
                                                                未选择图片
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                            <?php elseif ($fieldType === 'icon'): ?>
                                                <div class="flex items-center gap-3">
                                                    <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                                        <i class="<?= h($currentVal) ?>"></i>
                                                    </span>
                                                    <input id="<?= h($fieldInputId) ?>" type="text" name="<?= h($fieldName) ?>"
                                                           value="<?= h($currentVal) ?>"
                                                           class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none font-mono text-sm"
                                                           placeholder="<?= h($defaultVal) ?>"
                                                           oninput="this.parentElement.querySelector('i').className = this.value">
                                                    <a href="https://fontawesome.com/icons" target="_blank" class="text-xs text-indigo-500 hover:underline">图标库</a>
                                                </div>

                                            <?php elseif ($fieldType === 'select'): ?>
                                                <select id="<?= h($fieldInputId) ?>" name="<?= h($fieldName) ?>"
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                                    <?php foreach (($field['options'] ?? []) as $optVal => $optLabel): ?>
                                                        <option value="<?= h($optVal) ?>" <?= $currentVal === (string)$optVal ? 'selected' : '' ?>>
                                                            <?= h($optLabel) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            <?php else: ?>
                                                <input id="<?= h($fieldInputId) ?>" type="text" name="<?= h($fieldName) ?>"
                                                       value="<?= h($currentVal) ?>"
                                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                       placeholder="<?= h($defaultVal) ?>">
                                            <?php endif; ?>

                                            <?php if ($userVal !== '' && $userVal !== $defaultVal && $fieldType !== 'color'): ?>
                                                <p class="text-xs text-slate-400 mt-1">默认值：<?= h(mb_substr($defaultVal, 0, 80)) ?><?= mb_strlen($defaultVal) > 80 ? '...' : '' ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-sm text-slate-500 cursor-pointer">
                                    <input type="checkbox" name="reset_blocks[]" value="<?= h($blockKey) ?>" class="w-4 h-4 text-rose-500 rounded">
                                    重置此区块为模板默认值
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-200">
            <a href="<?= url('/admin/appearance') ?>"
               class="px-6 py-2.5 text-slate-700 font-medium bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                返回
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/25">
                <i class="fas fa-save mr-2"></i>
                保存全部区块
            </button>
        </div>
    </form>
</div>

<script>
function setActiveBlockGroup(groupKey) {
    document.querySelectorAll('[data-block-group-nav]').forEach((nav) => {
        nav.classList.toggle('hidden', nav.dataset.blockGroupNav !== groupKey);
    });

    document.querySelectorAll('.block-group-tab').forEach((tab) => {
        const isActive = tab.dataset.blockGroupTab === groupKey;
        tab.classList.toggle('bg-slate-900', isActive);
        tab.classList.toggle('text-white', isActive);
        tab.classList.toggle('bg-white', !isActive);
        tab.classList.toggle('text-slate-600', !isActive);
    });
}

function setActiveBlockTab(blockKey) {
    document.querySelectorAll('.block-tab').forEach((tab) => {
        const isActive = tab.dataset.blockTab === blockKey;
        tab.classList.toggle('bg-indigo-600', isActive);
        tab.classList.toggle('text-white', isActive);
        tab.classList.toggle('bg-white', !isActive);
        tab.classList.toggle('text-slate-600', !isActive);
    });

    document.querySelectorAll('.block-panel').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.blockPanel !== blockKey);
    });
}

function switchBlockGroup(groupKey) {
    const firstTab = document.querySelector('[data-block-group-nav="' + groupKey + '"] [data-block-tab]');
    if (!firstTab) {
        setActiveBlockGroup(groupKey);
        document.querySelectorAll('.block-panel').forEach((panel) => panel.classList.add('hidden'));
        return;
    }

    setActiveBlockGroup(groupKey);
    setActiveBlockTab(firstTab.dataset.blockTab || '');
}

function switchBlockTab(blockKey) {
    const tab = document.querySelector('[data-block-tab="' + blockKey + '"]');
    const groupKey = tab ? (tab.dataset.blockGroup || '') : '';
    if (groupKey) {
        setActiveBlockGroup(groupKey);
    }
    setActiveBlockTab(blockKey);
}

function escapeBlockHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function normalizeBlockImageUrl(value) {
    const rawValue = String(value || '').trim();
    if (!rawValue) {
        return '';
    }

    if (/^(https?:)?\/\//i.test(rawValue) || rawValue.startsWith('data:') || rawValue.startsWith('/')) {
        return rawValue;
    }

    const basePath = (document.documentElement.dataset.appBasePath || '').replace(/\/$/, '');
    return (basePath ? basePath + '/' : '/') + rawValue.replace(/^\/+/, '');
}

function renderBlockImagePreview(previewId, value) {
    const preview = document.getElementById(previewId);
    if (!preview) {
        return;
    }

    const normalizedUrl = normalizeBlockImageUrl(value);
    if (!normalizedUrl) {
        preview.innerHTML = '<div class="flex h-24 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400">未选择图片</div>';
        return;
    }

    preview.innerHTML = '<img src="' + escapeBlockHtml(normalizedUrl) + '" alt="预览" class="h-24 rounded-lg border border-slate-200 object-cover">';
}

document.querySelectorAll('input[type="color"]').forEach((colorInput) => {
    const textInput = colorInput.nextElementSibling;
    if (textInput && textInput.tagName === 'INPUT') {
        colorInput.addEventListener('input', () => { textInput.value = colorInput.value; });
    }
});

document.querySelectorAll('[data-block-image-picker]').forEach((button) => {
    button.addEventListener('click', () => {
        const inputId = button.dataset.inputId || '';
        const previewId = button.dataset.previewId || '';
        const input = document.getElementById(inputId);
        if (!input || typeof openMediaLibrary !== 'function') {
            return;
        }

        openMediaLibrary((url) => {
            if (!url) {
                return;
            }

            input.value = url;
            renderBlockImagePreview(previewId, url);
        }, false, { type: 'image' });
    });
});

document.querySelectorAll('[data-block-image-input]').forEach((input) => {
    input.addEventListener('input', () => {
        renderBlockImagePreview(input.dataset.previewId || '', input.value);
    });
});

<?php if ($firstGroupKey !== '' && $firstBlockKey !== ''): ?>
setActiveBlockGroup('<?= h($firstGroupKey) ?>');
setActiveBlockTab('<?= h($firstBlockKey) ?>');
<?php endif; ?>
</script>
