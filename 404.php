<?php
$activePage = '';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page not found – PUP Maragondon University Library</title>
  <meta name="robots" content="noindex">
  <link rel="icon" type="image/png" href="/library-website1/assets/images/logo.png">
  <script>
    // Set the theme before first paint so dark-mode users never see a light flash,
    // and mark the document as scripted so CSS can gate entrance animations.
    (function () {
      var d = document.documentElement;
      d.classList.add("js");
      var t = null;
      try { t = localStorage.getItem("pup-theme"); } catch (e) {}
      if (!t) {
        t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
      }
      d.setAttribute("data-theme", t);
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&amp;family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/library-website1/style.css">
  <style>
    .nf-wrap {
      min-height: 68vh;
      display: flex;
      align-items: center;
      padding: var(--band) 0;
      background: var(--paper);
    }
    .nf-code {
      font-family: var(--font-body);
      font-size: 0.74rem; font-weight: 700;
      letter-spacing: 0.16em; text-transform: uppercase;
      color: var(--maroon); margin-bottom: 18px;
    }
    .nf-wrap h1 {
      font-size: clamp(2rem, 4.6vw, 3.2rem);
      margin-bottom: 18px;
    }
    .nf-wrap > .container > div > p {
      font-size: 1rem; color: var(--ink-mid);
      line-height: 1.75; max-width: 52ch; margin-bottom: 32px;
    }
    .nf-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 48px; }
    .nf-links {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 1px;
      background: var(--rule);
      border: 1px solid var(--rule);
      border-radius: var(--radius);
      overflow: hidden;
      max-width: 760px;
    }
    .nf-links a {
      background: var(--paper);
      padding: 20px 22px;
      transition: var(--transition);
    }
    .nf-links a:hover { background: var(--paper-alt); }
    .nf-links strong {
      display: block;
      font-family: var(--font-display);
      font-size: 1.02rem; font-weight: 600;
      margin-bottom: 5px;
    }
    .nf-links span { font-size: 0.84rem; color: var(--ink-mid); }
  </style>
</head>
<body>

<header id="main-header">
  <div class="header-container">
    <a href="/library-website1/index.php" class="logo">
      <img src="/library-website1/assets/images/logo.png" alt="" class="logo-img">
      <span class="logo-text-wrap">
        <span>PUP Maragondon Library</span>
        <span class="logo-sub">Polytechnic University of the Philippines</span>
      </span>
    </a>
  </div>
</header>

<main class="nf-wrap">
  <div class="container">
    <div>
      <p class="nf-code">Error 404</p>
      <h1>We couldn&rsquo;t find that page.</h1>
      <p>The page may have been moved or renamed, or the address may have been typed incorrectly. The links below cover most of what the library site holds.</p>

      <div class="nf-actions">
        <a href="/library-website1/index.php" class="btn-primary">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to home
        </a>
        <a href="/library-website1/contact.php" class="btn-outline">Contact the library</a>
      </div>

      <nav class="nf-links" aria-label="Popular pages">
        <a href="/library-website1/holdings.php">
          <strong>Library Holdings</strong>
          <span>Collections and special materials</span>
        </a>
        <a href="/library-website1/resources.php">
          <strong>Online Resources</strong>
          <span>Databases and e-resources</span>
        </a>
        <a href="/library-website1/services.php">
          <strong>Library Services</strong>
          <span>Circulation, reference, and more</span>
        </a>
        <a href="/library-website1/guidelines.php">
          <strong>Guidelines</strong>
          <span>Rules, borrowing, and privileges</span>
        </a>
      </nav>
    </div>
  </div>
</main>

<footer>
  <div class="footer-bottom" style="border-top:none;">
    <div class="container">
      <p>&copy; <?= date('Y') ?> Polytechnic University of the Philippines – Maragondon Campus</p>
    </div>
  </div>
</footer>

</body>
</html>
