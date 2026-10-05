<?php
session_start();
include('db.php');

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php?page=home");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username  = mysqli_real_escape_string($conn, trim($_POST['username']));
    $fullname  = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password  = $_POST['password'];
    $phone     = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $job_title = mysqli_real_escape_string($conn, trim($_POST['job_title']));
    $location  = mysqli_real_escape_string($conn, trim($_POST['location']));
    
    // تعيين الصلاحية الافتراضية كما في الصورة
    $role = 'Super Admin'; 

    // التأكد من أن الإيميل غير مسجل مسبقاً
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $error = 'البريد الإلكتروني مسجل بالفعل!';
    } else {
        // تشفير كلمة المرور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // استعلام الإدخال بالحقول الجديدة
        $stmt = $conn->prepare("INSERT INTO users (username, fullname, email, password, phone, job_title, location, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $username, $fullname, $email, $hashed_password, $phone, $job_title, $location, $role);
        
        if ($stmt->execute()) {
            $success = 'تم إنشاء الحساب بنجاح! جاري تحويلك...';
            // التحويل التلقائي لصفحة الدخول بعد ثانيتين
            header("refresh:2;url=index.php");
        } else {
            $error = 'حدث خطأ أثناء التسجيل.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حساب جديد | SecureShield</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px 0; box-sizing: border-box; }
        .login-box { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 450px; text-align: center; border-top: 4px solid #28a745; }
        .login-box h2 { margin: 0 0 20px; color: #1a1a1a; }
        .input-group { margin-bottom: 15px; text-align: right; }
        .input-group label { display: block; margin-bottom: 5px; color: #666; font-size: 14px; font-weight: 600; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Cairo'; outline: none; transition: 0.3s; }
        .input-group input:focus { border-color: #28a745; }
        .btn { background: #28a745; color: #fff; border: none; width: 100%; padding: 12px; border-radius: 8px; font-family: 'Cairo'; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn:hover { background: #218838; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; text-align: right; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; text-align: right; }
        .link { display: block; margin-top: 20px; color: #666; text-decoration: none; font-size: 14px; }
        .link a { color: #30b1ff; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>إنشاء حساب جديد</h2>
    
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="input-group">
            <label>اسم المستخدم (لتسجيل الدخول)</label>
            <input type="text" name="username" required placeholder="أدخل اسم مستخدم فريد">
        </div>
        <div class="input-group">
            <label>الاسم الكامل</label>
            <input type="text" name="fullname" required placeholder="مثال: المهندس / مدير النظام">
        </div>
        <div class="input-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" required placeholder="admin@shieldpro.local">
        </div>
        <div class="input-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" required placeholder="مثال: 249+++++++++">
        </div>
        <div class="input-group">
            <label>المسمى الوظيفي</label>
            <input type="text" name="job_title" required placeholder="مثال: مطور برمجيات & خبير أمن سيبراني">
        </div>
        <div class="input-group">
            <label>موقع العمل</label>
            <input type="text" name="location" required placeholder="مثال: الخرطوم، السودان">
        </div>
        <div class="input-group">
            <label>كلمة المرور</label>
            <input type="password" name="password" required placeholder="••••••••" minlength="6">
        </div>
        <button type="submit" name="register" class="btn">تسجيل الآن</button>
    </form>
    
    <div class="link">
        لديك حساب بالفعل؟ <a href="index.php">تسجيل الدخول</a>
    </div>
</div>

</body>
</html>