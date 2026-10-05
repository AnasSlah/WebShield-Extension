<?php
session_start();
include('db.php');

// لو المستخدم مسجل دخول بالفعل، حوله فوراً للوحة التحكم
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php?page=home");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // التحقق من كلمة المرور المشفرة
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php?page=home");
            exit;
        } else {
            $error = 'كلمة المرور غير صحيحة.';
        }
    } else {
        $error = 'البريد الإلكتروني غير مسجل.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول | SecureShield</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 350px; text-align: center; border-top: 4px solid #30b1ff; }
        .login-box h2 { margin: 0 0 20px; color: #1a1a1a; }
        .input-group { margin-bottom: 20px; text-align: right; }
        .input-group label { display: block; margin-bottom: 8px; color: #666; font-size: 14px; font-weight: 600; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Cairo'; outline: none; transition: 0.3s; }
        .input-group input:focus { border-color: #30b1ff; }
        .btn { background: #30b1ff; color: #fff; border: none; width: 100%; padding: 12px; border-radius: 8px; font-family: 'Cairo'; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #1a9eed; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .link { display: block; margin-top: 20px; color: #666; text-decoration: none; font-size: 14px; }
        .link a { color: #30b1ff; font-weight: bold; text-decoration: none; }
        .logo { font-size: 40px; color: #30b1ff; margin-bottom: 10px; display: inline-block; }
    </style>
</head>
<body>

<div class="login-box">
    <div class="logo">🛡️</div>
    <h2>تسجيل الدخول</h2>
    
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="input-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" required placeholder="admin@example.com">
        </div>
        <div class="input-group">
            <label>كلمة المرور</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" name="login" class="btn">دخول للوحة التحكم</button>
    </form>
    
    <div class="link">
        ليس لديك حساب؟ <a href="register.php">مستخدم جديد</a>
    </div>
</div>

</body>
</html>