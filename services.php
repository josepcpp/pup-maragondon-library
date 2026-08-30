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
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

<section style="background:var(--parchment);padding:60px 0 20px;">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Reader's Services</span>
      <h2 class="section-title">How We <span>Serve You</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub" style="margin:16px auto 0;">Readers services are provided through the Circulation, Reference, Filipiniana and Periodicals sections.</p>
    </div>
  </div>
</section>

<?php if (!empty($services)): ?>
<section class="services-tabs" style="background:var(--parchment);">
  <div class="container reveal">
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

<section style="background:var(--parchment);padding-bottom:100px;">
  <div class="container reveal">
    <div class="hours-widget">
      <h3><i class="fa-regular fa-clock" style="margin-right:12px;"></i>Library Hours</h3>
      <p>Visit us during regular working hours for in-person services. Please note our "No Noon Break" policy.</p>
      <div class="hours-status">
        <span class="status-dot" id="status-dot"></span>
        <span id="library-status">Checking status...</span>
      </div>
      <div class="hours-grid">
        <?php
        $hours_data = @json_decode(@file_get_contents(__DIR__ . '/data/about.json'), true);
        $hours_sched = $hours_data['schedule'] ?? [];
        if (!empty($hours_sched)):
            foreach ($hours_sched as $row):
        ?>
        <div class="hours-row">
          <div class="day"><?= h($row['day']) ?></div>
          <div class="time"><?= h($row['time']) ?></div>
        </div>
        <?php endforeach; else: ?>
        <div class="hours-row"><div class="day">Monday – Friday</div><div class="time">8:00 AM – 8:00 PM</div></div>
        <div class="hours-row"><div class="day">Saturday</div><div class="time">8:00 AM – 5:00 PM</div></div>
        <div class="hours-row"><div class="day">Sunday</div><div class="time">Closed</div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
