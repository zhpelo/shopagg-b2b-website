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
$requestedGroupKey = trim((string)($_GET['group'] ?? ''));
$requestedBlockKey = trim((string)($_GET['block'] ?? ''));
$activeGroupKey = isset($groupedDefinitions[$requestedGroupKey]) ? $requestedGroupKey : $firstGroupKey;
$activeBlockKey = $firstBlockKey;

if ($requestedBlockKey !== '') {
    foreach ($groupedDefinitions as $groupKey => $group) {
        if (isset($group['blocks'][$requestedBlockKey])) {
            $activeGroupKey = $groupKey;
            $activeBlockKey = $requestedBlockKey;
            break;
        }
    }
}

if ($activeBlockKey === $firstBlockKey && $activeGroupKey !== '' && isset($groupedDefinitions[$activeGroupKey])) {
    $activeBlockKey = array_key_first($groupedDefinitions[$activeGroupKey]['blocks']) ?? $firstBlockKey;
}
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
        <input type="hidden" name="active_group" value="<?= h($activeGroupKey) ?>" data-active-block-group>
        <input type="hidden" name="active_block" value="<?= h($activeBlockKey) ?>" data-active-block-key>

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
                                class="block-group-tab px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $groupKey === $activeGroupKey ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
                                onclick="switchBlockGroup('<?= h($groupKey) ?>')">
                            <?= h($group['label']) ?>
                            <span class="ml-2 text-xs opacity-75">(<?= count($group['blocks']) ?>)</span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($groupedDefinitions as $groupKey => $group): ?>
                    <div class="mt-5 <?= $groupKey === $activeGroupKey ? '' : 'hidden' ?>" data-block-group-nav="<?= h($groupKey) ?>">
                        <div class="mb-3 flex items-center gap-2 text-sm text-slate-500">
                            <i class="fas fa-folder-open text-slate-400"></i>
                            <span>当前分组：<?= h($group['label']) ?></span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($group['blocks'] as $blockKey => $block): ?>
                                <button type="button"
                                        data-block-tab="<?= h($blockKey) ?>"
                                        data-block-group="<?= h($groupKey) ?>"
                                        class="block-tab px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $blockKey === $activeBlockKey ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?>"
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
                    <div class="block-panel <?= $blockKey === $activeBlockKey ? '' : 'hidden' ?>" data-block-panel="<?= h($blockKey) ?>" data-block-group="<?= h($groupKey) ?>">
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
                                    $defaultVal = (string)($field['default'] ?? '');
                                    $hasUserVal = isset($userValues[$blockKey])
                                        && is_array($userValues[$blockKey])
                                        && array_key_exists($fieldKey, $userValues[$blockKey])
                                        && (string)$userValues[$blockKey][$fieldKey] !== '';
                                    $userVal = $hasUserVal ? (string)$userValues[$blockKey][$fieldKey] : '';
                                    $currentVal = $hasUserVal ? $userVal : $defaultVal;
                                    $fieldType = (string)($field['type'] ?? 'text');
                                    if ($fieldType === 'media') {
                                        $mediaTypeKey = (string)($field['media_type_key'] ?? '');
                                        $mediaTypeDefault = $mediaTypeKey !== '' && isset($block['fields'][$mediaTypeKey])
                                            ? (string)($block['fields'][$mediaTypeKey]['default'] ?? 'image')
                                            : (string)($field['default_media_type'] ?? 'image');
                                        $mediaTypeCurrent = $mediaTypeKey !== ''
                                            ? (string)($userValues[$blockKey][$mediaTypeKey] ?? $mediaTypeDefault)
                                            : $mediaTypeDefault;
                                        $mediaTypeCurrent = $mediaTypeCurrent === 'video' ? 'video' : 'image';
                                        $legacyImageKey = (string)($field['legacy_image_key'] ?? '');
                                        $legacyVideoKey = (string)($field['legacy_video_key'] ?? '');
                                        $hasMediaUserVal = isset($userValues[$blockKey][$fieldKey]) && (string)$userValues[$blockKey][$fieldKey] !== '';
                                        if (!$hasMediaUserVal) {
                                            $legacyImageVal = $legacyImageKey !== '' ? (string)($userValues[$blockKey][$legacyImageKey] ?? '') : '';
                                            $legacyVideoVal = $legacyVideoKey !== '' ? (string)($userValues[$blockKey][$legacyVideoKey] ?? '') : '';
                                            if ($mediaTypeCurrent === 'video' && $legacyVideoVal !== '') {
                                                $currentVal = $legacyVideoVal;
                                            } elseif ($mediaTypeCurrent === 'image' && $legacyImageVal !== '') {
                                                $currentVal = $legacyImageVal;
                                            } elseif ($legacyImageVal !== '') {
                                                $currentVal = $legacyImageVal;
                                            }
                                        }
                                    }
                                    $previewUrl = $currentVal !== '' ? (str_starts_with($currentVal, 'http') ? $currentVal : asset_url($currentVal)) : '';
                                ?>
                                    <?php if ($fieldType === 'hidden'): ?>
                                        <input id="<?= h($fieldInputId) ?>" type="hidden" name="<?= h($fieldName) ?>" value="<?= h($currentVal) ?>">
                                        <?php continue; ?>
                                    <?php endif; ?>
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

                                            <?php elseif ($fieldType === 'media'): ?>
                                                <?php
                                                    $mediaTypeKey = (string)($field['media_type_key'] ?? '');
                                                    $mediaTypeInputId = $mediaTypeKey !== '' ? 'block-field-' . $blockKey . '-' . $mediaTypeKey : '';
                                                    $mediaAllowedType = (string)($field['allowed'] ?? 'all');
                                                    $mediaAllowedType = in_array($mediaAllowedType, ['image', 'video', 'all'], true) ? $mediaAllowedType : 'all';
                                                    $mediaTypeForPreview = $mediaTypeCurrent ?? (string)($field['default_media_type'] ?? 'image');
                                                    $mediaPreviewValue = strtolower((string)$currentVal);
                                                    $isKnownImagePreview = str_contains($mediaPreviewValue, 'images.unsplash.com') || preg_match('/\.(jpg|jpeg|png|gif|webp|avif|svg|bmp)(\?.*)?$/i', (string)$currentVal);
                                                    $isVideoPreview = !$isKnownImagePreview && ($mediaTypeForPreview === 'video' || preg_match('/\.(mp4|webm|ogv|ogg|mov)(\?.*)?$/i', (string)$currentVal));
                                                ?>
                                                <div class="space-y-3" data-block-media-field>
                                                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                                                        <input id="<?= h($fieldInputId) ?>" name="<?= h($fieldName) ?>"
                                                               value="<?= h($currentVal) ?>"
                                                               data-block-media-input
                                                               data-preview-id="<?= h($fieldPreviewId) ?>"
                                                               data-type-input-id="<?= h($mediaTypeInputId) ?>"
                                                               class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                               placeholder="图片或视频路径 / URL">
                                                        <button type="button"
                                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors text-sm"
                                                                data-block-media-picker
                                                                data-input-id="<?= h($fieldInputId) ?>"
                                                                data-preview-id="<?= h($fieldPreviewId) ?>"
                                                                data-type-input-id="<?= h($mediaTypeInputId) ?>"
                                                                data-allowed-type="<?= h($mediaAllowedType) ?>">
                                                            <i class="fas fa-photo-video"></i>
                                                            选择媒体
                                                        </button>
                                                    </div>
                                                    <div id="<?= h($fieldPreviewId) ?>" class="rounded-xl border border-slate-200 bg-white p-3">
                                                        <?php if ($previewUrl !== ''): ?>
                                                            <?php if ($isVideoPreview): ?>
                                                                <video class="h-28 max-w-full rounded-lg border border-slate-200 bg-slate-900 object-cover" controls playsinline preload="metadata">
                                                                    <source src="<?= h($previewUrl) ?>">
                                                                </video>
                                                            <?php else: ?>
                                                                <img src="<?= h($previewUrl) ?>"
                                                                     alt="预览" class="h-24 rounded-lg border border-slate-200 object-cover">
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div class="flex h-24 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400">
                                                                未选择媒体
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="text-xs text-slate-400">选择图片或视频后，媒体类型会自动保存。</p>
                                                </div>

                                            <?php elseif ($fieldType === 'product_picker'): ?>
                                                <?php
                                                    $pickerMultiple = ($field['multiple'] ?? true) !== false;
                                                    $pickerLimit = (int)($field['limit'] ?? 0);
                                                    $pickerStatus = (string)($field['status'] ?? 'active');
                                                ?>
                                                <div class="space-y-3" data-product-picker-field>
                                                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                                                        <input id="<?= h($fieldInputId) ?>" name="<?= h($fieldName) ?>"
                                                               value="<?= h($currentVal) ?>"
                                                               data-product-picker-input
                                                               type="hidden">
                                                        <div class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500">
                                                            通过商品选择弹窗勾选商品，已选商品会显示在下方。
                                                        </div>
                                                        <button type="button"
                                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors text-sm"
                                                                data-product-picker-open
                                                                data-product-picker-multiple="<?= $pickerMultiple ? 'true' : 'false' ?>"
                                                                data-product-picker-max="<?= h((string)$pickerLimit) ?>"
                                                                data-product-picker-status="<?= h($pickerStatus) ?>">
                                                            <i class="fas fa-box"></i>
                                                            勾选商品
                                                        </button>
                                                        <button type="button"
                                                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors text-sm"
                                                                data-product-picker-clear>
                                                            <i class="fas fa-times"></i>
                                                            清空
                                                        </button>
                                                    </div>
                                                    <?php if ($pickerLimit > 0): ?>
                                                        <p class="text-xs text-slate-400">最多选择 <?= $pickerLimit ?> 个商品，保存值为商品 ID 列表。</p>
                                                    <?php else: ?>
                                                        <p class="text-xs text-slate-400">保存值为商品 ID 列表。</p>
                                                    <?php endif; ?>
                                                    <div data-product-picker-preview class="rounded-xl border border-slate-200 bg-white p-3">
                                                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-400">正在加载已选商品...</div>
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
                                                <?php
                                                    $selectOptions = is_array($field['options'] ?? null) ? $field['options'] : [];
                                                    $currentSelectVal = (string)$currentVal;
                                                    $hasSelectedOption = false;
                                                ?>
                                                <select id="<?= h($fieldInputId) ?>" name="<?= h($fieldName) ?>"
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                                    <?php foreach ($selectOptions as $optVal => $optLabel): ?>
                                                        <?php
                                                            $isSelected = $currentSelectVal === (string)$optVal;
                                                            $hasSelectedOption = $hasSelectedOption || $isSelected;
                                                        ?>
                                                        <option value="<?= h((string)$optVal) ?>" <?= $isSelected ? 'selected' : '' ?>>
                                                            <?= h($optLabel) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <?php if ($currentSelectVal !== '' && !$hasSelectedOption): ?>
                                                        <option value="<?= h($currentSelectVal) ?>" selected>
                                                            <?= h('已保存：' . $currentSelectVal) ?>
                                                        </option>
                                                    <?php endif; ?>
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
function persistActiveBlockState(groupKey, blockKey, updateUrl = true) {
    const groupInput = document.querySelector('[data-active-block-group]');
    const blockInput = document.querySelector('[data-active-block-key]');
    if (groupInput) {
        groupInput.value = groupKey || '';
    }
    if (blockInput) {
        blockInput.value = blockKey || '';
    }

    if (!updateUrl || typeof window.history === 'undefined') {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.delete('success');
    if (groupKey) {
        url.searchParams.set('group', groupKey);
    } else {
        url.searchParams.delete('group');
    }
    if (blockKey) {
        url.searchParams.set('block', blockKey);
    } else {
        url.searchParams.delete('block');
    }
    window.history.replaceState({}, '', url.toString());
}

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
        persistActiveBlockState(groupKey, '');
        return;
    }

    setActiveBlockGroup(groupKey);
    const blockKey = firstTab.dataset.blockTab || '';
    setActiveBlockTab(blockKey);
    persistActiveBlockState(groupKey, blockKey);
}

