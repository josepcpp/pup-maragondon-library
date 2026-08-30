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
$email  = $_lnk['contact_email']   ?? 'library@pup.edu.ph';

$_s      = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true) ?: [];
$uni_lib = $_s['university_library_url'] ?? '';
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

<section style="background:var(--paper-alt);padding:var(--band) 0;">
  <div class="container">

    <div class="section-header reveal">
      <span class="section-label">Partnerships</span>
      <h2 class="section-title">Our <span>institutional linkages</span></h2>
      <div class="section-divider"></div>
      <?php if ($intro): ?>
      <p class="section-sub truncate-text"><?= h($intro) ?></p>
      <?php endif; ?>
    </div>

    <?php if (!empty($cards)): ?>
    <div class="linkages-grid reveal">
      <?php foreach ($cards as $card): ?>
      <div class="linkage-card">
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
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
          <a href="mailto:<?= h($email) ?>" class="btn-outline">
            <span>Contact the library</span> <i class="fa-solid fa-envelope" aria-hidden="true"></i>
          </a>
          <?php if ($uni_lib): ?>
          <a href="<?= h($uni_lib) ?>" target="_blank" rel="noopener" class="btn-outline">
            <span>PUP University Library</span> <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
