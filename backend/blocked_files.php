<?php
// جلب رقم المستخدم الحالي من الجلسة
$user_id = $_SESSION['user_id'];

// 1. منطق حذف سجل ملف من القائمة (خاص بالمستخدم الحالي فقط)
if (isset($_GET['del_file_id'])) {
    $id = intval($_GET['del_file_id']);
    // حذف السجل من قاعدة البيانات بشرط تطابق رقم المستخدم
    if ($conn->query("DELETE FROM scanned_files WHERE id = $id AND user_id = $user_id")) {
        echo "<script>window.location.href='dashboard.php?page=blocked_files';</script>";
        exit;
    }
}
?>

<style>
    .data-section { 
        background: white; 
        padding: 35px; 
        border-radius: 20px; 
        box-shadow: 0 10px 20px rgba(0,0,0,0.02); 
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 1px solid #eee;
        border-radius: 10px;
        outline: none;
        font-family: 'Cairo';
    }

    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
    }

    table { width: 100%; border-collapse: collapse; }
    th { padding: 18px; text-align: right; color: #aaa; font-weight: 600; border-bottom: 2px solid #f4f4f4; }
    td { padding: 18px; border-bottom: 1px solid #f8f9fa; color: #444; vertical-align: middle; }
    
    .file-icon { margin-left: 10px; color: #777; }
    .badge { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
    
    .bg-danger { background: #e74c3c; } 
    .bg-safe { background: #28a745; }

    .action-btn { background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 18px; text-decoration: none; display: inline-block; }
    .action-btn:hover { transform: scale(1.2); color: #cc0000; }
</style>

<div class="data-section">
    <div class="section-header">
        <h2 style="margin: 0;"><i class="fas fa-file-shield" style="margin-left: 10px; color: var(--primary);"></i> سجل جميع عمليات التنزيل</h2>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="fileSearch" placeholder="ابحث عن ملف محدد...">
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>اسم الملف</th>
                <th>الحجم</th>
                <th>الحالة</th>
                <th>النوع / التهديد</th>
                <th>تاريخ الفحص</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody id="filesTable">
            <?php
            // استعلام لجلب الملفات الخاصة بالمستخدم الحالي فقط
            $sql = "SELECT * FROM scanned_files WHERE user_id = $user_id ORDER BY id DESC";
            
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $fileName = htmlspecialchars($row['file_name']);
                    
                    // جلب الحجم مع وضع قيمة افتراضية لو فارغ
                    $fileSize = !empty($row['file_size']) ? htmlspecialchars($row['file_size']) : 'غير معروف';
                    
                    // تحديد هل الملف خطير أم آمن بناءً على المسجل في قاعدة البيانات
                    $db_status = strtolower($row['status']);
                    $is_danger = (strpos($db_status, 'virus') !== false || strpos($db_status, 'blocked') !== false || strpos($db_status, 'flagged') !== false);
                    
                    $status_txt = $is_danger ? 'محظور' : 'آمن';
                    $badge_class = $is_danger ? 'bg-danger' : 'bg-safe';
                    
                    // تحديد الأيقونة ديناميكياً
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $icon = "fa-file"; // أيقونة افتراضية للملفات المجهولة (unknown)
                    
                    if (in_array($ext, ['zip', 'rar', '7z'])) {
                        $icon = "fa-file-archive";
                    } elseif (in_array($ext, ['exe', 'bat', 'sh', 'apk'])) {
                        $icon = "fa-file-code";
                    } elseif (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
                        $icon = "fa-file-image";
                    } elseif ($ext == 'pdf') {
                        $icon = "fa-file-pdf";
                    }

                    // معالجة النص المعروض في خانة التهديد
                    $virus_type = !empty($row['virus_type']) ? htmlspecialchars($row['virus_type']) : 'سليم';

                    echo "<tr>
                            <td style='font-weight: 600;'><i class='fas $icon file-icon'></i> $fileName</td>
                            <td dir='ltr' style='text-align: right; font-weight: bold; color: #30b1ff;'>$fileSize</td>
                            <td><span class='badge $badge_class'>$status_txt</span></td>
                            <td><small style='color: #888;'>$virus_type</small></td>
                            <td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>
                            <td>
                                <a href='dashboard.php?page=blocked_files&del_file_id={$row['id']}' 
                                   class='action-btn' 
                                   onclick='return confirm(\"هل أنت متأكد من حذف هذا السجل؟\")' 
                                   title='حذف نهائي'>
                                    <i class='fas fa-trash-can'></i>
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center; padding: 40px; color: #ccc;'>لا توجد عمليات تنزيل مسجلة حالياً</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    // وظيفة البحث اللحظي في جدول الملفات
    document.getElementById('fileSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#filesTable tr');
        
        rows.forEach(row => {
            if (row.cells.length > 1) {
                row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
            }
        });
    });
</script>