<div class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-language"></i></span>
                前台翻译功能
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">启用网页翻译</span>
                    <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="translate_enabled">
                        <option value="1" <?= ($settings['translate_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>启用</option>
                        <option value="0" <?= ($settings['translate_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>禁用</option>
                    </select>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">根据浏览器语言自动翻译</span>
                    <select class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="translate_auto_browser">
                        <option value="0" <?= ($settings['translate_auto_browser'] ?? '0') === '0' ? 'selected' : '' ?>>关闭</option>
                        <option value="1" <?= ($settings['translate_auto_browser'] ?? '0') === '1' ? 'selected' : '' ?>>开启</option>
                    </select>
                    <p class="text-xs text-slate-500">仅在用户尚未手动选择语言时生效</p>
                </label>
            </div>

            <div class="mt-6 space-y-3" data-translate-language-picker>
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <label class="text-sm font-medium text-slate-700">前台可选翻译语种</label>
                        <p class="mt-1 text-xs text-slate-500">
                            当前支持 <?= h((string)($supportedTranslateLanguageCount ?? count($translateLanguageOptions))) ?> 个 Google Translate 语种，已选择 <span data-translate-language-count>0</span> 个。
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600" type="button" data-translate-language-action="select-all">全选</button>
                        <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-300 hover:text-indigo-600" type="button" data-translate-language-action="clear">清空</button>
                    </div>
                </div>
                <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" type="search" placeholder="搜索语种名称、英文名称或语言代码" data-translate-language-search>
                <input type="hidden" name="translate_languages[]" value="en">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($translateLanguageOptions as $langCode => $langLabel): ?>
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700" data-translate-language-option data-search="<?= h(mb_strtolower($langCode . ' ' . $langLabel, 'UTF-8')) ?>">
                            <input class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" type="checkbox" name="translate_languages[]" value="<?= h($langCode) ?>" <?= in_array($langCode, $selectedTranslateLanguages, true) ? 'checked' : '' ?> <?= $langCode === 'en' ? 'disabled' : '' ?> data-translate-language-checkbox>
                            <span><?= h($langLabel) ?> <?= $langCode === 'en' ? '(默认原文)' : '' ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-slate-500">English 固定保留，用于“英文不翻译（恢复原文）”。</p>
            </div>
        </div>
    </div>

    <div class="space-y-6 xl:col-span-4">
        <div class="card p-6">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-info"></i></span>
                设置说明
            </div>
            <div class="space-y-3 text-sm leading-6 text-slate-600">
                <p><strong>启用网页翻译</strong>：控制前台是否显示翻译下拉框。</p>
                <p><strong>自动翻译</strong>：按浏览器语言自动切换（若不在可选语种内则保持英文）。</p>
                <p><strong>可选语种</strong>：控制前台下拉框中的语言列表。</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var picker = document.querySelector('[data-translate-language-picker]');
    if (!picker) return;

    var search = picker.querySelector('[data-translate-language-search]');
    var countEl = picker.querySelector('[data-translate-language-count]');
    var options = Array.prototype.slice.call(picker.querySelectorAll('[data-translate-language-option]'));
    var checkboxes = Array.prototype.slice.call(picker.querySelectorAll('[data-translate-language-checkbox]'));

    function updateCount() {
        if (!countEl) return;
        countEl.textContent = String(checkboxes.filter(function(checkbox) {
            return checkbox.checked;
        }).length);
    }

    if (search) {
        search.addEventListener('input', function() {
            var keyword = search.value.trim().toLowerCase();
            options.forEach(function(option) {
                var haystack = option.getAttribute('data-search') || '';
                option.style.display = !keyword || haystack.indexOf(keyword) !== -1 ? '' : 'none';
            });
        });
    }

    picker.querySelectorAll('[data-translate-language-action]').forEach(function(button) {
        button.addEventListener('click', function() {
            var shouldCheck = button.getAttribute('data-translate-language-action') === 'select-all';
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.disabled) checkbox.checked = shouldCheck;
            });
            updateCount();
        });
    });

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateCount);
    });
    updateCount();
});
</script>
