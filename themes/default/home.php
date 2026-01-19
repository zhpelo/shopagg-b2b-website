<section class="hero is-medium brand-gradient">
  <div class="hero-body">
    <div class="container">
      <p class="title has-text-white"><?= h($site['tagline']) ?></p>
      <p class="subtitle has-text-white"><?= h($site['about']) ?></p>
      <a class="button is-light" href="/contact"><?= h(t('cta_quote')) ?></a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="columns">
      <div class="column">
        <div class="box soft-card">
          <h3 class="title is-6"><?= h(t('home_quality_title')) ?></h3>
          <p class="is-size-7"><?= h(t('home_quality_desc')) ?></p>
        </div>
      </div>
      <div class="column">
        <div class="box soft-card">
          <h3 class="title is-6"><?= h(t('home_logistics_title')) ?></h3>
          <p class="is-size-7"><?= h(t('home_logistics_desc')) ?></p>
        </div>
      </div>
      <div class="column">
        <div class="box soft-card">
          <h3 class="title is-6"><?= h(t('home_support_title')) ?></h3>
          <p class="is-size-7"><?= h(t('home_support_desc')) ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="level">
      <div class="level-left">
        <h2 class="title is-4"><?= h(t('section_featured_products')) ?></h2>
      </div>
      <div class="level-right">
        <a class="button is-link is-light" href="/products"><?= h(t('btn_view_all')) ?></a>
      </div>
    </div>
    <div class="columns is-multiline">
      <?php foreach ($products as $p): ?>
        <div class="column is-4">
          <div class="card soft-card">
            <div class="card-content">
              <p class="title is-6"><a href="/product/<?= h($p['slug']) ?>"><?= h($p['title']) ?></a></p>
              <p class="content is-size-7"><?= h($p['summary']) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="level">
      <div class="level-left">
        <h2 class="title is-4"><?= h(t('section_success_cases')) ?></h2>
      </div>
      <div class="level-right">
        <a class="button is-link is-light" href="/cases"><?= h(t('btn_view_all')) ?></a>
      </div>
    </div>
    <div class="columns is-multiline">
      <?php foreach ($cases as $c): ?>
        <div class="column is-4">
          <div class="card soft-card">
            <div class="card-content">
              <p class="title is-6"><a href="/case/<?= h($c['slug']) ?>"><?= h($c['title']) ?></a></p>
              <p class="content is-size-7"><?= h($c['summary']) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>