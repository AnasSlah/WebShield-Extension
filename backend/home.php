<?php
// جلب رقم المستخدم من الجلسة (Session) الحالية
$user_id = $_SESSION['user_id']; 

// 1. منطق الحذف السريع من صفحة الهوم (للمستخدم الحالي فقط)
if (isset($_GET['del_id'])) {
    $id = intval($_GET['del_id']);
    if ($conn->query("DELETE FROM blacklisted_sites WHERE id = $id AND user_id = $user_id")) {
        echo "<script>window.location.href='dashboard.php?page=home';</script>";
        exit;
    }
}

$error_msg = ''; // متغير لتخزين رسالة الخطأ

// 2. منطق إضافة موقع جديد (يُسجل باسم المستخدم الحالي)
if (isset($_POST['add_site_home'])) {
    $raw_url = $_POST['domain'];
    $clean_domain = str_ireplace('www.', '', parse_url($raw_url, PHP_URL_HOST) ?: $raw_url);
    
    $domain = mysqli_real_escape_string($conn, $clean_domain);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    if (!empty($domain)) {
        // فحص ما إذا كان هذا المستخدم قد قام بحظر هذا الموقع مسبقاً
        $check = $conn->query("SELECT id FROM blacklisted_sites WHERE domain = '$domain' AND user_id = $user_id");
        
        if ($check && $check->num_rows > 0) {
            // الموقع موجود بالفعل عند هذا العميل
            $error_msg = "هذا الموقع موجود بالفعل في قائمة الحظر الخاصة بك!";
        } else {
            // الموقع غير موجود، قم بالإضافة
            $conn->query("INSERT INTO blacklisted_sites (user_id, domain, reason) VALUES ($user_id, '$domain', '$reason')");
            $conn->query("INSERT INTO scanned_sites (user_id, site_url) VALUES ($user_id, '$domain')");
            echo "<script>window.location.href='dashboard.php?page=home';</script>";
            exit;
        }
    }
}

// 3. جلب الإحصائيات الخاصة بالمستخدم الحالي فقط
$scanned_total = $conn->query("SELECT COUNT(*) as total FROM scanned_sites WHERE user_id = $user_id")->fetch_assoc()['total'];
$sites_count = $conn->query("SELECT COUNT(*) as total FROM blacklisted_sites WHERE user_id = $user_id")->fetch_assoc()['total'];
$files_count = $conn->query("SELECT COUNT(*) as total FROM scanned_files WHERE user_id = $user_id AND (status='virus' OR status='blocked' OR status='flagged')")->fetch_assoc()['total'];
?>

