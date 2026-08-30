<?php
// Admin CMS configuration
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');

// Absolute paths
define('BASE_PATH',   realpath(__DIR__ . '/..'));
define('DATA_PATH',   BASE_PATH . '/data/');
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('CRED_FILE',   DATA_PATH . 'admin_credentials.json');

// Session config
define('SESSION_TIMEOUT',    7200);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME',       900);

// Allowed image types
define('ALLOWED_MIME', ['image/jpeg','image/png','image/gif','image/webp']);
define('ALLOWED_EXT',  ['jpg','jpeg','png','gif','webp']);
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);

// Allowed video types
define('VIDEO_MIME', ['video/mp4','video/webm','video/ogg','video/quicktime']);
define('VIDEO_EXT',  ['mp4','webm','ogv','ogg','mov']);
define('MAX_VIDEO_BYTES', 20 * 1024 * 1024); // 20 MB — safe default for shared hosting

// Fixed crop dimensions per upload context (width x height, 0 = no crop)
define('UPLOAD_DIMENSIONS', [
    'arrivals'  => [300, 400],   // 3:4  portrait (book cover)
    'personnel' => [400, 400],   // 1:1  square   (staff photo)
    'programs'  => [800, 450],   // 16:9 landscape (event photo)
    'resources' => [300, 300],   // 1:1  square   (logo)
    'featured'  => [800, 600],   // 4:3  (spotlight)
    'services'  => [720, 480],   // 3:2  (section image)
    'about'     => [800, 500],   // 8:5  (timeline / banner)
    'media'     => [0,   0],     // no crop — preserve original
]);

// ── Credentials ───────────────────────────────────────────────────
function cms_get_credentials(): array {
    if (file_exists(CRED_FILE)) {
        $c = json_decode(file_get_contents(CRED_FILE), true);
        if (!empty($c['username']) && !empty($c['password_hash'])) return $c;
    }
    $hash = password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT, ['cost' => 12]);
    $cred = ['username' => DEFAULT_ADMIN_USER, 'password_hash' => $hash];
    file_put_contents(CRED_FILE, json_encode($cred, JSON_PRETTY_PRINT));
    return $cred;
}

// ── JSON helpers ──────────────────────────────────────────────────
function cms_load(string $file): array {
    $path = DATA_PATH . $file;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}


function cms_save(string $file, array $data): bool {
    $path = DATA_PATH . $file;
    $lock = $path . '.lock';
    $tmp  = $path . '.tmp';

    // json_encode() returns false on malformed UTF-8. Writing that would put an
    // empty string on disk and still report success, replacing good content
    // with nothing — so refuse before opening anything.
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        error_log('cms_save: json_encode failed for ' . $file . ' — ' . json_last_error_msg());
        return false;
    }

    $fh = fopen($lock, 'c');
    if (!$fh) return false;
    flock($fh, LOCK_EX);
    $ok = file_put_contents($tmp, $json) !== false && rename($tmp, $path);
    flock($fh, LOCK_UN);
    fclose($fh);
    // Clean up orphaned temp and lock files
    @unlink($lock);
    if (!$ok) @unlink($tmp);
    return $ok;
}

function cms_next_id(array $items): int {
    if (empty($items)) return 1;
    return max(array_column($items, 'id')) + 1;
}

// ── CSRF ──────────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ── GD image crop/resize (cover fit, center crop) ─────────────────
function cms_crop_image(string $source, string $dest, int $width, int $height): bool {
    if (!function_exists('imagecreatefromjpeg')) return false;
    $info = @getimagesize($source);
    if (!$info) return false;

    [$src_w, $src_h, $type] = $info;

    $src_img = match($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
        IMAGETYPE_PNG  => @imagecreatefrompng($source),
        IMAGETYPE_GIF  => @imagecreatefromgif($source),
        IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        default        => false,
    };
    if (!$src_img) return false;

    // Cover fit: scale so the image fills the box, then center-crop
    $src_ratio = $src_w / $src_h;
    $dst_ratio = $width  / $height;

    if ($src_ratio > $dst_ratio) {
        $crop_h = $src_h;
        $crop_w = (int)round($src_h * $dst_ratio);
        $crop_x = (int)round(($src_w - $crop_w) / 2);
        $crop_y = 0;
    } else {
        $crop_w = $src_w;
        $crop_h = (int)round($src_w / $dst_ratio);
        $crop_x = 0;
        $crop_y = (int)round(($src_h - $crop_h) / 2);
    }

    $dst_img = imagecreatetruecolor($width, $height);

    // Preserve PNG transparency
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($dst_img, false);
        imagesavealpha($dst_img, true);
        $transparent = imagecolorallocatealpha($dst_img, 0, 0, 0, 127);
        imagefill($dst_img, 0, 0, $transparent);
    }

    imagecopyresampled($dst_img, $src_img, 0, 0, $crop_x, $crop_y,
                       $width, $height, $crop_w, $crop_h);

    $ok = match($type) {
        IMAGETYPE_JPEG => imagejpeg($dst_img, $dest, 88),
        IMAGETYPE_PNG  => imagepng($dst_img, $dest, 8),
        IMAGETYPE_GIF  => imagegif($dst_img, $dest),
        IMAGETYPE_WEBP => imagewebp($dst_img, $dest, 88),
        default        => false,
    };

    return (bool)$ok;
}

