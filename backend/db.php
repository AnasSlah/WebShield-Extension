<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "security_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); 
?>