<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px; }
    .stat-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; }
    .stat-info h3 { margin: 0; font-size: 14px; color: #999; margin-bottom: 8px; font-weight: 600; }
    .stat-info .number { font-size: 32px; font-weight: 800; color: #1a1a1a; }
    .stat-icon { width: 65px; height: 65px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; }

    .data-section { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); margin-bottom: 30px; }
    .data-section h2 { font-size: 20px; margin-bottom: 25px; color: #1a1a1a; display: flex; align-items: center; }

    .add-box { display: flex; gap: 15px; margin-bottom: 30px; background: #f8f9fa; padding: 25px; border-radius: 15px; }
    .add-box input, .add-box select { flex: 1; padding: 14px 20px; border: 1px solid #e0e0e0; border-radius: 10px; outline: none; font-family: 'Cairo'; }
    .btn-submit { background: #30b1ff; color: white; border: none; padding: 0 35px; border-radius: 10px; cursor: pointer; font-weight: 700; }
    
    .alert-error { background: #f8d7da; color: #dc3545; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; border: 1px solid #f5c6cb; }

    table { width: 100%; border-collapse: collapse; }
    th { padding: 18px; text-align: right; color: #aaa; font-weight: 600; border-bottom: 2px solid #f4f4f4; }
    td { padding: 18px; border-bottom: 1px solid #f8f9fa; color: #444; vertical-align: middle; }
    .badge { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
    .bg-danger { background: #dc3545; }
    .bg-safe { background: #28a745; }
    .action-btn { color: #ff4d4d; text-decoration: none; font-size: 18px; transition: 0.2s; }
    .file-icon { color: #777; margin-left: 8px; }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info"><h3>المواقع المفحوصة</h3><div class="number"><?php echo number_format($scanned_total); ?></div></div>
        <div class="stat-icon" style="background: rgba(48, 177, 255, 0.1); color: #30b1ff;"><i class="fas fa-globe"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info"><h3>مواقع محظورة</h3><div class="number"><?php echo $sites_count; ?></div></div>
        <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;"><i class="fas fa-shield-virus"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info"><h3>ملفات محظورة</h3><div class="number"><?php echo $files_count; ?></div></div>
        <div class="stat-icon" style="background: rgba(168, 50, 164, 0.1); color: #a832a4;"><i class="fas fa-file-circle-exclamation"></i></div>
    </div>
</div>

<div class="data-section">
    <h2><i class="fas fa-list-check" style="color: var(--primary); margin-left: 10px;"></i> إضافة سريعة وآخر المواقع المحظورة</h2>
    
    <?php if($error_msg): ?>
        <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form class="add-box" method="POST" action="dashboard.php?page=home">
        <input type="text" name="domain" placeholder="أدخل الرابط أو الدومين هنا" required>
        <select name="reason" required>
            <option value="" disabled selected>اختر سبب الحظر</option>
            <option value="موقع تصيد (Phishing)">موقع تصيد (Phishing)</option>
            <option value="برمجيات خبيثة (Malware)">برمجيات خبيثة (Malware)</option>
            <option value="سبام وإعلانات مزعجة">سبام وإعلانات مزعجة</option>
            <option value="تتبع وتجسس">تتبع وتجسس</option>
            <option value="أخرى">أخرى</option>
        </select>
        <button type="submit" name="add_site_home" class="btn-submit">إضافة الآن</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>الدومين المحظور</th>
                <th>السبب</th>
                <th>تاريخ الحظر</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // جلب مواقع المستخدم الحالي فقط
            $latest = $conn->query("SELECT * FROM blacklisted_sites WHERE user_id = $user_id ORDER BY id DESC LIMIT 3");
            if ($latest && $latest->num_rows > 0) {
                while($row = $latest->fetch_assoc()) {
                    $bg = '#595959';
                    if (strpos($row['reason'], 'Phishing') !== false) $bg = '#dc3545';
                    if (strpos($row['reason'], 'Malware') !== false) $bg = '#a832a4';
                    
                    echo "<tr>
                            <td style='font-weight: 600;'>" . htmlspecialchars($row['domain']) . "</td>
                            <td><span class='badge' style='background: $bg;'>{$row['reason']}</span></td>
                            <td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>
                            <td>
                                <a href='dashboard.php?page=home&del_id={$row['id']}' class='action-btn' onclick='return confirm(\"حذف السجل؟\")'><i class='fas fa-trash-can'></i></a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; color:#ccc;'>لا توجد مواقع محظورة حالياً</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="data-section">
    <h2><i class="fas fa-file-shield" style="color: var(--primary); margin-left: 10px;"></i> أحدث عمليات التنزيل</h2>
    <table>
        <thead>
            <tr>
                <th>اسم الملف</th>
                <th>الحالة</th>
                <th>تاريخ الفحص</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // جلب ملفات المستخدم الحالي فقط
            $latest_files = $conn->query("SELECT * FROM scanned_files WHERE user_id = $user_id ORDER BY id DESC LIMIT 4");
            if ($latest_files && $latest_files->num_rows > 0) {
                while($row = $latest_files->fetch_assoc()) {
                    $fileName = htmlspecialchars($row['file_name']);
                    $db_status = strtolower($row['status']);
                    $is_danger = (strpos($db_status, 'virus') !== false || strpos($db_status, 'blocked') !== false || strpos($db_status, 'flagged') !== false);
                    
                    $status_txt = $is_danger ? 'محظور' : 'آمن';
                    $badge_class = $is_danger ? 'bg-danger' : 'bg-safe';
                    
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $icon = "fa-file"; 
                    if (in_array($ext, ['zip', 'rar', '7z'])) $icon = "fa-file-archive";
                    elseif (in_array($ext, ['exe', 'bat', 'sh', 'apk'])) $icon = "fa-file-code";
                    elseif (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) $icon = "fa-file-image";
                    elseif ($ext == 'pdf') $icon = "fa-file-pdf";

                    echo "<tr>
                            <td style='font-weight: 600;'><i class='fas $icon file-icon'></i> $fileName</td>
                            <td><span class='badge $badge_class'>$status_txt</span></td>
                            <td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center; color:#ccc;'>لا توجد عمليات تنزيل مسجلة</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>