// ── File upload ───────────────────────────────────────────────────
// $accept: 'image' | 'video' | 'any'
function cms_upload_file(array $file, string $subfolder, string $accept = 'image'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload error code: ' . $file['error']];
    }

    $is_video  = $accept === 'video';
    $is_any    = $accept === 'any';
    $mime_list = $is_video ? VIDEO_MIME : ($is_any ? array_merge(ALLOWED_MIME, VIDEO_MIME) : ALLOWED_MIME);
    $ext_list  = $is_video ? VIDEO_EXT  : ($is_any ? array_merge(ALLOWED_EXT,  VIDEO_EXT)  : ALLOWED_EXT);
    $max_bytes = $is_video ? MAX_VIDEO_BYTES : MAX_UPLOAD_BYTES;

    if ($file['size'] > $max_bytes) {
        $mb = round($max_bytes / 1024 / 1024);
        return ['ok' => false, 'error' => "File too large (max {$mb} MB)."];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ext_list)) {
        return ['ok' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $ext_list)];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $mime_list)) {
        return ['ok' => false, 'error' => 'Invalid MIME type detected.'];
    }

    $subfolder = preg_replace('/[^a-z0-9_\-]/', '', strtolower($subfolder));
    $dir       = UPLOAD_PATH . $subfolder . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['ok' => false, 'error' => 'Could not create upload directory. Check server permissions.'];
    }

    $prefix = in_array($mime, VIDEO_MIME) ? 'vid_' : 'img_';
    $name   = uniqid($prefix, true) . '.' . $ext;
    $dest   = $dir . $name;
    $rel    = 'assets/uploads/' . $subfolder . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Failed to save file. Check directory permissions.'];
    }

    // Apply GD crop for images when dimensions are defined
    $dims = UPLOAD_DIMENSIONS[$subfolder] ?? [0, 0];
    if (in_array($mime, ALLOWED_MIME) && $dims[0] > 0 && $dims[1] > 0) {
        if (!cms_crop_image($dest, $dest, $dims[0], $dims[1])) {
            return ['ok' => false, 'error' => 'File saved but image processing failed. Ensure GD extension is enabled.'];
        }
    }

    return ['ok' => true, 'path' => $rel, 'mime' => $mime];
}

// ── Media library helpers ─────────────────────────────────────────
function cms_media_list(bool $force_refresh = false): array {
    // Cache in session for 5 minutes to avoid re-scanning disk on every request
    if (!$force_refresh
        && isset($_SESSION['_media_cache'], $_SESSION['_media_cache_time'])
        && time() - $_SESSION['_media_cache_time'] < 300) {
        return $_SESSION['_media_cache'];
    }

    $files = [];
    if (!is_dir(UPLOAD_PATH)) return $files;

    $finfo_obj = new finfo(FILEINFO_MIME_TYPE);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        UPLOAD_PATH, RecursiveDirectoryIterator::SKIP_DOTS
    ));
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        $abs = $f->getPathname();
        $ext = strtolower($f->getExtension());
        if (!in_array($ext, ALLOWED_EXT) && !in_array($ext, VIDEO_EXT)) continue;

        $rel  = 'assets/uploads/' . str_replace('\\', '/', substr($abs, strlen(UPLOAD_PATH)));
        $mime = $finfo_obj->file($abs);

        $files[] = [
            'url'    => $rel,
            'name'   => $f->getFilename(),
            'folder' => basename($f->getPath()),
            'size'   => $f->getSize(),
            'mtime'  => $f->getMTime(),
            'type'   => in_array($ext, VIDEO_EXT) ? 'video' : 'image',
            'mime'   => $mime,
        ];
    }
    usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);

    $_SESSION['_media_cache']      = $files;
    $_SESSION['_media_cache_time'] = time();
    return $files;
}

// Bust the media cache (call after upload or delete)
function cms_media_cache_bust(): void {
    unset($_SESSION['_media_cache'], $_SESSION['_media_cache_time']);
}