function switchBlockTab(blockKey) {
    const tab = document.querySelector('[data-block-tab="' + blockKey + '"]');
    const groupKey = tab ? (tab.dataset.blockGroup || '') : '';
    if (groupKey) {
        setActiveBlockGroup(groupKey);
    }
    setActiveBlockTab(blockKey);
    persistActiveBlockState(groupKey, blockKey);
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

function inferBlockMediaType(value, fallback = 'image') {
    const fullValue = String(value || '').toLowerCase();
    if (fullValue.includes('images.unsplash.com')) {
        return 'image';
    }
    const rawValue = fullValue.split('?')[0];
    if (/\.(mp4|webm|ogv|ogg|mov)$/.test(rawValue)) {
        return 'video';
    }
    if (/\.(jpg|jpeg|png|gif|webp|avif|svg|bmp)$/.test(rawValue)) {
        return 'image';
    }
    return fallback === 'video' ? 'video' : 'image';
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

function renderBlockMediaPreview(previewId, value, mediaType = 'image') {
    const preview = document.getElementById(previewId);
    if (!preview) {
        return;
    }

    const normalizedUrl = normalizeBlockImageUrl(value);
    if (!normalizedUrl) {
        preview.innerHTML = '<div class="flex h-24 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400">未选择媒体</div>';
        return;
    }

    const resolvedType = inferBlockMediaType(value, mediaType);
    if (resolvedType === 'video') {
        preview.innerHTML = '<video class="h-28 max-w-full rounded-lg border border-slate-200 bg-slate-900 object-cover" controls playsinline preload="metadata"><source src="' + escapeBlockHtml(normalizedUrl) + '"></video>';
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
        }, false, { type: 'image', lockType: true });
    });
});

