<!-- 页面头部 -->
<div class="page-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/16 text-white">
                    <i class="fas fa-comment-dots"></i>
                </span>
                <span>留言详情 #<?= $message['id'] ?></span>
            </h1>
            <p class="mt-2 flex items-center gap-2 text-sm text-white/80">
                <i class="far fa-clock text-xs"></i>
                <span><?= format_date($message['created_at']) ?></span>
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= url('/admin/messages') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/35 bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-user"></i></span>
                客户信息
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">客户姓名</p>
                    <p class="text-lg font-semibold text-slate-900"><?= h($message['name']) ?></p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">公司名称</p>
                    <p class="text-lg text-slate-700">
                        <?= h($message['company']) ?: '<span class="text-slate-300">未填写</span>' ?>
                    </p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">邮箱地址</p>
                    <p>
                        <a href="mailto:<?= h($message['email']) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 transition hover:text-sky-700">
                            <i class="fas fa-envelope text-xs"></i>
                            <span><?= h($message['email']) ?></span>
                        </a>
                    </p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">联系电话</p>
                    <p>
                        <?php if (!empty($message['phone'])): ?>
                            <a href="tel:<?= h($message['phone']) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 transition hover:text-sky-700">
                                <i class="fas fa-phone text-xs"></i>
                                <span><?= h($message['phone']) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="text-sm text-slate-300">未填写</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-comment-alt"></i></span>
                留言内容
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm leading-8 text-slate-700">
                <?= nl2br(h($message['message'])) ?>
            </div>

            <div class="mt-5 border-t border-slate-200 pt-5 text-xs text-slate-500">
                <p class="flex items-center gap-2">
                    <i class="far fa-clock text-[11px]"></i>
                    <span>提交时间：<?= format_date($message['created_at']) ?></span>
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-6 xl:col-span-4">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-bolt"></i></span>
                快捷操作
            </div>

            <div class="flex flex-col gap-3">
                <a href="mailto:<?= h($message['email']) ?>?subject=<?= urlencode('Re: 感谢您的留言') ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600">
                    <i class="fas fa-reply text-xs"></i>
                    <span>邮件回复客户</span>
                </a>

                <?php if (!empty($message['phone'])): ?>
                    <a href="tel:<?= h($message['phone']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                        <i class="fas fa-phone text-xs"></i>
                        <span>拨打电话</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
            <div class="section-title">
                <span class="icon-box success"><i class="fas fa-id-card"></i></span>
                客户名片
            </div>

            <div class="mb-5 text-center">
                <div class="avatar-circle lg mx-auto mb-3">
                    <?= strtoupper(mb_substr($message['name'], 0, 1)) ?>
                </div>
                <p class="text-lg font-semibold text-slate-900"><?= h($message['name']) ?></p>
                <?php if (!empty($message['company'])): ?>
                    <p class="mt-1 text-sm text-slate-500"><?= h($message['company']) ?></p>
                <?php endif; ?>
            </div>

            <div class="space-y-3 text-sm text-slate-600">
                <?php if (!empty($message['email'])): ?>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-envelope text-xs text-slate-400"></i>
                        <span><?= h($message['email']) ?></span>
                    </p>
                <?php endif; ?>
                <?php if (!empty($message['phone'])): ?>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-phone text-xs text-slate-400"></i>
                        <span><?= h($message['phone']) ?></span>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
            <div class="section-title">
                <span class="icon-box danger"><i class="fas fa-exclamation-triangle"></i></span>
                危险操作
            </div>

            <a
                href="<?= url('/admin/messages/delete?id=' . (int)$message['id']) ?>"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-100"
                data-confirm-message="确定要删除此留言吗？此操作不可恢复。">
                <i class="fas fa-trash text-xs"></i>
                <span>删除此留言</span>
            </a>
        </div>
    </div>
</div>