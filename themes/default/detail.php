<section class="section">
  <div class="container">
    <div class="columns">
      <div class="column is-7">
        <?php if (!empty($images)): ?>
          <div class="box soft-card">
            <div class="carousel">
              <figure class="image is-4by3">
                <img id="product-carousel-image" src="<?= h($images[0]) ?>" alt="<?= h($item['title']) ?>" style="cursor:zoom-in">
              </figure>
              <div class="buttons is-centered" style="margin-top:12px">
                <button class="button is-light" id="carousel-prev">上一张</button>
                <button class="button is-light" id="carousel-next">下一张</button>
              </div>
            </div>
            <div class="columns is-multiline" style="margin-top:10px">
              <?php foreach ($images as $idx => $img): ?>
                <div class="column is-3">
                  <figure class="image is-4by3">
                    <img class="carousel-thumb" data-index="<?= (int)$idx ?>" src="<?= h($img) ?>" alt="<?= h($item['title']) ?>" style="cursor:pointer">
                  </figure>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
        <article class="content">
          <h2 class="title is-4">产品介绍</h2>
          <div><?= $item['content'] ?></div>
        </article>
      </div>
      <div class="column is-5">
        <div class="box soft-card">
          <h1 class="title is-3"><?= h($item['title']) ?></h1>
          <p class="is-size-7 has-text-grey"><?= h($item['created_at']) ?></p>
          <p class="tag is-light"><?= h($item['category_name'] ?? '未分类') ?></p>
          <div style="margin-top:16px">
            <a class="button is-link" href="#inquiry">发送询单</a>
            <?php
              $wa = $whatsapp ?? '';
              $waDigits = preg_replace('/\\D+/', '', $wa);
            ?>
            <?php if (!empty($waDigits)): ?>
              <a class="button is-success" target="_blank" rel="noopener" href="https://wa.me/<?= h($waDigits) ?>">WhatsApp</a>
            <?php endif; ?>
          </div>
          <?php if (!empty($item['summary'])): ?>
            <div class="content" style="margin-top:16px"><?= h($item['summary']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php if (!empty($inquiry_form)): ?>
  <section class="section" id="inquiry">
    <div class="container">
      <div class="box soft-card">
        <h2 class="title is-5"><?= h(t('detail_send_inquiry')) ?></h2>
        <form method="post" action="/inquiry">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="product_id" value="<?= h((string)$item['id']) ?>">
          <div class="columns">
            <div class="column">
              <div class="field">
                <label class="label"><?= h(t('form_name')) ?></label>
                <div class="control">
                  <input class="input" name="name" required>
                </div>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label"><?= h(t('form_email')) ?></label>
                <div class="control">
                  <input class="input" name="email" type="email" required>
                </div>
              </div>
            </div>
          </div>
          <div class="columns">
            <div class="column">
              <div class="field">
                <label class="label"><?= h(t('form_company')) ?></label>
                <div class="control">
                  <input class="input" name="company">
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
            <label class="label"><?= h(t('form_requirements')) ?></label>
            <div class="control">
              <textarea class="textarea" name="message" rows="5"></textarea>
            </div>
          </div>
          <button class="button is-link" type="submit"><?= h(t('btn_send_inquiry')) ?></button>
        </form>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php if (!empty($images)): ?>
  <div class="modal" id="image-lightbox">
    <div class="modal-background"></div>
    <div class="modal-content">
      <p class="image">
        <img id="lightbox-image" src="<?= h($images[0]) ?>" alt="<?= h($item['title']) ?>">
      </p>
    </div>
    <button class="modal-close is-large" aria-label="close"></button>
  </div>
  <script>
    (function () {
      const images = <?= json_encode($images, JSON_UNESCAPED_UNICODE) ?>;
      let current = 0;
      const main = document.getElementById("product-carousel-image");
      const prev = document.getElementById("carousel-prev");
      const next = document.getElementById("carousel-next");
      const thumbs = document.querySelectorAll(".carousel-thumb");
      const lightbox = document.getElementById("image-lightbox");
      const lightboxImage = document.getElementById("lightbox-image");
      function show(index) {
        current = (index + images.length) % images.length;
        if (main) main.src = images[current];
      }
      if (prev) prev.addEventListener("click", () => show(current - 1));
      if (next) next.addEventListener("click", () => show(current + 1));
      thumbs.forEach(el => {
        el.addEventListener("click", () => show(parseInt(el.dataset.index || "0", 10)));
      });
      if (main && lightbox && lightboxImage) {
        main.addEventListener("click", () => {
          lightboxImage.src = images[current];
          lightbox.classList.add("is-active");
        });
        lightbox.addEventListener("click", () => lightbox.classList.remove("is-active"));
      }
    })();
  </script>
<?php endif; ?>