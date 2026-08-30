<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$programs = cms_load('programs.json');
$editing  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: programs.php'); exit; }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $programs = array_values(array_filter($programs, fn($p) => $p['id'] !== $id));
        cms_save('programs.json', $programs);
        flash_set('success','Program deleted.');
        header('Location: programs.php'); exit;
    }

    if (in_array($action, ['add','edit'])) {
        $id    = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if (!$title) { flash_set('error','Title is required.'); header('Location: programs.php'); exit; }

        $photos = [];
        // Handle multiple photo uploads
        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['name'] as $i => $name) {
                if (empty($name)) continue;
                $f = [
                    'name'     => $name,
                    'type'     => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                    'error'    => $_FILES['photos']['error'][$i],
                    'size'     => $_FILES['photos']['size'][$i],
                ];
                $up = cms_upload_file($f, 'programs');
                if ($up['ok']) $photos[] = $up['path'];
            }
        }
        // Handle photos picked from media library
        foreach ($_POST['picked_photos'] ?? [] as $pp) {
            $abs = realpath(BASE_PATH . '/' . $pp);
            $up  = realpath(UPLOAD_PATH);
            if ($abs && $up && strpos($abs, $up) === 0 && is_file($abs)) {
                $photos[] = $pp;
            }
        }

        // Merge with existing photos if editing
        if ($action === 'edit') {
            foreach ($programs as $p) {
                if ($p['id'] === $id) { $photos = array_merge($p['photos'] ?? [], $photos); break; }
            }
        }

        // Remove specific photos
        $remove = $_POST['remove_photos'] ?? [];
        $photos = array_values(array_filter($photos, fn($ph) => !in_array($ph, $remove)));

        $item = [
            'title'       => $title,
            'label'       => trim($_POST['label'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'when'        => trim($_POST['when'] ?? ''),
            'who'         => trim($_POST['who'] ?? ''),
            'what'        => trim($_POST['what'] ?? ''),
            'contact'     => trim($_POST['contact'] ?? ''),
            'photos'      => $photos,
        ];

        if ($action === 'add') {
            $item['id'] = cms_next_id($programs);
            $programs[] = $item;
            flash_set('success','Program added.');
        } else {
            foreach ($programs as &$p) {
                if ($p['id'] === $id) { $item['id'] = $id; $p = $item; break; }
            } unset($p);
            flash_set('success','Program updated.');
        }
        cms_save('programs.json', $programs);
        header('Location: programs.php'); exit;
    }
}

if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($programs as $p) { if ($p['id'] === $eid) { $editing = $p; break; } }
}

$page_title = 'Programs & Events';
$page_icon  = 'fa-solid fa-calendar-days';
$active_nav = 'programs';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Programs &amp; Events</h1>
    <p>Manage programs, events, and activities shown on the Programs page.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('prog-modal')">
    <i class="fa-solid fa-plus"></i> Add Program
  </button>
</div>

