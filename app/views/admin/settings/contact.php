<div class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-phone"></i></span>
                联系信息
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">邮箱</span>
                    <span class="relative block">
                        <i class="fas fa-envelope pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_email" value="<?= h($settings['company_email'] ?? '') ?>" placeholder="contact@example.com">
                    </span>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">电话</span>
                    <span class="relative block">
                        <i class="fas fa-phone pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_phone" value="<?= h($settings['company_phone'] ?? '') ?>" placeholder="+86 123 4567 8900">
                    </span>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium text-slate-700">WhatsApp</span>
                    <span class="relative block">
                        <i class="fab fa-whatsapp pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="whatsapp" value="<?= h($settings['whatsapp'] ?? '') ?>" placeholder="+86 123 4567 8900">
                    </span>
                </label>
            </div>

            <label class="mt-6 block space-y-2">
                <span class="text-sm font-medium text-slate-700">公司地址</span>
                <span class="relative block">
                    <i class="fas fa-map-marker-alt pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" name="company_address" value="<?= h($settings['company_address'] ?? '') ?>" placeholder="详细地址">
                </span>
            </label>
        </div>

        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-share-alt"></i></span>
                社交媒体
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <?php
                $social_icons = [
                    'facebook' => ['Facebook', 'fab fa-facebook-f', '#1877f2'],
                    'instagram' => ['Instagram', 'fab fa-instagram', '#e4405f'],
                    'twitter' => ['Twitter', 'fab fa-twitter', '#1da1f2'],
                    'linkedin' => ['LinkedIn', 'fab fa-linkedin-in', '#0a66c2'],
                    'youtube' => ['YouTube', 'fab fa-youtube', '#ff0000']
                ];
                foreach ($social_icons as $key => $info): ?>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-slate-700"><?= $info[0] ?></span>
                        <span class="relative block">
                            <i class="<?= $info[1] ?> pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color: <?= $info[2] ?>;"></i>
                            <input class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 focus:border-sky-400 focus:ring-2 focus:ring-sky-100" name="<?= $key ?>" value="<?= h($settings[$key] ?? '') ?>" placeholder="<?= $info[0] ?> URL">
                        </span>
                    </label>
                <?php endforeach; ?>
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
                <p><strong>邮箱 / 电话 / WhatsApp</strong>：用于页脚、联系模块和询盘转化入口。</p>
                <p><strong>公司地址</strong>：建议填写完整地址，便于搜索引擎和客户识别。</p>
                <p><strong>社交媒体</strong>：前台会按已填写的链接显示对应图标。</p>
            </div>
        </div>
    </div>
</div>
