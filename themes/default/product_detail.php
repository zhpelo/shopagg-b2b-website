<section class="section">
  <div class="container">
    <div class="columns">
      <div class="column is-6">
        <?php if (!empty($images)): ?>
          <div class="box soft-card">
            <div class="carousel">
              <figure class="image is-1by1">
                <img id="product-carousel-image" src="<?= h($images[0]) ?>" alt="<?= h($item['title']) ?>" style="cursor:zoom-in; object-fit:cover; width:100%; height:100%">
              </figure>
              <div class="buttons is-centered" style="margin-top:12px">
                <button class="button is-light" id="carousel-prev">上一张</button>
                <button class="button is-light" id="carousel-next">下一张</button>
              </div>
            </div>
            <div class="columns is-multiline" style="margin-top:10px">
              <?php foreach ($images as $idx => $img): ?>
                <div class="column is-3">
                  <figure class="image is-1by1">
                    <img class="carousel-thumb" data-index="<?= (int)$idx ?>" src="<?= h($img) ?>" alt="<?= h($item['title']) ?>" style="cursor:zoom-in; object-fit:cover; width:100%; height:100%">
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
      <div class="column is-6">
        <div class="box soft-card">
          <h1 class="title is-3"><?= h($item['title']) ?></h1>
          <p class="is-size-7 has-text-grey"><?= h($item['created_at']) ?></p>
          <p class="tag is-light"><?= h($item['category_name'] ?? '未分类') ?></p>

          <?php if (!empty($item['summary'])): ?>
            <div class="content" style="margin-top:16px"><?= h($item['summary']) ?></div>
          <?php endif; ?>

          <?php if (!empty($price_tiers)): ?>
            <div class="price-tiers-wrap mt-5 mb-4">
              <div class="columns is-mobile is-variable is-3">
                <?php foreach ($price_tiers as $tier): ?>
                  <div class="column">
                    <div class="price-tier">
                      <div class="has-text-weight-bold is-size-4" style="color: #333;">
                        <?= h($tier['currency']) ?>$<?= h((string)$tier['price']) ?>
                      </div>
                      <div class="is-size-7 has-text-grey">
                        <?= number_format((float)$tier['min_qty']) ?><?php if (!empty($tier['max_qty'])): ?>-<?= number_format((float)$tier['max_qty']) ?><?php else: ?>+<?php endif; ?> Pieces
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <hr class="my-5" style="background-color: #f0f0f0; height: 1px; border: none;">

          <div class="action-buttons">
            <div class="columns is-mobile is-variable is-2">
              <div class="column">
                <button class="button is-danger is-medium is-fullwidth is-rounded has-text-weight-bold" id="open-inquiry-modal" style="background-color: #d65a53; border: none; height: 50px;">
                  Send Inquiry
                </button>
              </div>
              <div class="column">
                <?php
                  $wa = $whatsapp ?? '';
                  $waDigits = preg_replace('/\\D+/', '', $wa);
                ?>
                <a style="background-color: #25D366; color:#fff;" class="button is-white is-medium is-fullwidth is-rounded has-text-weight-bold" 
                   target="_blank" rel="noopener" 
                   href="<?= !empty($waDigits) ? 'https://wa.me/'.h($waDigits) : '#inquiry' ?>"
                   style="border: 1px solid #333; height: 50px; color: #333;">
                  <span class="icon" style="color:#fff;">
                    <i class="fa-brands fa-whatsapp"></i>
                  </span>
                  <span>Chat Now</span>
                </a>
              </div>
            </div>
          </div>

          <div class="sample-info mt-4 is-size-7 has-text-grey-dark">
            Still deciding? Get samples of <span class="has-text-weight-bold">US$ <?= !empty($price_tiers) ? h($price_tiers[0]['currency']).' '.h((string)($price_tiers[0]['price'] * 2)) : '50.00' ?>/Piece</span> ! 
            <a href="#inquiry" class="has-text-weight-bold is-underlined has-text-black" onclick="document.getElementById('open-inquiry-modal').click(); return false;">Request Sample</a>
          </div>
         
          
        </div>
      </div>
    </div>
  </div>
