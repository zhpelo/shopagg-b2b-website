<section class="section">
  <div class="container">
    <div class="columns">
      <div class="column is-5">
        <h1 class="title is-3"><?= h(t('contact_title')) ?></h1>
        <p class="subtitle is-6"><?= h($site['company_bio'] ?? '') ?></p>
        <div class="content">
          <p><strong><?= h(t('about_address')) ?>:</strong> <?= h($site['company_address'] ?? '') ?></p>
          <p><strong><?= h(t('form_email')) ?>:</strong> <?= h($site['company_email'] ?? '') ?></p>
          <p><strong><?= h(t('form_phone')) ?>:</strong> <?= h($site['company_phone'] ?? '') ?></p>
        </div>
      </div>
      <div class="column is-7">
        <div class="box soft-card">
<form method="post" action="<?= url('/contact') ?>">
  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <div class="columns">
              <div class="column">
                <div class="field">
                  <label class="label"><?= h(t('form_name')) ?></label>
                  <div class="control">
                    <input class="input" name="name" required placeholder="<?= h(t('form_name_placeholder')) ?>">
                  </div>
                </div>
              </div>
              <div class="column">
                <div class="field">
                  <label class="label"><?= h(t('form_email')) ?></label>
                  <div class="control">
                    <input class="input" name="email" type="email" required placeholder="<?= h(t('form_email_placeholder')) ?>">
                  </div>
                </div>
              </div>
            </div>
            <div class="columns">
              <div class="column">
                <div class="field">
                  <label class="label"><?= h(t('form_company')) ?></label>
                  <div class="control">
                    <input class="input" name="company" placeholder="<?= h(t('form_company_placeholder')) ?>">
                  </div>
                </div>
              </div>
              <div class="column">
                <div class="field">
                  <label class="label"><?= h(t('form_phone')) ?></label>
                  <div class="control">
                    <input class="input" name="phone">
                  </div>
                </div>
              </div>
            </div>
            <div class="field">
              <label class="label"><?= h(t('form_message_label')) ?></label>
              <div class="control">
                <textarea class="textarea" name="message" rows="6" required placeholder="<?= h(t('form_req_placeholder')) ?>"></textarea>
              </div>
            </div>
            <button class="button is-link" type="submit"><?= h(t('contact_message')) ?></button>
</form>
        </div>
      </div>
    </div>
  </div>
</section>
