<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$rules   = cms_load('guidelines.json');
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: guidelines.php'); exit; }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $rules = array_values(array_filter($rules, fn($r) => $r['id'] !== $id));
        cms_save_flash('guidelines.json', $rules, 'Rule deleted.');
        header('Location: guidelines.php'); exit;
    }

    if (in_array($action, ['add','edit'])) {
        $id = (int)($_POST['id'] ?? 0);
        $items = array_values(array_filter(
            array_map('trim', $_POST['items'] ?? []),
            'strlen'
        ));
        $rule = [
            'icon'  => trim($_POST['icon']  ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'open'  => isset($_POST['open']),
            'intro' => trim($_POST['intro'] ?? ''),
            'type'  => 'list',
            'items' => $items,
        ];
        if (!$rule['title']) { flash_set('error','Title is required.'); header('Location: guidelines.php'); exit; }

        if ($action === 'add') {
            $rule['id'] = cms_next_id($rules);
            $rules[] = $rule;
            flash_set('success','Rule added.');
        } else {
            foreach ($rules as &$r) {
                if ($r['id'] === $id) { $rule['id'] = $id; $r = $rule; break; }
            } unset($r);
            flash_set('success','Rule updated.');
        }
        cms_save_checked('guidelines.json', $rules);
        header('Location: guidelines.php'); exit;
    }
}

if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($rules as $r) { if ($r['id'] === $eid) { $editing = $r; break; } }
}

$page_title = 'Guidelines';
$page_icon  = 'fa-solid fa-gavel';
$active_nav = 'guidelines';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Guidelines</h1>
    <p>Manage the library rules and guidelines accordion.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('rule-modal')">
    <i class="fa-solid fa-plus"></i> Add Rule
  </button>
</div>

<div class="card">
  <?php if (empty($rules)): ?>
  <div class="empty-state"><i class="fa-solid fa-gavel"></i><p>No guidelines yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th>Title</th><th>Intro</th><th>Items</th><th>Default</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($rules as $rule): ?>
        <tr>
          <td>
            <strong><i class="<?= h($rule['icon'] ?? '') ?>" style="color:var(--maroon);margin-right:6px;"></i><?= h($rule['title']) ?></strong>
          </td>
          <td style="font-size:0.78rem;color:var(--ink-light);"><?= h(mb_strimwidth($rule['intro'] ?? '', 0, 60, '…')) ?></td>
          <td><span class="badge badge-maroon"><?= count($rule['items'] ?? []) ?></span></td>
          <td><?= !empty($rule['open']) ? '<span class="badge" style="background:var(--green-light,#e6f4ea);color:#1a7f37;">Open</span>' : '<span style="color:var(--ink-light);font-size:0.78rem;">Closed</span>' ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit=<?= $rule['id'] ?>" class="btn btn-outline btn-sm btn-icon-only" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm btn-icon-only"
                        data-confirm="Delete '<?= h($rule['title']) ?>'?" title="Delete">
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
<div class="modal-backdrop <?= $editing ? 'show' : '' ?>" id="rule-modal">
  <div class="modal" style="max-width:680px;">
    <div class="modal-header">
      <h3><?= $editing ? 'Edit Rule' : 'Add Rule' ?></h3>
      <button class="modal-close" onclick="closeRuleModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body" style="max-height:75vh;overflow-y:auto;">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= h($editing['title'] ?? '') ?>" required>
          </div>
          <div class="form-group" style="flex:0 0 200px;">
            <label class="form-label">Icon class</label>
            <input type="text" name="icon" class="form-control" value="<?= h($editing['icon'] ?? '') ?>"
                   placeholder="fa-solid fa-gavel">
          </div>
        </div>

        <div class="form-group">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.85rem;">
            <input type="checkbox" name="open" <?= !empty($editing['open']) ? 'checked' : '' ?>>
            Start expanded (open by default in accordion)
          </label>
        </div>

        <div class="form-group">
          <label class="form-label">Intro paragraph</label>
          <textarea name="intro" class="form-control" rows="2"
                    placeholder="Optional text shown before the bullet list"><?= h($editing['intro'] ?? '') ?></textarea>
        </div>

        <div style="border-top:1px solid var(--border);margin:14px 0 10px;padding-top:12px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <strong style="font-size:0.88rem;">Bullet Items</strong>
            <button type="button" class="btn btn-outline btn-sm" onclick="addRuleItem()">
              <i class="fa-solid fa-plus"></i> Add item
            </button>
          </div>
          <div id="rule-items">
            <?php foreach (($editing['items'] ?? []) as $item): ?>
            <div style="display:flex;gap:6px;margin-bottom:6px;">
              <input type="text" name="items[]" class="form-control" value="<?= h($item) ?>">
              <button type="button" class="btn btn-danger btn-sm btn-icon-only"
                      onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeRuleModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Rule</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeRuleModal() {
  document.getElementById('rule-modal').classList.remove('show');
  if (window.location.search.includes('edit=')) history.replaceState(null,'','guidelines.php');
}
document.getElementById('rule-modal').addEventListener('click', function(e) { if (e.target===this) closeRuleModal(); });

function addRuleItem() {
  const c = document.getElementById('rule-items');
  const d = document.createElement('div');
  d.style.cssText = 'display:flex;gap:6px;margin-bottom:6px;';
  d.innerHTML = `<input type="text" name="items[]" class="form-control" placeholder="Enter rule item…">
    <button type="button" class="btn btn-danger btn-sm btn-icon-only" onclick="this.parentElement.remove()">
      <i class="fa-solid fa-xmark"></i></button>`;
  c.appendChild(d);
  d.querySelector('input').focus();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
