<div class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-code"></i></span>
                自定义代码
            </div>

            <div class="space-y-5">
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">Head 自定义代码</span>
                    <textarea class="min-h-[150px] w-full rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 font-mono text-sm text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="head_code" rows="6" placeholder="例如：统计代码、验证代码、全站样式..."><?= h($settings['head_code'] ?? '') ?></textarea>
                    <p class="text-xs text-slate-500">会插入到 &lt;head&gt; 末尾，请确保代码安全。</p>
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-medium text-slate-700">Footer 自定义代码</span>
                    <textarea class="min-h-[150px] w-full rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 font-mono text-sm text-slate-100 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="footer_code" rows="6" placeholder="例如：脚本、像素追踪代码..."><?= h($settings['footer_code'] ?? '') ?></textarea>
                    <p class="text-xs text-slate-500">会插入到 &lt;/body&gt; 前。</p>
                </label>
            </div>
        </div>
    </div>

    <div class="space-y-6 xl:col-span-4">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-info"></i></span>
                设置说明
            </div>
            <div class="space-y-3 text-sm leading-6 text-slate-600">
                <p><strong>Head</strong>：用于统计/验证/全站样式等。</p>
                <p><strong>Footer</strong>：用于脚本或追踪代码。</p>
                <p>保存后会在前台模板中输出。</p>
            </div>
        </div>
    </div>
</div>
