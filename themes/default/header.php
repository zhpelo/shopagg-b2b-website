<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($seo['title'] ?? $site['name']) ?></title>
    <meta name="description" content="<?= h($seo['description'] ?? $site['tagline']) ?>">
    <?php if (!empty($seo['keywords'])): ?>
    <meta name="keywords" content="<?= h($seo['keywords']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= h($seo['canonical']) ?>">
    <meta property="og:title" content="<?= h($seo['title'] ?? $site['name']) ?>">
    <meta property="og:description" content="<?= h($seo['description'] ?? $site['tagline']) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($site['og_image'])): ?>
    <meta property="og:image" content="<?= h($site['og_image']) ?>">
    <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body{background:#f9fafb}
    .brand-gradient{background:linear-gradient(120deg,#0f172a,#1e293b)}
    .soft-card{box-shadow:0 10px 30px rgba(15,23,42,0.08)}
    .tagline{opacity:.7}
    </style>
</head>
<body>
<nav class="navbar is-white is-spaced" role="navigation">
  <div class="container">
    <div class="navbar-brand">
      <a class="navbar-item" href="/">
      <div>
          <strong><?= h($site['name']) ?></strong>
          <div class="tagline is-size-7"><?= h($site['tagline']) ?></div>
        </div>
      </a>
      <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="main-nav">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>
    <div id="main-nav" class="navbar-menu">
      <div class="navbar-end">
        <a class="navbar-item" href="/"><?= h(t('nav_home')) ?></a>
        <a class="navbar-item" href="/products"><?= h(t('nav_products')) ?></a>
        <a class="navbar-item" href="/cases"><?= h(t('nav_cases')) ?></a>
        <a class="navbar-item" href="/blog"><?= h(t('nav_blog')) ?></a>
        <a class="navbar-item" href="/contact"><?= h(t('nav_contact')) ?></a>
        <a class="navbar-item" href="/about"><?= h(t('nav_about')) ?></a>
        <div class="navbar-item has-dropdown is-hoverable">
          <a class="navbar-link"><?= h($languages[$lang] ?? $lang) ?></a>
          <div class="navbar-dropdown">
            <?php foreach ($languages as $code => $label): ?>
              <a class="navbar-item" href="<?= h(lang_switch_url($code)) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="navbar-item">
          <a class="button is-link" href="/contact"><?= h(t('cta_quote')) ?></a>
        </div>
      </div>
    </div>
  </div>
</nav>
<main>