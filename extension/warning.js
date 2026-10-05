document.addEventListener('DOMContentLoaded', function() {
    // 1. جلب سبب الحظر من الرابط وعرضه
    const params = new URLSearchParams(window.location.search);
    const reason = params.get('reason');
    const reasonDiv = document.getElementById('reason');
    
    if (reason && reasonDiv) {
        reasonDiv.innerText = "سبب الحظر: " + decodeURIComponent(reason);
    }

    // 2. تفعيل زر الرجوع بدلاً من onclick القديم
    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            e.preventDefault();
            history.back();
        });
    }
});