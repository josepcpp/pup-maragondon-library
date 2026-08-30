<?php
$page_title = 'Dashboard';
$page_icon  = 'fa-solid fa-gauge';
$active_nav = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$arrivals  = cms_load('arrivals.json');
$personnel = cms_load('personnel.json');
$programs  = cms_load('programs.json');
$resources = cms_load('resources.json');
$holdings  = cms_load('holdings.json');
$settings  = cms_load('settings.json');
$featured  = cms_load('featured.json');

$json_files = [
  'arrivals.json','holdings.json','programs.json','personnel.json',
  'resources.json','featured.json','settings.json','admin_credentials.json'
];
$recent = [];
foreach ($json_files as $f) {
  $path = DATA_PATH . $f;
  if (file_exists($path)) {
    $recent[] = ['file' => $f, 'modified' => filemtime($path)];
  }
}
usort($recent, fn($a,$b) => $b['modified'] <=> $a['modified']);
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Welcome back, <?= h($_SESSION['admin_user'] ?? 'Admin') ?></h1>
    <p>Here's an overview of your library content.</p>
  </div>
  <a href="../index.php" target="_blank" class="btn btn-outline btn-sm">
    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Public Site
  </a>
</div>

<!-- Stat Cards -->
<div class="stat-cards">
  <a class="stat-card" href="arrivals.php">
    <div class="stat-card-icon"><i class="fa-solid fa-star"></i></div>
    <div>
      <span class="stat-card-num"><?= count($arrivals) ?></span>
      <span class="stat-card-label">New Arrivals</span>
    </div>
  </a>
  <a class="stat-card" href="programs.php">
    <div class="stat-card-icon"><i class="fa-solid fa-calendar-days"></i></div>
    <div>
      <span class="stat-card-num"><?= count($programs) ?></span>
      <span class="stat-card-label">Programs</span>
    </div>
  </a>
  <a class="stat-card" href="resources.php">
    <div class="stat-card-icon"><i class="fa-solid fa-database"></i></div>
    <div>
      <span class="stat-card-num"><?= count($resources) ?></span>
      <span class="stat-card-label">Online Resources</span>
    </div>
  </a>
  <a class="stat-card" href="personnel.php">
    <div class="stat-card-icon"><i class="fa-solid fa-users"></i></div>
    <div>
      <span class="stat-card-num"><?= count($personnel) ?></span>
      <span class="stat-card-label">Personnel</span>
    </div>
  </a>
  <a class="stat-card" href="holdings.php">
    <div class="stat-card-icon"><i class="fa-solid fa-book"></i></div>
    <div>
      <span class="stat-card-num"><?= count($holdings['categories'] ?? []) ?></span>
      <span class="stat-card-label">Holdings Categories</span>
    </div>
  </a>
  <a class="stat-card" href="featured.php">
    <div class="stat-card-icon"><i class="fa-solid fa-bookmark"></i></div>
    <div>
      <span class="stat-card-num"><?= !empty($featured['title']) && $featured['title'] !== 'Featured Resource Title' ? '✓' : '–' ?></span>
      <span class="stat-card-label">Featured Set</span>
    </div>
  </a>
</div>

<!-- Two-column: Quick Actions + Recent Changes -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="dashboard-grid">

  <!-- Quick Actions -->
  <div class="card">
    <div class="card-header">
      <h2><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
      <a href="arrivals.php" class="btn btn-outline" style="justify-content:flex-start;">
        <i class="fa-solid fa-plus" style="color:var(--maroon);"></i> Add New Arrival
      </a>
      <a href="featured.php" class="btn btn-outline" style="justify-content:flex-start;">
        <i class="fa-solid fa-bookmark" style="color:var(--maroon);"></i> Update Featured Resource
      </a>
      <a href="programs.php" class="btn btn-outline" style="justify-content:flex-start;">
        <i class="fa-solid fa-calendar-plus" style="color:var(--maroon);"></i> Add Program / Event
      </a>
      <a href="personnel.php" class="btn btn-outline" style="justify-content:flex-start;">
        <i class="fa-solid fa-user-plus" style="color:var(--maroon);"></i> Add Personnel
      </a>
      <a href="settings.php" class="btn btn-outline" style="justify-content:flex-start;">
        <i class="fa-solid fa-sliders" style="color:var(--maroon);"></i> Edit Site Settings
      </a>
    </div>
  </div>

  <!-- Recent Changes -->
  <div class="card">
    <div class="card-header">
      <h2><i class="fa-solid fa-clock-rotate-left"></i> Recent File Changes</h2>
    </div>
    <div class="card-body" style="padding:0;">
      <table class="cms-table">
        <thead><tr><th>File</th><th>Last Modified</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($recent, 0, 6) as $r): ?>
          <tr>
            <td>
              <span class="badge badge-maroon" style="font-size:0.68rem;"><?= h($r['file']) ?></span>
            </td>
            <td style="color:var(--ink-light);font-size:0.78rem;">
              <?= date('M j, Y g:i A', $r['modified']) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<style>@media(max-width:700px){.dashboard-grid{grid-template-columns:1fr!important;}}</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
