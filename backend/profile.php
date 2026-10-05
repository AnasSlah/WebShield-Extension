<?php
// تم إزالة session_start و db.php لمنع التداخل لأنها موجودة بالفعل في dashboard.php

// التحقق من تسجيل الدخول كإجراء أمني إضافي
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// 1. معالجة طلب تحديث البيانات الأساسية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $new_email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $new_phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $new_location = mysqli_real_escape_string($conn, trim($_POST['location']));

    $update_query = "UPDATE users SET fullname='$new_fullname', email='$new_email', phone='$new_phone', location='$new_location' WHERE id='$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success_msg = "تم تحديث البيانات بنجاح!";
    } else {
        $error_msg = "حدث خطأ أثناء التحديث.";
    }
}

// 2. معالجة طلب تغيير كلمة السر
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // جلب كلمة السر الحالية من قاعدة البيانات
    $pass_query = "SELECT password FROM users WHERE id = '$user_id'";
    $pass_result = mysqli_query($conn, $pass_query);
    $user_data = mysqli_fetch_assoc($pass_result);
    $db_password = $user_data['password'];

    // التحقق من تطابق كلمة السر القديمة
    if (password_verify($current_password, $db_password) || $current_password == $db_password) {
        
        // التحقق من تطابق كلمة السر الجديدة مع التأكيد
        if ($new_password === $confirm_password) {
            
            // تشفير كلمة السر الجديدة
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // تحديث قاعدة البيانات
            $update_pass_query = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
            if (mysqli_query($conn, $update_pass_query)) {
                $success_msg = "تم تغيير كلمة المرور بنجاح!";
            } else {
                $error_msg = "حدث خطأ أثناء تغيير كلمة المرور.";
            }
        } else {
            $error_msg = "كلمة المرور الجديدة وتأكيدها غير متطابقين.";
        }
    } else {
        $error_msg = "كلمة المرور الحالية غير صحيحة.";
    }
}

// جلب بيانات المستخدم لعرضها
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    $username  = htmlspecialchars($user['username']);
    $fullname  = htmlspecialchars($user['fullname']);
    $email     = htmlspecialchars($user['email']);
    $phone     = htmlspecialchars($user['phone']);
    $job_title = htmlspecialchars($user['job_title']);
    $location  = htmlspecialchars($user['location']);
    $role      = htmlspecialchars($user['role']);
    
    $access_level = ($role === 'Super Admin') ? 'وصول كامل (Full Access)' : 'وصول محدود';

    $created_at = strtotime($user['created_at']);
    $months = [
        "Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل",
        "May" => "مايو", "Jun" => "يونيو", "Jul" => "يوليو", "Aug" => "أغسطس",
        "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر"
    ];
    $month_en = date('M', $created_at);
    $year = date('Y', $created_at);
    $join_date = $months[$month_en] . ' ' . $year;
}
?>

