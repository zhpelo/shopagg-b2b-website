<section class="section">
  <div class="container">
    <div class="columns">
      <div class="column is-5">
        <h1 class="title is-3">Contact Us</h1>
        <p class="subtitle is-6"><?= h($site['about']) ?></p>
        <div class="content">
          <p><strong>Address:</strong> <?= h($site['address']) ?></p>
          <p><strong>Email:</strong> <?= h($site['email']) ?></p>
          <p><strong>Phone:</strong> <?= h($site['phone']) ?></p>
        </div>
      </div>
      <div class="column is-7">
        <div class="box soft-card">
          <form method="post" action="/contact">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
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
              <label class="label">Message</label>
              <div class="control">
                <textarea class="textarea" name="message" rows="6" required></textarea>
              </div>
            </div>
            <button class="button is-link" type="submit">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>