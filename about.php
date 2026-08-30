<?php
$activePage = 'about';
if (!function_exists('h')) {
    function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
$_abt  = @json_decode(@file_get_contents(__DIR__ . '/data/about.json'), true);
$_abt  = is_array($_abt) ? $_abt : [];
$vgmo  = $_abt['vgmo']     ?? [];
$sched = $_abt['schedule'] ?? [];
$tline = $_abt['timeline'] ?? [];
$note  = $_abt['hours_note'] ?? 'The University Library observes a NO NOON BREAK policy.';

$_s      = @json_decode(@file_get_contents(__DIR__ . '/data/settings.json'), true) ?: [];
$uni_lib = $_s['university_library_url'] ?? '';

// Defaults if schedule empty
if (empty($sched)) {
    $sched = [
        ['day'=>'Monday',   'day_num'=>1,'time'=>'8:00 AM – 8:00 PM','open'=>true],
        ['day'=>'Tuesday',  'day_num'=>2,'time'=>'8:00 AM – 8:00 PM','open'=>true],
        ['day'=>'Wednesday','day_num'=>3,'time'=>'8:00 AM – 8:00 PM','open'=>true],
        ['day'=>'Thursday', 'day_num'=>4,'time'=>'8:00 AM – 8:00 PM','open'=>true],
        ['day'=>'Friday',   'day_num'=>5,'time'=>'8:00 AM – 8:00 PM','open'=>true],
        ['day'=>'Saturday', 'day_num'=>6,'time'=>'8:00 AM – 5:00 PM','open'=>true],
        ['day'=>'Sunday',   'day_num'=>0,'time'=>'Closed','open'=>false],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About – PUP Maragondon Digital Library</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ── About: Vision / Goals / Mission / Objectives ─────────────────── */
    .about-vgmo-section { background: var(--paper); padding: var(--band) 0; }
    .about-vgmo-intro   { max-width: 640px; margin-bottom: clamp(36px, 5vw, 52px); }

    .vgmo-cards-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1px;
      background: var(--rule);
      border: 1px solid var(--rule);
      border-radius: var(--radius-lg);
      overflow: hidden;
    }
    .vgmo-card-v2 {
      background: var(--paper);
      display: flex;
      flex-direction: column;
    }
    /* The coloured stripe on every card was pure decoration — removed. */
    .vgmo-card-stripe { display: none; }
    .vgmo-card-body   { padding: clamp(26px, 3.2vw, 36px); flex: 1; }
    .vgmo-card-head   { margin-bottom: 18px; }
    .vgmo-icon-circle { display: none; }
    .vgmo-card-label {
      display: block;
      font-size: 0.68rem; font-weight: 700;
      letter-spacing: 0.13em; text-transform: uppercase;
      color: var(--maroon); margin-bottom: 8px;
    }
    .vgmo-card-title {
      font-family: var(--font-display);
      font-size: 1.32rem; font-weight: 600;
      color: var(--ink); line-height: 1.2;
    }
    .vgmo-card-text {
      font-size: 0.9rem; color: var(--ink-mid); line-height: 1.8;
    }
    .vgmo-card-list { display: flex; flex-direction: column; }
    .vgmo-card-list li {
      display: flex; align-items: flex-start; gap: 12px;
      font-size: 0.88rem; color: var(--ink-mid); line-height: 1.7;
      padding: 11px 0;
      border-top: 1px solid var(--rule);
    }
    .vgmo-card-list li::before {
      content: ""; flex-shrink: 0;
      width: 7px; height: 1px; margin-top: 12px;
      background: var(--maroon);
    }

    /* ── About: operating hours ───────────────────────────────────────── */
    .about-hours-section {
      background: var(--paper-alt);
      border-block: 1px solid var(--rule);
      padding: var(--band) 0;
    }
    .hours-layout {
      display: grid; grid-template-columns: 1fr 1.25fr;
      gap: clamp(36px, 5vw, 64px); align-items: start;
    }
    .hours-live-status {
      display: inline-flex; align-items: center; gap: 9px;
      font-size: 0.8rem; font-weight: 600;
      letter-spacing: 0.04em; text-transform: uppercase;
      color: var(--ink-mid); margin-bottom: 20px;
    }
    .hours-live-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: #16a34a; flex-shrink: 0;
    }
    .hours-live-dot.closed-dot { background: #dc2626; }
    .hours-info-side h2 {
      font-size: clamp(1.7rem, 3.2vw, 2.4rem);
      line-height: 1.12; margin-bottom: 14px;
    }
    .hours-info-side p {
      font-size: 0.95rem; color: var(--ink-mid);
      line-height: 1.75; margin-bottom: 22px;
    }
    .hours-note {
      display: flex; align-items: flex-start; gap: 11px;
      border-left: 2px solid var(--gold);
      padding: 4px 0 4px 15px;
      font-size: 0.85rem; color: var(--ink-mid); line-height: 1.7;
    }
    .hours-note i { color: var(--gold-dark); font-size: 0.85rem; margin-top: 4px; flex-shrink: 0; }

    .hours-schedule-card {
      background: var(--paper);
      border: 1px solid var(--rule);
      border-radius: var(--radius-lg);
      padding: clamp(26px, 3.4vw, 36px);
    }
    .hours-card-title {
      display: flex; align-items: center; gap: 10px;
      font-family: var(--font-display);
      font-size: 1.08rem; font-weight: 600;
      color: var(--ink);
      padding-bottom: 16px; margin-bottom: 4px;
      border-bottom: 1px solid var(--rule);
    }
    .hours-card-title i { color: var(--maroon); font-size: 0.88rem; }
    .hours-rows { display: flex; flex-direction: column; }
    .hours-row-v2 {
      display: flex; align-items: center; justify-content: space-between;
      gap: 20px; padding: 13px 0;
      border-bottom: 1px solid var(--rule);
    }
    .hours-row-v2:last-child { border-bottom: none; }
    .hours-row-v2.is-today .hours-day-name { color: var(--maroon); font-weight: 700; }
    .hours-day-name {
      display: flex; align-items: center; gap: 10px;
      font-size: 0.89rem; font-weight: 500; color: var(--ink);
    }
    .today-badge {
      font-size: 0.58rem; font-weight: 700;
      letter-spacing: 0.1em; text-transform: uppercase;
      background: var(--gold); color: #2b1d00;
      padding: 2px 7px; border-radius: 2px;
    }
    .hours-time-badge {
      font-size: 0.87rem; font-weight: 500;
      font-variant-numeric: tabular-nums;
    }
    .hours-time-badge.open-time   { color: var(--ink-mid); }
    .hours-time-badge.closed-time { color: var(--ink-light); }

    /* ── About: history timeline — a single left rail ─────────────────── */
    .about-history-section {
      background: var(--paper);
      padding: var(--band) 0;
    }
    .timeline-wrapper {
      position: relative;
      margin-top: clamp(36px, 5vw, 52px);
      padding-left: 30px;
    }
    .timeline-line {
      position: absolute; left: 0; top: 6px; bottom: 6px;
      width: 1px; background: var(--rule);
    }
    /* The animated gradient fill is gone; the rail is simply a rule. */
    .timeline-line-fill { display: none; }

    .tl-event {
      display: block;
      position: relative;
      padding-bottom: clamp(30px, 4vw, 42px);
      cursor: pointer;
    }
    .tl-event:last-child { padding-bottom: 0; }
    .tl-left, .tl-right { display: block; padding: 0; }
    .tl-empty { display: none; }

    .tl-center {
      position: absolute;
      left: -30px; top: 6px;
      display: block;
    }
    .tl-dot {
      width: 9px; height: 9px; border-radius: 50%;
      background: var(--paper);
      border: 1px solid var(--maroon);
      transition: background-color 0.2s ease;
    }
    .tl-event:hover .tl-dot,
    .tl-event.active .tl-dot { background: var(--maroon); }
    .tl-year-pill { display: none; }

    .tl-card {
      background: transparent;
      border: none;
      padding: 0;
      max-width: 62ch;
    }
    .tl-year {
      display: block;
      font-family: var(--font-body);
      font-size: 0.74rem; font-weight: 700;
      letter-spacing: 0.13em; text-transform: uppercase;
      color: var(--maroon); margin-bottom: 8px;
    }
    .tl-title {
      display: block;
      font-family: var(--font-display);
      font-size: 1.25rem; font-weight: 600;
      color: var(--ink); line-height: 1.2; margin-bottom: 8px;
      transition: color 0.2s ease;
    }
    .tl-event:hover .tl-title { color: var(--maroon); }
    .tl-card p {
      font-size: 0.9rem; color: var(--ink-mid); line-height: 1.75; margin: 0;
    }
    .tl-detail {
      display: grid; grid-template-rows: 0fr;
      transition: grid-template-rows 0.3s ease;
    }
    .tl-event.active .tl-detail { grid-template-rows: 1fr; }
    .tl-detail > * { overflow: hidden; }
    .tl-detail-inner {
      margin-top: 14px; padding-top: 13px;
      border-top: 1px solid var(--rule);
      font-size: 0.86rem; color: var(--ink-mid); line-height: 1.75;
    }
    .tl-hint {
      margin-top: clamp(28px, 4vw, 38px);
      font-size: 0.8rem; color: var(--ink-light);
    }
    .tl-hint i { margin-right: 6px; color: var(--maroon); }

    @media (max-width: 960px) {
      .vgmo-cards-grid { grid-template-columns: 1fr; }
      .hours-layout    { grid-template-columns: 1fr; gap: 32px; }
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
      <span>About</span>
    </div>
    <h1>About the Library</h1>
    <p>Learn our story, vision, and the values that guide our service to the PUP community.</p>
  </div>
</section>

<!-- VGMO -->
<section class="about-vgmo-section">
  <div class="container">
    <div class="about-vgmo-intro">
      <span class="section-label">Who we are</span>
      <h2 class="section-title">Vision, goals, mission <span>&amp; objectives</span></h2>
      <div class="section-divider"></div>
      <?php if ($uni_lib): ?>
      <p class="section-sub">
        The Maragondon Campus Library operates within the
        <a href="<?= h($uni_lib) ?>" target="_blank" rel="noopener" style="color:var(--maroon);text-decoration:underline;text-underline-offset:2px;">PUP University Library</a>
        system, and shares its vision, goals, mission, and objectives.
      </p>
      <?php endif; ?>
    </div>
    <div class="vgmo-cards-grid">

      <div class="vgmo-card-v2">
        <div class="vgmo-card-body">
          <div class="vgmo-card-head">
            <div class="vgmo-icon-circle"><i class="fa-solid fa-bullseye"></i></div>
            <div>
              <span class="vgmo-card-label">Our Purpose</span>
              <div class="vgmo-card-title">Mission Statement</div>
            </div>
          </div>
          <p class="vgmo-card-text"><?= h($vgmo['mission'] ?? 'Guided by the PUP philosophy, the PUP University Library is committed to provide a user-centered approach in all aspects of its programs and services.') ?></p>
        </div>
      </div>

      <div class="vgmo-card-v2">
        <div class="vgmo-card-body">
          <div class="vgmo-card-head">
            <div class="vgmo-icon-circle"><i class="fa-regular fa-eye"></i></div>
            <div>
              <span class="vgmo-card-label">Looking Forward</span>
              <div class="vgmo-card-title">Our Vision</div>
            </div>
          </div>
          <p class="vgmo-card-text"><?= h($vgmo['vision'] ?? 'The PUP University Library envisions itself as an excellent library and information center.') ?></p>
        </div>
      </div>

      <div class="vgmo-card-v2">
        <div class="vgmo-card-body">
          <div class="vgmo-card-head">
            <div class="vgmo-icon-circle"><i class="fa-solid fa-flag"></i></div>
            <div>
              <span class="vgmo-card-label">Strategic Direction</span>
              <div class="vgmo-card-title">Our Goals</div>
            </div>
          </div>
          <?php if (!empty($vgmo['goals'])): ?>
          <ul class="vgmo-card-list">
            <?php foreach ($vgmo['goals'] as $g): ?><li><?= h($g) ?></li><?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="vgmo-card-text">No goals defined yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="vgmo-card-v2">
        <div class="vgmo-card-body">
          <div class="vgmo-card-head">
            <div class="vgmo-icon-circle"><i class="fa-solid fa-list-check"></i></div>
            <div>
              <span class="vgmo-card-label">Core Commitments</span>
              <div class="vgmo-card-title">Objectives</div>
            </div>
          </div>
          <?php if (!empty($vgmo['objectives'])): ?>
          <ul class="vgmo-card-list">
            <?php foreach ($vgmo['objectives'] as $o): ?><li><?= h($o) ?></li><?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="vgmo-card-text">No objectives defined yet.</p>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- LIBRARY HOURS -->
<section class="about-hours-section">
  <div class="container">
    <div class="hours-layout reveal">
      <div class="hours-info-side">
        <div class="hours-live-status">
          <span class="hours-live-dot" id="hours-dot"></span>
          <span id="hours-status-text">Checking status…</span>
        </div>
        <h2>Library<br><span style="color:var(--maroon);">Operating Hours</span></h2>
        <p>The PUP University Library is open to students, faculty, and staff.</p>
        <?php if ($note): ?>
        <div class="hours-note">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <span><?= h($note) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <div class="hours-schedule-card">
        <div class="hours-card-title">
          <i class="fa-regular fa-clock"></i>
          Weekly Schedule
        </div>
        <div class="hours-rows">
          <?php foreach ($sched as $row): ?>
          <div class="hours-row-v2" data-day="<?= (int)($row['day_num'] ?? 0) ?>">
            <span class="hours-day-name">
              <?= h($row['day']) ?>
              <span class="today-badge" style="display:none;">TODAY</span>
            </span>
            <span class="hours-time-badge <?= !empty($row['open']) ? 'open-time' : 'closed-time' ?>">
              <?= h($row['time']) ?>
            </span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HISTORY TIMELINE -->
<section class="about-history-section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Our Story</span>
      <h2 class="section-title">Library <span>History</span></h2>
      <div class="section-divider"></div>
      <p class="section-sub" style="margin:16px auto 0;">A legacy of academic service, the PUP Maragondon Library has continuously evolved to meet the changing needs of students, faculty, and researchers since its founding.</p>
    </div>

    <?php if (!empty($tline)): ?>
    <div class="timeline-wrapper">
      <div class="timeline-line"></div>
      <div class="timeline-line-fill" id="timeline-fill"></div>

      <?php foreach ($tline as $i => $ev): $isLeft = ($i % 2 === 0); ?>
      <div class="tl-event" id="tl-<?= $i ?>" onclick="toggleTimeline(<?= $i ?>)">
        <?php if ($isLeft): ?>
          <div class="tl-left">
            <div class="tl-card">
              <span class="tl-year"><?= h($ev['year'] ?? '') ?></span>
              <span class="tl-title"><?= h($ev['title'] ?? '') ?></span>
              <p><?= h($ev['description'] ?? '') ?></p>
              <?php if (!empty($ev['detail'])): ?>
              <div class="tl-detail"><div class="tl-detail-inner"><?= h($ev['detail']) ?></div></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="tl-center"><div class="tl-dot"></div><div class="tl-year-pill"><?= h($ev['year'] ?? '') ?></div></div>
          <div class="tl-empty"></div>
        <?php else: ?>
          <div class="tl-empty"></div>
          <div class="tl-center"><div class="tl-dot"></div><div class="tl-year-pill"><?= h($ev['year'] ?? '') ?></div></div>
          <div class="tl-right">
            <div class="tl-card">
              <span class="tl-year"><?= h($ev['year'] ?? '') ?></span>
              <span class="tl-title"><?= h($ev['title'] ?? '') ?></span>
              <p><?= h($ev['description'] ?? '') ?></p>
              <?php if (!empty($ev['detail'])): ?>
              <div class="tl-detail"><div class="tl-detail-inner"><?= h($ev['detail']) ?></div></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="tl-hint">
      <i class="fa-regular fa-hand-pointer"></i>
      Click any milestone card to expand its full story
    </p>
    <?php else: ?>
    <p style="text-align:center;color:var(--ink-light);margin-top:40px;">No timeline events yet.</p>
    <?php endif; ?>
  </div>
</section>


<script>
(function() {
  const schedule = <?= json_encode(array_map(fn($r) => ['day_num'=>(int)($r['day_num']??0),'open'=>!empty($r['open'])], $sched), JSON_UNESCAPED_UNICODE) ?>;
  const dot  = document.getElementById('hours-dot');
  const text = document.getElementById('hours-status-text');
  if (!dot || !text) return;
  const now  = new Date();
  const day  = now.getDay();
  const hour = now.getHours();
  const todayRow = schedule.find(r => r.day_num === day);
  const isOpen = todayRow ? todayRow.open : false;
  text.textContent = isOpen ? 'Library is Currently Open' : 'Library is Currently Closed';
  if (!isOpen) dot.classList.add('closed-dot');

  document.querySelectorAll('.hours-row-v2').forEach(row => {
    if (parseInt(row.dataset.day) === day) {
      row.classList.add('is-today');
      const badge = row.querySelector('.today-badge');
      if (badge) badge.style.display = '';
    }
  });
})();

function toggleTimeline(idx) {
  const el = document.getElementById('tl-' + idx);
  const wasActive = el.classList.contains('active');
  document.querySelectorAll('.tl-event').forEach(e => e.classList.remove('active'));
  if (!wasActive) el.classList.add('active');
}

</script>

<?php include 'includes/footer.php'; ?>
