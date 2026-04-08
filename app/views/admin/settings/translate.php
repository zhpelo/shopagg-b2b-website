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

            <div class="mt-6 space-y-3">
                <label class="text-sm font-medium text-slate-700">前台可选翻译语种</label>
                <input type="hidden" name="translate_languages[]" value="en">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($translateLanguageOptions as $langCode => $langLabel): ?>
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            <input class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" type="checkbox" name="translate_languages[]" value="<?= h($langCode) ?>" <?= in_array($langCode, $selectedTranslateLanguages, true) ? 'checked' : '' ?> <?= $langCode === 'en' ? 'disabled' : '' ?>>
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
