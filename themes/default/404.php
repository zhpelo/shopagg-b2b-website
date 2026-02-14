<?php
/**
 * 页面模板：404 错误页面
 * 作用：展示页面未找到的错误提示与返回首页按钮。
 * 用途：当访问不存在的页面时显示。
 * 变量：无特殊变量依赖。
 */
?>
<section class="section">
    <div class="container has-text-centered">
        <h1 class="title is-1">404</h1>
        <p class="subtitle is-4"><?= h(t('not_found_title')) ?></p>
        <p class="mb-6"><?= h(t('not_found_desc')) ?></p>
        <a class="button is-link" href="<?= url('/') ?>"><?= h(t('btn_go_home')) ?></a>
    </div>
</section>