<style>
    :root {
        --primary: #30b1ff;
        --success: #28a745;
        --dark: #1a1a1a;
        --danger: #dc3545;
    }

    .profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; max-width: 1000px; margin: 0 auto; }

    .profile-card, .details-card { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
    .profile-card { text-align: center; }

    .avatar-wrapper { position: relative; width: 120px; height: 120px; margin: 0 auto 20px; }
    .avatar-main { width: 100%; height: 100%; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 50px; border: 5px solid #f4f7f6; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .status-online { position: absolute; bottom: 5px; left: 5px; width: 20px; height: 20px; background: var(--success); border: 3px solid white; border-radius: 50%; }

    .profile-card h3 { margin: 10px 0 5px; font-size: 24px; color: var(--dark); }
    .profile-card p { color: #888; font-size: 14px; margin-bottom: 20px; }
    .role-badge { background: rgba(48, 177, 255, 0.1); color: var(--primary); padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; }

    .details-card h2 { font-size: 20px; margin-bottom: 25px; color: var(--dark); border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f9f9f9; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #aaa; font-weight: 600; width: 30%; }
    .info-value { color: var(--dark); font-weight: 700; width: 70%; text-align: left; }

    .edit-input { width: 100%; padding: 8px 15px; border: 1px solid var(--primary); border-radius: 8px; font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: 700; color: var(--dark); outline: none; text-align: right; transition: 0.3s; box-sizing: border-box; }
    .edit-input:focus { box-shadow: 0 0 8px rgba(48, 177, 255, 0.3); }

    .action-buttons { margin-top: 30px; display: flex; gap: 15px; }
    .btn-profile { flex: 1; padding: 12px; border-radius: 10px; border: none; font-family: 'Cairo'; font-weight: 700; cursor: pointer; transition: 0.3s; }
    .btn-edit { background: var(--primary); color: white; }
    .btn-edit:hover { background: #1a94e6; }
    .btn-save { background: var(--success); color: white; display: none; }
    .btn-save:hover { background: #218838; }
    .btn-password { background: #f8f9fa; color: #666; border: 1px solid #eee; }
    .btn-password:hover { background: #eee; }

    .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: bold; text-align: center; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .modal-content { background-color: #fff; padding: 30px; border-radius: 15px; width: 400px; max-width: 90%; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: right; }
    .close-btn { position: absolute; left: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #aaa; transition: 0.3s; }
    .close-btn:hover { color: var(--danger); }
    .modal-content h3 { margin-top: 0; color: var(--dark); margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .input-group { margin-bottom: 15px; }
    .input-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; font-size: 14px; }
    .input-group input { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Cairo'; transition: 0.3s; }
    .input-group input:focus { border-color: var(--primary); outline: none; }
</style>

<div class="profile-grid">
    <div class="profile-card">
        <div class="avatar-wrapper">
            <div class="avatar-main">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="status-online" title="متصل الآن"></div>
        </div>
        <h3><?php echo $username; ?></h3>
        <p><?php echo $job_title; ?></p>
        <span class="role-badge"><?php echo $role; ?></span>
        
        <div style="margin-top: 30px; text-align: right;">
            <div class="info-row">
                <span class="info-label">آخر ظهور</span>
                <span class="info-value">الآن</span>
            </div>
            <div class="info-row">
                <span class="info-label">عضو منذ</span>
                <span class="info-value"><?php echo $join_date; ?></span>
            </div>
        </div>
    </div>

    <div class="details-card">
        <h2><i class="fas fa-id-card-clip" style="margin-left: 10px; color: var(--primary);"></i> المعلومات الأساسية</h2>
        
        <?php if($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form id="profileForm" method="POST" action="">
            <div class="info-row">
                <span class="info-label">الاسم الكامل</span>
                <span class="info-value editable" data-name="fullname"><?php echo $fullname; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">البريد الإلكتروني</span>
                <span class="info-value editable" data-name="email"><?php echo $email; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">رقم الهاتف</span>
                <span class="info-value editable" data-name="phone" dir="ltr"><?php echo $phone; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">صلاحيات الوصول</span>
                <span class="info-value"><?php echo $access_level; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">موقع العمل</span>
                <span class="info-value editable" data-name="location"><?php echo $location; ?></span>
            </div>

            <div class="action-buttons">
                <button type="button" id="editBtn" class="btn-profile btn-edit"><i class="fas fa-user-pen" style="margin-left: 8px;"></i> تعديل البيانات</button>
                <button type="submit" name="update_profile" id="saveBtn" class="btn-profile btn-save"><i class="fas fa-check" style="margin-left: 8px;"></i> حفظ التعديلات</button>
                <button type="button" id="openPasswordModal" class="btn-profile btn-password"><i class="fas fa-key" style="margin-left: 8px;"></i> تغيير كلمة السر</button>
            </div>
        </form>
    </div>
</div>

<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3><i class="fas fa-lock" style="color: var(--primary); margin-left: 8px;"></i> تغيير كلمة المرور</h3>
        <form method="POST" action="">
            <div class="input-group">
                <label>كلمة المرور الحالية</label>
                <input type="password" name="current_password" required placeholder="أدخل كلمة المرور الحالية">
            </div>
            <div class="input-group">
                <label>كلمة المرور الجديدة</label>
                <input type="password" name="new_password" required minlength="6" placeholder="أدخل كلمة المرور الجديدة">
            </div>
            <div class="input-group">
                <label>تأكيد كلمة المرور الجديدة</label>
                <input type="password" name="confirm_password" required minlength="6" placeholder="أعد إدخال كلمة المرور الجديدة">
            </div>
            <button type="submit" name="change_password" class="btn-profile btn-edit" style="width: 100%; margin-top: 15px;"><i class="fas fa-save" style="margin-left: 8px;"></i> حفظ كلمة المرور</button>
        </form>
    </div>
</div>

<script>
    // 1. سكربت تعديل البيانات
    document.getElementById('editBtn').addEventListener('click', function() {
        let editableFields = document.querySelectorAll('.editable');
        editableFields.forEach(function(field) {
            let currentValue = field.innerText.trim();
            let inputName = field.getAttribute('data-name');
            let inputType = (inputName === 'email') ? 'email' : 'text';
            let dir = (inputName === 'phone' || inputName === 'email') ? 'dir="ltr"' : '';
            field.innerHTML = `<input type="${inputType}" name="${inputName}" value="${currentValue}" class="edit-input" ${dir} required>`;
        });
        this.style.display = 'none';
        document.getElementById('saveBtn').style.display = 'block';
    });

    // 2. سكربت نافذة تغيير كلمة السر
    const modal = document.getElementById("passwordModal");
    const btnPassword = document.getElementById("openPasswordModal");
    const closeBtn = document.querySelector(".close-btn");

    // فتح النافذة
    btnPassword.addEventListener('click', function() {
        modal.style.display = "flex";
    });

    // إغلاق النافذة عند الضغط على X
    closeBtn.addEventListener('click', function() {
        modal.style.display = "none";
    });

    // إغلاق النافذة عند الضغط في أي مكان خارج المربع الأبيض
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
</script>