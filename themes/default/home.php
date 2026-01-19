<section class="hero is-medium brand-gradient">
  <div class="hero-body">
    <div class="container">
      <p class="title has-text-white"><?= h($site['tagline']) ?></p>
      <p class="subtitle has-text-white"><?= h($site['about']) ?></p>
      <a class="button is-light" href="/contact">Request a Quote</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="columns">
      <div class="column">
        <div class="box soft-card">
          <h3 class="title is-6">Quality Assurance</h3>
          <p class="is-size-7">ISO-aligned production with strict QC before shipment.</p>
        </div>
      </div>
      <div class="column">
        <div class="box soft-card">
          <h3 class="title is-6">Global Logistics</h3>
          <p class="is-size-7">On-time delivery with consolidated freight options.</p>
        </div>
      </div>
      <div class="column">
        <div class="box soft-card">
          <h3 class="title is-6">Dedicated Support</h3>
          <p class="is-size-7">One-to-one account service for long-term buyers.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="level">
      <div class="level-left">
        <h2 class="title is-4">Featured Products</h2>
      </div>
      <div class="level-right">
        <a class="button is-link is-light" href="/products">View All</a>
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
        <h2 class="title is-4">Success Cases</h2>
      </div>
      <div class="level-right">
        <a class="button is-link is-light" href="/cases">View All</a>
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