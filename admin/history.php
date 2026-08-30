<?php
// Process before any HTML output
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

// Human labels for the content files, so the page reads as content rather
// than filenames. Anything not listed falls back to its filename.
$CONTENT_LABELS = [
    'settings.json'   => ['Site settings',    'Titles, hero copy, contact details, external links'],
    'about.json'      => ['About page',       'Vision and mission, weekly schedule, history timeline'],
    'services.json'   => ['Library services', 'Reader service sections'],
    'resources.json'  => ['Online resources', 'Database listings'],
    'holdings.json'   => ['Library holdings', 'Collection categories'],
    'guidelines.json' => ['Guidelines',       'Rules and policies'],
    'programs.json'   => ['Programs & events','Programs, schedules, photos'],
    'linkages.json'   => ['Linkages',         'Partnerships and agreements'],
    'personnel.json'  => ['Personnel',        'Staff directory'],
    'arrivals.json'   => ['New arrivals',     'Recently added items on the home page'],
    'featured.json'   => ['Featured resource','The home page spotlight'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Invalid CSRF token.');
        header('Location: history.php'); exit;
    }

    $file    = $_POST['file']    ?? '';
    $version = $_POST['version'] ?? '';

    if (($_POST['action'] ?? '') === 'restore') {
        if (cms_restore($file, $version)) {
            $label = $CONTENT_LABELS[$file][0] ?? $file;
            flash_set('success', $label . ' restored. The version it replaced was saved, so you can undo this.');
        } else {
            flash_set('error', 'Could not restore that version.');
        }
        header('Location: history.php?file=' . urlencode($file)); exit;
    }
}

// Which file are we looking at? Anything unrecognised falls back to the overview.
$sel = $_GET['file'] ?? '';
if (!in_array($sel, CMS_CONTENT_FILES, true)) $sel = '';

$page_title = 'Version History';
$page_icon  = 'fa-solid fa-clock-rotate-left';
$active_nav = 'history';
require_once __DIR__ . '/includes/header.php';

$token = csrf_token();

function hist_bytes(int $b): string {
    return $b >= 1048576 ? round($b / 1048576, 1) . ' MB'
         : ($b >= 1024   ? round($b / 1024) . ' KB' : $b . ' B');
}
function hist_ago(int $ts): string {
    $d = time() - $ts;
    if ($d < 60)    return 'just now';
    if ($d < 3600)  return floor($d / 60) . ' min ago';
    if ($d < 86400) return floor($d / 3600) . ' hr ago';
    return floor($d / 86400) . ' day' . (floor($d / 86400) === 1.0 ? '' : 's') . ' ago';
}
?>

<div class="page-header">
  <div class="page-header-text">
    <h1><?= $sel ? h($CONTENT_LABELS[$sel][0] ?? $sel) . ' history' : 'Version History' ?></h1>
    <p>
      <?php if ($sel): ?>
        The last <?= CMS_VERSIONS_KEPT ?> saved versions. Restoring one keeps the current version, so you can undo it.
      <?php else: ?>
        Every save keeps a copy of what it replaced &mdash; the last <?= CMS_VERSIONS_KEPT ?> per content type.
      <?php endif; ?>
    </p>
  </div>
  <?php if ($sel): ?>
  <a href="history.php" class="btn btn-outline btn-sm">
    <i class="fa-solid fa-arrow-left"></i> All content
  </a>
  <?php endif; ?>
</div>

<?php if (!$sel): ?>

  <div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-layer-group"></i> Content</h2></div>
    <div class="card-body" style="padding:0;">
      <table class="cms-table">
        <thead>
          <tr><th>Content</th><th>Last saved</th><th>Versions</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach (CMS_CONTENT_FILES as $f):
          $path     = DATA_PATH . $f;
          $exists   = is_file($path);
          $versions = cms_versions($f);
          [$label, $desc] = $CONTENT_LABELS[$f] ?? [$f, ''];
        ?>
          <tr>
            <td>
              <strong><?= h($label) ?></strong>
              <?php if ($desc): ?>
              <span style="display:block;font-size:0.78rem;color:var(--ink-light);"><?= h($desc) ?></span>
              <?php endif; ?>
            </td>
            <td style="color:var(--ink-light);font-size:0.8rem;white-space:nowrap;">
              <?= $exists ? h(date('M j, Y g:i A', filemtime($path))) : '&mdash;' ?>
            </td>
            <td>
              <?php if ($versions): ?>
                <span class="badge badge-maroon"><?= count($versions) ?></span>
              <?php else: ?>
                <span style="color:var(--ink-light);font-size:0.8rem;">none yet</span>
              <?php endif; ?>
            </td>
            <td style="text-align:right;">
              <?php if ($versions): ?>
              <a href="history.php?file=<?= urlencode($f) ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-clock-rotate-left"></i> History
              </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <p class="note">
    Versions are written to <code>data/_versions/</code>, which is not reachable from the web.
    Only the most recent <?= CMS_VERSIONS_KEPT ?> are kept per content type; older ones are removed automatically.
  </p>

<?php else:
  $versions = cms_versions($sel);
  $current  = DATA_PATH . $sel;
?>

  <div class="card">
    <div class="card-header">
      <h2><i class="fa-solid fa-file-lines"></i> Current version</h2>
      <span style="font-size:0.8rem;color:var(--ink-light);">
        <?= is_file($current) ? h(date('M j, Y g:i A', filemtime($current))) . ' · ' . hist_bytes((int)filesize($current)) : 'not saved yet' ?>
      </span>
    </div>
  </div>

  <?php if (!$versions): ?>
    <div class="card"><div class="empty-state">
      <i class="fa-solid fa-clock-rotate-left"></i>
      <p>No previous versions yet. The next time you save this content, the version it replaces will appear here.</p>
    </div></div>
  <?php else: ?>

  <div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-clock-rotate-left"></i> Previous versions</h2></div>
    <div class="card-body" style="padding:0;">
      <table class="cms-table">
        <thead>
          <tr><th>Saved</th><th>Size</th><th>Contents</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($versions as $i => $v):
          $vpath = cms_version_path($sel, $v['name']);
          $body  = $vpath ? (string)@file_get_contents($vpath) : '';
          $pretty = json_encode(json_decode($body, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>
          <tr>
            <td style="white-space:nowrap;">
              <strong><?= h(date('M j, Y g:i A', $v['saved_at'])) ?></strong>
              <span style="display:block;font-size:0.76rem;color:var(--ink-light);">
                <?= h(hist_ago($v['saved_at'])) ?><?= $i === 0 ? ' · most recent' : '' ?>
              </span>
            </td>
            <td style="color:var(--ink-light);font-size:0.8rem;white-space:nowrap;"><?= h(hist_bytes($v['size'])) ?></td>
            <td>
              <details>
                <summary style="cursor:pointer;font-size:0.82rem;color:var(--maroon);">Preview</summary>
                <pre style="margin-top:10px;max-height:280px;overflow:auto;background:var(--paper-alt);border:1px solid var(--rule);border-radius:var(--radius);padding:12px;font-size:0.74rem;line-height:1.5;"><?= h($pretty !== false ? $pretty : $body) ?></pre>
              </details>
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action"  value="restore">
                <input type="hidden" name="file"    value="<?= h($sel) ?>">
                <input type="hidden" name="version" value="<?= h($v['name']) ?>">
                <button type="submit" class="btn btn-outline btn-sm"
                        data-confirm="Restore this version? The current content will be kept as a new version, so you can undo it.">
                  <i class="fa-solid fa-rotate-left"></i> Restore
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
