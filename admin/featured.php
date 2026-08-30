<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();

$featured = cms_load('featured.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash_set('error','Invalid CSRF token.'); header('Location: featured.php'); exit; }

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link        = trim($_POST['link'] ?? '#');
    $image       = trim($_POST['image'] ?? '');

    if (!$title) { flash_set('error','Title is required.'); header('Location: featured.php'); exit; }

    if (!empty($_FILES['image_file']['name'])) {
        $upload = cms_upload_file($_FILES['image_file'], 'resources');
        if ($upload['ok']) $image = $upload['path'];
        else { flash_set('error', $upload['error']); header('Location: featured.php'); exit; }
    }

    $featured = [
        'title'       => $title,
        'description' => $description,
        'link'        => $link,
        'image'       => $image,
        'updated'     => date('Y-m-d'),
    ];
    cms_save_flash('featured.json', $featured, 'Featured resource updated.');
    header('Location: featured.php'); exit;
}

$page_title = 'Featured Resource';
$page_icon  = 'fa-solid fa-bookmark';
$active_nav = 'featured';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Featured Resource</h1>
    <p>Set the spotlight resource shown on the homepage "Resource Spotlight" section.</p>
  </div>
</div>

<div class="card" style="max-width:720px;">
  <div class="card-header">
    <h2><i class="fa-solid fa-pen-to-square"></i> Edit Featured Resource</h2>
    <?php if (!empty($featured['updated'])): ?>
      <span style="font-size:0.76rem;color:var(--ink-light);">Last updated: <?= h($featured['updated']) ?></span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">

      <div class="form-group">
        <label class="form-label">Title <span class="req">*</span></label>
        <input type="text" name="title" class="form-control" required
               value="<?= h($featured['title'] ?? '') ?>" placeholder="Resource title">
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"
                  placeholder="Brief description of the featured resource..."><?= h($featured['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Link / URL</label>
        <input type="text" name="link" class="form-control"
               value="<?= h($featured['link'] ?? '#') ?>" placeholder="https://...">
        <p class="form-hint">Link users to the full resource. Use # if not applicable.</p>
      </div>

      <div class="form-group">
        <label class="form-label">Cover Image</label>
        <div class="upload-zone">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <p>Click or drag & drop an image</p>
          <small>JPG, PNG, WEBP — max 5 MB</small>
          <input type="file" name="image_file" accept="image/*" style="display:none;">
          <?php if (!empty($featured['image'])): ?>
            <img src="../<?= h($featured['image']) ?>" class="upload-preview" style="display:block;">
          <?php else: ?>
            <img class="upload-preview">
          <?php endif; ?>
        </div>
        <div class="media-url-row" style="margin-top:8px;">
          <input type="text" name="image" id="feat-img-url" class="form-control"
                 placeholder="Or pick from media library…" value="<?= h($featured['image'] ?? '') ?>">
          <button type="button" class="btn btn-outline btn-sm" onclick="openMediaPicker('feat-img-url','feat-img-prev')">
            <i class="fa-solid fa-photo-film"></i> Browse
          </button>
        </div>
        <img id="feat-img-prev" src="<?= !empty($featured['image']) ? '../'.h($featured['image']) : '' ?>"
             style="<?= !empty($featured['image']) ? 'display:block;' : 'display:none;' ?>margin-top:6px;max-height:80px;border-radius:var(--radius);">
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Save Featured Resource
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
