<?php
// جلب رقم المستخدم الحالي من الجلسة
$user_id = $_SESSION['user_id'];

// 1. منطق حذف موقع من القائمة (خاص بالمستخدم الحالي فقط)
if (isset($_GET['del_id'])) {
    $id = intval($_GET['del_id']);
    // تنفيذ الحذف من قاعدة البيانات مع التأكد من ملكية السجل
    if ($conn->query("DELETE FROM blacklisted_sites WHERE id = $id AND user_id = $user_id")) {
        echo "<script>window.location.href='dashboard.php?page=blocked_sites';</script>";
        exit;
    }
}

// 2. منطق تحديث البيانات (Update) بعد التعديل (خاص بالمستخدم الحالي فقط)
if (isset($_POST['update_site'])) {
    $id = intval($_POST['site_id']);
    $domain = mysqli_real_escape_string($conn, $_POST['domain']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // التحديث مع التأكد من ملكية السجل
    if ($conn->query("UPDATE blacklisted_sites SET domain='$domain', reason='$reason' WHERE id=$id AND user_id=$user_id")) {
        echo "<script>window.location.href='dashboard.php?page=blocked_sites';</script>";
        exit;
    }
}

// 3. جلب بيانات الموقع المختار للتعديل (للمستخدم الحالي فقط)
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $res = $conn->query("SELECT * FROM blacklisted_sites WHERE id = $edit_id AND user_id = $user_id");
    $edit_data = $res->fetch_assoc();
}
?>

<style>
    /* التنسيق القديم المعتمد (Flat Design) */
    .data-section { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); margin-bottom: 30px; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    
    /* ستايل نموذج التعديل */
    .edit-box { background: #f8f9fa; padding: 25px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #e0e0e0; }
    .edit-box h3 { margin-top: 0; color: #1a1a1a; font-size: 18px; margin-bottom: 15px; }
    .form-row { display: flex; gap: 15px; flex-wrap: wrap; }
    .form-row input, .form-row select { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Cairo'; outline: none; background: white; }
    
    .btn-save { background: #30b1ff; color: white; border: none; padding: 0 30px; border-radius: 10px; cursor: pointer; font-weight: 700; transition: 0.3s; }
    .btn-save:hover { background: #1a94e6; }

    .search-box { position: relative; width: 300px; }
    .search-box input { width: 100%; padding: 12px 40px 12px 15px; border: 1px solid #eee; border-radius: 10px; outline: none; font-family: 'Cairo'; }
    .search-box i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }

    table { width: 100%; border-collapse: collapse; }
    th { padding: 18px; text-align: right; color: #aaa; font-weight: 600; border-bottom: 2px solid #f4f4f4; }
    td { padding: 18px; border-bottom: 1px solid #f8f9fa; color: #444; vertical-align: middle; }

    .badge { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
    
    .actions-wrapper { display: flex; gap: 10px; }
    .action-btn { background: none; border: none; font-size: 18px; cursor: pointer; transition: 0.2s; text-decoration: none; display: flex; align-items: center; justify-content: center; }
    
    .btn-edit { color: #30b1ff; }
    .btn-edit:hover { transform: scale(1.2); }
    .btn-delete { color: #ff4d4d; }
    .btn-delete:hover { transform: scale(1.2); color: #cc0000; }
</style>

<?php if ($edit_data): ?>
<div class="data-section edit-box">
    <h3><i class="fas fa-edit"></i> تعديل بيانات الموقع: <?php echo htmlspecialchars($edit_data['domain']); ?></h3>
    <form method="POST" class="form-row">
        <input type="hidden" name="site_id" value="<?php echo $edit_data['id']; ?>">
        <input type="text" name="domain" value="<?php echo htmlspecialchars($edit_data['domain']); ?>" placeholder="الدومين" required>
        
        <select name="reason" required>
            <option value="" disabled>اختر سبب الحظر</option>
            <option value="موقع تصيد (Phishing)" <?php if($edit_data['reason'] == 'موقع تصيد (Phishing)') echo 'selected'; ?>>موقع تصيد (Phishing)</option>
            <option value="برمجيات خبيثة (Malware)" <?php if($edit_data['reason'] == 'برمجيات خبيثة (Malware)') echo 'selected'; ?>>برمجيات خبيثة (Malware)</option>
            <option value="سبام وإعلانات مزعجة" <?php if($edit_data['reason'] == 'سبام وإعلانات مزعجة') echo 'selected'; ?>>سبام وإعلانات مزعجة</option>
            <option value="تتبع وتجسس" <?php if($edit_data['reason'] == 'تتبع وتجسس') echo 'selected'; ?>>تتبع وتجسس</option>
            <option value="أخرى" <?php if($edit_data['reason'] == 'أخرى') echo 'selected'; ?>>أخرى</option>
        </select>
        
        <button type="submit" name="update_site" class="btn-save">حفظ التعديلات</button>
        <a href="dashboard.php?page=blocked_sites" style="padding:12px; color:#999; text-decoration:none; font-weight:bold;">إلغاء</a>
    </form>
</div>
<?php endif; ?>

<div class="data-section">
    <div class="section-header">
        <h2 style="margin: 0;"><i class="fas fa-ban" style="margin-left: 10px; color: #dc3545;"></i> إدارة المواقع المحظورة</h2>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="siteSearch" placeholder="ابحث عن دومين محدد...">
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>الدومين المحظور</th>
                <th>سبب الحظر</th>
                <th>تاريخ الإضافة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody id="sitesTable">
            <?php
            // جلب بيانات المواقع الخاصة بالمستخدم الحالي فقط وعرضها في الجدول
            $result = $conn->query("SELECT * FROM blacklisted_sites WHERE user_id = $user_id ORDER BY id DESC");
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $reason = $row['reason'];
                    // تحديد لون البادج (Flat Colors)
                    $bg = '#595959';
                    if (strpos($reason, 'Phishing') !== false) $bg = '#dc3545';
                    if (strpos($reason, 'Malware') !== false) $bg = '#a832a4';
                    if (strpos($reason, 'سبام') !== false) $bg = '#17a2b8';
                    if (strpos($reason, 'تتبع') !== false) $bg = '#f39c12';

                    echo "<tr>
                            <td style='font-weight: 600;'>" . htmlspecialchars($row['domain']) . "</td>
                            <td><span class='badge' style='background: $bg;'>$reason</span></td>
                            <td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>
                            <td>
                                <div class='actions-wrapper'>
                                    <a href='dashboard.php?page=blocked_sites&edit_id={$row['id']}' 
                                       class='action-btn btn-edit' title='تعديل'>
                                        <i class='fas fa-pen-to-square'></i>
                                    </a>
                                    <a href='dashboard.php?page=blocked_sites&del_id={$row['id']}' 
                                       class='action-btn btn-delete' 
                                       onclick='return confirm(\"هل أنت متأكد من حذف السجل؟\")'>
                                        <i class='fas fa-trash-can'></i>
                                    </a>
                                </div>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding: 20px; color: #999;'>لا توجد مواقع محظورة مسجلة بحسابك حالياً</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    // وظيفة البحث اللحظي في الجدول
    document.getElementById('siteSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#sitesTable tr');
        rows.forEach(row => {
            if (row.cells.length > 1) {
                row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
            }
        });
    });
</script>