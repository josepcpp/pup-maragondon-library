<?php
$activePage = 'services';
if (!function_exists('h')) {
    function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
$services = @json_decode(@file_get_contents(__DIR__ . '/data/services.json'), true);
$services = is_array($services) ? $services : [];
$first = $services[0]['tab_id'] ?? 'circulation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Services – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .tab-panel-visual img {
      width: 100%;
      aspect-ratio: 3/2;
      object-fit: cover;
      border-radius: var(--radius-lg);
      display: block;
    }
  </style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<section class="inner-hero">
  <div class="hero-overlay"></div>
  <div class="container inner-hero-content">
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>Library Services</span>
    </div>
    <h1>Library Services</h1>
    <p>Comprehensive reader's services designed to support your academic journey at PUP Maragondon.</p>
  </div>
</section>

<?php if (!empty($services)): ?>
<section class="services-tabs" style="background:var(--paper-alt);padding-top:var(--band);">
  <div class="container reveal">
    <div class="section-header">
      <span class="section-label">Reader services</span>
      <h2 class="section-title">How we <span>serve you</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub">Reader services are provided through the Circulation, Reference, Filipiniana, and Periodicals sections.</p>
    </div>
    <div class="tab-nav">
      <?php foreach ($services as $i => $svc): ?>
      <button class="tab-btn <?= $i === 0 ? 'active' : '' ?>" data-tab="tab-<?= h($svc['tab_id']) ?>" id="<?= h($svc['tab_id']) ?>">
        <?php if (!empty($svc['icon'])): ?><i class="<?= h($svc['icon']) ?>"></i><?php endif; ?>
        <?= h($svc['title']) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($services as $i => $svc): ?>
    <div class="tab-panel <?= $i === 0 ? 'active' : '' ?>" id="tab-<?= h($svc['tab_id']) ?>">
      <div class="tab-panel-text">
        <h2><?= h($svc['title']) ?></h2>
        <?php if (!empty($svc['description'])): ?>
        <p class="truncate-text"><?= h($svc['description']) ?></p>
        <?php endif; ?>
        <?php if (!empty($svc['items'])): ?>
        <ul class="service-detail-list">
          <?php foreach ($svc['items'] as $item): ?>
          <li>
            <?php if (!empty($item['icon'])): ?><i class="<?= h($item['icon']) ?>"></i><?php endif; ?>
            <div>
              <h5><?= h($item['title'] ?? '') ?></h5>
              <?php if (!empty($item['description'])): ?>
              <p class="truncate-text"><?= h($item['description']) ?></p>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <?php if (!empty($svc['image'])): ?>
      <div class="tab-panel-visual">
        <img src="<?= h($svc['image']) ?>" alt="<?= h($svc['title']) ?>">
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
