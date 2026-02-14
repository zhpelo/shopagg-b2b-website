</main>
<footer class="footer has-background-white py-6 mt-6">
    <div class="container">
        <div class="columns">
            <div class="column is-5">
                <h3 class="title is-5"><?= h($site['name']) ?></h3>
                <p class="subtitle is-6 mb-4"><?= h($site['tagline']) ?></p>
                <div class="buttons">
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
                        <p class="has-text-weight-semibold"><?= h(t('footer_company')) ?></p>
                        <p class=""><?= h($site['company_address'] ?? '') ?></p>
                    </div>
                    <div class="column">
                        <p class="has-text-weight-semibold"><?= h(t('footer_contact')) ?></p>
                        <p class=""><?= h($site['company_email'] ?? '') ?></p>
                        <p class=""><?= h($site['company_phone'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
        <p class="has-text-grey ">© <?= date('Y') ?> <?= h($site['name']) ?>. <?= h(t('footer_rights')) ?></p>
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