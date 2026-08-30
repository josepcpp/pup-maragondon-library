<?php
$activePage = 'contact';
if (!function_exists('h')) {
    function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

$s       = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true) ?: [];
$about   = @json_decode(@file_get_contents(__DIR__ . '/data/about.json'),    true) ?: [];
$contact = $s['contact'] ?? [];
$sched   = $about['schedule'] ?? [];
$note    = $about['hours_note'] ?? '';

$email   = $contact['email']   ?? '';
$phone   = $contact['phone']   ?? '';
$address = $contact['address'] ?? '';
$hours   = $contact['hours']   ?? '';
$survey  = $s['survey_url']    ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact – PUP Maragondon University Library</title>
  <meta name="description" content="Contact details, location, and opening hours for the PUP Maragondon Campus Library.">
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .contact-layout {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: clamp(36px, 5vw, 72px);
      align-items: start;
    }
    .contact-list-lg { display: flex; flex-direction: column; }
    .contact-row {
      display: grid;
      grid-template-columns: 130px 1fr;
      gap: 20px;
      padding: 20px 0;
      border-top: 1px solid var(--rule);
      align-items: baseline;
    }
    .contact-row:last-child { border-bottom: 1px solid var(--rule); }
    .contact-row dt {
      font-size: 0.72rem; font-weight: 700;
      letter-spacing: 0.13em; text-transform: uppercase;
      color: var(--maroon);
    }
    .contact-row dd {
      font-size: 0.95rem; color: var(--ink); line-height: 1.7;
    }
    .contact-row dd a {
      color: var(--maroon);
      text-decoration: underline;
      text-underline-offset: 2px;
    }
    .contact-row dd small {
      display: block;
      font-size: 0.84rem;
      color: var(--ink-light);
      margin-top: 3px;
    }
    .contact-aside {
      background: var(--paper);
      border: 1px solid var(--rule);
      border-radius: var(--radius-lg);
      padding: clamp(26px, 3.4vw, 34px);
    }
    .contact-aside h3 {
      display: flex; align-items: center; gap: 10px;
      font-size: 1.08rem; font-weight: 600;
      color: var(--ink);
      padding-bottom: 16px; margin-bottom: 4px;
      border-bottom: 1px solid var(--rule);
    }
    .contact-aside h3 i { color: var(--maroon); font-size: 0.88rem; }
    .contact-hours-row {
      display: flex; align-items: center; justify-content: space-between;
      gap: 20px; padding: 12px 0;
      border-bottom: 1px solid var(--rule);
      font-size: 0.88rem;
    }
    .contact-hours-row:last-of-type { border-bottom: none; }
    .contact-hours-row.is-today .chr-day { color: var(--maroon); font-weight: 700; }
    .chr-day  { color: var(--ink); font-weight: 500; }
    .chr-time { color: var(--ink-mid); font-variant-numeric: tabular-nums; }
    .chr-time.closed { color: var(--ink-light); }
    .contact-note {
      display: flex; gap: 11px;
      border-left: 2px solid var(--gold);
      padding: 4px 0 4px 15px;
      margin-top: 20px;
      font-size: 0.84rem; color: var(--ink-mid); line-height: 1.7;
    }
    .contact-actions {
      display: flex; flex-wrap: wrap; gap: 12px;
      margin-top: clamp(32px, 4vw, 40px);
    }
    @media (max-width: 900px) {
      .contact-layout { grid-template-columns: 1fr; }
      .contact-row    { grid-template-columns: 1fr; gap: 6px; }
    }
  </style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<section class="inner-hero">
  <div class="container inner-hero-content">
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      <span>Contact</span>
    </div>
    <h1>Contact the Library</h1>
    <p>Reach the reference desk for research help, resource access, or questions about your library account.</p>
  </div>
</section>

<section style="padding: var(--band) 0; background: var(--paper-alt);">
  <div class="container">
    <div class="contact-layout">

      <div class="reveal">
        <div class="section-header" style="margin-bottom: 28px;">
          <span class="section-label">Get in touch</span>
          <h2 class="section-title">How to <span>reach us</span></h2>
          <div class="section-divider"></div>
        </div>

        <dl class="contact-list-lg">
          <?php if ($email): ?>
          <div class="contact-row">
            <dt>Email</dt>
            <dd>
              <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
              <small>The reference desk aims to reply within one working day.</small>
            </dd>
          </div>
          <?php endif; ?>

          <?php if ($phone): ?>
          <div class="contact-row">
            <dt>Telephone</dt>
            <dd>
              <?= h($phone) ?>
              <small>Available during opening hours.</small>
            </dd>
          </div>
          <?php endif; ?>

          <?php if ($address): ?>
          <div class="contact-row">
            <dt>Address</dt>
            <dd>
              PUP Maragondon Campus Library<br><?= h($address) ?>
            </dd>
          </div>
          <?php endif; ?>

          <div class="contact-row">
            <dt>In person</dt>
            <dd>
              Visit the circulation desk on campus.
              <small>Bring a valid PUP ID to register or borrow materials.</small>
            </dd>
          </div>

          <div class="contact-row">
            <dt>Library staff</dt>
            <dd>
              <a href="administration.php">See the personnel directory</a>
              <small>Contact a specific librarian or section directly.</small>
            </dd>
          </div>
        </dl>

        <div class="contact-actions">
          <?php if ($email): ?>
          <a href="mailto:<?= h($email) ?>" class="btn-primary">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i> Email the library
          </a>
          <?php endif; ?>
          <?php if ($survey): ?>
          <a href="survey.php" class="btn-outline">Give feedback</a>
          <?php endif; ?>
        </div>
      </div>

      <aside class="contact-aside reveal">
        <h3><i class="fa-regular fa-clock" aria-hidden="true"></i> Opening hours</h3>

        <?php if (!empty($sched)): ?>
          <?php $today = (int)date('w'); ?>
          <?php foreach ($sched as $row): ?>
          <div class="contact-hours-row <?= (int)($row['day_num'] ?? -1) === $today ? 'is-today' : '' ?>">
            <span class="chr-day"><?= h($row['day'] ?? '') ?></span>
            <span class="chr-time <?= empty($row['open']) ? 'closed' : '' ?>"><?= h($row['time'] ?? '') ?></span>
          </div>
          <?php endforeach; ?>
        <?php elseif ($hours): ?>
          <div class="contact-hours-row">
            <span class="chr-day">Opening hours</span>
            <span class="chr-time"><?= h($hours) ?></span>
          </div>
        <?php endif; ?>

        <?php if ($note): ?>
        <p class="contact-note">
          <i class="fa-solid fa-circle-info" aria-hidden="true" style="color:var(--gold-dark);margin-top:3px;"></i>
          <span><?= h($note) ?></span>
        </p>
        <?php endif; ?>
      </aside>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
