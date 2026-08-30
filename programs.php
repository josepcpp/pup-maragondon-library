<?php
$activePage = 'programs';
$programs = @json_decode(@file_get_contents(__DIR__ . '/data/programs.json'), true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Programs &amp; Events – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .collage-item { cursor: pointer; }
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
      <span>Programs &amp; Events</span>
    </div>
    <h1>Programs, Events<br>&amp; Activities</h1>
    <p>Engaging initiatives that connect students, faculty, and the community to the world of knowledge.</p>
  </div>
</section>

<section class="programs-section" style="background:var(--parchment);">
  <div class="container">

    <?php if (!empty($programs)): ?>

      <?php foreach ($programs as $i => $prog): ?>

        <?php if ($i > 0): ?>
        <hr style="border:none;border-top:1px solid var(--border);margin:80px 0;">
        <?php endif; ?>

        <div class="program-block reveal">
          <div class="program-block-header">
            <span class="section-label"><?= htmlspecialchars($prog['label'] ?? 'Program') ?></span>
            <h2><?= htmlspecialchars($prog['title'] ?? '') ?></h2>
            <p><?= htmlspecialchars($prog['description'] ?? '') ?></p>
          </div>

          <?php if (!empty($prog['photos'])): ?>
          <div class="collage-grid">
            <?php foreach ($prog['photos'] as $photo): ?>
            <div class="collage-item" data-img="<?= htmlspecialchars($photo) ?>">
              <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($prog['title'] ?? '') ?>" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius);">
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if (!empty($prog['when']) || !empty($prog['who']) || !empty($prog['what']) || !empty($prog['contact'])): ?>
          <div class="program-info-grid" style="margin-top:40px;">
            <?php if (!empty($prog['when'])): ?>
            <div class="program-info-card">
              <h4>When is it Held?</h4>
              <p><?= htmlspecialchars($prog['when']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($prog['who'])): ?>
            <div class="program-info-card">
              <h4>Who Should Attend?</h4>
              <p><?= htmlspecialchars($prog['who']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($prog['what'])): ?>
            <div class="program-info-card">
              <h4>What's Covered?</h4>
              <p><?= htmlspecialchars($prog['what']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($prog['contact'])): ?>
            <div class="program-info-card">
              <h4>Get in Touch</h4>
              <p><?= htmlspecialchars($prog['contact']) ?></p>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

      <?php endforeach; ?>

    <?php else: ?>
      <div style="text-align:center;padding:80px 0;color:var(--ink-light);">
        <i class="fa-solid fa-calendar-days" style="font-size:3rem;opacity:0.18;display:block;margin-bottom:16px;"></i>
        <p>No programs or events have been added yet. <a href="admin/programs.php" style="color:var(--maroon);">Manage programs in the admin panel.</a></p>
      </div>
    <?php endif; ?>

  </div>
</section>

<div class="lightbox-overlay" id="lightbox">
  <button class="lightbox-close" id="lightbox-close"><i class="fa-solid fa-xmark"></i></button>
  <img class="lightbox-img" src="" alt="Gallery image" id="lightbox-img">
</div>

<script>
  (function () {
    const box    = document.getElementById('lightbox');
    const boxImg = document.getElementById('lightbox-img');
    const close  = () => { box.classList.remove('show'); boxImg.src = ''; document.body.style.overflow = ''; };

    document.querySelectorAll('.collage-item').forEach(item => {
      item.addEventListener('click', function () {
        const src = this.dataset.img;
        if (!src) return;
        boxImg.src = src;
        box.classList.add('show');
        document.body.style.overflow = 'hidden';
      });
    });

    document.getElementById('lightbox-close')?.addEventListener('click', close);
    box?.addEventListener('click', e => { if (e.target === box) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  })();
</script>

<?php include 'includes/footer.php'; ?>
