  </div><!-- /.admin-content -->
</main><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- ══════════════════════════════════════════
     MEDIA PICKER MODAL (shared across all admin pages)
════════════════════════════════════════════ -->
<div class="modal-backdrop" id="media-picker-modal" style="z-index:9100;">
  <div class="modal" style="max-width:900px;width:95vw;">
    <div class="modal-header">
      <h3><i class="fa-solid fa-photo-film"></i> Media Library</h3>
      <button class="modal-close" onclick="closeMediaPicker()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <input type="text" id="mp-search" class="form-control" placeholder="Search files…" style="max-width:240px;" oninput="mpFilter()">
      <select id="mp-type" class="form-control" style="max-width:140px;" onchange="mpFilter()">
        <option value="">All types</option>
        <option value="image">Images</option>
        <option value="video">Videos</option>
      </select>
      <select id="mp-folder" class="form-control" style="max-width:160px;" onchange="mpFilter()">
        <option value="">All folders</option>
      </select>
      <span id="mp-count" style="font-size:0.78rem;color:var(--ink-light);margin-left:auto;"></span>
    </div>
    <div id="mp-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;padding:16px 20px;max-height:55vh;overflow-y:auto;">
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--ink-light);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;"></i><br>Loading media…
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeMediaPicker()">Cancel</button>
    </div>
  </div>
</div>

<script>
// ── Sidebar toggle ──────────────────────────────────────────────
const sidebarToggle  = document.getElementById('sidebar-toggle');
const adminSidebar   = document.getElementById('admin-sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
if (sidebarToggle) {
  sidebarToggle.addEventListener('click', () => {
    adminSidebar.classList.toggle('open');
    sidebarOverlay.classList.toggle('show');
  });
  sidebarOverlay.addEventListener('click', () => {
    adminSidebar.classList.remove('open');
    sidebarOverlay.classList.remove('show');
  });
}

// ── Auto-dismiss flash messages ─────────────────────────────────
document.querySelectorAll('.flash').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity 0.5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 4000);
});

// ── Upload zone drag-and-drop + preview ────────────────────────
function initUploadZones() {
  document.querySelectorAll('.upload-zone:not([data-init])').forEach(zone => {
    zone.dataset.init = '1';
    const input   = zone.querySelector('input[type=file]');
    const preview = zone.querySelector('.upload-preview');
    if (!input) return;

    zone.addEventListener('click', e => { if (e.target !== input) input.click(); });

    ['dragenter','dragover'].forEach(ev => zone.addEventListener(ev, e => {
      e.preventDefault(); zone.classList.add('drag-over');
    }));
    ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, () => {
      zone.classList.remove('drag-over');
    }));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      }
    });

    input.addEventListener('change', () => {
      if (!input.files[0] || !preview) return;
      const file = input.files[0];
      if (file.type.startsWith('video/')) {
        preview.style.display = 'none';
        const vp = zone.querySelector('.video-preview') || (() => {
          const v = document.createElement('div');
          v.className = 'video-preview';
          v.style.cssText = 'padding:12px;text-align:center;color:var(--maroon);font-size:0.85rem;';
          zone.appendChild(v);
          return v;
        })();
        vp.innerHTML = '<i class="fa-solid fa-film" style="font-size:2rem;display:block;margin-bottom:6px;"></i>' + file.name;
      } else {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  });
}
initUploadZones();

// ── Delete confirmation ─────────────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  });
});

// ── Media Picker ────────────────────────────────────────────────
let _mpFiles    = [];
let _mpLoading  = false;
let _mpTarget   = null; // { inputEl, previewEl, callback }

async function openMediaPicker(inputId, previewId, callback) {
  _mpTarget = {
    input:    inputId && inputId !== '__prog_pick__' ? document.getElementById(inputId) : null,
    preview:  previewId ? document.getElementById(previewId) : null,
    callback: callback || null,
  };
  document.getElementById('media-picker-modal').classList.add('show');

  if (_mpFiles.length === 0 && !_mpLoading) await mpLoad();
  else if (_mpFiles.length > 0) mpRender(_mpFiles);
}

function closeMediaPicker() {
  document.getElementById('media-picker-modal').classList.remove('show');
}

