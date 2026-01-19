<!-- 案例顶部 Hero -->
<section class="hero is-link is-bold brand-gradient">
    <div class="hero-body">
        <div class="container">
            <div class="columns is-vcentered">
                <div class="column is-8">
                    <p class="tag is-info is-light mb-2">成功案例</p>
                    <h1 class="title is-1"><?= h($item['title']) ?></h1>
                    <?php if (!empty($item['summary'])): ?>
                        <p class="subtitle is-5 mt-3"><?= h($item['summary']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns">
            <!-- 左侧详情 -->
            <div class="column is-8">
                <div class="box soft-card p-6">
                    <h2 class="title is-4 mb-5">项目详情</h2>
                    <article class="content">
                        <?= $item['content'] ?>
                    </article>
                </div>
            </div>

            <!-- 右侧边栏 -->
            <div class="column is-4">
                <div class="box soft-card">
                    <h3 class="title is-5 mb-4">关于此案例</h3>
                    <div class="field mb-4">
                        <label class="label is-small has-text-grey">发布时间</label>
                        <p class="is-size-6"><?= date('Y年m月d日', strtotime($item['created_at'])) ?></p>
                    </div>
                    <hr>
                    <div class="content">
                        <p class="is-size-7 has-text-grey">如果您对该解决方案感兴趣，或有类似的需求，欢迎联系我们的专家团队获取专业咨询。</p>
                        <a href="/contact" class="button is-link is-fullwidth">立即咨询</a>
                    </div>
                </div>

                <!-- 分享/返回 -->
                <div class="mt-4">
                    <a href="/cases" class="button is-fullwidth is-light">返回所有案例</a>
                </div>
            </div>
        </div>
    </div>
</section>

