<?php
/**
 * 模板片段：站点底部
 * 作用：输出公司信息、社交媒体链接、版权信息与全站脚本。
 * 变量：$site（站点设置）。
 * 注意：由布局模板自动引入，不应独立渲染。
 */
?>
</main>
<footer class="footer footer-modern">
    <div class="container">
        <div class="columns footer-top">
            <div class="column is-5">
                <h3 class="title is-5 footer-brand"><?= h($site['name']) ?></h3>
                <p class="subtitle is-6 footer-tagline"><?= h($site['tagline']) ?></p>
                <div class="footer-social">
                    <?php if (!empty($site['facebook'])): ?>
                        <a class="button is-light is-rounded" href="<?= h($site['facebook']) ?>" target="_blank" title="Facebook"><span class="icon"><i class="fab fa-facebook-f"></i></span></a>
                    <?php endif; ?>
                    <?php if (!empty($site['instagram'])): ?>
                        <a class="button is-light is-rounded" href="<?= h($site['instagram']) ?>" target="_blank" title="Instagram"><span class="icon"><i class="fab fa-instagram"></i></span></a>
                    <?php endif; ?>
                    <?php if (!empty($site['twitter'])): ?>
                        <a class="button is-light is-rounded" href="<?= h($site['twitter']) ?>" target="_blank" title="Twitter"><span class="icon"><i class="fab fa-twitter"></i></span></a>
                    <?php endif; ?>
                    <?php if (!empty($site['linkedin'])): ?>
                        <a class="button is-light is-rounded" href="<?= h($site['linkedin']) ?>" target="_blank" title="LinkedIn"><span class="icon"><i class="fab fa-linkedin-in"></i></span></a>
                    <?php endif; ?>
                    <?php if (!empty($site['youtube'])): ?>
                        <a class="button is-light is-rounded" href="<?= h($site['youtube']) ?>" target="_blank" title="YouTube"><span class="icon"><i class="fab fa-youtube"></i></span></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="column is-7">
                <div class="columns">
                    <div class="column">
                        <p class="footer-title"><?= h(t('footer_contact')) ?></p>
                        <div class="footer-links">
                            <a href="mailto:<?= h($site['company_email'] ?? '') ?>" target="_blank">
                                <span class="icon"><i class="fas fa-envelope"></i></span>
                                <span><?= h($site['company_email'] ?? '') ?></span>
                            </a>
                            <a href="tel:<?= h($site['company_phone'] ?? '') ?>" target="_blank">
                                <span class="icon"><i class="fas fa-phone"></i></span>
                                <span><?= h($site['company_phone'] ?? '') ?></span>
                            </a>
                            <a href="https://goo.gl/maps/<?= h($site['company_address'] ?? '') ?>" target="_blank">
                                <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                                <span><?= h($site['company_address'] ?? '') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="has-text-grey">© <?= date('Y') ?> <?= h($site['name']) ?>. <?= h(t('footer_rights')) ?></p>
        </div>
    </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const burgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
        burgers.forEach(el => {
            el.addEventListener('click', () => {
                const target = document.getElementById(el.dataset.target);
                el.classList.toggle('is-active');
                if (target) target.classList.toggle('is-active');
            });
        });
    });
</script>
</body>

</html>