</section>
<?php if (!empty($inquiry_form)): ?>
  <div class="modal" id="inquiry-modal">
    <div class="modal-background"></div>
    <div class="modal-content" style="width: 90%; max-width: 800px;">
      <div class="box soft-card p-6 relative">
        <button class="delete is-pulled-right close-inquiry-modal" aria-label="close" style="position: absolute; right: 20px; top: 20px;"></button>
        <div class="columns">
          <div class="column is-4 is-hidden-mobile">
            <h2 class="title is-4">Request a Quote</h2>
            <p class="subtitle is-6 has-text-grey">Complete the form below and our team will get back to you within 24 hours.</p>
            <div class="mt-5">
              <div class="is-flex is-align-items-center mb-3">
                <span class="icon has-text-link mr-2"><i class="fas fa-check-circle"></i></span>
                <span class="is-size-7">Direct Factory Pricing</span>
              </div>
              <div class="is-flex is-align-items-center mb-3">
                <span class="icon has-text-link mr-2"><i class="fas fa-check-circle"></i></span>
                <span class="is-size-7">OEM/ODM Support</span>
              </div>
              <div class="is-flex is-align-items-center">
                <span class="icon has-text-link mr-2"><i class="fas fa-check-circle"></i></span>
                <span class="is-size-7">Global Shipping</span>
              </div>
            </div>
          </div>
          <div class="column is-8-desktop is-12-mobile">
            <h2 class="title is-4 is-hidden-tablet">Request a Quote</h2>
            <form method="post" action="/inquiry">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="product_id" value="<?= h((string)$item['id']) ?>">
              
              <div class="columns">
                <div class="column">
                  <div class="field">
                    <label class="label is-size-7">Your Name *</label>
                    <div class="control">
                      <input class="input" name="name" required placeholder="Full Name">
                    </div>
                  </div>
                </div>
                <div class="column">
                  <div class="field">
                    <label class="label is-size-7">Email Address *</label>
                    <div class="control">
                      <input class="input" name="email" type="email" required placeholder="example@email.com">
                    </div>
                  </div>
                </div>
              </div>

              <div class="columns">
                <div class="column">
                  <div class="field">
                    <label class="label is-size-7">Company Name</label>
                    <div class="control">
                      <input class="input" name="company" placeholder="Company Ltd.">
                    </div>
                  </div>
                </div>
                <div class="column">
                  <div class="field">
                    <label class="label is-size-7">Quantity Needed</label>
                    <div class="control">
                      <input class="input" name="quantity" placeholder="e.g. 500 units">
                    </div>
                  </div>
                </div>
              </div>

              <div class="field">
                <label class="label is-size-7">Requirements</label>
                <div class="control">
                  <textarea class="textarea" name="message" rows="3" placeholder="Project requirements, customization, etc."></textarea>
                </div>
              </div>

              <div class="field mt-5">
                <button class="button is-link is-large is-fullwidth" type="submit">
                  <span class="icon"><i class="fas fa-paper-plane"></i></span>
                  <span>Send My Inquiry</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
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

      // Inquiry Modal
      const inquiryModal = document.getElementById("inquiry-modal");
      const openInquiryBtn = document.getElementById("open-inquiry-modal");
      const closeInquiryBtns = document.querySelectorAll(".close-inquiry-modal, #inquiry-modal .modal-background");
      
      if (openInquiryBtn && inquiryModal) {
        openInquiryBtn.addEventListener("click", () => inquiryModal.classList.add("is-active"));
      }
      closeInquiryBtns.forEach(btn => {
        btn.addEventListener("click", () => inquiryModal.classList.remove("is-active"));
      });
    })();
  </script>
<?php endif; ?>