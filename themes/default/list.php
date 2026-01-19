<section class="section">
  <div class="container">
    <h1 class="title is-3"><?= h($title) ?></h1>
    <div class="columns is-multiline">
      <?php foreach ($items as $item): ?>
        <div class="column is-4">
          <div class="card soft-card">
            <div class="card-content">
              <p class="title is-6"><a href="<?= h($item['url']) ?>"><?= h($item['title']) ?></a></p>
              <p class="content is-size-7"><?= h($item['summary']) ?></p>
              <a class="button is-small is-link is-light" href="<?= h($item['url']) ?>">Read More</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>