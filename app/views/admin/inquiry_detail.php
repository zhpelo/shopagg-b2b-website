<?php
$status_styles = [
    'pending' => ['bg' => 'rgba(255, 193, 7, 0.15)', 'color' => '#d39e00', 'icon' => 'clock', 'gradient' => 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)'],
    'contacted' => ['bg' => 'rgba(23, 162, 184, 0.15)', 'color' => '#17a2b8', 'icon' => 'phone', 'gradient' => 'linear-gradient(135deg, #17a2b8 0%, #20c997 100%)'],
    'quoted' => ['bg' => 'rgba(40, 167, 69, 0.15)', 'color' => '#28a745', 'icon' => 'file-invoice-dollar', 'gradient' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)'],
    'closed' => ['bg' => 'rgba(108, 117, 125, 0.15)', 'color' => '#6c757d', 'icon' => 'check-circle', 'gradient' => 'linear-gradient(135deg, #6c757d 0%, #495057 100%)']
];
$status_labels = [
    'pending' => '待处理',
    'contacted' => '已联系',
    'quoted' => '已报价',
    'closed' => '已关闭'
];
$style = $status_styles[$inquiry['status']] ?? $status_styles['pending'];

$statusButtonClasses = [
    'pending' => [
        'active' => 'bg-amber-500 text-white border-transparent shadow-sm',
        'inactive' => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
        'icon' => 'clock',
        'label' => '待处理',
    ],
    'contacted' => [
        'active' => 'bg-sky-500 text-white border-transparent shadow-sm',
        'inactive' => 'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100',
        'icon' => 'phone',
        'label' => '已联系',
    ],
    'quoted' => [
        'active' => 'bg-emerald-500 text-white border-transparent shadow-sm',
        'inactive' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
        'icon' => 'file-invoice-dollar',
        'label' => '已报价',
    ],
    'closed' => [
        'active' => 'bg-slate-700 text-white border-transparent shadow-sm',
        'inactive' => 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100',
        'icon' => 'check-circle',
        'label' => '已关闭',
    ],
];
?>

