<?php
$activePage = 'guidelines';
if (!function_exists('h')) {
    function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
$rules = @json_decode(@file_get_contents(__DIR__ . '/data/guidelines.json'), true);
$rules = is_array($rules) ? $rules : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guidelines – PUP Maragondon Digital Library</title>
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
      <span>Guidelines</span>
    </div>
    <h1>Guidelines &amp; Policies</h1>
    <p>Understanding library rules helps ensure fair access and a respectful environment for everyone.</p>
  </div>
</section>

<section class="guidelines-section" style="background:var(--parchment);">
  <div class="container">
    <div class="section-header reveal">
      <span class="section-label">Library Policies</span>
      <h2 class="section-title">Rules &amp; <span>Procedures</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub" style="margin:16px auto 0;">
        Adhering to library rules and regulations ensures a productive environment. Noncompliance may be grounds for suspension of library privileges or expulsion from the university.
      </p>
    </div>

    <div class="accordion reveal">
      <?php if (!empty($rules)): ?>
      <?php foreach ($rules as $rule): ?>
      <div class="accordion-item <?= !empty($rule['open']) ? 'open' : '' ?>">
        <div class="accordion-header">
          <h4>
            <?php if (!empty($rule['icon'])): ?><i class="<?= h($rule['icon']) ?>"></i><?php endif; ?>
            <?= h($rule['title']) ?>
          </h4>
          <i class="fa-solid fa-chevron-down accordion-toggle"></i>
        </div>
        <div class="accordion-body">
          <?php if (!empty($rule['intro'])): ?>
          <p class="truncate-text"><?= h($rule['intro']) ?></p>
          <?php endif; ?>
          <?php if (!empty($rule['items'])): ?>
          <ul>
            <?php foreach ($rule['items'] as $item): ?>
            <li><?= h($item) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <p style="text-align:center;color:var(--ink-light);padding:40px 0;">No guidelines defined yet.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="survey-banner reveal">
  <div class="container">
    <h2>Have Feedback About Our Services?</h2>
    <p>Help us improve by answering our brief library satisfaction survey. Your input matters to us.</p>
    <a href="survey.php" class="btn-primary">Take the Survey <i class="fa-solid fa-arrow-right"></i></a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
