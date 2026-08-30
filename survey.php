<?php
$activePage = 'survey';
$s = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true) ?: [];
$survey_url = $s['survey_url'] ?? 'https://forms.gle/iJicqykCupxRXPy57';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Survey – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .survey-layout {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: clamp(36px, 5vw, 72px);
      align-items: start;
    }
    .survey-meta { display: flex; flex-direction: column; }
    .survey-meta > div {
      display: grid;
      grid-template-columns: 130px 1fr;
      gap: 20px;
      padding: 15px 0;
      border-top: 1px solid var(--rule);
      align-items: baseline;
    }
    .survey-meta > div:last-child { border-bottom: 1px solid var(--rule); }
    .survey-meta dt {
      font-size: 0.72rem; font-weight: 700;
      letter-spacing: 0.13em; text-transform: uppercase;
      color: var(--maroon);
    }
    .survey-meta dd { font-size: 0.92rem; color: var(--ink); }

    .survey-aside-title {
      font-family: "Source Sans 3", sans-serif;
      font-size: 0.74rem; font-weight: 700;
      letter-spacing: 0.13em; text-transform: uppercase;
      color: var(--maroon);
      padding-bottom: 14px;
      border-bottom: 1px solid var(--rule);
    }
    .survey-topics { display: flex; flex-direction: column; }
    .survey-topics li {
      padding: 15px 0;
      border-bottom: 1px solid var(--rule);
    }
    .survey-topics strong {
      display: block;
      font-size: 0.92rem; font-weight: 600;
      color: var(--ink); margin-bottom: 3px;
    }
    .survey-topics span {
      font-size: 0.85rem; color: var(--ink-mid); line-height: 1.65;
    }
    @media (max-width: 900px) {
      .survey-layout    { grid-template-columns: 1fr; }
      .survey-meta > div { grid-template-columns: 1fr; gap: 4px; }
    }
  </style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<section class="inner-hero" style="min-height:45vh;">
  <div class="hero-overlay"></div>
  <div class="container inner-hero-content">
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <i class="fa-solid fa-chevron-right"></i>
      <span>Library Survey</span>
    </div>
    <h1>Library Survey</h1>
    <p>Your feedback helps us improve our services for the entire PUP Maragondon community.</p>
  </div>
</section>

<section style="background:var(--paper-alt);padding:var(--band) 0;">
  <div class="container">

    <div class="survey-layout">
      <div class="reveal">
        <div class="section-header" style="margin-bottom:26px;">
          <span class="section-label">We value your opinion</span>
          <h2 class="section-title">Help us <span>serve better</span></h2>
          <div class="section-divider"></div>
          <p class="section-sub">The library is committed to the continuous improvement of its services. Your input helps us meet the changing needs of the academic community.</p>
        </div>

        <dl class="survey-meta">
          <div>
            <dt>Time needed</dt>
            <dd>5–10 minutes</dd>
          </div>
          <div>
            <dt>Privacy</dt>
            <dd>Responses are confidential and anonymous</dd>
          </div>
          <div>
            <dt>Platform</dt>
            <dd>Google Forms, opens in a new tab</dd>
          </div>
        </dl>

        <a href="<?= htmlspecialchars($survey_url) ?>" target="_blank" rel="noopener" class="btn-primary" style="margin-top:28px;">
          <span>Take the survey</span>
          <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>
      </div>

      <div class="reveal">
        <h3 class="survey-aside-title">What we ask about</h3>
        <ul class="survey-topics">
          <?php
          $items = [
            ['Overall satisfaction', 'Your overall experience with library services.'],
            ['Collection quality',   'The range and relevance of available materials.'],
            ['Staff &amp; service',  'The helpfulness and professionalism of personnel.'],
            ['Digital resources',    'The usability and availability of online databases.'],
            ['Facilities',           'Reading rooms, study spaces, and equipment.'],
            ['Suggestions',          'Your ideas for how we can improve.'],
          ];
          foreach ($items as $it):
          ?>
          <li>
            <strong><?= $it[0] ?></strong>
            <span><?= $it[1] ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
