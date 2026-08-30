<?php
$page_title = 'Library Holdings';
$page_icon  = 'fa-solid fa-book';
$active_nav = 'holdings';
require_once __DIR__ . '/includes/header.php';

$holdings = cms_load('holdings.json');
$stats     = $holdings['stats']      ?? [];
$cats      = $holdings['categories'] ?? [];
$token     = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: holdings.php'); exit; }
    $action = $_POST['action'] ?? '';

    if ($action === 'save_stats') {
        $new_stats = [];
        $targets  = array_values($_POST['stat_target'] ?? []);
        $suffixes = array_values($_POST['stat_suffix'] ?? []);
        $slabels  = array_values($_POST['stat_label']  ?? []);
        $count    = min(count($targets), count($suffixes), count($slabels));
        for ($i = 0; $i < $count; $i++) {
            $new_stats[] = [
                'target' => max(0, (int)$targets[$i]),
                'suffix' => trim($suffixes[$i]),
                'label'  => trim($slabels[$i]),
            ];
        }
        $holdings['stats'] = $new_stats;
        cms_save_flash('holdings.json', $holdings, 'Stats updated.');
        header('Location: holdings.php'); exit;
    }

    if ($action === 'save_cats') {
        $new_cats = [];
        $ctitles = array_values($_POST['cat_title'] ?? []);
        $cnums   = array_values($_POST['cat_num']   ?? []);
        $cicons  = array_values($_POST['cat_icon']  ?? []);
        $cdescs  = array_values($_POST['cat_desc']  ?? []);
        $ctags   = array_values($_POST['cat_tag']   ?? []);
        $count   = min(count($ctitles), count($cnums), count($cicons), count($cdescs), count($ctags));
        for ($i = 0; $i < $count; $i++) {
            $new_cats[] = [
                'num'         => max(1, (int)($cnums[$i] ?: $i + 1)),
                'icon'        => trim($cicons[$i]),
                'title'       => trim($ctitles[$i]),
                'description' => trim($cdescs[$i]),
                'tag'         => trim($ctags[$i]),
            ];
        }
        $holdings['categories'] = $new_cats;
        cms_save_flash('holdings.json', $holdings, 'Categories updated.');
        header('Location: holdings.php'); exit;
    }
}
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Library Holdings</h1>
    <p>Edit the stats counters and holding categories shown on the Holdings page.</p>
  </div>
</div>

<!-- Stats -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header">
    <h2><i class="fa-solid fa-chart-bar"></i> Statistics Strip</h2>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="save_stats">
      <div id="stats-list">
        <?php foreach ($stats as $i => $s): ?>
        <div class="stat-row form-row" style="align-items:flex-end;margin-bottom:12px;" data-index="<?= $i ?>">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Target Number</label>
            <input type="number" name="stat_target[]" class="form-control" value="<?= (int)$s['target'] ?>" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Suffix</label>
            <input type="text" name="stat_suffix[]" class="form-control" value="<?= h($s['suffix']) ?>" placeholder="e.g. +">
          </div>
          <div class="form-group" style="margin-bottom:0;flex:2;">
            <label class="form-label">Label</label>
            <input type="text" name="stat_label[]" class="form-control" value="<?= h($s['label']) ?>" required>
          </div>
          <div style="margin-bottom:0;">
            <button type="button" class="btn btn-danger btn-sm btn-icon-only remove-stat" title="Remove">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">
        <button type="button" id="add-stat" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-plus"></i> Add Stat
        </button>
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-floppy-disk"></i> Save Stats
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Categories -->
<div class="card">
  <div class="card-header">
    <h2><i class="fa-solid fa-layer-group"></i> Holding Categories</h2>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="save_cats">
      <div id="cats-list">
        <?php foreach ($cats as $i => $c): ?>
        <div class="card" style="margin-bottom:16px;border-color:var(--border);" data-cat-index="<?= $i ?>">
          <div class="card-body">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Number</label>
                <input type="number" name="cat_num[]" class="form-control" value="<?= (int)$c['num'] ?>">
              </div>
              <div class="form-group">
                <label class="form-label">FA Icon class</label>
                <input type="text" name="cat_icon[]" class="form-control"
                       value="<?= h($c['icon']) ?>" placeholder="fa-solid fa-book">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Title <span class="req">*</span></label>
                <input type="text" name="cat_title[]" class="form-control" value="<?= h($c['title']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Badge Tag</label>
                <input type="text" name="cat_tag[]" class="form-control" value="<?= h($c['tag']) ?>" placeholder="General Lending">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea name="cat_desc[]" class="form-control" rows="3"><?= h($c['description']) ?></textarea>
            </div>
            <div style="text-align:right;">
              <button type="button" class="btn btn-danger btn-sm remove-cat"><i class="fa-solid fa-trash"></i> Remove</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">
        <button type="button" id="add-cat" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> Add Category</button>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Save Categories</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('add-stat').addEventListener('click',()=>{
  const list = document.getElementById('stats-list');
  const row = document.createElement('div');
  row.className='stat-row form-row';
  row.style='align-items:flex-end;margin-bottom:12px;';
  row.innerHTML=`
    <div class="form-group" style="margin-bottom:0;"><label class="form-label">Target Number</label><input type="number" name="stat_target[]" class="form-control" required></div>
    <div class="form-group" style="margin-bottom:0;"><label class="form-label">Suffix</label><input type="text" name="stat_suffix[]" class="form-control" placeholder="e.g. +"></div>
    <div class="form-group" style="margin-bottom:0;flex:2;"><label class="form-label">Label</label><input type="text" name="stat_label[]" class="form-control" required></div>
    <div><button type="button" class="btn btn-danger btn-sm btn-icon-only remove-stat"><i class="fa-solid fa-trash"></i></button></div>`;
  list.appendChild(row);
});
document.addEventListener('click',e=>{
  if(e.target.closest('.remove-stat')) e.target.closest('.stat-row').remove();
  if(e.target.closest('.remove-cat')) e.target.closest('[data-cat-index]').remove();
});
document.getElementById('add-cat').addEventListener('click',()=>{
  const list=document.getElementById('cats-list');
  const idx=list.children.length;
  const div=document.createElement('div');
  div.className='card'; div.style='margin-bottom:16px;border-color:var(--border);';
  div.setAttribute('data-cat-index',idx);
  div.innerHTML=`<div class="card-body">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Number</label><input type="number" name="cat_num[]" class="form-control" value="${idx+1}"></div>
      <div class="form-group"><label class="form-label">FA Icon class</label><input type="text" name="cat_icon[]" class="form-control" placeholder="fa-solid fa-book"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Title <span class="req">*</span></label><input type="text" name="cat_title[]" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Badge Tag</label><input type="text" name="cat_tag[]" class="form-control"></div>
    </div>
    <div class="form-group"><label class="form-label">Description</label><textarea name="cat_desc[]" class="form-control" rows="3"></textarea></div>
    <div style="text-align:right;"><button type="button" class="btn btn-danger btn-sm remove-cat"><i class="fa-solid fa-trash"></i> Remove</button></div>
  </div>`;
  list.appendChild(div);
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
