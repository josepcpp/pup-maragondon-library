<?php
$activePage = 'holdings';
$h = @json_decode(@file_get_contents(__DIR__ . '/data/holdings.json'), true) ?: [];
$cats  = $h['categories'] ?? [];
$s = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true) ?: [];
$opac_url = $s['opac_url'] ?? 'https://ils.pup.edu.ph/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Holdings – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'includes/nav.php'; ?>

<section class="inner-hero">
  <div class="hero-overlay"></div>
  <div class="container inner-hero-content">
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>Library Holdings</span>
    </div>
    <h1>Library Holdings</h1>
    <p>Explore our diverse and growing collection of books, references, and special materials.</p>
  </div>
</section>

<section style="background:var(--parchment);padding:80px 0 20px;">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Our Collections</span>
      <h2 class="section-title">What We <span>Hold</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub" style="margin:16px auto 0;">Our books are arranged on the shelves according to the Library of Congress Classification System, where fields of knowledge are identified by letters and combined with Arabic numbers to form call numbers.</p>
    </div>
  </div>
</section>

<!-- HOLDINGS GRID -->
<section style="background:var(--parchment);padding:80px 0 100px;">
  <div class="container">
    <div class="holdings-grid reveal">
      <?php foreach ($cats as $cat): ?>
      <div class="holding-card">
        <span class="holding-num"><?= (int)($cat['num'] ?? 1) ?></span>
        <div class="holding-icon"><i class="<?= htmlspecialchars($cat['icon'] ?? 'fa-solid fa-book') ?>"></i></div>
        <h3><?= htmlspecialchars($cat['title'] ?? '') ?></h3>
        <p><?= htmlspecialchars($cat['description'] ?? '') ?></p>
        <span class="holding-tag"><?= htmlspecialchars($cat['tag'] ?? '') ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CATALOG ACCESS BANNER -->
    <div class="admin-contact-banner reveal" style="margin-top:clamp(48px,6vw,72px);">
      <div class="acb-text">
        <h3>Search our catalog</h3>
        <p>The Online Public Access Catalog (OPAC) is a module of the Koha library software for searching the bibliographic details of our materials. It is available 24/7.</p>
      </div>
      <div class="acb-actions">
        <a href="<?= htmlspecialchars($opac_url) ?>" target="_blank" rel="noopener" class="btn-gold">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search OPAC
        </a>
        <a href="services.php" class="btn-hero-outline">Library services</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
