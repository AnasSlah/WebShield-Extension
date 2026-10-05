<?php

ob_start();
// 1. تشغيل الـ Session في بداية الـ API لمعرفة المستخدم الحالي
session_start(); 
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include('db.php');

// جلب رقم المستخدم لو مسجل دخول
$current_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : NULL;

// =======================================
// مفتاح VirusTotal الخاص بك
// =======================================
$vt_api_key = "55540c918a940ee59bca60647d6a5c3369866bf88e18ad281b9aa31a2f6ada3b"; 

function is_private_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

function validate_url($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return false;
    if (is_private_ip(gethostbyname($host))) return false;
    return true;
}

// =======================================
// جلب اسم الملف الحقيقي من السيرفر
// =======================================
function get_remote_file_name($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true, 
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if (preg_match('/filename[^;=\n]*=(?:UTF-8\'\')?([\'"]?)(.*?)\1/i', $response, $matches)) {
        return trim($matches[2], " '\"");
    }
    
    $basename = basename(parse_url($url, PHP_URL_PATH));
    if ($basename && strpos($basename, '.') !== false) {
        return $basename;
    }
    
    return "unknown";
}

// =======================================
// جلب نوع الملف الحقيقي (Magic Numbers)
// =======================================
function get_real_file_type($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RANGE => '0-15',
        CURLOPT_TIMEOUT => 4,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    ]);
    
    $data = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$data) return ["type" => "unknown", "mime" => "unknown"];
    $bytes = strtolower(bin2hex($data));

    if (strpos($bytes, '4d5a') === 0) return ["type" => "EXE", "mime" => $contentType];
    if (strpos($bytes, '504b0304') === 0) return ["type" => "ZIP", "mime" => $contentType];
    if (strpos($bytes, '7f454c46') === 0) return ["type" => "ELF", "mime" => $contentType];
    if (strpos($bytes, '25504446') === 0) return ["type" => "PDF", "mime" => $contentType];

    return ["type" => "SAFE", "mime" => $contentType];
}

// =======================================
// فحص الرابط عبر VirusTotal
// =======================================
function check_virustotal_url($url, $api_key) {
    if(empty($api_key)) return "disabled";
    $url_id = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.virustotal.com/api/v3/urls/" . $url_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-apikey: " . $api_key]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $data = json_decode($response, true);
        if (isset($data['data']['attributes']['last_analysis_stats'])) {
            if ($data['data']['attributes']['last_analysis_stats']['malicious'] > 0) return "malicious";
        }
        return "safe";
    }
    return "unknown";
}

// =======================================
// جلب قائمة المواقع المحظورة للإضافة (مخصصة للمستخدم)
// =======================================
if (isset($_GET['action']) && $_GET['action'] === 'get_blocked_sites') {
    $sites = [];
    
    // التعديل هنا: جلب المواقع لو المستخدم مسجل دخول فقط، ونجيب مواقعه هو بس
    if ($current_user_id) {
        $result = $conn->query("SELECT domain, reason FROM blacklisted_sites WHERE user_id = $current_user_id");
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $sites[$row['domain']] = $row['reason'];
            }
        }
    }
    
    ob_clean();
    echo json_encode(["status" => "success", "data" => $sites]);
    exit;
}

// =======================================
// فحص الملفات
// =======================================
$action = $_GET['action'] ?? '';

if ($action === 'check_file_security') {
    $file_url = trim($_GET['url'] ?? '');
    
    if (!validate_url($file_url)) {
        echo json_encode(["status" => "danger", "virus_type" => "رابط غير صالح"]);
        exit;
    }

    $raw_name = strtolower(trim($_GET['name'] ?? 'unknown'));
    if ($raw_name === 'unknown' || $raw_name === 'ملف مجهول' || strpos($raw_name, '.') === false) {
        $real_name = get_remote_file_name($file_url);
        if ($real_name !== 'unknown') {
            $raw_name = strtolower($real_name);
        }
    }
    
    $file_name = $raw_name;
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $real = get_real_file_type($file_url);
    $real_type = $real['type'];
    $mime = strtolower($real['mime']);

    $status_json = "safe";
    $status_db = "safe"; 
    $virus_type = "ملف آمن";

    $malware_patterns = ['trojan', 'keygen', 'crack', 'rat', 'stealer', 'malware', 'virus'];
    foreach ($malware_patterns as $bad) {
        if (strpos($file_name, $bad) !== false || strpos($file_url, $bad) !== false) {
            $status_json = "danger"; $status_db = "virus"; $virus_type = "اسم لبرمجية ضارة"; 
            break;
        }
    }

    $innocent_exts = ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'mp4', 'pdf', 'txt', 'doc', 'docx'];
    if ($status_json !== "danger") {
        if (in_array($ext, $innocent_exts) && in_array($real_type, ['EXE', 'ELF'])) {
            $status_json = "danger"; $status_db = "virus"; $virus_type = "ملف خبيث مموه";
        }
    }

    if ($status_json !== "danger" && in_array($real_type, ['EXE', 'ELF', 'ZIP', 'APK'])) {
        $vt_result = check_virustotal_url($file_url, $vt_api_key);
        if ($vt_result === "malicious") {
            $status_json = "danger"; $status_db = "virus"; $virus_type = "فيروس (VirusTotal)";
        }
    }

    $file_size = isset($_GET['size']) ? trim($_GET['size']) : '';
    if (empty($file_size) || $file_size === 'قيد الحساب...' || $file_size === 'undefined') {
        $file_size = 'غير معروف';
    }

    // التسجيل في قاعدة البيانات مع إرفاق رقم المستخدم
    $stmt = $conn->prepare("INSERT INTO scanned_files (user_id, file_name, file_url, file_size, status, virus_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $current_user_id, $file_name, $file_url, $file_size, $status_db, $virus_type);
    $stmt->execute();

    ob_clean();
    echo json_encode([
        "status" => $status_json,
        "virus_type" => $virus_type,
        "file_type" => $real_type,
        "file_name" => $file_name 
    ]);
    exit;
}
?>