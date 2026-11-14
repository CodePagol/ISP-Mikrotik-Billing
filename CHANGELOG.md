# Changelog

All notable changes to the **ISP Mikrotik Billing** project will be documented in this file.

---

## [v2.0.1] - 2026-11-14

### 🚀 New Features & Enhancements
- **GitHub Release & Changelog Update Engine:**
  - Integrated `UpdateManagerService` to automatically detect releases, tags, and changelogs from GitHub.
  - Added modern interactive Changelog modal popup in **Plugin Manager** allowing admins to review changes before upgrading.
  - **Super Admin Notification Badge:** Added auto-updating notification badge in the admin navigation header to alert Super Admins whenever a new core update is released.
- **Smart Dual-Engine Updater (cPanel / Shared Hosting Ready):**
  - **Git Mode:** Fast, lightweight diff-only update via `git pull origin main` when Git is available.
  - **cPanel Safe ZIP Merge Mode:** If Git is not installed or the project was uploaded via ZIP, it automatically downloads and extracts the release package using PHP `ZipArchive`.
  - **Protected Data & Config:** Strictly shields `.env`, `storage/` (customer documents, backups, logs), `plugins/`, and database configurations during core updates.

### 🧹 Code Cleanup & Optimizations
- **Composer Cleaned:** Removed unused packages (`laravel/socialite`, `laravel/pennant`, `laravel/scout`, `phpoffice/phpspreadsheet`, `spatie/laravel-backup-server`, `pestphp/pest-plugin`, and orphaned `"Plugins\\": "plugins/"` namespace) reducing vendor size and dependencies.
- **Cache Invalidation:** Improved update cache busting on manual check, reducing polling delays and stale cache states.

### 📱 UI & Responsiveness
- **100% Responsive Modal Standards:** All administrative modals now feature sticky pinned headers/footers with smooth vertical scrolling (`min-height: 0`, `overflow-y: auto`) and dynamic viewport height (`100dvh`) support.

---

## [v2.0.0] - 2026-10-01

### 🚀 New Features
- **Pay-Bridge Payment Gateway Integration:** Integrated modern `codepagol/pay-bridge` payment driver package.
- **Universal Storage Symlink Manager:** Dynamic symbolic link manager for VPS, cPanel primary domain, Addon domains, and subdomains.
- **Standalone cPanel Helper:** Added `scripts/cpanel-storage-link.php`.

### 🛡️ Security Hardening
- **bKash IPN Forgery Mitigation:** 6-layer security gate with HMAC-SHA256 signature verification and Query Payment API validation.
- **Eliminated Obfuscated Code (`eval`):** Replaced legacy `eval()` with transparent Attribution Guard under AGPL-3.0 compliance.

### 🐛 Bug Fixes
- **Console Scheduler Fix:** Resolved scheduler `withoutOverlapping` name exception.
- **Hotspot Analytics Date Bug:** Fixed Carbon object mutation in `HotspotManager`.
