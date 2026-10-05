<?php
session_start();
session_unset();    // مسح متغيرات الجلسة
session_destroy();  // تدمير ملف الجلسة على السيرفر
// مسح كوكيز المتصفح المتعلقة بالجلسة
setcookie(session_name(), '', time() - 3600, '/'); 
header("Location: index.php");
exit;
?>