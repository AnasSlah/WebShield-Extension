let blockedSitesCache = {};

function formatBytes(bytes) {
    if (!bytes || bytes === -1 || bytes === 0) return 'قيد الحساب...';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

async function fetchBlockedSites() {
    try {
        const response = await fetch("http://localhost/testing/api.php?action=get_blocked_sites");
        if (response.ok) {
            const data = await response.json();
            if (data.status === "success") blockedSitesCache = data.data;
        }
    } catch (error) { console.error("⚠️ فشل الاتصال:", error); }
}

chrome.runtime.onStartup.addListener(fetchBlockedSites);
chrome.runtime.onInstalled.addListener(fetchBlockedSites);
fetchBlockedSites(); 

chrome.tabs.onCreated.addListener(fetchBlockedSites);
chrome.tabs.onActivated.addListener(fetchBlockedSites);
chrome.windows.onFocusChanged.addListener(fetchBlockedSites);

chrome.alarms.create("syncBlockedSites", { periodInMinutes: 1 });
chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === "syncBlockedSites") fetchBlockedSites();
});

chrome.webNavigation.onBeforeNavigate.addListener(async (details) => {
    if (details.frameId !== 0) return;
    const storage = await chrome.storage.local.get('active');
    if (storage.active === false) return;

    try {
        const url = new URL(details.url);
        if (url.protocol !== "http:" && url.protocol !== "https:") return;
        let domain = url.hostname.replace('www.', '');

        fetch("http://localhost/testing/log_scan.php", {
            method: "POST", headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ url: domain })
        }).catch(() => {});

        if (blockedSitesCache[domain]) {
            updatePopupHistory(domain, 'blocked');
            chrome.tabs.update(details.tabId, { url: chrome.runtime.getURL(`warning.html?reason=${encodeURIComponent(blockedSitesCache[domain])}`) });
            return;
        } 
        
        const isIpOrLocal = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/.test(domain) || domain === 'localhost';
        if (!isIpOrLocal) {
            try {
                const dnsCheck = await fetch(`https://dns.google/resolve?name=${domain}`);
                const dnsData = await dnsCheck.json();
                if (dnsData.Status === 3 || !dnsData.Answer) {
                    updatePopupHistory(domain, 'blocked');
                    chrome.tabs.update(details.tabId, { url: chrome.runtime.getURL(`warning.html?reason=نطاق وهمي`) });
                    return; 
                }
            } catch (dnsError) {}
        }
        updatePopupHistory(domain, 'safe');
    } catch (e) {}
});

function updatePopupHistory(domain, status) {
    chrome.storage.local.get('history', (data) => {
        let history = data.history || [];
        if (history.length === 0 || history[history.length - 1].domain !== domain) {
            history.push({ domain: domain, status: status });
            if (history.length > 10) history.shift();
            chrome.storage.local.set({ history: history });
        }
    });
}

const delay = (ms) => new Promise(res => setTimeout(res, ms));

chrome.downloads.onCreated.addListener(async (downloadItem) => {
    const storage = await chrome.storage.local.get('active');
    if (storage.active === false) return;

    let activeTabId = null;
    let cleanFileName = "ملف مجهول";
    let finalFileName = cleanFileName;
    let finalFileSize = "قيد الحساب...";

    try {
        chrome.downloads.pause(downloadItem.id);

        if (downloadItem.filename) {
            cleanFileName = downloadItem.filename.split(/[/\\]/).pop();
        }

        finalFileSize = formatBytes(downloadItem.totalBytes);

        const [activeTab] = await chrome.tabs.query({ active: true, currentWindow: true });
        if (activeTab && activeTab.url.startsWith('http')) {
            activeTabId = activeTab.id;
            await chrome.scripting.executeScript({
                target: { tabId: activeTabId },
                func: injectUI,
                args: [cleanFileName, finalFileSize]
            }).catch(() => { activeTabId = null; });
        }

        // ====================================================
        // التتبع اللحظي: صيد الاسم والحجم معاً من المتصفح
        // ====================================================
        let namePoller = setInterval(async () => {
            let [dl] = await chrome.downloads.search({id: downloadItem.id});
            if (dl) {
                let tempName = dl.filename ? dl.filename.split(/[/\\]/).pop() : null;
                let tempSize = (dl.totalBytes > 0) ? formatBytes(dl.totalBytes) : null;
                
                let isNameReady = (tempName && !tempName.endsWith('.crdownload') && !tempName.startsWith('Unconfirmed') && tempName !== "ملف مجهول");

                if (isNameReady) finalFileName = tempName;
                if (tempSize) finalFileSize = tempSize;

                if ((isNameReady || tempSize) && activeTabId) {
                    updateUI(activeTabId, null, null, null, finalFileName, finalFileSize);
                }

                if (isNameReady && tempSize) {
                    clearInterval(namePoller);
                }
            }
        }, 400);

        await delay(500);
        if (activeTabId) updateUI(activeTabId, 30, "جاري مطابقة القواعد وفحص الرابط...");

        // التأكد النهائي من الحجم قبل الإرسال للسيرفر
        let [finalDl] = await chrome.downloads.search({id: downloadItem.id});
        if (finalDl && finalDl.totalBytes > 0) {
            finalFileSize = formatBytes(finalDl.totalBytes);
        }

        const apiUrl = `http://localhost/testing/api.php?action=check_file_security&name=${encodeURIComponent(finalFileName)}&url=${encodeURIComponent(downloadItem.url)}&size=${encodeURIComponent(finalFileSize)}`;
        const response = await fetch(apiUrl);
        
        clearInterval(namePoller);

        if (!response.ok) throw new Error("فشل الاتصال");

        const result = await response.json();
        
        if (result.file_name && result.file_name !== 'unknown' && finalFileName === "ملف مجهول") {
            finalFileName = result.file_name;
        }

        await delay(600); 
        if (activeTabId) updateUI(activeTabId, 70, "تحليل التكوين (Magic Numbers)...", "#30b1ff", finalFileName, finalFileSize);
        await delay(400);

        if (result.status === "safe") {
            if (activeTabId) {
                updateUI(activeTabId, 100, "✅ آمن! جاري التنزيل...", "#28a745", finalFileName, finalFileSize);
                setTimeout(() => removeUI(activeTabId), 3000);
            }
            chrome.downloads.resume(downloadItem.id);
            return;
        }

        if (activeTabId) {
            updateUI(activeTabId, 100, "🚫 تحذير! تم اكتشاف تهديد أمني.", "#dc3545", finalFileName, finalFileSize);
            setTimeout(() => removeUI(activeTabId), 3000);
        }

        chrome.downloads.cancel(downloadItem.id);
        const warningUrl = chrome.runtime.getURL(
            `warning.html?reason=${encodeURIComponent(result.virus_type || "ملف ضار")}&file=${encodeURIComponent(downloadItem.url)}&name=${encodeURIComponent(finalFileName)}`
        );
        chrome.tabs.create({ url: warningUrl });

    } catch (err) {
        chrome.downloads.cancel(downloadItem.id);
        if (activeTabId) {
            updateUI(activeTabId, 100, "⚠️ فشل الفحص، تم إلغاء التنزيل.", "#dc3545");
            setTimeout(() => removeUI(activeTabId), 3000);
        }
    }
});

