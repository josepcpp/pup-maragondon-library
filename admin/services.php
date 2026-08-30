<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$services = cms_load('services.json');
$editing  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: services.php'); exit; }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $services = array_values(array_filter($services, fn($s) => $s['id'] !== $id));
        cms_save_flash('services.json', $services, 'Service deleted.');
        header('Location: services.php'); exit;
    }

    if (in_array($action, ['add','edit'])) {
        $id     = (int)($_POST['id'] ?? 0);
        $image  = trim($_POST['image'] ?? '');

        if (!empty($_FILES['image_file']['name'])) {
            $up = cms_upload_file($_FILES['image_file'], 'services');
            if ($up['ok']) $image = $up['path'];
            else { flash_set('error', $up['error']); header('Location: services.php'); exit; }
        }

        // Build items array from parallel arrays
        $item_icons  = $_POST['item_icon']  ?? [];
        $item_titles = $_POST['item_title'] ?? [];
        $item_descs  = $_POST['item_desc']  ?? [];
        $items = [];
        foreach ($item_titles as $k => $t) {
            $t = trim($t);
            if ($t === '') continue;
            $items[] = [
                'icon'        => trim($item_icons[$k]  ?? ''),
                'title'       => $t,
                'description' => trim($item_descs[$k] ?? ''),
            ];
        }

        $svc = [
            'tab_id'      => preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim($_POST['tab_id'] ?? ''))),
            'icon'        => trim($_POST['icon'] ?? ''),
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image'       => $image,
            'items'       => $items,
        ];
        if (!$svc['title']) { flash_set('error','Title is required.'); header('Location: services.php'); exit; }

        if ($action === 'add') {
            $svc['id'] = cms_next_id($services);
            $services[] = $svc;
            flash_set('success','Service added.');
        } else {
            foreach ($services as &$s) {
                if ($s['id'] === $id) { $svc['id'] = $id; $s = $svc; break; }
            } unset($s);
            flash_set('success','Service updated.');
        }
        cms_save_checked('services.json', $services);
        header('Location: services.php'); exit;
    }

    if ($action === 'reorder') {
        $order = array_map('intval', $_POST['order'] ?? []);
        $indexed = [];
        foreach ($services as $s) $indexed[$s['id']] = $s;
        $reordered = [];
        foreach ($order as $oid) { if (isset($indexed[$oid])) $reordered[] = $indexed[$oid]; }
        cms_save_flash('services.json', $reordered, 'Order saved.');
        header('Location: services.php'); exit;
    }
}

if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($services as $s) { if ($s['id'] === $eid) { $editing = $s; break; } }
}

$page_title = 'Services';
$page_icon  = 'fa-solid fa-hands-helping';
$active_nav = 'svc';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Services</h1>
    <p>Manage the service tabs shown on the Services page.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('svc-modal')">
    <i class="fa-solid fa-plus"></i> Add Service
  </button>
</div>

