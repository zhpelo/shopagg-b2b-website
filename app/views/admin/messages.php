<!-- 页面头部 -->
<div class="page-header">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="inline-flex h-5 w-5 items-center justify-center mr-2"><i class="fas fa-comment-dots"></i></span>
                    联系留言
                </h1>
                <p class="mt-1 text-sm text-white/80">共收到 <?= count($messages) ?> 条留言</p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="relative rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
        <button type="button" class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition hover:bg-emerald-200" data-dismiss-parent aria-label="关闭提示">
            <i class="fas fa-times text-xs"></i>
        </button>
        <?= h($_GET['success']) ?>
    </div>
<?php endif; ?>

<?php if (empty($messages)): ?>
    <!-- 空状态 -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="empty-state">
            <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-inbox"></i></span>
            <p>暂无留言记录</p>
        </div>
    </div>
<?php else: ?>
    <!-- 留言列表 -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden [&_thead_th]:sticky [&_thead_th]:top-0 [&_thead_th]:border-b [&_thead_th]:border-slate-200 [&_thead_th]:px-5 [&_thead_th]:py-3 [&_thead_th]:text-left [&_thead_th]:text-xs [&_thead_th]:font-bold [&_thead_th]:uppercase [&_thead_th]:tracking-wider [&_thead_th]:text-slate-500 [&_tbody_td]:px-5 [&_tbody_td]:py-4 [&_tbody_td]:align-middle [&_tbody_td]:border-b [&_tbody_td]:border-slate-100 [&_tbody_tr:last-child_td]:border-b-0 [&_tbody_tr:hover]:bg-slate-50">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-slate-700">
                <thead class="bg-gradient-to-b from-white to-slate-50">
                    <tr>
                        <th style="width: 15%;">客户信息</th>
                        <th style="width: 18%;">联系方式</th>
                        <th style="width: 12%;">公司</th>
                        <th style="width: 30%;">留言内容</th>
                        <th style="width: 13%;">时间</th>
                        <th style="width: 12%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $row): ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="icon-box mr-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        <?= strtoupper(mb_substr($row['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= h($row['name']) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-500">
                                    <p><span class="inline-flex h-4 w-4 items-center justify-center text-slate-500 mr-1"><i class="fas fa-envelope"></i></span> <?= h($row['email']) ?></p>
                                    <?php if (!empty($row['phone'])): ?>
                                        <p class="mt-1"><span class="inline-flex h-4 w-4 items-center justify-center text-slate-500 mr-1"><i class="fas fa-phone"></i></span> <?= h($row['phone']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($row['company'])): ?>
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= h($row['company']) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="max-w-[400px] text-xs leading-6 text-slate-600">
                                    <?= nl2br(h($row['message'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-500">
                                    <span class="inline-flex h-4 w-4 items-center justify-center mr-1"><i class="far fa-clock"></i></span>
                                    <?= format_date($row['created_at']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= url('/admin/messages/detail?id=' . (int)$row['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 transition hover:bg-cyan-100" title="查看详情">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= url('/admin/messages/delete?id=' . (int)$row['id']) ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" title="删除" data-confirm-message="确定要删除此留言吗？">
                                        <span class="inline-flex h-5 w-5 items-center justify-center"><i class="fas fa-trash"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>