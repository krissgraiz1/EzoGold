<?php
// config.php
session_start();

// --- جلسات أكثر ثباتاً + مجلد حفظ داخل المشروع ---
try {
    $sessDir = __DIR__ . '/sessions';
    if (!is_dir($sessDir)) { @mkdir($sessDir, 0777, true); }
    if (is_dir($sessDir) && is_writable($sessDir)) {
        @ini_set('session.save_path', $sessDir);
    }
    @ini_set('session.cookie_lifetime', 0);       // تبقى حتى إغلاق المتصفح
    @ini_set('session.gc_maxlifetime', 36000);    // 10 ساعات
    @ini_set('display_errors', 1);
    @ini_set('display_startup_errors', 1);
    @error_reporting(E_ALL);
} catch (Exception $e) {}

date_default_timezone_set('Africa/Tripoli');

// --- حساب المسار الأساسي للروابط (URL base) ---
$DOC_ROOT = str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__));
$PROJ_ROOT = str_replace('\\','/', realpath(__DIR__));
$BASE_PATH = rtrim(str_replace($DOC_ROOT, '', $PROJ_ROOT), '/');
if ($BASE_PATH === '') { $BASE_PATH = ''; } // مشروع في الجذر

function base_url($path = '') {
    global $BASE_PATH;
    $p = ltrim($path, '/');
    if ($BASE_PATH === '' || $BASE_PATH === '/') {
        return '/' . $p;
    }
    return $BASE_PATH . '/' . $p;
}

try {
    $dbPath = __DIR__ . '/data.sqlite';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Database error: ' . $e->getMessage());
}

function is_logged_in() { return isset($_SESSION['user_id']); }
function require_login() { if (!is_logged_in()) { header('Location: ' . base_url('index.php')); exit; } }
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function setting($key, $default = '') {
    global $db;
    $stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['value'] : $default;
}
?>
