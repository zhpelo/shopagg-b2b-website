<?php
/**
 * 后台 - 模板区块管理
 * @var array $definitions 区块定义（来自 blocks.php）
 * @var array $userValues  用户自定义值
 * @var string $theme      当前主题
 */
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">模板区块配置</h1>
                <p class="text-indigo-100 mt-1">自定义网站各区块的文字、图片、颜色和图标，无需修改代码</p>
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

        <!-- 区块选择导航 -->
        <div class="flex flex-wrap gap-2 mb-8 pb-4 border-b border-slate-200">
            <?php $idx = 0; foreach ($definitions as $blockKey => $block): ?>
                <button type="button"
                        data-block-tab="<?= h($blockKey) ?>"
                        class="block-tab px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $idx === 0 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>"
                        onclick="switchBlockTab('<?= h($blockKey) ?>')">
                    <?= h($block['label']) ?>
                </button>
            <?php $idx++; endforeach; ?>
        </div>

        <!-- 区块表单面板 -->
        <?php $idx = 0; foreach ($definitions as $blockKey => $block): ?>
            <div class="block-panel <?= $idx > 0 ? 'hidden' : '' ?>" data-block-panel="<?= h($blockKey) ?>">
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 mb-6">
                    <div class="mb-6">
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
                            $defaultVal = $field['default'] ?? '';
                            $userVal = $userValues[$blockKey][$fieldKey] ?? '';
                            $currentVal = $userVal !== '' ? $userVal : $defaultVal;
                            $fieldType = $field['type'] ?? 'text';
                        ?>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700">
                                        <?= h($field['label']) ?>
                                    </label>
                                    <span class="text-xs text-slate-400"><?= h($fieldType) ?></span>
                                </div>
                                <div class="md:col-span-9">
                                    <?php if ($fieldType === 'textarea'): ?>
                                        <textarea name="<?= h($fieldName) ?>" rows="3"
                                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none resize-y"
                                                  placeholder="<?= h($defaultVal) ?>"><?= h($currentVal) ?></textarea>

                                    <?php elseif ($fieldType === 'color'): ?>
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="<?= h($fieldName) ?>"
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
                                        <div class="flex items-center gap-4">
                                            <input type="text" name="<?= h($fieldName) ?>"
                                                   value="<?= h($currentVal) ?>"
                                                   class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                                   placeholder="图片路径或URL">
                                            <button type="button"
                                                    class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-colors text-sm media-picker-btn"
                                                    data-target="<?= h($fieldName) ?>">
                                                <i class="fas fa-image mr-1"></i> 选择
                                            </button>
                                        </div>
                                        <?php if ($currentVal): ?>
                                            <div class="mt-2">
                                                <img src="<?= h(str_starts_with($currentVal, 'http') ? $currentVal : asset_url($currentVal)) ?>"
                                                     alt="预览" class="h-20 rounded-lg border border-slate-200 object-cover">
                                            </div>
                                        <?php endif; ?>

                                    <?php elseif ($fieldType === 'icon'): ?>
                                        <div class="flex items-center gap-3">
                                            <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                                <i class="<?= h($currentVal) ?>"></i>
                                            </span>
                                            <input type="text" name="<?= h($fieldName) ?>"
                                                   value="<?= h($currentVal) ?>"
                                                   class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none font-mono text-sm"
                                                   placeholder="<?= h($defaultVal) ?>"
                                                   oninput="this.parentElement.querySelector('i').className = this.value">
                                            <a href="https://fontawesome.com/icons" target="_blank" class="text-xs text-indigo-500 hover:underline">图标库</a>
                                        </div>

                                    <?php elseif ($fieldType === 'select'): ?>
                                        <select name="<?= h($fieldName) ?>"
                                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none bg-white">
                                            <?php foreach (($field['options'] ?? []) as $optVal => $optLabel): ?>
                                                <option value="<?= h($optVal) ?>" <?= $currentVal === (string)$optVal ? 'selected' : '' ?>>
                                                    <?= h($optLabel) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php else: /* text */ ?>
                                        <input type="text" name="<?= h($fieldName) ?>"
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

                    <!-- 重置此区块 -->
                    <div class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-500 cursor-pointer">
                            <input type="checkbox" name="reset_blocks[]" value="<?= h($blockKey) ?>" class="w-4 h-4 text-rose-500 rounded">
                            重置此区块为模板默认值
                        </label>
                    </div>
                </div>
            </div>
        <?php $idx++; endforeach; ?>

        <!-- 提交 -->
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
function switchBlockTab(key) {
    // 隐藏所有面板
    document.querySelectorAll('.block-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.block-tab').forEach(t => {
        t.classList.remove('bg-indigo-600', 'text-white');
        t.classList.add('bg-slate-100', 'text-slate-600');
    });
    // 显示目标面板
    const panel = document.querySelector('[data-block-panel="' + key + '"]');
    const tab = document.querySelector('[data-block-tab="' + key + '"]');
    if (panel) panel.classList.remove('hidden');
    if (tab) {
        tab.classList.remove('bg-slate-100', 'text-slate-600');
        tab.classList.add('bg-indigo-600', 'text-white');
    }
}

// 颜色输入联动
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    const textInput = colorInput.nextElementSibling;
    if (textInput && textInput.tagName === 'INPUT') {
        colorInput.addEventListener('input', () => { textInput.value = colorInput.value; });
    }
});
</script>