document.querySelectorAll('[data-block-image-input]').forEach((input) => {
    input.addEventListener('input', () => {
        renderBlockImagePreview(input.dataset.previewId || '', input.value);
    });
});

document.querySelectorAll('[data-block-media-picker]').forEach((button) => {
    button.addEventListener('click', () => {
        const inputId = button.dataset.inputId || '';
        const previewId = button.dataset.previewId || '';
        const typeInputId = button.dataset.typeInputId || '';
        const allowedType = button.dataset.allowedType || 'all';
        const input = document.getElementById(inputId);
        const typeInput = typeInputId ? document.getElementById(typeInputId) : null;
        if (!input || typeof openMediaLibrary !== 'function') {
            return;
        }

        openMediaLibrary((item) => {
            if (!item) {
                return;
            }

            const url = item.public_path || item.url || '';
            if (!url) {
                return;
            }

            const mediaType = item.type === 'video' ? 'video' : 'image';
            input.value = url;
            if (typeInput) {
                typeInput.value = mediaType;
            }
            renderBlockMediaPreview(previewId, url, mediaType);
        }, false, { type: allowedType, returnObjects: true, lockType: allowedType !== 'all' });
    });
});

document.querySelectorAll('[data-block-media-input]').forEach((input) => {
    input.addEventListener('input', () => {
        const typeInput = input.dataset.typeInputId ? document.getElementById(input.dataset.typeInputId) : null;
        const inferredType = inferBlockMediaType(input.value, typeInput ? typeInput.value : 'image');
        if (typeInput && input.value.trim() !== '') {
            typeInput.value = inferredType;
        }
        renderBlockMediaPreview(input.dataset.previewId || '', input.value, inferredType);
    });
});

<?php if ($firstGroupKey !== '' && $firstBlockKey !== ''): ?>
setActiveBlockGroup('<?= h($activeGroupKey) ?>');
setActiveBlockTab('<?= h($activeBlockKey) ?>');
persistActiveBlockState('<?= h($activeGroupKey) ?>', '<?= h($activeBlockKey) ?>', false);
<?php endif; ?>
</script>
