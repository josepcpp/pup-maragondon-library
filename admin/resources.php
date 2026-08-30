<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$resources = cms_load('resources.json');
$editing   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: resources.php'); exit; }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $resources = array_values(array_filter($resources, fn($r) => $r['id'] !== $id));
        cms_save('resources.json', $resources);
        flash_set('success','Resource deleted.');
        header('Location: resources.php'); exit;
    }

    if (in_array($action, ['add','edit'])) {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if (!$name) { flash_set('error','Name is required.'); header('Location: resources.php'); exit; }

        $logo = trim($_POST['logo'] ?? '');
        if (!empty($_FILES['logo_file']['name'])) {
            $up = cms_upload_file($_FILES['logo_file'], 'resources');
            if ($up['ok']) $logo = $up['path'];
            else { flash_set('error', $up['error']); header('Location: resources.php'); exit; }
        }

        $item = [
            'name'        => $name,
            'logo'        => $logo,
            'description' => trim($_POST['description'] ?? ''),
            'url'         => trim($_POST['url'] ?? '#'),
            'category'    => trim($_POST['category'] ?? ''),
        ];

        if ($action === 'add') {
            $item['id'] = cms_next_id($resources);
            $resources[] = $item;
            flash_set('success','Resource added.');
        } else {
            foreach ($resources as &$r) {
                if ($r['id'] === $id) { $item['id'] = $id; $r = $item; break; }
            } unset($r);
            flash_set('success','Resource updated.');
        }
        cms_save('resources.json', $resources);
        header('Location: resources.php'); exit;
    }
}

if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($resources as $r) { if ($r['id'] === $eid) { $editing = $r; break; } }
}

$categories = ['Academic','E-Books','News & Periodicals','Government','Other'];

$page_title = 'Online Resources';
$page_icon  = 'fa-solid fa-database';
$active_nav = 'resources';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Online Resources</h1>
    <p>Manage the e-resource partner databases shown on the Resources page.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('res-modal')">
    <i class="fa-solid fa-plus"></i> Add Resource
  </button>
</div>

<div class="card">
  <div class="card-header"><h2><i class="fa-solid fa-database"></i> Databases & Resources (<?= count($resources) ?>)</h2></div>
  <?php if (empty($resources)): ?>
    <div class="empty-state"><i class="fa-solid fa-database"></i><p>No resources yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th style="width:70px;">Logo</th><th>Name</th><th>Category</th><th>URL</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($resources as $r): ?>
        <tr>
          <td>
            <?php if ($r['logo']): ?>
              <img src="../<?= h($r['logo']) ?>" style="height:36px;max-width:70px;object-fit:contain;border-radius:4px;" alt="<?= h($r['name']) ?>">
            <?php else: ?>
              <div class="thumb-placeholder"><i class="fa-solid fa-database"></i></div>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= h($r['name']) ?></strong>
            <?php if ($r['description']): ?>
              <br><span style="font-size:0.76rem;color:var(--ink-light);"><?= h(mb_substr($r['description'],0,70)) ?>…</span>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-gold"><?= h($r['category'] ?? '') ?></span></td>
          <td style="font-size:0.78rem;">
            <?php if ($r['url'] && $r['url'] !== '#'): ?>
              <a href="<?= h($r['url']) ?>" target="_blank" style="color:var(--maroon);">
                <?= h(parse_url($r['url'], PHP_URL_HOST) ?: $r['url']) ?>
              </a>
            <?php else: ?>
              <span style="color:var(--ink-light);">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $r['id'] ?>" class="btn btn-outline btn-sm btn-icon-only" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-danger btn-sm btn-icon-only" data-confirm="Delete '<?= h($r['name']) ?>'?"><i class="fa-solid fa-trash"></i></button>
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

<!-- Modal -->
<div class="modal-backdrop <?= $editing ? 'show' : '' ?>" id="res-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><?= $editing ? 'Edit Resource' : 'Add Resource' ?></h3>
      <button class="modal-close" onclick="closeModal('res-modal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Database Name <span class="req">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?= h($editing['name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category" class="form-control">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= h($cat) ?>" <?= ($editing['category'] ?? '') === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."><?= h($editing['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Access URL</label>
          <input type="text" name="url" class="form-control" value="<?= h($editing['url'] ?? '') ?>" placeholder="https://...">
        </div>
        <div class="form-group">
          <label class="form-label">Logo</label>
          <div class="upload-zone">
            <i class="fa-solid fa-image"></i>
            <p>Upload logo image</p>
            <small>PNG with transparent background recommended</small>
            <input type="file" name="logo_file" accept="image/*" style="display:none;">
            <?php if (!empty($editing['logo'])): ?>
              <img src="../<?= h($editing['logo']) ?>" class="upload-preview" style="display:block;max-height:60px;object-fit:contain;">
            <?php else: ?>
              <img class="upload-preview">
            <?php endif; ?>
          </div>
          <div class="media-url-row" style="margin-top:8px;">
            <input type="text" name="logo" id="res-logo-url" class="form-control"
                   placeholder="Or pick from media library…" value="<?= h($editing['logo'] ?? '') ?>">
            <button type="button" class="btn btn-outline btn-sm" onclick="openMediaPicker('res-logo-url','res-logo-prev')">
              <i class="fa-solid fa-photo-film"></i> Browse
            </button>
          </div>
          <img id="res-logo-prev" src="<?= !empty($editing['logo']) ? '../'.h($editing['logo']) : '' ?>"
               style="<?= !empty($editing['logo']) ? 'display:block;' : 'display:none;' ?>margin-top:6px;max-height:60px;object-fit:contain;border-radius:var(--radius);">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('res-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Save Changes' : 'Add Resource' ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) {
  document.getElementById(id).classList.remove('show');
  if (window.location.search.includes('edit=')) window.location.href = 'resources.php';
}
document.getElementById('res-modal').addEventListener('click', function(e){ if(e.target===this) closeModal('res-modal'); });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
