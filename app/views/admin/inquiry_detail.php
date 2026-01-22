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
?>

<!-- 页面头部 -->
<div class="page-header animate-in" style="background: <?= $style['gradient'] ?>;">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-file-invoice"></i></span>
                    询单详情 #<?= $inquiry['id'] ?>
                </h1>
                <p class="subtitle is-6">
                    <span class="icon is-small mr-1"><i class="fas fa-<?= $style['icon'] ?>"></i></span>
                    <?= $status_labels[$inquiry['status']] ?? $inquiry['status'] ?>
                    &nbsp;·&nbsp;
                    <?= format_date($inquiry['created_at']) ?>
                </p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/inquiries" class="button is-white is-outlined">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <!-- 左侧：客户信息和询单内容 -->
    <div class="column is-8">
        <!-- 客户信息卡片 -->
        <div class="admin-card mb-5 animate-in delay-1" style="padding: 2rem;">
            <div class="section-title">
                <span class="icon-box info"><i class="fas fa-user"></i></span>
                客户信息
            </div>
            
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">客户姓名</label>
                        <p class="is-size-5 has-text-weight-semibold"><?= h($inquiry['name']) ?></p>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">公司名称</label>
                        <p class="is-size-5"><?= h($inquiry['company']) ?: '<span class="has-text-grey-light">未填写</span>' ?></p>
                    </div>
                </div>
            </div>
            
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">邮箱地址</label>
                        <p>
                            <a href="mailto:<?= h($inquiry['email']) ?>" class="has-text-link">
                                <span class="icon is-small mr-1"><i class="fas fa-envelope"></i></span>
                                <?= h($inquiry['email']) ?>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">联系电话</label>
                        <p>
                            <?php if (!empty($inquiry['phone'])): ?>
                            <a href="tel:<?= h($inquiry['phone']) ?>" class="has-text-link">
                                <span class="icon is-small mr-1"><i class="fas fa-phone"></i></span>
                                <?= h($inquiry['phone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="has-text-grey-light">未填写</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 询单内容卡片 -->
        <div class="admin-card mb-5 animate-in delay-2" style="padding: 2rem;">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-comment-alt"></i></span>
                询单内容
            </div>
            
            <div class="columns mb-4">
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">需求产品</label>
                        <p>
                            <?php if (!empty($inquiry['product_title'])): ?>
                            <span class="tag is-info is-medium"><?= h($inquiry['product_title']) ?></span>
                            <?php else: ?>
                            <span class="tag is-light is-medium">通用咨询</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">需求数量</label>
                        <p class="is-size-5 has-text-weight-semibold">
                            <?= h($inquiry['quantity']) ?: '<span class="has-text-grey-light has-text-weight-normal">未指定</span>' ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label is-small has-text-grey">详细需求</label>
                <div class="content" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; line-height: 1.8;">
                    <?= nl2br(h($inquiry['message'])) ?>
                </div>
            </div>
        </div>
        
        <!-- 来源信息卡片 -->
        <div class="admin-card animate-in delay-3" style="padding: 2rem;">
            <div class="section-title">
                <span class="icon-box warning"><i class="fas fa-info-circle"></i></span>
                来源信息
            </div>
            
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">IP 地址</label>
                        <p>
                            <span class="icon is-small mr-1 has-text-grey"><i class="fas fa-map-marker-alt"></i></span>
                            <?= h($inquiry['ip']) ?: '未知' ?>
                        </p>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">提交时间</label>
                        <p>
                            <span class="icon is-small mr-1 has-text-grey"><i class="far fa-clock"></i></span>
                            <?= format_date($inquiry['created_at']) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($inquiry['source_url'])): ?>
            <div class="field">
                <label class="label is-small has-text-grey">来源页面</label>
                <p>
                    <a href="<?= h($inquiry['source_url']) ?>" target="_blank" class="has-text-link">
                        <span class="icon is-small mr-1"><i class="fas fa-external-link-alt"></i></span>
                        <?= h($inquiry['source_url']) ?>
                    </a>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($inquiry['user_agent'])): ?>
            <div class="field">
                <label class="label is-small has-text-grey">浏览器信息</label>
                <p class="is-size-7 has-text-grey" style="word-break: break-all;">
                    <?= h($inquiry['user_agent']) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 右侧：状态管理和操作 -->
    <div class="column is-4">
        <!-- 状态管理卡片 -->
        <div class="admin-card mb-5 animate-in delay-1" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box success"><i class="fas fa-tasks"></i></span>
                状态管理
            </div>
            
            <div class="mb-4">
                <label class="label is-small has-text-grey mb-2">当前状态</label>
                <span class="tag is-medium" style="background: <?= $style['bg'] ?>; color: <?= $style['color'] ?>;">
                    <span class="icon is-small mr-1"><i class="fas fa-<?= $style['icon'] ?>"></i></span>
                    <?= $status_labels[$inquiry['status']] ?? $inquiry['status'] ?>
                </span>
            </div>
            
            <div class="field">
                <label class="label is-small has-text-grey mb-2">更改状态</label>
                <div class="buttons">
                    <a href="/admin/inquiries/status?id=<?= $inquiry['id'] ?>&status=pending&redirect=<?= urlencode('/admin/inquiries/detail?id=' . $inquiry['id']) ?>" 
                       class="button is-small <?= $inquiry['status'] === 'pending' ? 'is-warning' : 'is-warning is-light' ?>">
                        <span class="icon"><i class="fas fa-clock"></i></span>
                        <span>待处理</span>
                    </a>
                    <a href="/admin/inquiries/status?id=<?= $inquiry['id'] ?>&status=contacted&redirect=<?= urlencode('/admin/inquiries/detail?id=' . $inquiry['id']) ?>" 
                       class="button is-small <?= $inquiry['status'] === 'contacted' ? 'is-info' : 'is-info is-light' ?>">
                        <span class="icon"><i class="fas fa-phone"></i></span>
                        <span>已联系</span>
                    </a>
                    <a href="/admin/inquiries/status?id=<?= $inquiry['id'] ?>&status=quoted&redirect=<?= urlencode('/admin/inquiries/detail?id=' . $inquiry['id']) ?>" 
                       class="button is-small <?= $inquiry['status'] === 'quoted' ? 'is-success' : 'is-success is-light' ?>">
                        <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span>已报价</span>
                    </a>
                    <a href="/admin/inquiries/status?id=<?= $inquiry['id'] ?>&status=closed&redirect=<?= urlencode('/admin/inquiries/detail?id=' . $inquiry['id']) ?>" 
                       class="button is-small <?= $inquiry['status'] === 'closed' ? 'is-dark' : 'is-dark is-light' ?>">
                        <span class="icon"><i class="fas fa-check-circle"></i></span>
                        <span>已关闭</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 快捷操作卡片 -->
        <div class="admin-card mb-5 animate-in delay-2" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-bolt"></i></span>
                快捷操作
            </div>
            
            <div class="buttons is-flex is-flex-direction-column" style="gap: 0.75rem;">
                <a href="mailto:<?= h($inquiry['email']) ?>?subject=<?= urlencode('Re: 询单回复 - ' . ($inquiry['product_title'] ?? '通用咨询')) ?>" 
                   class="button is-info is-fullwidth">
                    <span class="icon"><i class="fas fa-reply"></i></span>
                    <span>邮件回复客户</span>
                </a>
                
                <?php if (!empty($inquiry['phone'])): ?>
                <a href="tel:<?= h($inquiry['phone']) ?>" class="button is-success is-fullwidth">
                    <span class="icon"><i class="fas fa-phone"></i></span>
                    <span>拨打电话</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($inquiry['product_id'])): ?>
                <a href="/admin/products/edit?id=<?= $inquiry['product_id'] ?>" class="button is-link is-light is-fullwidth">
                    <span class="icon"><i class="fas fa-box"></i></span>
                    <span>查看关联产品</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 危险操作 -->
        <div class="admin-card animate-in delay-3" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box danger"><i class="fas fa-exclamation-triangle"></i></span>
                危险操作
            </div>
            
            <a href="/admin/inquiries/delete?id=<?= $inquiry['id'] ?>" 
               class="button is-danger is-outlined is-fullwidth"
               onclick="return confirm('确定要删除此询单吗？此操作不可恢复。')">
                <span class="icon"><i class="fas fa-trash"></i></span>
                <span>删除此询单</span>
            </a>
        </div>
    </div>
</div>