<div class="card">
  <div class="card-header">
    <h2><i class="fa-solid fa-list"></i> All Programs (<?= count($programs) ?>)</h2>
  </div>
  <?php if (empty($programs)): ?>
    <div class="empty-state"><i class="fa-solid fa-calendar-days"></i><p>No programs yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th>Title</th><th>Label</th><th>Photos</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($programs as $p): ?>
        <tr>
          <td><strong><?= h($p['title']) ?></strong>
            <?php if ($p['description']): ?>
              <br><span style="font-size:0.78rem;color:var(--ink-light);"><?= h(mb_substr($p['description'],0,80)) ?>…</span>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-gold"><?= h($p['label'] ?? '') ?></span></td>
          <td style="font-size:0.8rem;color:var(--ink-light);"><?= count($p['photos'] ?? []) ?> photo(s)</td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $p['id'] ?>" class="btn btn-outline btn-sm btn-icon-only" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="btn btn-danger btn-sm btn-icon-only" data-confirm="Delete '<?= h($p['title']) ?>'?"><i class="fa-solid fa-trash"></i></button>
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
<div class="modal-backdrop <?= $editing ? 'show' : '' ?>" id="prog-modal" style="align-items:flex-start;padding:30px 0;overflow-y:auto;">
  <div class="modal" style="max-width:680px;">
    <div class="modal-header">
      <h3><?= $editing ? 'Edit Program' : 'Add Program' ?></h3>
      <button class="modal-close" onclick="closeModal('prog-modal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" required value="<?= h($editing['title'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Label <span style="font-weight:400;color:var(--ink-light);">(e.g. Annual Program)</span></label>
            <input type="text" name="label" class="form-control" value="<?= h($editing['label'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= h($editing['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">When is it Held?</label>
            <textarea name="when" class="form-control" rows="2"><?= h($editing['when'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Who Should Attend?</label>
            <textarea name="who" class="form-control" rows="2"><?= h($editing['who'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">What's Covered?</label>
            <textarea name="what" class="form-control" rows="2"><?= h($editing['what'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Contact / Schedule Info</label>
            <textarea name="contact" class="form-control" rows="2"><?= h($editing['contact'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Photos <span style="font-weight:400;color:var(--ink-light);">(multiple allowed)</span></label>
          <div class="upload-zone">
            <i class="fa-solid fa-images"></i>
            <p>Click or drag & drop photos</p>
            <small>JPG, PNG, WEBP — max 5 MB each</small>
            <input type="file" name="photos[]" accept="image/*" multiple style="display:none;">
          </div>
          <div style="margin-top:8px;display:flex;gap:8px;align-items:center;">
            <button type="button" class="btn btn-outline btn-sm" onclick="openMediaPicker('__prog_pick__',null,progPickCallback)">
              <i class="fa-solid fa-photo-film"></i> Pick from Media Library
            </button>
            <span style="font-size:0.74rem;color:var(--ink-light);">Adds to photo list below</span>
          </div>
          <div id="prog-picked" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
          <?php if (!empty($editing['photos'])): ?>
          <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;" id="existing-photos">
            <?php foreach ($editing['photos'] as $ph): ?>
            <div style="position:relative;display:inline-block;">
              <img src="../<?= h($ph) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
              <label style="position:absolute;top:-6px;right:-6px;background:#dc2626;color:#fff;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.65rem;">
                <input type="checkbox" name="remove_photos[]" value="<?= h($ph) ?>" style="display:none;" onchange="this.closest('div').style.opacity=this.checked?'0.3':'1'">
                ×
              </label>
            </div>
            <?php endforeach; ?>
          </div>
          <p style="font-size:0.72rem;color:var(--ink-light);margin-top:6px;">Click × on existing photos to remove them on save.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('prog-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Save Changes' : 'Add Program' ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) {
  document.getElementById(id).classList.remove('show');
  if (window.location.search.includes('edit=')) window.location.href = 'programs.php';
}
document.getElementById('prog-modal').addEventListener('click', function(e){ if(e.target===this) closeModal('prog-modal'); });
document.querySelectorAll('#existing-photos label').forEach(lbl => {
  lbl.addEventListener('click', e => {
    const cb = lbl.querySelector('input');
    cb.checked = !cb.checked;
    lbl.closest('div').style.opacity = cb.checked ? '0.3' : '1';
  });
});

// Media picker callback for programs — appends a hidden input + thumbnail
function progPickCallback(url, type) {
  if (type !== 'image') return;
  const c = document.getElementById('prog-picked');
  const w = document.createElement('div');
  w.style.cssText = 'position:relative;display:inline-block;';
  w.innerHTML = `<img src="../${url}" style="width:80px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
    <input type="hidden" name="picked_photos[]" value="${url}">
    <button type="button" onclick="this.parentElement.remove()"
      style="position:absolute;top:-6px;right:-6px;background:#dc2626;color:#fff;width:20px;height:20px;border-radius:50%;border:none;cursor:pointer;font-size:0.65rem;">×</button>`;
  c.appendChild(w);
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
