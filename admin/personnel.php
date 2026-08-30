<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$personnel = cms_load('personnel.json');
$editing   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: personnel.php'); exit; }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $personnel = array_values(array_filter($personnel, fn($p) => $p['id'] !== $id));
        cms_save('personnel.json', $personnel);
        flash_set('success','Personnel deleted.');
        header('Location: personnel.php'); exit;
    }

    if (in_array($action, ['add','edit'])) {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        if (!$name) { flash_set('error','Name is required.'); header('Location: personnel.php'); exit; }

        $photo = trim($_POST['photo'] ?? '');
        if (!empty($_FILES['photo_file']['name'])) {
            $up = cms_upload_file($_FILES['photo_file'], 'personnel');
            if ($up['ok']) $photo = $up['path'];
            else { flash_set('error', $up['error']); header('Location: personnel.php'); exit; }
        }

        $item = [
            'name'           => $name,
            'role'           => trim($_POST['role']           ?? ''),
            'email'          => trim($_POST['email']          ?? ''),
            'ext'            => trim($_POST['ext']            ?? ''),
            'photo'          => $photo,
            'bio'            => trim($_POST['bio']            ?? ''),
            'specialization' => trim($_POST['specialization'] ?? ''),
            'department'     => trim($_POST['department']     ?? ''),
        ];

        if ($action === 'add') {
            $item['id'] = cms_next_id($personnel);
            $personnel[] = $item;
            flash_set('success','Personnel added.');
        } else {
            foreach ($personnel as &$p) {
                if ($p['id'] === $id) { $item['id'] = $id; $p = $item; break; }
            } unset($p);
            flash_set('success','Personnel updated.');
        }
        cms_save('personnel.json', $personnel);
        header('Location: personnel.php'); exit;
    }
}

if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($personnel as $p) { if ($p['id'] === $eid) { $editing = $p; break; } }
}

$page_title = 'Personnel';
$page_icon  = 'fa-solid fa-users';
$active_nav = 'personnel';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Personnel</h1>
    <p>Manage library staff shown on the Administration page.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('staff-modal')">
    <i class="fa-solid fa-user-plus"></i> Add Personnel
  </button>
</div>

<div class="card">
  <div class="card-header"><h2><i class="fa-solid fa-id-card"></i> Staff (<?= count($personnel) ?>)</h2></div>
  <?php if (empty($personnel)): ?>
    <div class="empty-state"><i class="fa-solid fa-users"></i><p>No personnel yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th style="width:60px;">Photo</th><th>Name</th><th>Role</th><th>Specialization</th><th>Email</th><th>Ext</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($personnel as $p): ?>
        <tr>
          <td>
            <?php if ($p['photo']): ?>
              <img src="../<?= h($p['photo']) ?>" class="thumb" style="border-radius:50%;" alt="">
            <?php else: ?>
              <div class="thumb-placeholder" style="border-radius:50%;"><i class="fa-solid fa-user"></i></div>
            <?php endif; ?>
          </td>
          <td><strong><?= h($p['name']) ?></strong></td>
          <td style="color:var(--ink-light);"><?= h($p['role'] ?? '') ?></td>
          <td style="font-size:0.8rem;color:var(--ink-light);"><?= h($p['specialization'] ?? '—') ?></td>
          <td style="font-size:0.8rem;"><?= h($p['email'] ?? '') ?></td>
          <td style="font-size:0.8rem;color:var(--ink-light);"><?= h($p['ext'] ?? '—') ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $p['id'] ?>" class="btn btn-outline btn-sm btn-icon-only" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="btn btn-danger btn-sm btn-icon-only" data-confirm="Remove <?= h($p['name']) ?>?"><i class="fa-solid fa-trash"></i></button>
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
<div class="modal-backdrop <?= $editing ? 'show' : '' ?>" id="staff-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><?= $editing ? 'Edit Staff Member' : 'Add Staff Member' ?></h3>
      <button class="modal-close" onclick="closeModal('staff-modal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <div class="form-group">
          <label class="form-label">Full Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" required value="<?= h($editing['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Role / Position</label>
          <input type="text" name="role" class="form-control" value="<?= h($editing['role'] ?? '') ?>" placeholder="e.g. University Librarian">
        </div>
        <div class="form-group">
          <label class="form-label">Bio / Short Description</label>
          <textarea name="bio" class="form-control" rows="3" placeholder="A brief professional description shown on the personnel card…"><?= h($editing['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Specialization</label>
            <input type="text" name="specialization" class="form-control" value="<?= h($editing['specialization'] ?? '') ?>" placeholder="e.g. Reference Services">
          </div>
          <div class="form-group">
            <label class="form-label">Department / Section</label>
            <input type="text" name="department" class="form-control" value="<?= h($editing['department'] ?? '') ?>" placeholder="e.g. Circulation Section">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= h($editing['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Extension</label>
            <input type="text" name="ext" class="form-control" value="<?= h($editing['ext'] ?? '') ?>" placeholder="e.g. 1234">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Profile Photo</label>
          <div class="upload-zone">
            <i class="fa-solid fa-camera"></i>
            <p>Click or drag & drop a photo</p>
            <small>JPG, PNG — max 5 MB · Square crop recommended</small>
            <input type="file" name="photo_file" accept="image/*" style="display:none;">
            <?php if (!empty($editing['photo'])): ?>
              <img src="../<?= h($editing['photo']) ?>" class="upload-preview" style="display:block;border-radius:50%;width:100px;height:100px;object-fit:cover;margin:12px auto 0;">
            <?php else: ?>
              <img class="upload-preview" style="border-radius:50%;width:100px;height:100px;object-fit:cover;margin:12px auto 0;">
            <?php endif; ?>
          </div>
          <div class="media-url-row" style="margin-top:8px;">
            <input type="text" name="photo" id="staff-photo-url" class="form-control"
                   placeholder="Or pick from media library…" value="<?= h($editing['photo'] ?? '') ?>">
            <button type="button" class="btn btn-outline btn-sm" onclick="openMediaPicker('staff-photo-url','staff-photo-prev')">
              <i class="fa-solid fa-photo-film"></i> Browse
            </button>
          </div>
          <img id="staff-photo-prev" src="<?= !empty($editing['photo']) ? '../'.h($editing['photo']) : '' ?>"
               style="<?= !empty($editing['photo']) ? 'display:block;' : 'display:none;' ?>margin-top:6px;max-height:70px;border-radius:50%;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('staff-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Save Changes' : 'Add Staff' ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) {
  document.getElementById(id).classList.remove('show');
  if (window.location.search.includes('edit=')) window.location.href = 'personnel.php';
}
document.getElementById('staff-modal').addEventListener('click', function(e){ if(e.target===this) closeModal('staff-modal'); });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
