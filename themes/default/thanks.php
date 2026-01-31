<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-6 has-text-centered">
                <div class="box soft-card p-6">
                    <span class="icon is-large has-text-success mb-4">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </span>
                    <h1 class="title is-3"><?= h(t('thanks_title')) ?></h1>
                    <p class="subtitle is-6 has-text-grey mt-2">
                        <?= h(t('thanks_desc')) ?>
                    </p>
                    <div class="notification is-info is-light mt-5">
                        <p class="is-size-7"><strong><?= h(t('thanks_expected')) ?></strong></p>
                    </div>
                    <div class="buttons is-centered mt-6">
                        <a class="button is-link" href="<?= url('/') ?>"><?= h(t('btn_back_home')) ?></a>
                        <a class="button is-light" href="<?= url('/products') ?>"><?= h(t('btn_view_more')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>