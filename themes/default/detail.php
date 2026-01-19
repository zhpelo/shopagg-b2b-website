<section class="section">
  <div class="container">
    <article class="content">
      <h1 class="title is-3"><?= h($item['title']) ?></h1>
      <p class="is-size-7 has-text-grey"><?= h($item['created_at']) ?></p>
      <div><?= nl2br(h($item['content'])) ?></div>
    </article>
  </div>
</section>
<?php if (!empty($inquiry_form)): ?>
  <section class="section">
    <div class="container">
      <div class="box soft-card">
        <h2 class="title is-5">Send Inquiry</h2>
        <form method="post" action="/inquiry">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="product_id" value="<?= h((string)$item['id']) ?>">
          <div class="columns">
            <div class="column">
              <div class="field">
                <label class="label">Name</label>
                <div class="control">
                  <input class="input" name="name" required>
                </div>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label">Email</label>
                <div class="control">
                  <input class="input" name="email" type="email" required>
                </div>
              </div>
            </div>
          </div>
          <div class="columns">
            <div class="column">
              <div class="field">
                <label class="label">Company</label>
                <div class="control">
                  <input class="input" name="company">
                </div>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label">Phone</label>
                <div class="control">
                  <input class="input" name="phone">
                </div>
              </div>
            </div>
          </div>
          <div class="field">
            <label class="label">Requirements</label>
            <div class="control">
              <textarea class="textarea" name="message" rows="5"></textarea>
            </div>
          </div>
          <button class="button is-link" type="submit">Send Inquiry</button>
        </form>
      </div>
    </div>
  </section>
<?php endif; ?>