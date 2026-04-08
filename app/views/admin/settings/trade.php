<div class="space-y-6">
    <div class="card p-8">
        <div class="section-title">
            <span class="icon-box info"><i class="fas fa-globe"></i></span>
            贸易能力 (Trade Capacity)
        </div>

        <label class="block space-y-2">
            <span class="text-sm font-medium text-slate-700">主要市场</span>
            <span class="relative block">
                <i class="fas fa-map-marker-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_main_markets" value="<?= h($settings['company_main_markets'] ?? '') ?>" placeholder="北美、欧洲、东南亚">
            </span>
        </label>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">外贸人数</span>
                <span class="relative block">
                    <i class="fas fa-user-tie pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_trade_staff" value="<?= h($settings['company_trade_staff'] ?? '') ?>" placeholder="10">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">贸易条款 (Incoterms)</span>
                <span class="relative block">
                    <i class="fas fa-file-contract pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_incoterms" value="<?= h($settings['company_incoterms'] ?? '') ?>" placeholder="FOB, CIF, EXW">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">支付方式</span>
                <span class="relative block">
                    <i class="fas fa-credit-card pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_payment_terms" value="<?= h($settings['company_payment_terms'] ?? '') ?>" placeholder="T/T, L/C, PayPal">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">平均交期</span>
                <span class="relative block">
                    <i class="fas fa-shipping-fast pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_lead_time" value="<?= h($settings['company_lead_time'] ?? '') ?>" placeholder="15-30天">
                </span>
            </label>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-3">
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">海外分支</span>
                <span class="relative block">
                    <i class="fas fa-globe-americas pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_overseas_agent" value="<?= h($settings['company_overseas_agent'] ?? 'No') ?>" placeholder="有/无">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">出口开始年份</span>
                <span class="relative block">
                    <i class="fas fa-calendar-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_export_year" value="<?= h($settings['company_export_year'] ?? '') ?>" placeholder="2005">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">最近港口</span>
                <span class="relative block">
                    <i class="fas fa-anchor pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="company_nearest_port" value="<?= h($settings['company_nearest_port'] ?? '') ?>" placeholder="上海港">
                </span>
            </label>
        </div>
    </div>

    <div class="card p-8">
        <div class="section-title">
            <span class="icon-box success"><i class="fas fa-flask"></i></span>
            研发能力 (R&D)
        </div>

        <div class="max-w-sm">
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">研发工程师人数</span>
                <span class="relative block">
                    <i class="fas fa-user-graduate pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="company_rd_engineers" value="<?= h($settings['company_rd_engineers'] ?? '') ?>" placeholder="5">
                </span>
            </label>
        </div>
    </div>
</div>
