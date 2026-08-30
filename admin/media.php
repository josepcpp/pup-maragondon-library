<?php
// Process before any HTML output
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: media.php'); exit; }

    if ($_POST['action'] === 'upload' && !empty($_FILES['media_file']['name'])) {
        $subfolder = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_POST['subfolder'] ?? 'media'));
        if (!$subfolder) $subfolder = 'media';
        $up = cms_upload_file($_FILES['media_file'], $subfolder, 'any');
        if ($up['ok']) {
            cms_media_cache_bust();
            flash_set('success', 'File uploaded: ' . basename($up['path']));
        } else {
            flash_set('error', $up['error']);
        }
        header('Location: media.php'); exit;
    }

    if ($_POST['action'] === 'delete') {
        $rel = $_POST['file'] ?? '';
        $abs = realpath(BASE_PATH . '/' . $rel);
        $up  = realpath(UPLOAD_PATH);
        if ($abs && $up && strpos($abs, $up) === 0 && is_file($abs)) {
            unlink($abs);
            cms_media_cache_bust();
            flash_set('success', 'File deleted.');
        } else {
            flash_set('error', 'Invalid file path.');
        }
        header('Location: media.php'); exit;
    }
}

$page_title = 'Media Manager';
$page_icon  = 'fa-solid fa-photo-film';
$active_nav = 'media';
require_once __DIR__ . '/includes/header.php';

$token  = csrf_token();
$files  = cms_media_list();

// Group by folder
$folders = [];
foreach ($files as $f) {
    $folders[$f['folder']][] = $f;
}

$subfolders = array_keys(UPLOAD_DIMENSIONS);
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Media Manager</h1>
    <p>Browse, upload, and manage all files in the library. Click any file to copy its URL.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('upload-modal')">
    <i class="fa-solid fa-cloud-arrow-up"></i> Upload File
  </button>
</div>

<!-- Stats bar -->
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
  <?php
  $img_count = count(array_filter($files, fn($f) => $f['type'] === 'image'));
  $vid_count = count($files) - $img_count;
  $total_size = array_sum(array_column($files, 'size'));
  $size_str = $total_size > 1048576 ? round($total_size/1048576, 1).' MB' : round($total_size/1024).' KB';
  ?>
  <div class="stat-card" style="flex:1;min-width:120px;text-align:center;">
    <div class="stat-card-num"><?= count($files) ?></div>
    <div class="stat-card-label">Total Files</div>
  </div>
  <div class="stat-card" style="flex:1;min-width:120px;text-align:center;">
    <div class="stat-card-num"><?= $img_count ?></div>
    <div class="stat-card-label">Images</div>
  </div>
  <div class="stat-card" style="flex:1;min-width:120px;text-align:center;">
    <div class="stat-card-num"><?= $vid_count ?></div>
    <div class="stat-card-label">Videos</div>
  </div>
  <div class="stat-card" style="flex:1;min-width:120px;text-align:center;">
    <div class="stat-card-num"><?= $size_str ?></div>
    <div class="stat-card-label">Total Size</div>
  </div>
</div>

<!-- Filter bar -->
<div class="card" style="margin-bottom:20px;">
  <div style="padding:14px 20px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <input type="text" id="filter-search" class="form-control" placeholder="Search filename…" style="max-width:240px;" oninput="filterMedia()">
    <select id="filter-type" class="form-control" style="max-width:140px;" onchange="filterMedia()">
      <option value="">All types</option>
      <option value="image">Images</option>
      <option value="video">Videos</option>
    </select>
    <select id="filter-folder" class="form-control" style="max-width:180px;" onchange="filterMedia()">
      <option value="">All folders</option>
      <?php foreach (array_keys($folders) as $folder): ?>
      <option value="<?= h($folder) ?>"><?= h($folder) ?> (<?= count($folders[$folder]) ?>)</option>
      <?php endforeach; ?>
    </select>
    <span id="filter-count" style="font-size:0.78rem;color:var(--ink-light);margin-left:auto;"><?= count($files) ?> files</span>
  </div>
</div>

<?php if (empty($files)): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-solid fa-photo-film"></i>
    <p>No files uploaded yet. Click "Upload File" to add your first media.</p>
  </div>
</div>
<?php else: ?>

