<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$settings = cms_load('settings.json');

// ── Handle POST before any HTML output so header() redirects work ─
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: settings.php'); exit; }
    $action = $_POST['action'] ?? '';

    // ── General / Contact ──
    if ($action === 'save_general') {
        // Accept empty or valid http(s) URL only — blocks javascript: and data: URIs
        $safe_url = function(string $v): string {
            $v = trim($v);
            if ($v === '') return '';
            if (!preg_match('/^https?:\/\//i', $v)) return '';
            return filter_var($v, FILTER_VALIDATE_URL) ? $v : '';
        };

        $settings['site_title']     = trim($_POST['site_title']   ?? '');
        $settings['hero_tagline']   = trim($_POST['hero_tagline'] ?? '');
        $settings['avp_youtube_id'] = preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($_POST['avp_youtube_id'] ?? ''));
        $settings['survey_url']     = $safe_url($_POST['survey_url'] ?? '');
        $settings['opac_url']       = $safe_url($_POST['opac_url']   ?? '');

        $contact_email = trim($_POST['contact_email'] ?? '');
        if ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Invalid contact email address.');
            header('Location: settings.php'); exit;
        }
        $settings['contact'] = [
            'address' => trim($_POST['contact_address'] ?? ''),
            'hours'   => trim($_POST['contact_hours']   ?? ''),
            'email'   => $contact_email,
            'phone'   => trim($_POST['contact_phone']   ?? ''),
        ];
        $settings['social'] = [
            'facebook' => $safe_url($_POST['social_facebook'] ?? ''),
            'youtube'  => $safe_url($_POST['social_youtube']  ?? ''),
            'twitter'  => $safe_url($_POST['social_twitter']  ?? ''),
        ];
        cms_save('settings.json', $settings);
        flash_set('success','Settings saved.');
        header('Location: settings.php'); exit;
    }

    // ── Hero Stats ──
    if ($action === 'save_hero_stats') {
        $hero   = [];
        $nums   = array_values($_POST['hero_num']   ?? []);
        $labels = array_values($_POST['hero_label'] ?? []);
        $count  = min(count($nums), count($labels));
        for ($i = 0; $i < $count; $i++) {
            $hero[] = ['num' => trim($nums[$i]), 'label' => trim($labels[$i])];
        }
        $settings['hero_stats'] = $hero;
        cms_save('settings.json', $settings);
        flash_set('success','Hero stats saved.');
        header('Location: settings.php#hero-stats'); exit;
    }

    // ── Counter Stats ──
    if ($action === 'save_counter_stats') {
        $counter  = [];
        $targets  = array_values($_POST['counter_target'] ?? []);
        $suffixes = array_values($_POST['counter_suffix'] ?? []);
        $clabels  = array_values($_POST['counter_label']  ?? []);
        $count    = min(count($targets), count($suffixes), count($clabels));
        for ($i = 0; $i < $count; $i++) {
            $counter[] = [
                'target' => max(0, (int)$targets[$i]),
                'suffix' => trim($suffixes[$i]),
                'label'  => trim($clabels[$i]),
            ];
        }
        $settings['counter_stats'] = $counter;
        cms_save('settings.json', $settings);
        flash_set('success','Counter stats saved.');
        header('Location: settings.php#counter-stats'); exit;
    }

    // ── Change Password ──
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new1     = $_POST['new_password']      ?? '';
        $new2     = $_POST['confirm_password']   ?? '';
        $cred     = cms_get_credentials();

        if (!password_verify($current, $cred['password_hash'])) {
            flash_set('error','Current password is incorrect.'); header('Location: settings.php#security'); exit;
        }
        if (strlen($new1) < 8) {
            flash_set('error','New password must be at least 8 characters.'); header('Location: settings.php#security'); exit;
        }
        if ($new1 !== $new2) {
            flash_set('error','Passwords do not match.'); header('Location: settings.php#security'); exit;
        }
        $cred['password_hash'] = password_hash($new1, PASSWORD_BCRYPT, ['cost'=>12]);
        file_put_contents(CRED_FILE, json_encode($cred, JSON_PRETTY_PRINT));
        flash_set('success','Password changed successfully.');
        header('Location: settings.php#security'); exit;
    }
}

