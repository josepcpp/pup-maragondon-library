<?php
// includes/nav.php
if (!isset($activePage)) $activePage = '';
?>
<a href="#main-content" class="skip-link">Skip to content</a>

<!-- MAIN HEADER -->
<header id="main-header">
  <div class="header-container">

    <!-- LOGO -->
    <a href="index.php" class="logo">
      <img src="assets/images/logo.png" alt="" class="logo-img">
      <span class="logo-text-wrap">
        <span>PUP Maragondon Library</span>
        <span class="logo-sub">Polytechnic University of the Philippines</span>
      </span>
    </a>

    <!-- DESKTOP NAV -->
    <nav aria-label="Main">
      <ul class="nav-links">
        <li>
          <a href="index.php" class="<?= $activePage==='home' ? 'active' : '' ?>">Home</a>
        </li>
        <li>
          <a href="about.php" class="<?= $activePage==='about' ? 'active' : '' ?>">About</a>
        </li>
        <li class="has-dropdown">
          <a href="services.php" class="<?= $activePage==='services' ? 'active' : '' ?>">Services</a>
          <ul class="dropdown-menu">
            <li><a href="services.php#circulation"><i class="fa-solid fa-rotate"></i> Circulation</a></li>
            <li><a href="services.php#reserve"><i class="fa-solid fa-bookmark"></i> Reserve</a></li>
            <li><a href="services.php#reference"><i class="fa-solid fa-magnifying-glass"></i> Reference</a></li>
            <li><a href="services.php#filipiniana"><i class="fa-solid fa-flag"></i> Filipiniana</a></li>
            <li><a href="services.php#periodical"><i class="fa-solid fa-newspaper"></i> Periodical</a></li>
          </ul>
        </li>
        <li class="has-dropdown">
          <a href="resources.php" class="<?= $activePage==='resources' ? 'active' : '' ?>">Resources</a>
          <ul class="dropdown-menu">
            <li><a href="resources.php"><i class="fa-solid fa-database"></i> Online Resources</a></li>
            <li><a href="holdings.php"><i class="fa-solid fa-book"></i> Library Holdings</a></li>
          </ul>
        </li>
        <li>
          <a href="guidelines.php" class="<?= $activePage==='guidelines' ? 'active' : '' ?>">Guidelines</a>
        </li>
        <li>
          <a href="programs.php" class="<?= $activePage==='programs' ? 'active' : '' ?>">Programs</a>
        </li>
        <li class="has-dropdown">
          <a href="#" class="<?= in_array($activePage, ['linkages','survey','administration','contact']) ? 'active' : '' ?>">More</a>
          <ul class="dropdown-menu">
            <li><a href="linkages.php"><i class="fa-solid fa-link"></i> Linkages</a></li>
            <li><a href="survey.php"><i class="fa-solid fa-clipboard-list"></i> Library Survey</a></li>
            <li><a href="administration.php"><i class="fa-solid fa-users"></i> Administration</a></li>
            <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> Contact</a></li>
          </ul>
        </li>
      </ul>

      <button type="button" id="theme-toggle" class="theme-toggle"
              title="Toggle dark mode" aria-label="Toggle dark mode" aria-pressed="false">
        <i class="fa-solid fa-moon" id="theme-icon" aria-hidden="true"></i>
      </button>

      <button type="button" class="hamburger" aria-label="Open menu" aria-controls="mobile-drawer">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
      </button>
    </nav>

  </div>
</header>

<!-- MOBILE DRAWER -->
<div class="mobile-drawer" id="mobile-drawer">
  <button type="button" class="drawer-close" aria-label="Close menu">
    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
  </button>

  <div class="drawer-brand">
    <img src="assets/images/logo.png" alt="">
    <div>
      <span class="drawer-brand-name">PUP Maragondon</span>
      <span class="drawer-brand-sub">University Library</span>
    </div>
  </div>

  <nav class="drawer-nav" aria-label="Mobile">
    <a href="index.php"          class="<?= $activePage==='home'           ? 'active' : '' ?>">Home</a>
    <a href="about.php"          class="<?= $activePage==='about'          ? 'active' : '' ?>">About</a>
    <a href="services.php"       class="<?= $activePage==='services'       ? 'active' : '' ?>">Services</a>
    <a href="resources.php"      class="<?= $activePage==='resources'      ? 'active' : '' ?>">Online Resources</a>
    <a href="holdings.php"       class="<?= $activePage==='holdings'       ? 'active' : '' ?>">Library Holdings</a>
    <a href="guidelines.php"     class="<?= $activePage==='guidelines'     ? 'active' : '' ?>">Guidelines</a>
    <a href="programs.php"       class="<?= $activePage==='programs'       ? 'active' : '' ?>">Programs &amp; Events</a>
    <a href="linkages.php"       class="<?= $activePage==='linkages'       ? 'active' : '' ?>">Linkages</a>
    <a href="survey.php"         class="<?= $activePage==='survey'         ? 'active' : '' ?>">Library Survey</a>
    <a href="administration.php" class="<?= $activePage==='administration' ? 'active' : '' ?>">Administration</a>
    <a href="contact.php"        class="<?= $activePage==='contact'        ? 'active' : '' ?>">Contact</a>
  </nav>
</div>
<div class="drawer-overlay" id="drawer-overlay"></div>

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-bottom-nav" aria-label="Quick navigation">
  <a href="index.php"      class="mbn-item <?= $activePage==='home'       ? 'active' : '' ?>">
    <i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span>
  </a>
  <a href="about.php"      class="mbn-item <?= $activePage==='about'      ? 'active' : '' ?>">
    <i class="fa-solid fa-circle-info" aria-hidden="true"></i><span>About</span>
  </a>
  <a href="services.php"   class="mbn-item <?= $activePage==='services'   ? 'active' : '' ?>">
    <i class="fa-solid fa-bell-concierge" aria-hidden="true"></i><span>Services</span>
  </a>
  <a href="resources.php"  class="mbn-item <?= $activePage==='resources'  ? 'active' : '' ?>">
    <i class="fa-solid fa-database" aria-hidden="true"></i><span>Resources</span>
  </a>
  <a href="guidelines.php" class="mbn-item <?= $activePage==='guidelines' ? 'active' : '' ?>">
    <i class="fa-solid fa-book-open" aria-hidden="true"></i><span>Rules</span>
  </a>
</nav>

<main id="main-content">
