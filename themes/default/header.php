<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($seo['title']) ?></title>
  <meta name="description" content="<?= h($seo['description']) ?>">
  <link rel="canonical" href="<?= h($seo['canonical']) ?>">
  <meta property="og:title" content="<?= h($seo['title']) ?>">
  <meta property="og:description" content="<?= h($seo['description']) ?>">
  <meta property="og:type" content="website">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
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
        <a class="navbar-item" href="/products">Products</a>
        <a class="navbar-item" href="/cases">Cases</a>
        <a class="navbar-item" href="/blog">Blog</a>
        <a class="navbar-item" href="/contact">Contact</a>
        <div class="navbar-item">
          <a class="button is-link" href="/contact">Request a Quote</a>
        </div>
      </div>
    </div>
  </div>
</nav>
<main>