<div class="card">
  <?php if (empty($services)): ?>
  <div class="empty-state"><i class="fa-solid fa-hands-helping"></i><p>No services yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th style="width:60px;">Image</th><th>Title</th><th>Tab ID</th><th>Items</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($services as $svc): ?>
        <tr>
          <td>
            <?php if (!empty($svc['image'])): ?>
              <img src="../<?= h($svc['image']) ?>" class="thumb" alt="">
            <?php else: ?>
              <div class="thumb-placeholder"><i class="fa-regular fa-image"></i></div>
            <?php endif; ?>
          </td>
          <td>
            <strong><i class="<?= h($svc['icon'] ?? '') ?>" style="color:var(--maroon);margin-right:6px;"></i><?= h($svc['title']) ?></strong>
            <div style="font-size:0.75rem;color:var(--ink-light);margin-top:2px;"><?= h(mb_strimwidth($svc['description'] ?? '', 0, 80, '…')) ?></div>
          </td>
          <td><code style="font-size:0.78rem;"><?= h($svc['tab_id'] ?? '') ?></code></td>
          <td><span class="badge badge-maroon"><?= count($svc['items'] ?? []) ?> items</span></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $svc['id'] ?>" class="btn btn-outline btn-sm btn-icon-only" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm btn-icon-only"
                        data-confirm="Delete '<?= h($svc['title']) ?>'?" title="Delete">
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
<div class="modal-backdrop <?= $editing ? 'show' : '' ?>" id="svc-modal" style="--modal-w:760px;">
  <div class="modal" style="max-width:760px;">
    <div class="modal-header">
      <h3><?= $editing ? 'Edit Service' : 'Add Service' ?></h3>
      <button class="modal-close" onclick="closeSvcModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body" style="max-height:75vh;overflow-y:auto;">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= h($editing['title'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Tab ID <span class="req">*</span></label>
            <input type="text" name="tab_id" class="form-control" value="<?= h($editing['tab_id'] ?? '') ?>"
                   placeholder="e.g. circulation" required>
            <small class="form-hint">Lowercase, no spaces.</small>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Icon class</label>
          <input type="text" name="icon" class="form-control" value="<?= h($editing['icon'] ?? '') ?>"
                 placeholder="e.g. fa-solid fa-rotate">
          <small class="form-hint">Font Awesome class string.</small>
        </div>

        <div class="form-group">
          <label class="form-label">Section Description</label>
          <textarea name="description" class="form-control" rows="3"><?= h($editing['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Section Image</label>
          <div class="upload-zone">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <p>Click or drag &amp; drop</p>
            <small>JPG, PNG, WEBP — auto-cropped to 720×480 px</small>
            <input type="file" name="image_file" accept="image/*" style="display:none;">
            <?php if (!empty($editing['image'])): ?>
              <img src="../<?= h($editing['image']) ?>" class="upload-preview" style="display:block;">
            <?php else: ?>
              <img class="upload-preview">
            <?php endif; ?>
          </div>
          <div class="media-url-row" style="margin-top:8px;">
            <input type="text" name="image" id="svc-img-url" class="form-control"
                   placeholder="Or pick from media library…" value="<?= h($editing['image'] ?? '') ?>">
            <button type="button" class="btn btn-outline btn-sm" onclick="openMediaPicker('svc-img-url','svc-img-preview')">
              <i class="fa-solid fa-photo-film"></i> Browse
            </button>
          </div>
          <img id="svc-img-preview" src="<?= !empty($editing['image']) ? '../'.h($editing['image']) : '' ?>"
               style="<?= !empty($editing['image']) ? 'display:block;' : 'display:none;' ?>margin-top:8px;max-height:80px;border-radius:var(--radius);">
        </div>

        <!-- Service Items -->
        <div style="border-top:1px solid var(--border);margin:16px 0 12px;padding-top:12px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <strong style="font-size:0.9rem;">Service Items</strong>
            <button type="button" class="btn btn-outline btn-sm" onclick="addSvcItem()">
              <i class="fa-solid fa-plus"></i> Add item
            </button>
          </div>
          <div id="svc-items">
            <?php foreach (($editing['items'] ?? []) as $item): ?>
            <div class="svc-item-row">
              <div class="form-row" style="margin-bottom:4px;">
                <div class="form-group" style="flex:0 0 180px;">
                  <input type="text" name="item_icon[]" class="form-control" value="<?= h($item['icon'] ?? '') ?>"
                         placeholder="fa-solid fa-star">
                </div>
                <div class="form-group">
                  <input type="text" name="item_title[]" class="form-control" value="<?= h($item['title'] ?? '') ?>"
                         placeholder="Item title" required>
                </div>
                <button type="button" class="btn btn-danger btn-sm btn-icon-only" style="margin-top:0;align-self:center;"
                        onclick="this.closest('.svc-item-row').remove()">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
              <div class="form-group" style="margin-bottom:10px;">
                <textarea name="item_desc[]" class="form-control" rows="2"
                          placeholder="Item description"><?= h($item['description'] ?? '') ?></textarea>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeSvcModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Service</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeSvcModal() {
  document.getElementById('svc-modal').classList.remove('show');
  if (window.location.search.includes('edit=')) history.replaceState(null,'','services.php');
}
document.getElementById('svc-modal').addEventListener('click', function(e) { if (e.target===this) closeSvcModal(); });

function addSvcItem() {
  const c = document.getElementById('svc-items');
  const d = document.createElement('div');
  d.className = 'svc-item-row';
  d.innerHTML = `
    <div class="form-row" style="margin-bottom:4px;">
      <div class="form-group" style="flex:0 0 180px;">
        <input type="text" name="item_icon[]" class="form-control" placeholder="fa-solid fa-star">
      </div>
      <div class="form-group">
        <input type="text" name="item_title[]" class="form-control" placeholder="Item title">
      </div>
      <button type="button" class="btn btn-danger btn-sm btn-icon-only" style="align-self:center;"
              onclick="this.closest('.svc-item-row').remove()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="form-group" style="margin-bottom:10px;">
      <textarea name="item_desc[]" class="form-control" rows="2" placeholder="Item description"></textarea>
    </div>`;
  c.appendChild(d);
  d.querySelector('input').focus();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
