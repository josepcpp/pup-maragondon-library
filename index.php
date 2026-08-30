<?php
$activePage = 'home';
$s        = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true) ?: [];
$about    = @json_decode(@file_get_contents(__DIR__ . '/data/about.json'),    true) ?: [];
$arrivals = @json_decode(@file_get_contents(__DIR__ . '/data/arrivals.json'), true) ?: [];
$featured = @json_decode(@file_get_contents(__DIR__ . '/data/featured.json'), true) ?: [];

$hero_tagline  = $s['hero_tagline']  ?? '';
$avp_id        = $s['avp_youtube_id'] ?? 'ScMzIvxBSi4';
$opac_url      = $s['opac_url']      ?? 'https://ils.pup.edu.ph/';
$hero_stats    = array_slice($s['hero_stats'] ?? [], 0, 3);
$avp_thumb     = 'https://img.youtube.com/vi/' . rawurlencode($avp_id) . '/hqdefault.jpg';
$contact       = $s['contact'] ?? [];

// Today's opening hours, from the schedule managed in the CMS
$today_num  = (int)date('w'); // 0 = Sunday … 6 = Saturday
$today_row  = null;
foreach ($about['schedule'] ?? [] as $row) {
    if ((int)($row['day_num'] ?? -1) === $today_num) { $today_row = $row; break; }
}
$is_open_today = !empty($today_row['open']);
$today_time    = $today_row['time'] ?? ($is_open_today ? '8:00 AM – 5:00 PM' : 'Closed');

