<!-- 案例顶部 Hero -->
<section class="hero is-link is-bold brand-gradient">
    <div class="hero-body">
        <div class="container">
            <div class="columns is-vcentered">
                <div class="column is-8">
                    <p class="tag is-info is-light mb-2"><?= h(t('case_success')) ?></p>
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
                    <h2 class="title is-4 mb-5"><?= h(t('case_details')) ?></h2>
                    <article class="content">
                        <?= $item['content'] ?>
                    </article>
                </div>
            </div>

            <!-- 右侧边栏 -->
            <div class="column is-4">
                <div class="box soft-card">
                    <h3 class="title is-5 mb-4"><?= h(t('case_about')) ?></h3>
                    <div class="field mb-4">
                        <label class="label is-small has-text-grey"><?= h(t('case_publish_time')) ?></label>
                        <p class="is-size-6"><?= format_date($item['created_at'], 'Y-m-d') ?></p>
                    </div>
                    <hr>
                    <div class="content">
                        <p class="is-size-7 has-text-grey"><?= h(t('case_interest')) ?></p>
                        <a href="<?= url('/contact') ?>" class="button is-link is-fullwidth"><?= h(t('cta_quote')) ?></a>
                    </div>
                </div>

                <!-- 分享/返回 -->
                <div class="mt-4">
                    <a href="<?= url('/cases') ?>" class="button is-fullwidth is-light"><?= h(t('case_back_list')) ?></a>
                </div>
            </div>
        </div>
    </div>
</section>
