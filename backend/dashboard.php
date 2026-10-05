<?php 
// 1. بدء الـ Session والتأكد من تسجيل الدخول قبل أي حاجة
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 2. استدعاء ملف الاتصال بقاعدة البيانات
include('db.php'); 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureShield Pro | لوحة التحكم</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #30b1ff;
            --secondary: #00e4fe;
            --dark: #1a1a1a;
            --danger: #dc3545;
            --success: #28a745;
            --bg: #f4f7f6;
            --sidebar-width: 280px;
        }

        * { box-sizing: border-box; }
        body { 
            font-family: 'Cairo', sans-serif; 
            background: var(--bg); 
            margin: 0; 
            display: flex; 
            min-height: 100vh; 
            direction: rtl; 
        }

        /* --- Sidebar (اليمين) --- */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--dark); 
            color: white; 
            height: 100vh; 
            padding: 40px 20px; 
            position: fixed; 
            right: 0; 
            top: 0;
            z-index: 1000; 
            box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            display: flex; 
            flex-direction: column; 
        }

        /* الشارة فوق النص وفي المنتصف */
        .sidebar h2 { 
            font-size: 22px; 
            color: var(--secondary); 
            text-align: center; 
            margin-bottom: 50px; 
            font-weight: 800; 
            display: flex;
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            gap: 15px;
        }

        .sidebar h2 i { font-size: 45px; color: var(--primary); }

        nav { 
            display: flex; 
            flex-direction: column; 
            height: 100%; 
        }

        .nav-link { 
            padding: 15px 20px; 
            color: #adb5bd; 
            display: flex; 
            align-items: center;
            text-decoration: none; 
            transition: 0.3s; 
            border-radius: 12px; 
            margin-bottom: 10px; 
            font-weight: 600;
        }

        /* حالة النشاط (Active) */
        .nav-link:hover, .nav-link.active { 
            background: rgba(48, 177, 255, 0.15); 
            color: var(--primary); 
        }

        .nav-link i { margin-left: 15px; font-size: 20px; }

        /* زر الخروج في الأسفل تماماً */
        .logout-link { 
            margin-top: auto; 
            color: #ff4d4d !important; 
        }

        /* --- المحتوى الرئيسي (اليسار) --- */
        .main-content { 
            margin-right: var(--sidebar-width); 
            flex: 1; 
            padding: 40px; 
            min-width: 0; 
        }
        
        .top-bar { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 40px; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 20px; 
        }
        .top-bar h1 { margin: 0; font-size: 28px; color: var(--dark); font-weight: 800; }
        .top-bar span { color: #888; font-size: 14px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>
            <i class="fas fa-shield-halved"></i>
            Shield Pro
        </h2>
        <nav>
            <?php 
                $current_page = isset($_GET['page']) ? $_GET['page'] : 'home'; 
            ?>
            
            <a href="?page=home" class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> الرئيسية
            </a>
            
            <a href="?page=blocked_sites" class="nav-link <?php echo ($current_page == 'blocked_sites') ? 'active' : ''; ?>">
                <i class="fas fa-ban"></i> المواقع المحظورة
            </a>
            
            <a href="?page=blocked_files" class="nav-link <?php echo ($current_page == 'blocked_files') ? 'active' : ''; ?>">
                <i class="fas fa-file-shield"></i> ملفات محظورة
            </a>
            
            <a href="?page=profile" class="nav-link <?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> الملف الشخصي
            </a>
            
            <a href="logout.php" class="nav-link logout-link">
                <i class="fas fa-sign-out-alt"></i> خروج
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h1>مرحباً، <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>
                <span>تاريخ اليوم: <?php echo date('d M, Y'); ?></span>
            </div>
        </div>

        <?php 
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
            $allowed_pages = ['home', 'blocked_sites', 'blocked_files', 'profile'];
            
            if (in_array($page, $allowed_pages)) {
                if (file_exists($page . '.php')) {
                    include($page . '.php');
                } else {
                    echo "<div style='padding:20px; background:white; border-radius:15px;'>خطأ: ملف الصفحة غير موجود.</div>";
                }
            } else {
                include('home.php');
            }
        ?>
    </div>

</body>
</html>