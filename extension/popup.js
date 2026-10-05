document.addEventListener('DOMContentLoaded', () => {
    const circle = document.getElementById('circle');
    const statusTxt = document.getElementById('status-txt');
    const list = document.getElementById('list');
    // جلب أيقونة Font Awesome بدلاً من SVG
    const shieldIcon = document.getElementById('shield-icon');

    // 1. استرجاع الحالة وتحديث الألوان والنصوص
    chrome.storage.local.get(['active', 'history'], (data) => {
        const isActive = data.active !== false; // القيمة الافتراضية مفعلة

        if (isActive) {
            // حالة التفعيل (الأخضر)
            circle.className = 'toggle-btn on';
            statusTxt.innerText = "الحماية: مفعلة";
            statusTxt.style.color = "#28a745";
            // تغيير لون الأيقونة باستخدام CSS color
            if (shieldIcon) shieldIcon.style.color = "#28a745";
        } else {
            // حالة الإيقاف (الأحمر)
            circle.className = 'toggle-btn off';
            statusTxt.innerText = "الحماية: غير مفعلة";
            statusTxt.style.color = "#dc3545";
            // تغيير لون الأيقونة للأحمر
            if (shieldIcon) shieldIcon.style.color = "#dc3545";
        }

        // عرض سجل المواقع (آخر 3)
        const items = data.history || [];
        list.innerHTML = items.slice(-3).reverse().map(i => `
            <div class="item">
                <span class="badge" style="background:${i.status === 'safe' ? '#28a745' : '#dc3545'}">
                    ${i.status === 'safe' ? 'آمن' : 'محظور'}
                </span>
                <span style="color: #333;">${i.domain}</span>
            </div>
        `).join('') || "<p style='text-align:center; font-size:10px; color:#ccc;'>لا توجد بيانات</p>";
    });

    // 2. الضغط على الدائرة للتبديل بين (مفعل / غير مفعل)
    circle.onclick = () => {
        chrome.storage.local.get('active', (data) => {
            const newState = data.active === false; // عكس الحالة الحالية
            chrome.storage.local.set({ active: newState }, () => {
                // إعادة تحميل الواجهة لتطبيق التغييرات فوراً
                location.reload();
            });
        });
    };

    // ==========================================
    // التعديل هنا: التوجيه لصفحة تسجيل الدخول
    // ==========================================
    document.getElementById('dash-btn').onclick = () => {
        chrome.tabs.create({url: "http://localhost/testing/index.php"});
    };
});