</main>
<footer class="footer">
  <div class="container">
    <div class="columns">
      <div class="column is-5">
        <h3 class="title is-5"><?= h($site['name']) ?></h3>
        <p class="subtitle is-6"><?= h($site['tagline']) ?></p>
      </div>
      <div class="column is-7">
        <div class="columns">
          <div class="column">
            <p class="has-text-weight-semibold">Company</p>
            <p class="is-size-7"><?= h($site['address']) ?></p>
          </div>
          <div class="column">
            <p class="has-text-weight-semibold">Contact</p>
            <p class="is-size-7"><?= h($site['email']) ?></p>
            <p class="is-size-7"><?= h($site['phone']) ?></p>
          </div>
        </div>
      </div>
    </div>
    <p class="has-text-grey is-size-7">© <?= date('Y') ?> <?= h($site['name']) ?>. All rights reserved.</p>
  </div>
</footer>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const burgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
    burgers.forEach(el => {
      el.addEventListener('click', () => {
        const target = document.getElementById(el.dataset.target);
        el.classList.toggle('is-active');
        if (target) target.classList.toggle('is-active');
      });
    });
  });
</script>
</body>
</html>