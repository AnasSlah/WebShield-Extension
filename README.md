# 🛡️ WebShield Extension

A comprehensive web security solution featuring a custom browser extension and a PHP/MySQL backend. **WebShield** is designed to protect users by blocking malicious websites, monitoring harmful files, and providing a centralized dashboard for security management.

## ✨ Features

### Browser Extension
* **🛑 Site Blocking:** Detects and prevents access to known malicious or restricted websites.
* **📁 File Scanning:** Monitors web activities and restricts harmful file interactions.
* **⚠️ Custom Warnings:** Displays a custom warning page (`warning.html`) when a user attempts to access a blocked resource.

### Backend & Dashboard (PHP)
* **📊 Admin Dashboard:** A dedicated control panel (`dashboard.php`) to manage security rules.
* **📝 Log Management:** View and track security scans and blocked attempts (`log_scan.php`).
* **🔐 User Authentication:** Secure registration, login, and profile management for administrators (`register.php`, `login.php`, `profile.php`).
* **⚙️ Dynamic Configuration:** Manage blocked sites and files directly from the database (`blocked_sites.php`, `blocked_files.php`).

<img width="975" height="571" alt="image" src="https://github.com/user-attachments/assets/89aaf8eb-1bbf-480b-a054-760da1affdf1" />

<img width="975" height="609" alt="image" src="https://github.com/user-attachments/assets/886adc1a-961e-4b98-b537-038a3cc99530" />

<img width="709" height="708" alt="image" src="https://github.com/user-attachments/assets/86eec4e9-112e-4810-bcd7-dbb4eea6498f" />

## 📂 Project Structure

The repository is divided into two main components:

```text
WebShield-Extension/
├── backend/               # PHP/MySQL backend (API & Admin Dashboard)
│   ├── security_db.sql    # Database structure and default data
│   ├── db.php             # Database connection configuration
│   └── ... (PHP pages and API endpoints)
└── extension/             # Browser extension source code
    ├── manifest.json      # Extension configuration
    ├── background.js      # Background service worker
    ├── popup.html/js      # Extension user interface
    └── ... (Warning pages and assets)


