<!-- 页面头部 -->
<div class="page-header bg-gradient-to-br from-gray-500 to-gray-700 shadow-[0_10px_40px_rgba(108,117,125,0.3)]">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/16 text-white">
                    <i class="fas fa-cog"></i>
                </span>
                <span>系统设置</span>
            </h1>
            <p class="mt-2 text-sm text-white/80">管理网站配置、公司资料、媒体展示和自定义代码。</p>
        </div>
    </div>
</div>

<form method="post" action="<?= url($settings_form_action ?? '/admin/settings-general') ?>" class="modern-form">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="tab" value="<?= h($tab) ?>">

    <?php include __DIR__ . '/' . ($settings_section_view ?? 'general') . '.php'; ?>

    <div class="mt-6">
        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800" type="submit">
            <i class="fas fa-save text-xs"></i>
            <span>保存设置</span>
        </button>
    </div>
</form>