// ====================================
// دوال حقن الواجهة (UI Injection)
// ====================================
function injectUI(filename, filesize) {
    if (document.getElementById('secure-shield-overlay')) return;
    
    const overlay = document.createElement('div');
    overlay.id = 'secure-shield-overlay';
    
    overlay.style.cssText = `
        position: fixed !important; top: 20px !important; right: 20px !important; width: 320px !important;
        background: #fff !important; border-radius: 12px !important; box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
        padding: 20px !important; z-index: 2147483647 !important; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        border-bottom: 4px solid #30b1ff !important; direction: rtl !important; 
        transition: all 0.3s ease !important; color: #333 !important; pointer-events: none !important; 
    `;
    
    overlay.innerHTML = `
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <span style="font-size: 22px; margin-left: 10px;">🛡️</span>
            <strong style="color: #1a1a1a; font-size: 16px;">درع الأمان | فحص التنزيل</strong>
        </div>
        <div style="font-size: 13px; color: #444; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right;">
            <b>الاسم:</b> <span id="ss-filename" dir="ltr" style="font-weight:bold; color:#1a1a1a;">${filename}</span>
        </div>
        <div style="font-size: 12px; color: #777; margin-bottom: 12px; text-align: right;">
            <b>الحجم:</b> <span id="ss-filesize" dir="ltr" style="font-weight: 600; color: #30b1ff;">${filesize}</span>
        </div>
        <div style="background: #f0f0f0 !important; border-radius: 8px !important; height: 8px !important; overflow: hidden !important; margin-bottom: 12px !important;">
            <div id="ss-progress" style="width: 10%; height: 100%; background: #30b1ff !important; transition: width 0.5s ease, background 0.3s ease !important;"></div>
        </div>
        <div id="ss-text" style="font-size: 13px !important; color: #30b1ff !important; font-weight: 700 !important;">جاري اعتراض الملف...</div>
    `;
    document.documentElement.appendChild(overlay);
}

function updateUI(tabId, percent, text, color = "#30b1ff", updatedName = null, updatedSize = null) {
    chrome.scripting.executeScript({
        target: { tabId: tabId },
        func: (p, t, c, n, s) => {
            if (p !== null) {
                const bar = document.getElementById('ss-progress');
                const txt = document.getElementById('ss-text');
                if (bar && txt) {
                    bar.style.setProperty('width', p + '%', 'important'); 
                    bar.style.setProperty('background', c, 'important');
                    txt.innerText = t; 
                    txt.style.setProperty('color', c, 'important');
                }
            }
            if (n && n !== "ملف مجهول") {
                const nameEl = document.getElementById('ss-filename');
                if (nameEl) nameEl.innerText = n;
            }
            if (s && s !== "قيد الحساب...") {
                const sizeEl = document.getElementById('ss-filesize');
                if (sizeEl) sizeEl.innerText = s;
            }
        },
        args: [percent, text, color, updatedName, updatedSize]
    }).catch(()=>{});
}

function removeUI(tabId) {
    chrome.scripting.executeScript({
        target: { tabId: tabId },
        func: () => {
            const el = document.getElementById('secure-shield-overlay');
            if (el) { el.style.setProperty('opacity', '0', 'important'); setTimeout(() => el.remove(), 400); }
        }
    }).catch(()=>{});
}