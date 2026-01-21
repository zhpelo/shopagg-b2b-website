<!-- 头部欢迎区 -->
<div class="dashboard-header animate-in">
    <h1 class="title is-3">👋 欢迎回来，<?= h($_SESSION['admin_display_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?></h1>
    <p class="subtitle is-6">今天是 <?= date('Y年m月d日 l') ?>，让我们看看网站的运营情况</p>
</div>

<!-- 核心数据统计 -->
<div class="mb-6">
    <div class="section-title">
        <span class="icon has-background-primary-light has-text-primary">
            <i class="fas fa-chart-pie"></i>
        </span>
        核心数据
    </div>
    <div class="columns is-multiline">
        <div class="column is-3-desktop is-6-tablet animate-in delay-1">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?= $counts['products'] ?></div>
                <div class="stat-label">产品总数</div>
                <div class="stat-badge neutral">
                    <i class="fas fa-check-circle mr-1"></i>
                    <?= $counts['active_products'] ?> 已上架
                </div>
                <a href="/admin/products" class="button is-small is-primary is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>管理</span>
                </a>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-2">
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-value"><?= $counts['cases'] ?></div>
                <div class="stat-label">成功案例</div>
                <a href="/admin/cases" class="button is-small is-info is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>管理</span>
                </a>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-3">
            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-pen-nib"></i>
                </div>
                <div class="stat-value"><?= $counts['posts'] ?></div>
                <div class="stat-label">博客文章</div>
                <a href="/admin/posts" class="button is-small is-success is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>管理</span>
                </a>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-4">
            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-value"><?= $counts['categories'] ?></div>
                <div class="stat-label">产品分类</div>
                <a href="/admin/product-categories" class="button is-small is-warning is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>管理</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 客户互动统计 -->
<div class="mb-6">
    <div class="section-title">
        <span class="icon has-background-danger-light has-text-danger">
            <i class="fas fa-users"></i>
        </span>
        客户互动
    </div>
    <div class="columns is-multiline">
        <div class="column is-3-desktop is-6-tablet animate-in delay-1">
            <div class="stat-card danger">
                <div class="stat-icon danger">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-value"><?= $counts['messages'] ?></div>
                <div class="stat-label">总留言数</div>
                <div class="stat-badge <?= $counts['today_messages'] > 0 ? 'up' : 'neutral' ?>">
                    <i class="fas fa-<?= $counts['today_messages'] > 0 ? 'arrow-up' : 'minus' ?> mr-1"></i>
                    今日 +<?= $counts['today_messages'] ?>
                </div>
                <a href="/admin/messages" class="button is-small is-danger is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>查看</span>
                </a>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-2">
            <div class="stat-card link">
                <div class="stat-icon link">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-value"><?= $counts['inquiries'] ?></div>
                <div class="stat-label">询单总数</div>
                <div class="stat-badge <?= $counts['pending_inquiries'] > 0 ? 'up' : 'neutral' ?>">
                    <i class="fas fa-clock mr-1"></i>
                    <?= $counts['pending_inquiries'] ?> 待处理
                </div>
                <a href="/admin/inquiries" class="button is-small is-link is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>管理</span>
                </a>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-3">
            <div class="stat-card dark">
                <div class="stat-icon dark">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-value"><?= $counts['users'] ?></div>
                <div class="stat-label">团队成员</div>
                <a href="/admin/staff" class="button is-small is-dark is-light mt-3">
                    <span class="icon is-small"><i class="fas fa-arrow-right"></i></span>
                    <span>管理</span>
                </a>
            </div>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-4">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-server"></i>
                </div>
                <div class="stat-value"><?= $counts['system_age_days'] ?></div>
                <div class="stat-label">运行天数</div>
                <div class="stat-badge neutral">
                    <i class="fas fa-image mr-1"></i>
                    <?= $counts['total_images'] ?> 媒体文件
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 活动统计 -->
<div class="mb-6">
    <div class="section-title">
        <span class="icon has-background-info-light has-text-info">
            <i class="fas fa-chart-line"></i>
        </span>
        活动统计
    </div>
    <div class="columns">
        <div class="column is-4 animate-in delay-1">
            <div class="activity-card">
                <h3>
                    <span class="icon has-text-warning"><i class="fas fa-sun"></i></span>
                    今日活动
                </h3>
                <div class="columns is-mobile">
                    <div class="column">
                        <div class="activity-stat">
                            <div class="value has-text-danger">+<?= $counts['today_messages'] ?></div>
                            <div class="label">新留言</div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="activity-stat highlight">
                            <div class="value">+<?= $counts['today_inquiries'] ?></div>
                            <div class="label">新询单</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-4 animate-in delay-2">
            <div class="activity-card">
                <h3>
                    <span class="icon has-text-info"><i class="fas fa-calendar-week"></i></span>
                    本周活动
                </h3>
                <div class="columns is-mobile">
                    <div class="column">
                        <div class="activity-stat">
                            <div class="value has-text-danger">+<?= $counts['week_messages'] ?></div>
                            <div class="label">新留言</div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="activity-stat highlight">
                            <div class="value">+<?= $counts['week_inquiries'] ?></div>
                            <div class="label">新询单</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-4 animate-in delay-3">
            <div class="activity-card">
                <h3>
                    <span class="icon has-text-success"><i class="fas fa-calendar-alt"></i></span>
                    本月概览
                </h3>
                <div class="columns is-mobile">
                    <div class="column">
                        <div class="activity-stat">
                            <div class="value"><?= $counts['month_messages'] ?></div>
                            <div class="label">留言</div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="activity-stat">
                            <div class="value"><?= $counts['month_inquiries'] ?></div>
                            <div class="label">询单</div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="activity-stat highlight">
                            <?php
                            $total_interactions = $counts['month_messages'] + $counts['month_inquiries'];
                            $conversion = $total_interactions > 0 ? round(($counts['month_inquiries'] / $total_interactions) * 100, 1) : 0;
                            ?>
                            <div class="value"><?= $conversion ?>%</div>
                            <div class="label">转化率</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 30天趋势图 -->
<div class="mb-6">
    <div class="section-title">
        <span class="icon has-background-success-light has-text-success">
            <i class="fas fa-chart-bar"></i>
        </span>
        近30天趋势
    </div>
    <div class="columns">
        <div class="column is-6 animate-in delay-1">
            <div class="activity-card">
                <h3>
                    <span class="icon has-text-danger"><i class="fas fa-envelope"></i></span>
                    留言趋势
                </h3>
                <div class="mini-chart">
                    <?php 
                    $maxMsg = max(1, max($counts['recent_messages']));
                    foreach ($counts['recent_messages'] as $i => $val): 
                        $height = ($val / $maxMsg) * 100;
                    ?>
                    <div class="bar" style="height: <?= max(4, $height) ?>%; background: linear-gradient(180deg, #dc3545, #e83e8c);" title="<?= date('m-d', strtotime('-' . (29-$i) . ' days')) ?>: <?= $val ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div class="is-flex is-justify-content-space-between mt-2" style="font-size: 0.75rem; color: #a0aec0;">
                    <span><?= date('m/d', strtotime('-29 days')) ?></span>
                    <span>今天</span>
                </div>
            </div>
        </div>
        <div class="column is-6 animate-in delay-2">
            <div class="activity-card">
                <h3>
                    <span class="icon has-text-link"><i class="fas fa-file-invoice"></i></span>
                    询单趋势
                </h3>
                <div class="mini-chart">
                    <?php 
                    $maxInq = max(1, max($counts['recent_inquiries']));
                    foreach ($counts['recent_inquiries'] as $i => $val): 
                        $height = ($val / $maxInq) * 100;
                    ?>
                    <div class="bar" style="height: <?= max(4, $height) ?>%;" title="<?= date('m-d', strtotime('-' . (29-$i) . ' days')) ?>: <?= $val ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div class="is-flex is-justify-content-space-between mt-2" style="font-size: 0.75rem; color: #a0aec0;">
                    <span><?= date('m/d', strtotime('-29 days')) ?></span>
                    <span>今天</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 快捷操作 -->
<div class="mb-5">
    <div class="section-title">
        <span class="icon has-background-warning-light has-text-warning-dark">
            <i class="fas fa-bolt"></i>
        </span>
        快捷操作
    </div>
    <div class="columns is-multiline">
        <div class="column is-3-desktop is-6-tablet animate-in delay-1">
            <a href="/admin/products/create" class="quick-action">
                <div class="action-icon has-background-primary-light has-text-primary">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <div class="action-text">添加产品</div>
                    <div class="action-desc">发布新的产品信息</div>
                </div>
            </a>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-2">
            <a href="/admin/posts/create" class="quick-action">
                <div class="action-icon has-background-success-light has-text-success">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <div class="action-text">写博客</div>
                    <div class="action-desc">发布新的文章内容</div>
                </div>
            </a>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-3">
            <a href="/admin/cases/create" class="quick-action">
                <div class="action-icon has-background-info-light has-text-info">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div class="action-text">添加案例</div>
                    <div class="action-desc">展示成功客户案例</div>
                </div>
            </a>
        </div>
        <div class="column is-3-desktop is-6-tablet animate-in delay-4">
            <a href="/admin/inquiries" class="quick-action">
                <div class="action-icon has-background-danger-light has-text-danger">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div>
                    <div class="action-text">处理询单</div>
                    <div class="action-desc"><?= $counts['pending_inquiries'] ?> 条待处理</div>
                </div>
            </a>
        </div>
    </div>
</div>
