<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-comment-dots"></i></span>
                    留言详情 #<?= $message['id'] ?>
                </h1>
                <p class="subtitle is-6">
                    <span class="icon is-small mr-1"><i class="far fa-clock"></i></span>
                    <?= format_date($message['created_at']) ?>
                </p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/messages" class="button is-white is-outlined">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <!-- 左侧：客户信息和留言内容 -->
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
                        <p class="is-size-5 has-text-weight-semibold"><?= h($message['name']) ?></p>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">公司名称</label>
                        <p class="is-size-5"><?= h($message['company']) ?: '<span class="has-text-grey-light">未填写</span>' ?></p>
                    </div>
                </div>
            </div>
            
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">邮箱地址</label>
                        <p>
                            <a href="mailto:<?= h($message['email']) ?>" class="has-text-link">
                                <span class="icon is-small mr-1"><i class="fas fa-envelope"></i></span>
                                <?= h($message['email']) ?>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="column is-6">
                    <div class="field">
                        <label class="label is-small has-text-grey">联系电话</label>
                        <p>
                            <?php if (!empty($message['phone'])): ?>
                            <a href="tel:<?= h($message['phone']) ?>" class="has-text-link">
                                <span class="icon is-small mr-1"><i class="fas fa-phone"></i></span>
                                <?= h($message['phone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="has-text-grey-light">未填写</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 留言内容卡片 -->
        <div class="admin-card animate-in delay-2" style="padding: 2rem;">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-comment-alt"></i></span>
                留言内容
            </div>
            
            <div class="content" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; line-height: 1.8;">
                <?= nl2br(h($message['message'])) ?>
            </div>
            
            <div class="mt-4 pt-4" style="border-top: 1px solid #f0f0f0;">
                <p class="is-size-7 has-text-grey">
                    <span class="icon is-small mr-1"><i class="far fa-clock"></i></span>
                    提交时间：<?= format_date($message['created_at']) ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- 右侧：快捷操作 -->
    <div class="column is-4">
        <!-- 快捷操作卡片 -->
        <div class="admin-card mb-5 animate-in delay-1" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box primary"><i class="fas fa-bolt"></i></span>
                快捷操作
            </div>
            
            <div class="buttons is-flex is-flex-direction-column" style="gap: 0.75rem;">
                <a href="mailto:<?= h($message['email']) ?>?subject=<?= urlencode('Re: 感谢您的留言') ?>" 
                   class="button is-info is-fullwidth">
                    <span class="icon"><i class="fas fa-reply"></i></span>
                    <span>邮件回复客户</span>
                </a>
                
                <?php if (!empty($message['phone'])): ?>
                <a href="tel:<?= h($message['phone']) ?>" class="button is-success is-fullwidth">
                    <span class="icon"><i class="fas fa-phone"></i></span>
                    <span>拨打电话</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 客户名片 -->
        <div class="admin-card mb-5 animate-in delay-2" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box success"><i class="fas fa-id-card"></i></span>
                客户名片
            </div>
            
            <div class="has-text-centered mb-4">
                <div class="avatar-circle lg mx-auto mb-3">
                    <?= strtoupper(mb_substr($message['name'], 0, 1)) ?>
                </div>
                <p class="is-size-5 has-text-weight-semibold"><?= h($message['name']) ?></p>
                <?php if (!empty($message['company'])): ?>
                <p class="has-text-grey"><?= h($message['company']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="is-size-7">
                <?php if (!empty($message['email'])): ?>
                <p class="mb-2">
                    <span class="icon is-small has-text-grey mr-1"><i class="fas fa-envelope"></i></span>
                    <?= h($message['email']) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($message['phone'])): ?>
                <p>
                    <span class="icon is-small has-text-grey mr-1"><i class="fas fa-phone"></i></span>
                    <?= h($message['phone']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 危险操作 -->
        <div class="admin-card animate-in delay-3" style="padding: 1.5rem;">
            <div class="section-title">
                <span class="icon-box danger"><i class="fas fa-exclamation-triangle"></i></span>
                危险操作
            </div>
            
            <a href="/admin/messages/delete?id=<?= $message['id'] ?>" 
               class="button is-danger is-outlined is-fullwidth"
               onclick="return confirm('确定要删除此留言吗？此操作不可恢复。')">
                <span class="icon"><i class="fas fa-trash"></i></span>
                <span>删除此留言</span>
            </a>
        </div>
    </div>
</div>