// The featured slot only earns its place on the page once it holds real content.
$featured_title = trim($featured['title'] ?? '');
$has_featured   = $featured_title !== '' && $featured_title !== 'Featured Resource Title';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($s['site_title'] ?? 'PUP Maragondon – University Library') ?></title>
  <meta name="description" content="<?= htmlspecialchars($hero_tagline ?: 'Collections, research archives, and digital resources of the PUP Maragondon Campus Library.') ?>">
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script>
    window.cmsArrivals = <?= json_encode(array_values($arrivals), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.cmsSettings = { avp_youtube_id: <?= json_encode($avp_id) ?> };
  </script>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<!-- ─────────────────────────────────────────────────────────────────────────
     1. HERO
     ───────────────────────────────────────────────────────────────────── -->
<section class="hero">
  <div class="container hero-grid">

    <div class="hero-content">
      <p class="hero-eyebrow">PUP Maragondon Campus</p>
      <h1>Discover, <em>Learn,</em><br>and Innovate.</h1>
      <p class="hero-sub"><?= htmlspecialchars($hero_tagline ?: 'Your gateway to PUP Maragondon\'s collections, theses, Filipiniana, and digital resources — available anytime, anywhere.') ?></p>
      <div class="hero-actions">
        <a href="<?= htmlspecialchars($opac_url) ?>" target="_blank" rel="noopener" class="btn-gold">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search the Catalog
        </a>
        <a href="resources.php" class="btn-hero-outline">Online Databases</a>
      </div>
    </div>

    <div class="hero-visual">
      <div class="hero-info-card">
        <div class="hic-header">
          <p class="hic-status">
            <span class="hbadge-dot"<?= $is_open_today ? '' : ' style="background:#9ca3af;"' ?>></span>
            <span><?= $is_open_today ? 'Open today' : 'Closed today' ?></span>
          </p>
          <span class="hic-time"><?= htmlspecialchars($today_time) ?></span>
        </div>

        <?php if ($hero_stats): ?>
        <div class="hic-divider"></div>
        <div class="hic-stats">
          <?php foreach ($hero_stats as $stat): ?>
          <div class="hic-stat">
            <span class="hic-stat-num"><?= htmlspecialchars($stat['num'] ?? '') ?></span>
            <span class="hic-stat-lbl"><?= htmlspecialchars($stat['label'] ?? '') ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="hic-divider"></div>
        <div class="hic-quick-links">
          <a href="<?= htmlspecialchars($opac_url) ?>" target="_blank" rel="noopener" class="hic-link">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            Search catalog (OPAC)
            <i class="fa-solid fa-arrow-right hic-arrow" aria-hidden="true"></i>
          </a>
          <a href="resources.php" class="hic-link">
            <i class="fa-solid fa-database" aria-hidden="true"></i>
            Online databases
            <i class="fa-solid fa-arrow-right hic-arrow" aria-hidden="true"></i>
          </a>
          <a href="holdings.php" class="hic-link">
            <i class="fa-solid fa-book" aria-hidden="true"></i>
            Browse holdings
            <i class="fa-solid fa-arrow-right hic-arrow" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ─────────────────────────────────────────────────────────────────────────
     2. WHERE TO START — an index, not a row of identical cards
     ───────────────────────────────────────────────────────────────────── -->
<section class="quick-access">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Where to start</span>
      <h2 class="section-title">Four ways into <span>the collection</span></h2>
      <div class="section-divider"></div>
    </div>

    <div class="quick-grid">
      <div class="q-card" onclick="location.href='holdings.php'">
        <div class="q-card-icon"><i class="fa-solid fa-book" aria-hidden="true"></i></div>
        <h3>Digital Library</h3>
        <p>E-books, modules, and PDF resources you can open from any device.</p>
      </div>
      <div class="q-card" onclick="location.href='holdings.php#theses'">
        <div class="q-card-icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
        <h3>Theses &amp; Research</h3>
        <p>Student dissertations and the campus research archive.</p>
      </div>
      <div class="q-card" onclick="location.href='resources.php'">
        <div class="q-card-icon"><i class="fa-solid fa-database" aria-hidden="true"></i></div>
        <h3>Online Databases</h3>
        <p>Emerald Insight, Gale, ProQuest, iG Library, and PressReader.</p>
      </div>
      <div class="q-card" onclick="location.href='services.php'">
        <div class="q-card-icon"><i class="fa-solid fa-bell-concierge" aria-hidden="true"></i></div>
        <h3>Reader Services</h3>
        <p>Circulation, reference, reserve, Filipiniana, and periodicals.</p>
      </div>
    </div>
  </div>
</section>

<!-- ─────────────────────────────────────────────────────────────────────────
     3. THE COLLECTION — featured lead + recent additions
     ───────────────────────────────────────────────────────────────────── -->
<section class="collection-section">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">From the shelves</span>
      <h2 class="section-title">Recently <span>added</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub">The latest books, theses, and multimedia to reach the collection.</p>
    </div>

    <?php if ($has_featured): ?>
    <article class="home-featured reveal">
      <div class="home-featured-media">
        <?php if (!empty($featured['image'])): ?>
          <img src="<?= htmlspecialchars($featured['image']) ?>"
               alt="<?= htmlspecialchars($featured_title) ?>" loading="lazy">
        <?php else: ?>
          <div class="home-featured-empty"><i class="fa-solid fa-book-open" aria-hidden="true"></i></div>
        <?php endif; ?>
      </div>
      <div class="home-featured-body">
        <span class="section-label">Featured this week</span>
        <h3><?= htmlspecialchars($featured_title) ?></h3>
        <?php if (!empty($featured['description'])): ?>
        <p><?= htmlspecialchars($featured['description']) ?></p>
        <?php endif; ?>
        <?php if (!empty($featured['link']) && $featured['link'] !== '#'): ?>
        <a href="<?= htmlspecialchars($featured['link']) ?>" class="btn-primary">
          View resource <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
        <?php endif; ?>
      </div>
    </article>
    <?php endif; ?>

    <div class="arrivals-grid" id="arrivals-container">
      <?php if (empty($arrivals)): ?>
      <p class="empty-note">No arrivals have been published yet.</p>
      <?php endif; ?>
    </div>

    <p class="section-more reveal">
      <a href="holdings.php" class="btn-ghost">Browse all holdings <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </p>
  </div>
</section>

<!-- ─────────────────────────────────────────────────────────────────────────
     4. VISIT & TOUR — the practical facts sit beside the tour, not in
        their own full-width strip of icon cards
     ───────────────────────────────────────────────────────────────────── -->
<section class="avp-section">
  <div class="container">
    <div class="avp-inner">

      <div class="avp-text reveal">
        <span class="section-label">Virtual tour</span>
        <h2>Explore the library<br>before you visit</h2>
        <p>Take a walk through our reading rooms, collaborative spaces, and multimedia facilities.</p>
        <ul class="avp-features">
          <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Interactive learning spaces</li>
          <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Open-access e-book collections</li>
          <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Multimedia viewing rooms</li>
          <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Filipiniana &amp; special collections</li>
        </ul>
        <button type="button" class="btn-primary" id="btn-watch-avp">
          <i class="fa-solid fa-play" aria-hidden="true"></i> Watch the tour
        </button>
      </div>

      <div class="avp-visual reveal">
        <div class="avp-thumbnail" id="play-avp-thumb" role="button" tabindex="0" aria-label="Play library tour video">
          <img src="<?= htmlspecialchars($avp_thumb) ?>" alt="" loading="lazy">
          <div class="avp-play-overlay"><span class="play-ring"><i class="fa-solid fa-play" aria-hidden="true"></i></span></div>
        </div>
      </div>

    </div>

    <!-- Practical facts, as a compact definition row -->
    <div class="visit-facts reveal">
      <div class="visit-fact">
        <h4>Hours</h4>
        <p><?= htmlspecialchars($contact['hours'] ?? 'Mon – Fri, 8:00 AM – 5:00 PM') ?><br>No noon break.</p>
      </div>
      <div class="visit-fact">
        <h4>Remote access</h4>
        <p>E-resources and databases are reachable off-campus with your PUP credentials.</p>
      </div>
      <div class="visit-fact">
        <h4>Library card</h4>
        <p>Present a valid PUP ID at the circulation desk to register and borrow.</p>
      </div>
      <div class="visit-fact">
        <h4>Ask a librarian</h4>
        <p><a href="contact.php">Get in touch</a> for research help and database navigation.</p>
      </div>
    </div>
  </div>
</section>

<!-- VIDEO MODAL -->
<div class="video-modal" id="video-modal" role="dialog" aria-modal="true" aria-label="Library tour video">
  <div class="modal-content">
    <span class="close-modal" id="close-modal" role="button" tabindex="0" aria-label="Close video">
      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </span>
    <div class="iframe-container">
      <iframe id="avp-iframe" src="" title="PUP Maragondon Library tour" allowfullscreen></iframe>
    </div>
  </div>
</div>

<!-- ─────────────────────────────────────────────────────────────────────────
     5. GETTING STARTED
     ───────────────────────────────────────────────────────────────────── -->
<section class="how-section">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Getting started</span>
      <h2 class="section-title">Using the library <span>in four steps</span></h2>
      <div class="section-divider"></div>
    </div>

    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">01</div>
        <h4>Get your library card</h4>
        <p>Present a valid PUP ID at the circulation desk. Registration is free for enrolled students and faculty.</p>
      </div>
      <div class="step-card">
        <div class="step-num">02</div>
        <h4>Search the catalog</h4>
        <p>Use the OPAC to locate books, theses, Filipiniana, and periodicals in the physical collection.</p>
      </div>
      <div class="step-card">
        <div class="step-num">03</div>
        <h4>Access digital resources</h4>
        <p>Sign in with your PUP credentials to reach Emerald, Gale, ProQuest, iG Library, and PressReader.</p>
      </div>
      <div class="step-card">
        <div class="step-num">04</div>
        <h4>Borrow &amp; return</h4>
        <p>Borrow up to three books for seven days. One renewal is allowed when no hold is pending.</p>
      </div>
    </div>

    <p class="section-more reveal">
      <a href="guidelines.php" class="btn-outline">Read the full guidelines</a>
    </p>
  </div>
</section>

<!-- ─────────────────────────────────────────────────────────────────────────
     6. CLOSING CTA
     ───────────────────────────────────────────────────────────────────── -->
<section class="tutorial-cta">
  <div class="container">
    <div class="cta-inner">
      <div class="cta-text">
        <h2>Need help navigating the library?</h2>
        <p>Our programs, orientations, and reference librarians are here to help you find sources and make the most of the collection.</p>
        <div class="cta-buttons">
          <a href="programs.php" class="btn-gold">View programs <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          <a href="contact.php" class="btn-hero-outline">Contact the library</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