<!-- Media grid -->
<div class="card">
  <div id="media-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;padding:20px;">
    <?php foreach ($files as $f):
      $size_label = $f['size'] > 1048576 ? round($f['size']/1048576,1).'MB' : round($f['size']/1024).'KB';
      $date_label = date('M j, Y', $f['mtime']);
    ?>
    <div class="media-card" data-type="<?= h($f['type']) ?>" data-folder="<?= h($f['folder']) ?>" data-name="<?= h(strtolower($f['name'])) ?>">
      <div class="media-thumb" onclick="copyUrl('<?= h($f['url']) ?>', this)">
        <?php if ($f['type'] === 'image'): ?>
          <img src="../<?= h($f['url']) ?>" alt="<?= h($f['name']) ?>" loading="lazy">
        <?php else: ?>
          <div class="media-video-icon">
            <i class="fa-solid fa-film"></i>
            <span><?= h(strtoupper(pathinfo($f['name'], PATHINFO_EXTENSION))) ?></span>
          </div>
        <?php endif; ?>
        <div class="media-copy-overlay"><i class="fa-solid fa-copy"></i> Copy URL</div>
      </div>
      <div class="media-info">
        <div class="media-name" title="<?= h($f['name']) ?>"><?= h($f['name']) ?></div>
        <div class="media-meta"><?= h($f['folder']) ?> · <?= $size_label ?></div>
        <div class="media-actions">
          <a href="../<?= h($f['url']) ?>" target="_blank" class="btn btn-outline btn-sm btn-icon-only" title="View">
            <i class="fa-solid fa-eye"></i>
          </a>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="file" value="<?= h($f['url']) ?>">
            <button type="submit" class="btn btn-danger btn-sm btn-icon-only"
                    data-confirm="Delete '<?= h($f['name']) ?>'? This cannot be undone." title="Delete">
              <i class="fa-solid fa-trash"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php endif; ?>

<!-- Upload modal -->
<div class="modal-backdrop" id="upload-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa-solid fa-cloud-arrow-up"></i> Upload File</h3>
      <button class="modal-close" onclick="closeModal('upload-modal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="upload">

        <div class="form-group">
          <label class="form-label">Save to folder <span class="req">*</span></label>
          <select name="subfolder" class="form-control">
            <?php foreach ($subfolders as $sf): ?>
            <option value="<?= h($sf) ?>"><?= h($sf) ?></option>
            <?php endforeach; ?>
            <option value="media">media (general)</option>
          </select>
          <small class="form-hint">Images will be automatically resized to the folder's template dimensions.</small>
        </div>

        <div class="form-group">
          <label class="form-label">File <span class="req">*</span></label>
          <div class="upload-zone">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <p>Click or drag &amp; drop</p>
            <small>Images (JPG, PNG, GIF, WEBP — max 5 MB) or Videos (MP4, WEBM — max 100 MB)</small>
            <input type="file" name="media_file" accept="image/*,video/*" style="display:none;" required>
            <img class="upload-preview">
          </div>
        </div>

        <div style="background:var(--parchment);border-radius:var(--radius);padding:12px 14px;font-size:0.8rem;color:var(--ink-light);">
          <strong style="color:var(--ink-mid);">Auto-crop dimensions by folder:</strong>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 16px;margin-top:6px;">
            <?php foreach (UPLOAD_DIMENSIONS as $folder => [$w, $h]): if (!$w) continue; ?>
            <span><?= h($folder) ?>: <?= $w ?>×<?= $h ?>px</span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('upload-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Upload</button>
      </div>
    </form>
  </div>
</div>

<!-- Copied toast -->
<div id="copy-toast" style="position:fixed;bottom:24px;right:24px;background:var(--maroon);color:#fff;padding:10px 18px;border-radius:var(--radius);font-size:0.85rem;opacity:0;transition:opacity 0.3s;pointer-events:none;z-index:9999;">
  <i class="fa-solid fa-check"></i> URL copied to clipboard
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.getElementById('upload-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal('upload-modal');
});

function copyUrl(url, el) {
  navigator.clipboard.writeText(url).then(() => {
    const t = document.getElementById('copy-toast');
    t.style.opacity = '1';
    setTimeout(() => t.style.opacity = '0', 2000);
  });
}

function filterMedia() {
  const q   = document.getElementById('filter-search').value.toLowerCase();
  const typ = document.getElementById('filter-type').value;
  const fld = document.getElementById('filter-folder').value;
  let count = 0;
  document.querySelectorAll('.media-card').forEach(card => {
    const match =
      (!q   || card.dataset.name.includes(q)) &&
      (!typ || card.dataset.type === typ) &&
      (!fld || card.dataset.folder === fld);
    card.style.display = match ? '' : 'none';
    if (match) count++;
  });
  document.getElementById('filter-count').textContent = count + ' files';
}
</script>

<style>
.media-card { border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; background:var(--white); transition:border-color 0.15s ease; }
.media-card:hover { border-color:var(--maroon); }
.media-thumb { position:relative; aspect-ratio:4/3; overflow:hidden; cursor:pointer; background:var(--parchment); }
.media-thumb img { width:100%;height:100%;object-fit:cover;display:block; }
.media-video-icon { width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:var(--maroon); }
.media-video-icon i { font-size:2rem; }
.media-video-icon span { font-size:0.65rem;font-weight:700;letter-spacing:1px;background:var(--maroon);color:#fff;padding:2px 6px;border-radius:4px; }
.media-copy-overlay { position:absolute;inset:0;background:rgba(128,0,0,0.85);color:#fff;display:flex;align-items:center;justify-content:center;gap:6px;font-size:0.8rem;font-weight:600;opacity:0;transition:opacity 0.2s; }
.media-thumb:hover .media-copy-overlay { opacity:1; }
.media-info { padding:8px 10px; }
.media-name { font-size:0.72rem;font-weight:600;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;margin-bottom:2px; }
.media-meta { font-size:0.64rem;color:var(--ink-light);margin-bottom:6px; }
.media-actions { display:flex;gap:4px; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
