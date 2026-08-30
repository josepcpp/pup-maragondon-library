<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$data  = cms_load('about.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: about.php'); exit; }
    $action = $_POST['action'] ?? '';

    // ── Save VGMO ──────────────────────────────────────────────
    if ($action === 'save_vgmo') {
        $data['vgmo']['mission']    = trim($_POST['mission'] ?? '');
        $data['vgmo']['vision']     = trim($_POST['vision'] ?? '');
        $data['vgmo']['goals']      = array_values(array_filter(array_map('trim', $_POST['goals'] ?? []), 'strlen'));
        $data['vgmo']['objectives'] = array_values(array_filter(array_map('trim', $_POST['objectives'] ?? []), 'strlen'));
        cms_save('about.json', $data);
        flash_set('success','VGMO content updated.');
        header('Location: about.php#vgmo'); exit;
    }

    // ── Save Hours ─────────────────────────────────────────────
    if ($action === 'save_hours') {
        $data['hours_note'] = trim($_POST['hours_note'] ?? '');
        $schedule = [];
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $nums = [1,2,3,4,5,6,0];
        foreach ($days as $i => $day) {
            $schedule[] = [
                'day'     => $day,
                'day_num' => $nums[$i],
                'time'    => trim($_POST['time_' . $i] ?? 'Closed'),
                'open'    => isset($_POST['open_' . $i]),
            ];
        }
        $data['schedule'] = $schedule;
        cms_save('about.json', $data);
        flash_set('success','Library hours updated.');
        header('Location: about.php#hours'); exit;
    }

    // ── Timeline CRUD ──────────────────────────────────────────
    if ($action === 'delete_event') {
        $id = (int)($_POST['id'] ?? 0);
        $data['timeline'] = array_values(array_filter($data['timeline'] ?? [], fn($e) => $e['id'] !== $id));
        cms_save('about.json', $data);
        flash_set('success','Timeline event deleted.');
        header('Location: about.php#timeline'); exit;
    }

    if (in_array($action, ['add_event','edit_event'])) {
        $id    = (int)($_POST['id'] ?? 0);
        $event = [
            'year'        => trim($_POST['year'] ?? ''),
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'detail'      => trim($_POST['detail'] ?? ''),
        ];
        if (!$event['year'] || !$event['title']) { flash_set('error','Year and title are required.'); header('Location: about.php'); exit; }

        $tl = $data['timeline'] ?? [];
        if ($action === 'add_event') {
            $event['id'] = cms_next_id($tl);
            $tl[] = $event;
        } else {
            foreach ($tl as &$e) { if ($e['id'] === $id) { $event['id'] = $id; $e = $event; break; } } unset($e);
        }
        $data['timeline'] = $tl;
        cms_save('about.json', $data);
        flash_set('success','Timeline event saved.');
        header('Location: about.php#timeline'); exit;
    }
}

$vgmo     = $data['vgmo']     ?? [];
$schedule = $data['schedule'] ?? [];
$timeline = $data['timeline'] ?? [];
$editing_event = null;
if (isset($_GET['edit_event'])) {
    $eid = (int)$_GET['edit_event'];
    foreach ($timeline as $e) { if ($e['id'] === $eid) { $editing_event = $e; break; } }
}

$page_title = 'About Page';
$page_icon  = 'fa-solid fa-circle-info';
$active_nav = 'about';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<!-- VGMO -->
<div class="page-header" id="vgmo">
  <div class="page-header-text">
    <h1>About — Vision, Mission, Goals &amp; Objectives</h1>
    <p>Edit the four VGMO cards shown on the About page.</p>
  </div>
</div>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
  <input type="hidden" name="action" value="save_vgmo">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <div class="card" style="padding:20px;">
      <h3 style="margin-bottom:12px;font-size:1rem;">Mission Statement</h3>
      <textarea name="mission" class="form-control" rows="6"><?= h($vgmo['mission'] ?? '') ?></textarea>
    </div>
    <div class="card" style="padding:20px;">
      <h3 style="margin-bottom:12px;font-size:1rem;">Our Vision</h3>
      <textarea name="vision" class="form-control" rows="6"><?= h($vgmo['vision'] ?? '') ?></textarea>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <div class="card" style="padding:20px;">
      <h3 style="margin-bottom:12px;font-size:1rem;">Goals <small style="color:var(--ink-light);font-weight:400;">(one per line field)</small></h3>
      <div id="goals-list">
        <?php foreach (($vgmo['goals'] ?? []) as $g): ?>
        <div style="display:flex;gap:6px;margin-bottom:8px;">
          <input type="text" name="goals[]" class="form-control" value="<?= h($g) ?>">
          <button type="button" class="btn btn-danger btn-sm btn-icon-only" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-outline btn-sm" style="margin-top:6px;" onclick="addListItem('goals-list','goals')"><i class="fa-solid fa-plus"></i> Add goal</button>
    </div>
    <div class="card" style="padding:20px;">
      <h3 style="margin-bottom:12px;font-size:1rem;">Objectives</h3>
      <div id="obj-list">
        <?php foreach (($vgmo['objectives'] ?? []) as $o): ?>
        <div style="display:flex;gap:6px;margin-bottom:8px;">
          <input type="text" name="objectives[]" class="form-control" value="<?= h($o) ?>">
          <button type="button" class="btn btn-danger btn-sm btn-icon-only" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-outline btn-sm" style="margin-top:6px;" onclick="addListItem('obj-list','objectives')"><i class="fa-solid fa-plus"></i> Add objective</button>
    </div>
  </div>

  <div style="text-align:right;margin-bottom:40px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save VGMO</button>
  </div>