async function mpLoad() {
  _mpLoading = true;
  try {
    const res  = await fetch('../api/media_list.php');
    _mpFiles   = await res.json();
    // Populate folder filter
    const folders = [...new Set(_mpFiles.map(f => f.folder))].sort();
    const sel = document.getElementById('mp-folder');
    sel.innerHTML = '<option value="">All folders</option>';
    folders.forEach(f => {
      const o = document.createElement('option');
      o.value = f; o.textContent = f;
      sel.appendChild(o);
    });
    mpRender(_mpFiles);
  } catch(e) {
    const msg = document.createElement('div');
    msg.style.cssText = 'grid-column:1/-1;text-align:center;padding:40px;color:var(--ink-light);';
    msg.textContent = 'Failed to load media.';
    const grid = document.getElementById('mp-grid');
    grid.innerHTML = '';
    grid.appendChild(msg);
  } finally {
    _mpLoading = false;
  }
}

function mpFilter() {
  const q   = document.getElementById('mp-search').value.toLowerCase();
  const typ = document.getElementById('mp-type').value;
  const fld = document.getElementById('mp-folder').value;
  const filtered = _mpFiles.filter(f =>
    (!q   || f.name.toLowerCase().includes(q)) &&
    (!typ || f.type === typ) &&
    (!fld || f.folder === fld)
  );
  mpRender(filtered);
}

function mpEsc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function mpRender(files) {
  const grid = document.getElementById('mp-grid');
  document.getElementById('mp-count').textContent = files.length + ' file' + (files.length !== 1 ? 's' : '');
  grid.innerHTML = '';

  if (!files.length) {
    const msg = document.createElement('div');
    msg.style.cssText = 'grid-column:1/-1;text-align:center;padding:40px;color:var(--ink-light);';
    msg.textContent = 'No files found.';
    grid.appendChild(msg);
    return;
  }

  files.forEach(f => {
    const size = f.size > 1048576 ? (f.size/1048576).toFixed(1)+'MB' : Math.round(f.size/1024)+'KB';
    const item = document.createElement('div');
    item.className = 'mp-item';
    item.title = f.name;
    item.dataset.url  = f.url;
    item.dataset.type = f.type;
    item.addEventListener('click', () => mpSelect(f.url, f.type));

    if (f.type === 'image') {
      const img = document.createElement('img');
      img.src = '../' + mpEsc(f.url);
      img.style.cssText = 'width:100%;height:80px;object-fit:cover;border-radius:4px;display:block;';
      img.loading = 'lazy';
      img.alt = f.name;
      item.appendChild(img);
    } else {
      const icon = document.createElement('div');
      icon.style.cssText = 'height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--parchment);border-radius:4px;gap:4px;';
      const i = document.createElement('i');
      i.className = 'fa-solid fa-film';
      i.style.cssText = 'font-size:1.5rem;color:var(--maroon);';
      const span = document.createElement('span');
      span.style.cssText = 'font-size:0.6rem;color:var(--ink-light);';
      span.textContent = f.folder;
      icon.appendChild(i);
      icon.appendChild(span);
      item.appendChild(icon);
    }

    const nameDiv = document.createElement('div');
    nameDiv.style.cssText = 'padding:4px 2px;font-size:0.62rem;color:var(--ink-light);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;';
    nameDiv.textContent = f.name;

    const sizeDiv = document.createElement('div');
    sizeDiv.style.cssText = 'font-size:0.58rem;color:var(--ink-light);';
    sizeDiv.textContent = size;

    item.appendChild(nameDiv);
    item.appendChild(sizeDiv);
    grid.appendChild(item);
  });
}

function mpSelect(url, type) {
  if (!_mpTarget) return;
  if (_mpTarget.callback) {
    _mpTarget.callback(url, type);
    closeMediaPicker();
    return;
  }
  if (_mpTarget.input) _mpTarget.input.value = url;
  if (_mpTarget.preview) {
    if (type === 'image') {
      _mpTarget.preview.src = '../' + url;
      _mpTarget.preview.style.display = 'block';
    } else {
      _mpTarget.preview.style.display = 'none';
    }
  }
  closeMediaPicker();
}

document.getElementById('media-picker-modal').addEventListener('click', function(e) {
  if (e.target === this) closeMediaPicker();
});
</script>
<style>
.mp-item {
  cursor: pointer;
  border: 2px solid transparent;
  border-radius: var(--radius);
  padding: 4px;
  transition: border-color 0.2s, background 0.2s;
}
.mp-item:hover { border-color: var(--maroon); background: rgba(128,0,0,0.05); }
.media-url-row {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-top: 8px;
  flex-wrap: wrap;
}
.media-url-row .form-control { flex: 1; min-width: 0; }
</style>
</body>
</html>
