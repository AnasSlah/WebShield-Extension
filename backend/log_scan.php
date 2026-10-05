<?php
// السماح للاكستنشن بالوصول (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include('db.php');

// استلام البيانات (URL الموقع المفحوص)
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['url'])) {
    $url = mysqli_real_escape_string($conn, $data['url']);

    // تسجيل العملية في جدول التتبع (scanned_sites)
    $sql = "INSERT INTO scanned_sites (site_url) VALUES ('$url')";
    
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "message" => "Site logged"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No URL provided"]);
}
?>