<!-- 1. Hero Section -->
<section class="hero is-large brand-gradient is-relative">
  <div class="hero-body">
    <div class="container">
      <div class="columns is-vcentered">
        <div class="column is-7">
          <h1 class="title is-1 has-text-white mb-5" style="line-height: 1.2;">
            <?= h($site['tagline'] ?: h(t('home_ready_title'))) ?>
          </h1>
          <p class="subtitle is-4 has-text-grey-light mb-6">
            <?= h(($site['company_bio'] ?? '') ?: h(t('home_ready_desc'))) ?>
          </p>
          <div class="buttons">
            <a class="button is-link is-large px-6" href="/products"><?= h(t('nav_products')) ?></a>
            <a class="button is-white is-outlined is-large px-6" href="/contact"><?= h(t('nav_contact')) ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Value Proposition (Trust Section) -->
<section class="section py-6" style="background: #fff; margin-top: -50px; position: relative; z-index: 5;">
  <div class="container">
    <div class="box soft-card py-6 border-none">
      <div class="columns has-text-centered">
        <div class="column">
          <div class="px-4">
            <span class="icon is-large has-text-link mb-4">
              <i class="fas fa-check-circle fa-2x"></i>
            </span>
            <h3 class="title is-5"><?= h(t('home_quality_title')) ?></h3>
            <p class="has-text-grey"><?= h(t('home_quality_desc')) ?></p>
          </div>
        </div>
        <div class="column" style="border-left: 1px solid #f0f0f0;">
          <div class="px-4">
            <span class="icon is-large has-text-link mb-4">
              <i class="fas fa-globe-americas fa-2x"></i>
            </span>
            <h3 class="title is-5"><?= h(t('home_logistics_title')) ?></h3>
            <p class="has-text-grey"><?= h(t('home_logistics_desc')) ?></p>
          </div>
        </div>
        <div class="column" style="border-left: 1px solid #f0f0f0;">
          <div class="px-4">
            <span class="icon is-large has-text-link mb-4">
              <i class="fas fa-user-shield fa-2x"></i>
            </span>
            <h3 class="title is-5"><?= h(t('home_support_title')) ?></h3>
            <p class="has-text-grey"><?= h(t('home_support_desc')) ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 3. Featured Products Grid -->
<section class="section">
  <div class="container">
    <div class="level mb-6">
      <div class="level-left">
        <div>
          <h2 class="title is-3 mb-2"><?= h(t('section_featured_products')) ?></h2>
          <p class="has-text-grey"><?= h(t('home_highlights')) ?></p>
        </div>
      </div>
      <div class="level-right">
        <a class="button is-link is-light" href="/products"><?= h(t('btn_view_all')) ?> &rarr;</a>
      </div>
    </div>
    
    <div class="columns is-multiline">
    <?php foreach ($products as $p): ?>
        <div class="column is-4">
          <div class="card soft-card h-100" style="display: flex; flex-direction: column; height: 100%;">
            <div class="card-image">
              <a href="<?= h($p['url']) ?>">
                <figure class="image is-1by1">
                  <img src="<?= h($p['cover'] ?: '/assets/no-image.png') ?>" alt="<?= h($p['title']) ?>" style="object-fit: cover;">
                </figure>
              </a>
            </div>
            <div class="card-content" style="flex-grow: 1;">
              <h3 class="title is-5 mb-2">
                <a href="<?= h($p['url']) ?>" class="has-text-dark"><?= h($p['title']) ?></a>
              </h3>
              <p class="content is-size-7 has-text-grey line-clamp-3">
                <?= h($p['summary']) ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 4. Factory/Company Highlight (Why Us) -->
<section class="section" style="background-color: #fcfcfc;">
  <div class="container">
    <div class="columns is-vcentered">
      <div class="column is-6">
        <h2 class="title is-3 mb-5"><?= h(t('home_why_us')) ?></h2>
        <div class="content has-text-grey is-medium">
          <p><?= h($site['company_bio'] ?? '') ?></p>
          <ul style="list-style-type: none; margin-left: 0;">
            <li class="mb-2"><span class="icon has-text-success"><i class="fas fa-check"></i></span> <?= h(t('home_iso')) ?></li>
            <li class="mb-2"><span class="icon has-text-success"><i class="fas fa-check"></i></span> <?= h(t('home_oem')) ?></li>
            <li class="mb-2"><span class="icon has-text-success"><i class="fas fa-check"></i></span> <?= h(t('home_rd')) ?></li>
          </ul>
        </div>
        <a href="/about" class="button is-link is-outlined"><?= h(t('nav_about')) ?></a>
      </div>
      <div class="column is-6">
        <figure class="image is-16by9 box p-0 overflow-hidden soft-card">
          <img src="<?= h($site['og_image'] ?? 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=800&q=80') ?>" alt="Factory" style="object-fit: cover;">
        </figure>
      </div>
    </div>
  </div>
</section>

<!-- 5. Success Cases Banner -->
<section class="section py-6">
  <div class="container">
    <div class="has-text-centered mb-6">
      <h2 class="title is-3"><?= h(t('section_success_cases')) ?></h2>
      <p class="subtitle is-6 has-text-grey"><?= h(t('home_global')) ?></p>
    </div>
    <div class="columns is-multiline">
      <?php foreach ($cases as $c): ?>
        <div class="column is-4">
          <a href="<?= h($c['url']) ?>">
            <div class="card soft-card overflow-hidden">
              <div class="card-image">
                <figure class="image is-3by2">
                  <img src="<?= h($c['cover'] ?: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= h($c['title']) ?>" style="object-fit: cover;">
                </figure>
              </div>
              <div class="card-content p-4">
                <h4 class="title is-6 mb-0"><?= h($c['title']) ?></h4>
              </div>
            </div>
          </a>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 6. Bottom CTA -->
<section class="section pb-6">
  <div class="container">
    <div class="box has-text-centered py-6 brand-gradient has-text-white border-none soft-card">
      <h2 class="title is-3 has-text-white mb-4"><?= h(t('home_ready_title')) ?></h2>
      <p class="subtitle is-5 has-text-grey-light mb-6"><?= h(t('home_ready_desc')) ?></p>
      <div class="buttons is-centered">
        <a href="/contact" class="button is-white is-large px-6"><?= h(t('cta_quote')) ?></a>
        <?php
          $wa = $site['whatsapp'] ?? '';
          $waDigits = preg_replace('/\D+/', '', $wa);
          if (!empty($waDigits)):
        ?>
          <a href="https://wa.me/<?= h($waDigits) ?>" target="_blank" class="button is-success is-large px-6">
            <span class="icon"><i class="fab fa-whatsapp"></i></span>
            <span><?= h(t('chat_now')) ?></span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
