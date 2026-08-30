<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$data  = cms_load('linkages.json');
$cards = $data['cards'] ?? [];
$editing_card = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: linkages.php'); exit; }
    $action = $_POST['action'] ?? '';

    // ── Save text sections ─────────────────────────────────────
    if ($action === 'save_text') {
        $data['intro']           = trim($_POST['intro']           ?? '');
        $data['mou_title']       = trim($_POST['mou_title']       ?? '');
        $data['mou_description'] = trim($_POST['mou_description'] ?? '');
        $data['cta_title']       = trim($_POST['cta_title']       ?? '');
        $data['cta_description'] = trim($_POST['cta_description'] ?? '');
        $data['contact_email']   = trim($_POST['contact_email']   ?? '');
        cms_save_flash('linkages.json', $data, 'Text content updated.');
        header('Location: linkages.php#text'); exit;
    }

    // ── Card CRUD ──────────────────────────────────────────────
    if ($action === 'delete_card') {
        $id = (int)($_POST['id'] ?? 0);
        $data['cards'] = array_values(array_filter($cards, fn($c) => $c['id'] !== $id));
        cms_save_flash('linkages.json', $data, 'Card deleted.');
        header('Location: linkages.php#cards'); exit;
    }

    if (in_array($action, ['add_card','edit_card'])) {
        $id   = (int)($_POST['id'] ?? 0);
        $card = [
            'icon'        => trim($_POST['icon']        ?? ''),
            'title'       => trim($_POST['title']       ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];
        if (!$card['title']) { flash_set('error','Title is required.'); header('Location: linkages.php'); exit; }

        if ($action === 'add_card') {
            $card['id'] = cms_next_id($cards);
            $data['cards'][] = $card;
            flash_set('success','Card added.');
        } else {
            foreach ($data['cards'] as &$c) {
                if ($c['id'] === $id) { $card['id'] = $id; $c = $card; break; }
            } unset($c);
            flash_set('success','Card updated.');
        }
        cms_save_checked('linkages.json', $data);
        header('Location: linkages.php#cards'); exit;
    }
}

$cards = $data['cards'] ?? [];
if (isset($_GET['edit_card'])) {
    $eid = (int)$_GET['edit_card'];
    foreach ($cards as $c) { if ($c['id'] === $eid) { $editing_card = $c; break; } }
}

$page_title = 'Linkages';
$page_icon  = 'fa-solid fa-link';
$active_nav = 'linkages';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<!-- Text Content -->
<div class="page-header" id="text">
  <div class="page-header-text">
    <h1>Linkages — Text Content</h1>
    <p>Edit the intro, MOU, and CTA paragraphs shown on the Linkages page.</p>
  </div>
</div>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
  <input type="hidden" name="action" value="save_text">

  <div class="card" style="padding:20px;margin-bottom:20px;">
    <div class="form-group" style="margin-bottom:16px;">
      <label class="form-label">Intro paragraph</label>
      <textarea name="intro" class="form-control" rows="3"><?= h($data['intro'] ?? '') ?></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <div>
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label">MOU Section Title</label>
          <input type="text" name="mou_title" class="form-control" value="<?= h($data['mou_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">MOU Description</label>
          <textarea name="mou_description" class="form-control" rows="3"><?= h($data['mou_description'] ?? '') ?></textarea>
        </div>
      </div>
      <div>
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label">CTA Section Title</label>
          <input type="text" name="cta_title" class="form-control" value="<?= h($data['cta_title'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label class="form-label">CTA Description</label>
          <textarea name="cta_description" class="form-control" rows="3"><?= h($data['cta_description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Contact Email</label>
          <input type="email" name="contact_email" class="form-control" value="<?= h($data['contact_email'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <div style="text-align:right;margin-bottom:40px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Text</button>
  </div>
</form>

<!-- Partner Cards -->
<div class="page-header" id="cards">
  <div class="page-header-text">
    <h1>Partner Cards</h1>
    <p>Manage the linkage partner cards shown on the Linkages page.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('card-modal')">
    <i class="fa-solid fa-plus"></i> Add Card
  </button>
</div>

<div class="card">
  <?php if (empty($cards)): ?>
  <div class="empty-state"><i class="fa-solid fa-link"></i><p>No partner cards yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th>Title</th><th>Description</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($cards as $card): ?>
        <tr>
          <td>
            <strong><i class="<?= h($card['icon'] ?? '') ?>" style="color:var(--maroon);margin-right:6px;"></i><?= h($card['title']) ?></strong>
          </td>
          <td style="font-size:0.78rem;color:var(--ink-light);"><?= h(mb_strimwidth($card['description'] ?? '', 0, 80, '…')) ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit_card=<?= $card['id'] ?>#cards" class="btn btn-outline btn-sm btn-icon-only" title="Edit">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete_card">
                <input type="hidden" name="id" value="<?= $card['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm btn-icon-only"
                        data-confirm="Delete '<?= h($card['title']) ?>'?" title="Delete">
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

<!-- Card Modal -->
<div class="modal-backdrop <?= $editing_card ? 'show' : '' ?>" id="card-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><?= $editing_card ? 'Edit Card' : 'Add Partner Card' ?></h3>
      <button class="modal-close" onclick="closeCardModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing_card ? 'edit_card' : 'add_card' ?>">
        <?php if ($editing_card): ?><input type="hidden" name="id" value="<?= $editing_card['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" value="<?= h($editing_card['title'] ?? '') ?>" required>
          </div>
          <div class="form-group" style="flex:0 0 200px;">
            <label class="form-label">Icon class</label>
            <input type="text" name="icon" class="form-control" value="<?= h($editing_card['icon'] ?? '') ?>"
                   placeholder="fa-solid fa-university">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= h($editing_card['description'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeCardModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Card</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeCardModal() {
  document.getElementById('card-modal').classList.remove('show');
  if (window.location.search.includes('edit_card=')) history.replaceState(null,'','linkages.php#cards');
}
document.getElementById('card-modal').addEventListener('click', function(e) { if (e.target===this) closeCardModal(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
