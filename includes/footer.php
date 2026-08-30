<?php
// Shared public footer — loads contact/social from settings.json
$_s  = @json_decode(@file_get_contents(__DIR__ . '/../data/settings.json'), true) ?: [];
$_c  = $_s['contact'] ?? [];
$_sc = $_s['social']  ?? [];

// Only render a social icon when a real URL is configured.
$_socials = array_filter([
    'facebook' => ['url' => $_sc['facebook'] ?? '', 'icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
    'youtube'  => ['url' => $_sc['youtube']  ?? '', 'icon' => 'fa-brands fa-youtube',    'label' => 'YouTube'],
    'twitter'  => ['url' => $_sc['twitter']  ?? '', 'icon' => 'fa-brands fa-x-twitter',  'label' => 'X'],
], fn($s) => !empty($s['url']) && $s['url'] !== '#');
?>
</main>

<footer>
  <div class="container">
    <div class="footer-grid">

      <div class="footer-col brand-col">
        <div class="footer-logo">
          <i class="fa-solid fa-book-open" aria-hidden="true"></i>
          <h3>PUP Maragondon Library</h3>
        </div>
        <p>Serving the Iskolar ng Bayan since 1987 with academic collections, research archives, and digital resources.</p>
        <?php if ($_socials): ?>
        <div class="social-icons">
          <?php foreach ($_socials as $s): ?>
          <a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener"
             aria-label="<?= htmlspecialchars($s['label']) ?>">
            <i class="<?= $s['icon'] ?>" aria-hidden="true"></i>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="index.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Home</a></li>
          <li><a href="about.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> About the Library</a></li>
          <li><a href="services.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Library Services</a></li>
          <li><a href="resources.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Online Resources</a></li>
          <li><a href="holdings.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Library Holdings</a></li>
          <li><a href="guidelines.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Guidelines</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Engage</h4>
        <ul>
          <li><a href="programs.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Programs &amp; Events</a></li>
          <li><a href="linkages.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Linkages</a></li>
          <li><a href="administration.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Administration</a></li>
          <li><a href="contact.php"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Contact Us</a></li>
          <?php if (!empty($_s['survey_url'])): ?>
          <li><a href="<?= htmlspecialchars($_s['survey_url']) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> Library Survey</a></li>
          <?php endif; ?>
          <?php if (!empty($_s['university_library_url'])): ?>
          <li><a href="<?= htmlspecialchars($_s['university_library_url']) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-angle-right" aria-hidden="true"></i> PUP University Library</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Visit</h4>
        <ul class="contact-list">
          <?php if (!empty($_c['address'])): ?>
          <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <span><?= htmlspecialchars($_c['address']) ?></span></li>
          <?php endif; ?>
          <?php if (!empty($_c['hours'])): ?>
          <li><i class="fa-solid fa-clock" aria-hidden="true"></i> <span><?= htmlspecialchars($_c['hours']) ?></span></li>
          <?php endif; ?>
          <?php if (!empty($_c['email'])): ?>
          <li><i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <span><a href="mailto:<?= htmlspecialchars($_c['email']) ?>"><?= htmlspecialchars($_c['email']) ?></a></span>
          </li>
          <?php endif; ?>
          <?php if (!empty($_c['phone'])): ?>
          <li><i class="fa-solid fa-phone" aria-hidden="true"></i> <span><?= htmlspecialchars($_c['phone']) ?></span></li>
          <?php endif; ?>
        </ul>
      </div>

    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <p>&copy; <?= date('Y') ?> Polytechnic University of the Philippines – Maragondon Campus</p>
    </div>
  </div>
</footer>

<button type="button" id="back-to-top" title="Back to top" aria-label="Back to top">
  <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</button>

<script src="script.js"></script>
</body>
</html>
