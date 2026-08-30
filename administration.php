<?php
$activePage = 'administration';
$personnel  = @json_decode(@file_get_contents(__DIR__ . '/data/personnel.json'), true) ?: [];
$s          = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'),  true) ?: [];
$contact    = $s['contact'] ?? [];
$lib_email  = $contact['email']   ?? 'library.maragondon@pup.edu.ph';
$lib_hours  = $contact['hours']   ?? 'Mon – Fri, 8:00 AM – 5:00 PM';
$lib_addr   = $contact['address'] ?? 'PUP Maragondon Campus Library';

function getInitials(string $name): string {
    $parts  = preg_split('/\s+/', trim($name));
    $result = '';
    foreach ($parts as $p) { if ($p !== '') $result .= strtoupper($p[0]); }
    return substr($result, 0, 2) ?: '?';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Personnel – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      <span>Library Personnel</span>
    </div>
    <h1>Library Personnel</h1>
    <p>Meet the dedicated professionals behind the PUP Maragondon Library.</p>
  </div>
</section>

<section style="background:var(--parchment);padding:80px 0 100px;">
  <div class="container">

    <div class="section-header reveal">
      <span class="section-label">Meet the Team</span>
      <h2 class="section-title">Our Library <span>Staff</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub" style="margin:16px auto 0;">Qualified library professionals dedicated to supporting research, learning, and knowledge access for the PUP Maragondon community.</p>
    </div>

    <?php if (!empty($personnel)): ?>
    <div class="personnel-big-grid reveal">
      <?php foreach ($personnel as $p):
        $name     = htmlspecialchars($p['name']           ?? '');
        $role     = htmlspecialchars($p['role']           ?? '');
        $email    = htmlspecialchars($p['email']          ?? '');
        $ext      = htmlspecialchars($p['ext']            ?? '');
        $photo    = htmlspecialchars($p['photo']          ?? '');
        $bio      = htmlspecialchars($p['bio']            ?? 'A dedicated library professional committed to supporting the research and academic needs of the PUP Maragondon community.');
        $spec     = htmlspecialchars($p['specialization'] ?? '');
        $dept     = htmlspecialchars($p['department']     ?? '');
        $initials = getInitials($p['name'] ?? '');
      ?>
      <div class="pbc-card stagger-card">

        <?php if ($photo): ?>
          <img src="<?= $photo ?>" class="pbc-photo" alt="<?= $name ?>">
        <?php else: ?>
          <div class="pbc-photo-placeholder">
            <span class="pbc-initials"><?= $initials ?></span>
          </div>
        <?php endif; ?>

        <div class="pbc-body">
          <h3 class="pbc-name"><?= $name ?></h3>
          <?php if ($role): ?>
          <span class="pbc-role">
            <i class="fa-solid fa-user-tie"></i> <?= $role ?>
          </span>
          <?php endif; ?>
          <?php if ($spec): ?>
          <span class="pbc-role" style="color:#1565c0;background:rgba(21,101,192,0.07);margin-top:-6px;">
            <i class="fa-solid fa-star"></i> <?= $spec ?>
          </span>
          <?php endif; ?>
          <?php if ($dept): ?>
          <span class="pbc-role" style="color:#2e7d32;background:rgba(46,125,50,0.07);margin-top:-6px;">
            <i class="fa-solid fa-building"></i> <?= $dept ?>
          </span>
          <?php endif; ?>
          <div class="pbc-divider"></div>
          <p class="pbc-bio"><?= $bio ?></p>
          <?php if ($email || $ext): ?>
          <div class="pbc-contacts">
            <?php if ($email): ?>
            <a href="mailto:<?= $email ?>" class="pbc-contact-row">
              <span class="pbc-contact-icon"><i class="fa-solid fa-envelope"></i></span>
              <?= $email ?>
            </a>
            <?php endif; ?>
            <?php if ($ext): ?>
            <div class="pbc-contact-row">
              <span class="pbc-contact-icon"><i class="fa-solid fa-phone"></i></span>
              Extension <?= $ext ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div style="text-align:center;padding:70px 0;color:var(--ink-light);">
      <i class="fa-solid fa-users" style="font-size:3rem;opacity:0.18;display:block;margin-bottom:16px;"></i>
      <p>No personnel records yet. <a href="admin/personnel.php" style="color:var(--maroon);">Add staff in the admin panel.</a></p>
    </div>
    <?php endif; ?>

    <!-- CONTACT CTA BANNER -->
    <div class="admin-contact-banner reveal">
      <div class="acb-text">
        <h3>Have a Question for Our Team?</h3>
        <p>Our reference librarians are ready to assist with research inquiries, resource navigation, and library account support.</p>
        <div class="acb-info">
          <div class="acb-info-item">
            <i class="fa-solid fa-clock"></i>
            <span><?= htmlspecialchars($lib_hours) ?></span>
          </div>
          <div class="acb-info-item">
            <i class="fa-solid fa-location-dot"></i>
            <span><?= htmlspecialchars($lib_addr) ?></span>
          </div>
          <div class="acb-info-item">
            <i class="fa-solid fa-envelope"></i>
            <span><?= htmlspecialchars($lib_email) ?></span>
          </div>
        </div>
      </div>
      <div class="acb-actions">
        <a href="mailto:<?= htmlspecialchars($lib_email) ?>" class="btn-gold">
          <i class="fa-solid fa-envelope"></i> Email Us
        </a>
        <a href="survey.php" class="btn-outline" style="border-color:rgba(255,255,255,0.3);color:#fff;">
          <i class="fa-solid fa-star"></i> Library Survey
        </a>
      </div>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