<!-- 页面头部 -->
<div class="page-header" style="background: <?= $style['gradient'] ?>;">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/16 text-white">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <span>询单详情 #<?= $inquiry['id'] ?></span>
            </h1>
            <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-white/80">
                <i class="fas fa-<?= $style['icon'] ?> text-xs"></i>
                <span><?= $status_labels[$inquiry['status']] ?? $inquiry['status'] ?></span>
                <span class="hidden sm:inline">&middot;</span>
                <span><?= format_date($inquiry['created_at']) ?></span>
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= url('/admin/inquiries') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/35 bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-user"></i></span>
                客户信息
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">客户姓名</p>
                    <p class="text-lg font-semibold text-slate-900"><?= h($inquiry['name']) ?></p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">公司名称</p>
                    <p class="text-lg text-slate-700"><?= h($inquiry['company']) ?: '<span class="text-slate-300">未填写</span>' ?></p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">邮箱地址</p>
                    <p>
                        <a href="mailto:<?= h($inquiry['email']) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 transition hover:text-sky-700">
                            <i class="fas fa-envelope text-xs"></i>
                            <span><?= h($inquiry['email']) ?></span>
                        </a>
                    </p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">联系电话</p>
                    <p>
                        <?php if (!empty($inquiry['phone'])): ?>
                            <a href="tel:<?= h($inquiry['phone']) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 transition hover:text-sky-700">
                                <i class="fas fa-phone text-xs"></i>
                                <span><?= h($inquiry['phone']) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="text-sm text-slate-300">未填写</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-comment-alt"></i></span>
                询单内容
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">需求产品</p>
                    <p>
                        <?php if (!empty($inquiry['product_title'])): ?>
                            <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-sm font-medium text-sky-700"><?= h($inquiry['product_title']) ?></span>
                        <?php else: ?>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">通用咨询</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">需求数量</p>
                    <p class="text-lg font-semibold text-slate-900">
                        <?= h($inquiry['quantity']) ?: '<span class="text-base font-normal text-slate-300">未指定</span>' ?>
                    </p>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">详细需求</p>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm leading-8 text-slate-700">
                    <?= nl2br(h($inquiry['message'])) ?>
                </div>
            </div>
        </div>

        <div class="card p-8">
            <div class="section-title">
                <span class="icon-box warning"><i class="fas fa-info-circle"></i></span>
                来源信息
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">IP 地址</p>
                    <p class="flex items-center gap-2 text-sm text-slate-700">
                        <i class="fas fa-map-marker-alt text-xs text-slate-400"></i>
                        <span><?= h($inquiry['ip']) ?: '未知' ?></span>
                    </p>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">提交时间</p>
                    <p class="flex items-center gap-2 text-sm text-slate-700">
                        <i class="far fa-clock text-xs text-slate-400"></i>
                        <span><?= format_date($inquiry['created_at']) ?></span>
                    </p>
                </div>
            </div>

            <?php if (!empty($inquiry['source_url'])): ?>
                <div class="mt-6 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">来源页面</p>
                    <p>
                        <a href="<?= h($inquiry['source_url']) ?>" target="_blank" class="inline-flex items-center gap-2 break-all text-sm font-medium text-sky-600 transition hover:text-sky-700">
                            <i class="fas fa-external-link-alt text-xs"></i>
                            <span><?= h($inquiry['source_url']) ?></span>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($inquiry['user_agent'])): ?>
                <div class="mt-6 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">浏览器信息</p>
                    <p class="break-all rounded-2xl border border-slate-200 bg-slate-50 p-4 text-slate-500">
                        <?= h($inquiry['user_agent']) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-6 xl:col-span-4">
        <div class="card p-6">
            <div class="section-title">
                <span class="icon-box success"><i class="fas fa-tasks"></i></span>
                状态管理
            </div>

            <div class="mb-5">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">当前状态</p>
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold" style="background: <?= $style['bg'] ?>; color: <?= $style['color'] ?>;">
                    <i class="fas fa-<?= $style['icon'] ?> text-xs"></i>
                    <span><?= $status_labels[$inquiry['status']] ?? $inquiry['status'] ?></span>
                </span>
            </div>

            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">更改状态</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <?php foreach ($statusButtonClasses as $statusKey => $statusInfo): ?>
                        <a
                            href="<?= url('/admin/inquiries/status?id=' . (int)$inquiry['id'] . '&status=' . urlencode($statusKey) . '&redirect=' . urlencode(url('/admin/inquiries/detail?id=' . $inquiry['id']))) ?>"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-medium transition <?= $inquiry['status'] === $statusKey ? $statusInfo['active'] : $statusInfo['inactive'] ?>">
                            <i class="fas fa-<?= $statusInfo['icon'] ?> text-xs"></i>
                            <span><?= $statusInfo['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-bolt"></i></span>
                快捷操作
            </div>

            <div class="flex flex-col gap-3">
                <a href="mailto:<?= h($inquiry['email']) ?>?subject=<?= urlencode('Re: 询单回复 - ' . ($inquiry['product_title'] ?? '通用咨询')) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600">
                    <i class="fas fa-reply text-xs"></i>
                    <span>邮件回复客户</span>
                </a>

                <?php if (!empty($inquiry['phone'])): ?>
                    <a href="tel:<?= h($inquiry['phone']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                        <i class="fas fa-phone text-xs"></i>
                        <span>拨打电话</span>
                    </a>
                <?php endif; ?>

                <?php if (!empty($inquiry['product_id'])): ?>
                    <a href="<?= url('/admin/products/edit?id=' . (int)$inquiry['product_id']) ?>" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                        <i class="fas fa-box text-xs"></i>
                        <span>查看关联产品</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card p-6">
            <div class="section-title">
                <span class="icon-box danger"><i class="fas fa-exclamation-triangle"></i></span>
                危险操作
            </div>

            <a
                href="<?= url('/admin/inquiries/delete?id=' . (int)$inquiry['id']) ?>"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-100"
                data-confirm-message="确定要删除此询单吗？此操作不可恢复。">
                <i class="fas fa-trash text-xs"></i>
                <span>删除此询单</span>
            </a>
        </div>
    </div>
</div>