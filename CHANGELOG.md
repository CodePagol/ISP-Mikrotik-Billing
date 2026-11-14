# Changelog

All notable changes to the **ISP Mikrotik Billing** project will be documented in this file.

---

## [v3.0.0] - 2026-11-14

### 🌟 Major Release Highlights
- **Smart Dual-Engine Update System:**
  - Automated detection of Git vs cPanel / Shared Hosting environments.
  - **Git Mode:** Ultra-fast, bandwidth-saving diff-only update via `git pull origin main`.
  - **cPanel Safe ZIP Fallback Mode:** Seamless 1-click update without Git CLI using native PHP `ZipArchive`.
  - **Zero-Loss Data Protection:** Strictly guards `.env`, `storage/` (customer files, logs, backups), `plugins/`, and database configurations during core updates.
- **Auto-Updating Super Admin Notification Engine:**
  - Real-time notification badge in the header navbar alerting Super Admins when new versions are published on GitHub.
  - Interactive popup modal displaying release notes and changelog before updating.
- **Dependency Clean & Vendor Optimization:**
  - Removed dead/unused packages (`laravel/socialite`, `laravel/pennant`, `laravel/scout`, `phpoffice/phpspreadsheet`, `spatie/laravel-backup-server`, `pestphp/pest-plugin`, and orphaned `Plugins` namespace).
  - Cleaned `composer.lock` achieving 0 warnings on `composer validate`.
- **100% Responsive Adaptive UI Across All Modals:**
  - Pin-fixed header & sticky footer architecture with smooth scrolling bodies (`min-height: 0`, `overflow-y: auto`).
  - Dynamic viewport height support (`100dvh`) eliminating overflow and zoom issues on mobile/tablet devices.

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
