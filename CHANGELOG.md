# Changelog

All notable changes to the **ISP Mikrotik Billing** project will be documented in this file.

---

## [v3.0.0] - 2026-11-14

### 🌟 Major Release Highlights

- **🔌 Extensible Developer Plugin Architecture & Ecosystem:**
  - **Modular Decoupled Design:** Developers can now build, publish, and monetize custom extensions independently inside `plugins/{slug}/` without modifying core system files.
  - **Standardized Plugin Manifest & Lifecycle:** Support for standard `plugin.json` (metadata, author, version, dependencies, license requirement, GitHub repository) and `Plugin.php` (hooks for `boot()`, `register()`, and `uninstall()`).
  - **Autonomous Database & Routing:** Plugins support their own database migrations (`plugins/{slug}/database/migrations`), dedicated routes (`web.php`, `api.php`), and Blade views.
  - **3 Modular Installation Methods for Admins & Developers:**
    1. **ZIP Archive Upload:** Upload pre-packaged plugins directly via admin UI.
    2. **GitHub / Git URL Clone:** Instant 1-click clone from any public or private Git repository with custom branch selection.
    3. **Composer Package Installation:** Install official or third-party extensions directly through Composer.
  - **In-App Developer Guide:** Embedded documentation tab inside **Plugin Manager &rarr; Developer Guide** with starter templates, directory hierarchy, and hook references.
  - **Commercial Licensing Guard:** Built-in licensing validation system for developers to safeguard commercial and proprietary extensions.
  - **Independent Plugin Versioning & 1-Click Updates:** Plugins in standalone repositories (e.g., `ISP-Billing-inventory-manager`) can be versioned and updated independently via the update engine without requiring a core system redeployment.

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