</form>

<!-- LIBRARY HOURS -->
<div class="page-header" id="hours">
  <div class="page-header-text">
    <h1>Library Hours</h1>
    <p>Edit the weekly schedule shown on the About page.</p>
  </div>
</div>

<form method="POST">
  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
  <input type="hidden" name="action" value="save_hours">
  <div class="card" style="padding:20px;margin-bottom:20px;">
    <div class="form-group" style="margin-bottom:16px;">
      <label class="form-label">Notice / Note</label>
      <input type="text" name="hours_note" class="form-control" value="<?= h($data['hours_note'] ?? '') ?>" placeholder="e.g. No Noon Break policy">
    </div>
    <table class="cms-table">
      <thead><tr><th>Day</th><th>Open?</th><th>Time / Status</th></tr></thead>
      <tbody>
        <?php
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        foreach ($days as $i => $day):
          $row = $schedule[$i] ?? ['open' => ($i < 6), 'time' => ($i < 5 ? '8:00 AM – 8:00 PM' : ($i === 5 ? '8:00 AM – 5:00 PM' : 'Closed'))];
        ?>
        <tr>
          <td><strong><?= h($day) ?></strong></td>
          <td>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
              <input type="checkbox" name="open_<?= $i ?>" <?= !empty($row['open']) ? 'checked' : '' ?>>
              Open
            </label>
          </td>
          <td><input type="text" name="time_<?= $i ?>" class="form-control" value="<?= h($row['time'] ?? '') ?>" placeholder="e.g. 8:00 AM – 5:00 PM" style="max-width:220px;"></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="text-align:right;margin-bottom:40px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Hours</button>
  </div>
</form>

<!-- TIMELINE -->
<div class="page-header" id="timeline">
  <div class="page-header-text">
    <h1>History Timeline</h1>
    <p>Manage the milestone events shown on the About page.</p>
  </div>
  <button class="btn btn-primary" onclick="openModal('event-modal')">
    <i class="fa-solid fa-plus"></i> Add Event
  </button>
</div>

<div class="card">
  <?php if (empty($timeline)): ?>
  <div class="empty-state"><i class="fa-solid fa-timeline"></i><p>No timeline events yet.</p></div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="cms-table">
      <thead><tr><th>Year</th><th>Title</th><th>Description</th><th style="width:110px;">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($timeline as $ev): ?>
        <tr>
          <td><strong><?= h($ev['year'] ?? '') ?></strong></td>
          <td><?= h($ev['title'] ?? '') ?></td>
          <td style="color:var(--ink-light);font-size:0.82rem;"><?= h(mb_strimwidth($ev['description'] ?? '', 0, 80, '…')) ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?edit_event=<?= $ev['id'] ?>#timeline" class="btn btn-outline btn-sm btn-icon-only" title="Edit"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete_event">
                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm btn-icon-only" data-confirm="Delete this event?" title="Delete"><i class="fa-solid fa-trash"></i></button>
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

<!-- Event modal -->
<div class="modal-backdrop <?= $editing_event ? 'show' : '' ?>" id="event-modal">
  <div class="modal">
    <div class="modal-header">
      <h3><?= $editing_event ? 'Edit Event' : 'Add Timeline Event' ?></h3>
      <button class="modal-close" onclick="closeModal('event-modal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="<?= $editing_event ? 'edit_event' : 'add_event' ?>">
        <?php if ($editing_event): ?><input type="hidden" name="id" value="<?= $editing_event['id'] ?>"><?php endif; ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Year <span class="req">*</span></label>
            <input type="text" name="year" class="form-control" placeholder="e.g. 1987" value="<?= h($editing_event['year'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="Milestone title" value="<?= h($editing_event['title'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Short Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Brief summary shown on the card"><?= h($editing_event['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Expanded Detail</label>
          <textarea name="detail" class="form-control" rows="3" placeholder="Full detail shown when card is clicked"><?= h($editing_event['detail'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('event-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Event</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) {
  document.getElementById(id).classList.remove('show');
  if (window.location.search.includes('edit_event=')) history.replaceState(null,'','about.php#timeline');
}
document.getElementById('event-modal').addEventListener('click', function(e) { if (e.target === this) closeModal('event-modal'); });

function addListItem(containerId, name) {
  const c = document.getElementById(containerId);
  const d = document.createElement('div');
  d.style.cssText = 'display:flex;gap:6px;margin-bottom:8px;';
  d.innerHTML = `<input type="text" name="${name}[]" class="form-control" placeholder="Enter item…">
    <button type="button" class="btn btn-danger btn-sm btn-icon-only" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>`;
  c.appendChild(d);
  d.querySelector('input').focus();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
