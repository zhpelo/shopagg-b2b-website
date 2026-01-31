<!-- 页面头部 -->
<div class="page-header animate-in">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-comment-dots"></i></span>
                    联系留言
                </h1>
                <p class="subtitle is-6">共收到 <?= count($messages) ?> 条留言</p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="notification is-success is-light animate-in">
    <button class="delete" onclick="this.parentElement.remove()"></button>
    <?= h($_GET['success']) ?>
</div>
<?php endif; ?>

<?php if (empty($messages)): ?>
<!-- 空状态 -->
<div class="admin-card animate-in delay-1">
    <div class="empty-state">
        <span class="icon"><i class="fas fa-inbox"></i></span>
        <p>暂无留言记录</p>
    </div>
</div>
<?php else: ?>
<!-- 留言列表 -->
<div class="modern-table animate-in delay-1">
    <div class="table-container">
        <table class="table is-fullwidth">
            <thead>
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
                        <div class="is-flex is-align-items-center">
                            <div class="icon-box mr-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                <?= strtoupper(mb_substr($row['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= h($row['name']) ?></strong>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="is-size-7">
                            <p><span class="icon is-small has-text-grey mr-1"><i class="fas fa-envelope"></i></span> <?= h($row['email']) ?></p>
                            <?php if (!empty($row['phone'])): ?>
                            <p class="mt-1"><span class="icon is-small has-text-grey mr-1"><i class="fas fa-phone"></i></span> <?= h($row['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($row['company'])): ?>
                        <span class="tag is-light"><?= h($row['company']) ?></span>
                        <?php else: ?>
                        <span class="has-text-grey-light">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="content is-size-7" style="max-width: 400px;">
                            <?= nl2br(h($row['message'])) ?>
                        </div>
                    </td>
                    <td>
                        <div class="is-size-7 has-text-grey">
                            <span class="icon is-small mr-1"><i class="far fa-clock"></i></span>
                            <?= format_date($row['created_at']) ?>
                        </div>
                    </td>
                    <td>
                        <div class="buttons are-small">
                            <a href="<?= url('/admin/messages/detail?id=' . (int)$row['id']) ?>" class="button is-info is-light" title="查看详情">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                            <a href="<?= url('/admin/messages/delete?id=' . (int)$row['id']) ?>" class="button is-danger is-light" title="删除" onclick="return confirm('确定要删除此留言吗？')">
                                <span class="icon"><i class="fas fa-trash"></i></span>
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
