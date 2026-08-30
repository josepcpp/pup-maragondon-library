<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$arrivals = cms_load('arrivals.json');
$editing  = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: arrivals.php'); exit; }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $arrivals = array_values(array_filter($arrivals, fn($a) => $a['id'] !== $id));
        cms_save('arrivals.json', $arrivals);
        flash_set('success', 'Arrival deleted.');
        header('Location: arrivals.php'); exit;
    }

    if (in_array($action, ['add','edit'])) {
        $id      = (int)($_POST['id'] ?? 0);
        $type    = trim($_POST['type'] ?? '');
        $title   = trim($_POST['title'] ?? '');
        $author  = trim($_POST['author'] ?? '');
        $date    = trim($_POST['date'] ?? '');
        $image   = trim($_POST['image'] ?? '');

        if (!$title) { flash_set('error','Title is required.'); header('Location: arrivals.php'); exit; }
        $allowed_types = ['E-Book','Book','Thesis','Module','Video','Journal','Magazine','Other'];
        if (!$type || !in_array($type, $allowed_types, true)) { flash_set('error','Please select a valid type.'); header('Location: arrivals.php'); exit; }

        // Handle file upload
        if (!empty($_FILES['image_file']['name'])) {
            $upload = cms_upload_file($_FILES['image_file'], 'arrivals');
            if ($upload['ok']) $image = $upload['path'];
            else { flash_set('error', $upload['error']); header('Location: arrivals.php'); exit; }
        }

        $item = compact('type','title','author','date','image');

        if ($action === 'add') {
            $item['id'] = cms_next_id($arrivals);
            $arrivals[] = $item;
            flash_set('success', 'Arrival added.');
        } else {
            foreach ($arrivals as &$a) {
                if ($a['id'] === $id) { $item['id'] = $id; $a = $item; break; }
            } unset($a);
            flash_set('success', 'Arrival updated.');
        }
        cms_save('arrivals.json', $arrivals);
        header('Location: arrivals.php'); exit;
    }
}

// Edit mode — load item
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($arrivals as $a) { if ($a['id'] === $eid) { $editing = $a; break; } }
}

$arrival_types = ['E-Book','Book','Thesis','Module','Video','Journal','Magazine','Other'];

$page_title = 'New Arrivals';
$page_icon  = 'fa-solid fa-star';
$active_nav = 'arrivals';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>New Arrivals</h1>
    <p>Manage the recently added items shown on the homepage.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('add-modal')">
    <i class="fa-solid fa-plus"></i> Add Arrival
  </button>
</div>

<div class="card">
  <div class="card-header">
    <h2><i class="fa-solid fa-list"></i> All Arrivals (<?= count($arrivals) ?>)</h2>
  </div>
  <?php if (empty($arrivals)): ?>
    <div class="empty-state"><i class="fa-solid fa-star"></i><p>No arrivals yet. Add one above.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead>
        <tr>
          <th style="width:60px;">Image</th>
          <th>Title</th>
          <th>Author</th>
          <th>Type</th>
          <th>Date</th>
          <th style="width:110px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($arrivals as $item): ?>
        <tr>
          <td>
            <?php if ($item['image']): ?>
              <img src="../<?= h($item['image']) ?>" class="thumb" alt="">
            <?php else: ?>
              <div class="thumb-placeholder"><i class="fa-regular fa-image"></i></div>
            <?php endif; ?>
          </td>
          <td><strong><?= h($item['title']) ?></strong></td>
          <td style="color:var(--ink-light);"><?= h($item['author'] ?? '') ?></td>
          <td><span class="badge badge-maroon"><?= h($item['type'] ?? '') ?></span></td>
          <td style="font-size:0.8rem;color:var(--ink-light);"><?= h($item['date'] ?? '') ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $item['id'] ?>" class="btn btn-outline btn-sm btn-icon-only" title="Edit">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm btn-icon-only"
                        data-confirm="Delete '<?= h($item['title']) ?>'?" title="Delete">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Add / Edit Modal -->
<div class="modal-backdrop <?= $editing ? 'show' : '' ?>" id="add-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><?= $editing ? 'Edit Arrival' : 'Add New Arrival' ?></h3>
      <button class="modal-close" onclick="closeModal('add-modal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?>
          <input type="hidden" name="id" value="<?= $editing['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Type <span class="req">*</span></label>
            <select name="type" class="form-control">
              <?php foreach ($arrival_types as $t): ?>
                <option value="<?= h($t) ?>" <?= ($editing['type'] ?? '') === $t ? 'selected' : '' ?>><?= h($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Date Added</label>
            <input type="text" name="date" class="form-control" placeholder="e.g. May 1, 2026"
                   value="<?= h($editing['date'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" name="title" class="form-control" placeholder="Resource title"
                 value="<?= h($editing['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Author / Source</label>
          <input type="text" name="author" class="form-control" placeholder="Author name or department"
                 value="<?= h($editing['author'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Cover Image</label>
          <div class="upload-zone">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <p>Click or drag & drop an image</p>
            <small>JPG, PNG, WEBP — max 5 MB</small>
            <input type="file" name="image_file" accept="image/*" style="display:none;">
            <?php if (!empty($editing['image'])): ?>
              <img src="../<?= h($editing['image']) ?>" class="upload-preview" style="display:block;">
            <?php else: ?>
              <img class="upload-preview">
            <?php endif; ?>
          </div>
          <div class="media-url-row" style="margin-top:8px;">
            <input type="text" name="image" id="arr-img-url" class="form-control"
                   placeholder="Or pick from media library…" value="<?= h($editing['image'] ?? '') ?>">
            <button type="button" class="btn btn-outline btn-sm" onclick="openMediaPicker('arr-img-url','arr-img-prev')">
              <i class="fa-solid fa-photo-film"></i> Browse
            </button>
          </div>
          <img id="arr-img-prev" src="<?= !empty($editing['image']) ? '../'.h($editing['image']) : '' ?>"
               style="<?= !empty($editing['image']) ? 'display:block;' : 'display:none;' ?>margin-top:6px;max-height:70px;border-radius:var(--radius);">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('add-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Save Changes' : 'Add Arrival' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show');
  if (id==='add-modal' && window.location.search.includes('edit=')) {
    window.location.href = 'arrivals.php';
  }
}
document.getElementById('add-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal('add-modal');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
