<?php
require_once dirname(__DIR__) . '/auth.php';
require_auth();
if (!isset($page_title)) $page_title = 'Admin';
if (!isset($page_icon))  $page_icon  = 'fa-solid fa-gear';
if (!isset($active_nav)) $active_nav = '';

$flash_success = flash_get('success');
$flash_error   = flash_get('error');
$flash_warning = flash_get('warning');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($page_title) ?> — PUP Library CMS</title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <link rel="stylesheet" href="../admin/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">

<!-- ── SIDEBAR ── -->
<aside class="admin-sidebar" id="admin-sidebar">
  <a href="index.php" class="sidebar-brand">
    <div class="sidebar-brand-icon"><i class="fa-solid fa-book-open"></i></div>
    <div class="sidebar-brand-text">
      <span class="sidebar-brand-title">PUP Maragondon</span>
      <span class="sidebar-brand-sub">Library CMS</span>
    </div>
  </a>

  <div class="sidebar-section">
    <span class="sidebar-section-label">Dashboard</span>
    <ul class="sidebar-nav">
      <li>
        <a href="index.php" class="<?= $active_nav === 'dashboard' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-gauge"></i> Overview
        </a>
      </li>
      <li>
        <a href="media.php" class="<?= $active_nav === 'media' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-photo-film"></i> Media Manager
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-section">
    <span class="sidebar-section-label">Homepage</span>
    <ul class="sidebar-nav">
      <li>
        <a href="arrivals.php" class="<?= $active_nav === 'arrivals' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-star"></i> New Arrivals
        </a>
      </li>
      <li>
        <a href="featured.php" class="<?= $active_nav === 'featured' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-bookmark"></i> Featured Resource
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-section">
    <span class="sidebar-section-label">Pages</span>
    <ul class="sidebar-nav">
      <li>
        <a href="about.php" class="<?= $active_nav === 'about' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-circle-info"></i> About
        </a>
      </li>
      <li>
        <a href="services.php" class="<?= $active_nav === 'svc' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-hands-helping"></i> Services
        </a>
      </li>
      <li>
        <a href="guidelines.php" class="<?= $active_nav === 'guidelines' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-gavel"></i> Guidelines
        </a>
      </li>
      <li>
        <a href="programs.php" class="<?= $active_nav === 'programs' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-calendar-days"></i> Programs &amp; Events
        </a>
      </li>
      <li>
        <a href="linkages.php" class="<?= $active_nav === 'linkages' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-link"></i> Linkages
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-section">
    <span class="sidebar-section-label">Library</span>
    <ul class="sidebar-nav">
      <li>
        <a href="holdings.php" class="<?= $active_nav === 'holdings' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-book"></i> Holdings
        </a>
      </li>
      <li>
        <a href="resources.php" class="<?= $active_nav === 'resources' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-database"></i> Online Resources
        </a>
      </li>
      <li>
        <a href="personnel.php" class="<?= $active_nav === 'personnel' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-users"></i> Personnel
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-section">
    <span class="sidebar-section-label">System</span>
    <ul class="sidebar-nav">
      <li>
        <a href="settings.php" class="<?= $active_nav === 'settings' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-sliders"></i> Site Settings
        </a>
      </li>
      <li>
        <a href="manual.php" class="<?= $active_nav === 'manual' ? 'active' : '' ?>">
          <i class="nav-icon fa-solid fa-book-open"></i> Admin Manual
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-footer">
    <a href="../index.php" target="_blank">
      <i class="fa-solid fa-arrow-up-right-from-square" style="color:var(--gold);width:18px;text-align:center;"></i>
      View Public Site
    </a>
    <a href="logout.php" style="color:rgba(255,100,100,0.6);" onclick="return confirm('Log out?')">
      <i class="fa-solid fa-right-from-bracket" style="width:18px;text-align:center;"></i>
      Log Out
    </a>
  </div>
</aside>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- ── MAIN ── -->
<main class="admin-main">
  <div class="admin-topbar">
    <div class="topbar-title">
      <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="page-icon"><i class="<?= h($page_icon) ?>"></i></div>
      <?= h($page_title) ?>
    </div>
    <div class="topbar-actions">
      <a href="../index.php" target="_blank" class="topbar-link">
        <i class="fa-solid fa-eye"></i> Public Site
      </a>
      <div class="topbar-user">
        <div class="user-avatar">A</div>
        <?= h($_SESSION['admin_user'] ?? 'Admin') ?>
      </div>
    </div>
  </div>

  <div class="admin-content">
    <?php if ($flash_success): ?>
      <div class="flash flash-success"><i class="fa-solid fa-circle-check"></i><?= h($flash_success) ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
      <div class="flash flash-error"><i class="fa-solid fa-circle-xmark"></i><?= h($flash_error) ?></div>
    <?php endif; ?>
    <?php if ($flash_warning): ?>
      <div class="flash flash-warning"><i class="fa-solid fa-triangle-exclamation"></i><?= h($flash_warning) ?></div>
    <?php endif; ?>
