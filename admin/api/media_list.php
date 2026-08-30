<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/auth.php';
require_auth();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(cms_media_list(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
