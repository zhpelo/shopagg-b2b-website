<div class="space-y-6">
    <div class="card p-8">
        <div class="section-title">
            <span class="icon-box primary"><i class="fas fa-building"></i></span>
            公司简介 (Profile)
        </div>

        <label class="block space-y-2">
            <span class="text-sm font-medium text-slate-700">详细介绍</span>
            <textarea class="min-h-[150px] w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_bio" rows="6" placeholder="介绍公司背景、历史和优势..."><?= h($settings['company_bio'] ?? '') ?></textarea>
        </label>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">业务类型</span>
                <span class="relative block">
                    <i class="fas fa-industry pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_business_type" value="<?= h($settings['company_business_type'] ?? '') ?>" placeholder="制造商 / 贸易商">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">主营产品</span>
                <span class="relative block">
                    <i class="fas fa-box pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_main_products" value="<?= h($settings['company_main_products'] ?? '') ?>" placeholder="产品类目">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">成立年份</span>
                <span class="relative block">
                    <i class="fas fa-calendar pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_year_established" value="<?= h($settings['company_year_established'] ?? '') ?>" placeholder="2000">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">员工人数</span>
                <span class="relative block">
                    <i class="fas fa-users pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_employees" value="<?= h($settings['company_employees'] ?? '') ?>" placeholder="50-100">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">厂房面积</span>
                <span class="relative block">
                    <i class="fas fa-warehouse pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_plant_area" value="<?= h($settings['company_plant_area'] ?? '') ?>" placeholder="5000㎡">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">注册资本</span>
                <span class="relative block">
                    <i class="fas fa-dollar-sign pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_registered_capital" value="<?= h($settings['company_registered_capital'] ?? '') ?>" placeholder="1000万">
                </span>
            </label>
        </div>
    </div>

    <div class="card p-8">
        <div class="section-title">
            <span class="icon-box warning"><i class="fas fa-star"></i></span>
            资质认证
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">SGS 报告编号</span>
                <span class="relative block">
                    <i class="fas fa-certificate pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="company_sgs_report" value="<?= h($settings['company_sgs_report'] ?? '') ?>" placeholder="报告编号">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">评分</span>
                <span class="relative block">
                    <i class="fas fa-star pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="company_rating" value="<?= h($settings['company_rating'] ?? '5.0/5') ?>" placeholder="5.0/5">
                </span>
            </label>
            <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">平均响应时间</span>
                <span class="relative block">
                    <i class="fas fa-clock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-amber-400 focus:ring-2 focus:ring-amber-100" name="company_response_time" value="<?= h($settings['company_response_time'] ?? '≤24h') ?>" placeholder="≤24h">
                </span>
            </label>
        </div>
    </div>
</div>
