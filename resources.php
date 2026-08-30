<?php
$activePage = 'resources';
$resources  = @json_decode(@file_get_contents(__DIR__ . '/data/resources.json'), true) ?: [];
$grouped    = [];
foreach ($resources as $r) {
    $grouped[$r['category'] ?? 'Other'][] = $r;
}

// Per-resource colour palette (cycles by id so each card looks distinct)
$palette = ['#c0392b','#0077b6','#2e7d32','#b45309','#6d4c8e','#00838f','#c62828','#1565c0'];

$catIcons = [
    'Academic'           => 'fa-graduation-cap',
    'E-Books'            => 'fa-book-open',
    'News & Periodicals' => 'fa-newspaper',
    'Government'         => 'fa-landmark',
    'Other'              => 'fa-database',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Resources – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <script>
    // Set the theme before first paint so dark-mode users never see a light flash,
    // and mark the document as scripted so CSS can gate entrance animations.
    (function () {
      var d = document.documentElement;
      d.classList.add("js");
      var t = null;
      try { t = localStorage.getItem("pup-theme"); } catch (e) {}
      if (!t) {
        t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
      }
      d.setAttribute("data-theme", t);
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&amp;display=swap" rel="stylesheet">
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
      <span>Online Resources</span>
    </div>
    <h1>Online Resources</h1>
    <p>Access world-class academic databases and e-resources available to the PUP Maragondon community.</p>
  </div>
</section>

<section class="resources-hero-section">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Digital Access</span>
      <h2 class="section-title">Our <span>E-Resource Partners</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub" style="margin:16px auto 0;">PUP provides access to leading international databases for research, academic journals, and digital publications — free for all PUP students and faculty.</p>
    </div>

    <?php if (empty($resources)): ?>
      <p style="text-align:center;color:var(--ink-light);padding:60px 0;">No resources listed yet.</p>
    <?php else: ?>

    <div class="resources-grid-v2">
      <?php foreach ($resources as $r):
        $category = $r['category'] ?? 'Other';
        $icon     = $catIcons[$category] ?? 'fa-database';
        $color    = $palette[($r['id'] - 1) % count($palette)];
        $hasUrl   = !empty($r['url']) && $r['url'] !== '#';
      ?>
      <div class="rc-card" style="--rc-color:<?= $color ?>">

        <!-- ── Header stripe ── -->
        <div class="rc-header">
          <div class="rc-header-bg"></div>

          <div class="rc-icon-wrap">
            <?php if (!empty($r['logo'])): ?>
              <img src="<?= htmlspecialchars($r['logo']) ?>"
                   alt="<?= htmlspecialchars($r['name']) ?>"
                   style="max-width:52px;max-height:52px;width:auto;height:auto;object-fit:contain;">
            <?php else: ?>
              <i class="fa-solid <?= $icon ?>"></i>
            <?php endif; ?>
          </div>

          <span class="rc-access-badge campus">
            <i class="fa-solid fa-building-columns"></i> On-Campus Access
          </span>
        </div>

        <!-- ── Body ── -->
        <div class="rc-body">
          <span class="rc-tag"><?= htmlspecialchars($category) ?></span>
          <div class="rc-name"><?= htmlspecialchars($r['name']) ?></div>
          <p class="rc-desc"><?= htmlspecialchars($r['description'] ?? '') ?></p>

          <!-- QR frame -->
          <div class="rc-qr-frame">
            <span class="rc-qr-corner tl"></span>
            <span class="rc-qr-corner tr"></span>
            <span class="rc-qr-corner bl"></span>
            <span class="rc-qr-corner br"></span>
            <div class="rc-qr-inner">
              <?php if (!empty($r['logo'])): ?>
                <img src="<?= htmlspecialchars($r['logo']) ?>"
                     alt="<?= htmlspecialchars($r['name']) ?>">
              <?php else: ?>
                <i class="fa-solid fa-qrcode"></i>
                <span>SCAN TO<br>ACCESS</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Access button -->
          <?php if ($hasUrl): ?>
          <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" class="rc-btn">
            <span>Access Database</span>
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span class="rc-btn-shine"></span>
          </a>
          <?php else: ?>
          <span class="rc-btn" style="opacity:0.45;cursor:default;">
            <span>Campus Access Only</span>
            <i class="fa-solid fa-building-columns"></i>
          </span>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
