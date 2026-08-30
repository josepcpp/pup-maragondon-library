<?php
$activePage = 'linkages';
if (!function_exists('h')) {
    function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
$_lnk  = @json_decode(@file_get_contents(__DIR__ . '/data/linkages.json'), true);
$_lnk  = is_array($_lnk) ? $_lnk : [];
$intro  = $_lnk['intro']           ?? 'The PUP University Library promotes collaboration with local and international community agencies and organizations to foster cooperation, making efficient and effective use of library resources.';
$cards  = $_lnk['cards']           ?? [];
$mou_t  = $_lnk['mou_title']       ?? 'Memoranda of Agreement';
$mou_d  = $_lnk['mou_description'] ?? 'Formal collaborations and coordination with various campus branches and community agencies allow the library to expand its reach and service capabilities effectively.';
$cta_t  = $_lnk['cta_title']       ?? 'Want to Partner With Us?';
$cta_d  = $_lnk['cta_description'] ?? 'The PUP University Library is committed to fostering cooperation with local and international agencies/organizations to make efficient and effective use of library resources.';
$email  = $_lnk['contact_email']   ?? 'library@pup.edu.ph';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Linkages – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .mou-block {
      display: grid;
      grid-template-columns: 0.8fr 1.2fr;
      gap: clamp(24px, 4vw, 56px);
      padding-top: clamp(32px, 4vw, 44px);
      border-top: 1px solid var(--rule);
    }
    .mou-block h3 { font-size: clamp(1.4rem, 2.6vw, 1.9rem); }
    .mou-block p {
      font-size: 0.93rem; color: var(--ink-mid);
      line-height: 1.8; margin-bottom: 24px; max-width: 60ch;
    }
    @media (max-width: 860px) { .mou-block { grid-template-columns: 1fr; } }
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
      <span>Linkages</span>
    </div>
    <h1>Linkages</h1>
    <p>Our partnerships with institutions that expand access to knowledge for the PUP Maragondon community.</p>
  </div>
</section>

<section style="background:var(--parchment);padding:80px 0 20px;">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Partnerships</span>
      <h2 class="section-title">Our <span>Institutional Linkages</span></h2>
      <div class="section-divider"></div>
      <?php if ($intro): ?>
      <p class="section-sub truncate-text" style="margin:16px auto 0;"><?= h($intro) ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section style="background:var(--parchment);padding-bottom:100px;">
  <div class="container">

    <?php if (!empty($cards)): ?>
    <div class="linkages-grid reveal">
      <?php foreach ($cards as $card): ?>
      <div class="linkage-card">
        <div class="linkage-logo-wrap"><i class="<?= h($card['icon'] ?? 'fa-solid fa-link') ?>"></i></div>
        <h3><?= h($card['title'] ?? '') ?></h3>
        <p class="truncate-text"><?= h($card['description'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="mou-block reveal">
      <div>
        <span class="section-label">Formal agreements</span>
        <h3><?= h($mou_t) ?></h3>
      </div>
      <div>
        <?php if ($mou_d): ?>
        <p class="truncate-text"><?= h($mou_d) ?></p>
        <?php endif; ?>
        <a href="mailto:<?= h($email) ?>" class="btn-outline">
          <span>Contact the library</span> <i class="fa-solid fa-envelope" aria-hidden="true"></i>
        </a>
      </div>
    </div>
  </div>
</section>

<section class="survey-banner reveal">
  <div class="container">
    <h2><?= h($cta_t) ?></h2>
    <p class="truncate-text"><?= h($cta_d) ?></p>
    <a href="mailto:<?= h($email) ?>" class="btn-primary">Contact the Library <i class="fa-solid fa-envelope"></i></a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