// ── Render page ───────────────────────────────────────────────────
$c  = $settings['contact'] ?? [];
$sc = $settings['social']  ?? [];
$hs = $settings['hero_stats']    ?? [];
$cs = $settings['counter_stats'] ?? [];

$page_title = 'Site Settings';
$page_icon  = 'fa-solid fa-sliders';
$active_nav = 'settings';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Site Settings</h1>
    <p>Configure site-wide content, contact info, and security.</p>
  </div>
</div>

<!-- General Settings -->
<div class="card" style="margin-bottom:24px;" id="general">
  <div class="card-header"><h2><i class="fa-solid fa-globe"></i> General &amp; Contact</h2></div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="save_general">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Site Title</label>
          <input type="text" name="site_title" class="form-control" value="<?= h($settings['site_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">AVP YouTube Video ID</label>
          <input type="text" name="avp_youtube_id" class="form-control"
                 value="<?= h($settings['avp_youtube_id'] ?? '') ?>" placeholder="e.g. ScMzIvxBSi4">
          <p class="form-hint">The part after watch?v= in the YouTube URL</p>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Hero Tagline</label>
        <textarea name="hero_tagline" class="form-control" rows="2"><?= h($settings['hero_tagline'] ?? '') ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Survey URL</label>
          <input type="text" name="survey_url" class="form-control" value="<?= h($settings['survey_url'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">OPAC URL</label>
          <input type="text" name="opac_url" class="form-control" value="<?= h($settings['opac_url'] ?? '') ?>">
        </div>
      </div>
      <hr style="border:none;border-top:1px solid var(--border);margin:16px 0;">
      <p style="font-size:0.8rem;font-weight:700;color:var(--ink);margin-bottom:12px;">Contact Information</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="contact_address" class="form-control" value="<?= h($c['address'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Office Hours</label>
          <input type="text" name="contact_hours" class="form-control" value="<?= h($c['hours'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="contact_email" class="form-control" value="<?= h($c['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="contact_phone" class="form-control" value="<?= h($c['phone'] ?? '') ?>">
        </div>
      </div>
      <hr style="border:none;border-top:1px solid var(--border);margin:16px 0;">
      <p style="font-size:0.8rem;font-weight:700;color:var(--ink);margin-bottom:12px;">Social Links</p>
      <div class="form-row-3">
        <div class="form-group">
          <label class="form-label"><i class="fa-brands fa-facebook-f" style="color:#1877f2;"></i> Facebook URL</label>
          <input type="text" name="social_facebook" class="form-control" value="<?= h($sc['facebook'] ?? '') ?>" placeholder="#">
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fa-brands fa-youtube" style="color:#ff0000;"></i> YouTube URL</label>
          <input type="text" name="social_youtube" class="form-control" value="<?= h($sc['youtube'] ?? '') ?>" placeholder="#">
        </div>
        <div class="form-group">
          <label class="form-label"><i class="fa-brands fa-twitter" style="color:#1da1f2;"></i> Twitter URL</label>
          <input type="text" name="social_twitter" class="form-control" value="<?= h($sc['twitter'] ?? '') ?>" placeholder="#">
        </div>
      </div>
      <div style="text-align:right;">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save General Settings</button>
      </div>
    </form>
  </div>
</div>

<!-- Hero Stats -->
<div class="card" style="margin-bottom:24px;" id="hero-stats">
  <div class="card-header">
    <h2><i class="fa-solid fa-chart-line"></i> Hero Stats <span style="font-weight:400;font-size:0.78rem;color:var(--ink-light);margin-left:8px;">— shown in hero section</span></h2>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="save_hero_stats">
      <div id="hero-stats-list">
        <?php foreach ($hs as $i => $s): ?>
        <div class="form-row hero-stat-row" style="align-items:flex-end;margin-bottom:10px;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Number / Value</label>
            <input type="text" name="hero_num[]" class="form-control" value="<?= h($s['num']) ?>" placeholder="8,500+">
          </div>
          <div class="form-group" style="margin-bottom:0;flex:2;">
            <label class="form-label">Label</label>
            <input type="text" name="hero_label[]" class="form-control" value="<?= h($s['label']) ?>" placeholder="Books">
          </div>
          <div><button type="button" class="btn btn-danger btn-sm btn-icon-only remove-hero-stat"><i class="fa-solid fa-trash"></i></button></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button type="button" id="add-hero-stat" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Add Stat</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Counter Stats -->
<div class="card" style="margin-bottom:24px;" id="counter-stats">
  <div class="card-header">
    <h2><i class="fa-solid fa-hashtag"></i> Animated Counters <span style="font-weight:400;font-size:0.78rem;color:var(--ink-light);margin-left:8px;">— scrolling number section</span></h2>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="save_counter_stats">
      <div id="counter-list">
        <?php foreach ($cs as $i => $s): ?>
        <div class="form-row counter-row" style="align-items:flex-end;margin-bottom:10px;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Target Number</label>
            <input type="number" name="counter_target[]" class="form-control" value="<?= (int)$s['target'] ?>">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Suffix</label>
            <input type="text" name="counter_suffix[]" class="form-control" value="<?= h($s['suffix']) ?>" placeholder="+">
          </div>
          <div class="form-group" style="margin-bottom:0;flex:2;">
            <label class="form-label">Label</label>
            <input type="text" name="counter_label[]" class="form-control" value="<?= h($s['label']) ?>">
          </div>
          <div><button type="button" class="btn btn-danger btn-sm btn-icon-only remove-counter"><i class="fa-solid fa-trash"></i></button></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button type="button" id="add-counter" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Add Counter</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Security -->
<div class="card" id="security">
  <div class="card-header"><h2><i class="fa-solid fa-shield-halved"></i> Security — Change Password</h2></div>
  <div class="card-body" style="max-width:420px;">
    <form method="POST" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="change_password">
      <div class="form-group">
        <label class="form-label">Current Password <span class="req">*</span></label>
        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label class="form-label">New Password <span class="req">*</span></label>
        <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
        <p class="form-hint">Minimum 8 characters.</p>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm New Password <span class="req">*</span></label>
        <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Change Password</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('click', e => {
  if (e.target.closest('.remove-hero-stat')) e.target.closest('.hero-stat-row').remove();
  if (e.target.closest('.remove-counter'))   e.target.closest('.counter-row').remove();
});
document.getElementById('add-hero-stat').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'form-row hero-stat-row'; row.style = 'align-items:flex-end;margin-bottom:10px;';
  row.innerHTML = `<div class="form-group" style="margin-bottom:0;"><label class="form-label">Number / Value</label><input type="text" name="hero_num[]" class="form-control" placeholder="8,500+"></div>
    <div class="form-group" style="margin-bottom:0;flex:2;"><label class="form-label">Label</label><input type="text" name="hero_label[]" class="form-control" placeholder="Books"></div>
    <div><button type="button" class="btn btn-danger btn-sm btn-icon-only remove-hero-stat"><i class="fa-solid fa-trash"></i></button></div>`;
  document.getElementById('hero-stats-list').appendChild(row);
});
document.getElementById('add-counter').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'form-row counter-row'; row.style = 'align-items:flex-end;margin-bottom:10px;';
  row.innerHTML = `<div class="form-group" style="margin-bottom:0;"><label class="form-label">Target Number</label><input type="number" name="counter_target[]" class="form-control"></div>
    <div class="form-group" style="margin-bottom:0;"><label class="form-label">Suffix</label><input type="text" name="counter_suffix[]" class="form-control" placeholder="+"></div>
    <div class="form-group" style="margin-bottom:0;flex:2;"><label class="form-label">Label</label><input type="text" name="counter_label[]" class="form-control"></div>
    <div><button type="button" class="btn btn-danger btn-sm btn-icon-only remove-counter"><i class="fa-solid fa-trash"></i></button></div>`;
  document.getElementById('counter-list').appendChild(row);
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
