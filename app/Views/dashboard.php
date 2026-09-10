<!DOCTYPE html>
<html lang="id">
<head>

    <!-- Dashboard Share Modal -->
    <div id="dashboardShareModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 14px; width: 90%; max-width: 540px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8);">
                <h3 id="dashboardShareModalTitle" style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0;">🔗 Bagikan Link CCTV</h3>
                <button class="modal-close" onclick="closeDashboardShareModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">Status Hak Akses: <span id="shareModalAccessBadge" style="font-size: 11px; padding: 2px 7px; border-radius: 5px; font-weight: 700;">Publik</span></div>
                        <div style="font-size: 11.5px; color: var(--text-secondary); margin-top: 2px;" id="shareModalAccessDesc">Dapat ditonton siapa saja tanpa login root</div>
                    </div>
                    <button id="btnToggleShareAccess" onclick="toggleShareAccessFromModal()" class="btn btn-ghost" style="font-size: 11.5px; padding: 5px 10px;">Ubah ke Privat</button>
                </div>

                <div class="form-field">
                    <label class="form-label">Link Tampilan Standalone (Web Player / Browser):</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="dashShareLinkWeb" class="form-input" readonly style="font-size: 12px;">
                        <button class="btn btn-primary" onclick="copyDashInput('dashShareLinkWeb')" style="padding: 6px 14px; font-size: 12px; white-space: nowrap;">Salin</button>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label">Direct WebRTC Player URL:</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="dashShareLinkStream" class="form-input" readonly style="font-size: 12px;">
                        <button class="btn btn-primary" onclick="copyDashInput('dashShareLinkStream')" style="padding: 6px 14px; font-size: 12px; white-space: nowrap;">Salin</button>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label">Kode Embed Iframe (Website / Aplikasi Lain):</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="dashShareLinkEmbed" class="form-input" readonly style="font-size: 12px;">
                        <button class="btn btn-primary" onclick="copyDashInput('dashShareLinkEmbed')" style="padding: 6px 14px; font-size: 12px; white-space: nowrap;">Salin</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 14px; width: 90%; max-width: 440px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8);">
                <h3 style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0;">🔑 Ganti Password Akun Root</h3>
                <button class="modal-close" onclick="closeChangePasswordModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <form onsubmit="handleChangePasswordSubmit(event)" class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-field">
                    <label class="form-label">Password Root Saat Ini *</label>
                    <input type="password" id="currentRootPass" required placeholder="Masukkan password lama" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Password Root Baru *</label>
                    <input type="password" id="newRootPass" required minlength="4" placeholder="Minimal 4 karakter" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Ulangi Password Baru *</label>
                    <input type="password" id="confirmNewRootPass" required minlength="4" placeholder="Ketik ulang password baru" class="form-input">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 6px;">
                    <button type="button" onclick="closeChangePasswordModal()" class="btn btn-ghost">Batal</button>
                    <button type="submit" id="btnSubmitChangePass" class="btn btn-primary">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ==========================================
        // 🌓 THEME MANAGER (LIGHT / DARK MODE)
        // ==========================================
        function initThemeUI() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            updateThemeToggleUI(currentTheme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('nvr_theme', newTheme);
            updateThemeToggleUI(newTheme);
            renderTimelineCanvas();
        }

        function updateThemeToggleUI(theme) {
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (icon && text) {
                if (theme === 'light') {
                    icon.innerText = '☀️';
                    text.innerText = 'Light';
                } else {
                    icon.innerText = '🌙';
                    text.innerText = 'Dark';
                }
            }
        }

        // Instant Theme Check (Prevents Flash of Dark/Light Theme on Page Load)
        (function() {
            const savedTheme = localStorage.getItem('nvr_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Clarity NVR CCTV Monitor</title>
    <!-- Iconic Favicon -->
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <link rel="shortcut icon" type="image/png" href="/assets/logo.png">
    <link rel="apple-touch-icon" href="/assets/logo.png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root, [data-theme="dark"] {
            --bg-base:     #0d1117;
            --bg-surface:  #161b22;
            --bg-elevated: #1c2333;
            --bg-overlay:  #21262d;
            --border-muted:   rgba(48, 54, 61, 1);
            --border-default: rgba(56, 65, 74, 1);
            --border-subtle:  rgba(33, 38, 45, 1);
            --text-primary:   #e6edf3;
            --text-secondary: #7d8590;
            --text-muted:     #484f58;
            --header-bg:   rgba(13, 17, 23, 0.96);
            --panel-bg:    #161b22;
            --input-bg:    #0d1117;
            --btn-bg:      rgba(177, 186, 196, 0.12);
            --btn-hover:   rgba(177, 186, 196, 0.2);
            --accent-blue:  #388bfd;
            --accent-teal:  #39d353;
            --accent-gold:  #d4a843;
            --accent-green: #3fb950;
            --accent-red:   #f85149;
            --accent-orange: #e3b341;
            --accent-blue-dim: rgba(56, 139, 253, 0.15);
            --accent-green-dim: rgba(63, 185, 80, 0.15);
            --accent-red-dim:   rgba(248, 81, 73, 0.15);
            --accent-gold-dim:  rgba(212, 168, 67, 0.15);
            --shadow-sm:  0 1px 0 rgba(0,0,0,0.3);
            --shadow-md:  0 3px 12px rgba(1,4,9,0.8);
            --shadow-lg:  0 8px 24px rgba(1,4,9,0.9);
            --glass-blur: blur(16px);
            --nav-width:      56px;
            --nav-width-open: 240px;
            --modal-bg: #161b22;
            --modal-header-bg: #1c2333;
            --ptz-bg: rgba(1,4,9,0.82);
            --timeline-bg: #161b22;
            --timeline-track: rgba(255,255,255,0.06);
            --thumb-bg: #e6edf3;
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-med:  250ms cubic-bezier(0.4, 0, 0.2, 1);
            /* legacy compat */
            --bg-main: #0d1117;
            --bg-card: #161b22;
            --bg-hover: #1c2333;
            --border-color: rgba(48,54,61,1);
            --shadow-lg: 0 8px 24px rgba(1,4,9,0.9);

            --player-header-bg: rgba(13, 17, 23, 0.82);
            --player-header-border: rgba(255, 255, 255, 0.12);
            --player-header-text: #ffffff;
            --player-btn-bg: rgba(255, 255, 255, 0.12);
            --player-btn-border: rgba(255, 255, 255, 0.22);
            --player-btn-hover-bg: rgba(255, 255, 255, 0.24);
            --player-btn-text: #ffffff;
    
        }

        [data-theme="light"] {
            --bg-base:     #ffffff;
            --bg-surface:  #f6f8fa;
            --bg-elevated: #eaeef2;
            --bg-overlay:  #d0d7de;
            --border-muted:   #d0d7de;
            --border-default: #d8dee4;
            --border-subtle:  #eaeef2;
            --text-primary:   #1f2328;
            --text-secondary: #636e7b;
            --text-muted:     #9198a1;
            --header-bg:   rgba(255,255,255,0.96);
            --panel-bg:    #f6f8fa;
            --input-bg:    #ffffff;
            --btn-bg:      #f6f8fa;
            --btn-hover:   #eaeef2;
            --accent-blue:  #0969da;
            --accent-teal:  #1a7f37;
            --accent-gold:  #9a6700;
            --accent-green: #1a7f37;
            --accent-red:   #cf222e;
            --accent-orange: #953800;
            --accent-blue-dim: rgba(9,105,218,0.1);
            --accent-green-dim: rgba(26,127,55,0.1);
            --accent-red-dim:   rgba(207,34,46,0.1);
            --accent-gold-dim:  rgba(154,103,0,0.1);
            --shadow-sm: 0 1px 0 rgba(0,0,0,0.04);
            --shadow-md: 0 3px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --modal-bg: #ffffff;
            --modal-header-bg: #f6f8fa;
            --ptz-bg: rgba(255,255,255,0.96);
            --timeline-bg: #ffffff;
            --timeline-track: #d0d7de;
            --thumb-bg: #0969da;
            /* legacy */
            --bg-main: #ffffff;
            --bg-card: #f6f8fa;
            --bg-hover: #eaeef2;
            --border-color: #d0d7de;

            --player-header-bg: rgba(255, 255, 255, 0.90);
            --player-header-border: rgba(0, 0, 0, 0.12);
            --player-header-text: #1f2328;
            --player-btn-bg: rgba(0, 0, 0, 0.06);
            --player-btn-border: rgba(0, 0, 0, 0.14);
            --player-btn-hover-bg: rgba(0, 0, 0, 0.12);
            --player-btn-text: #1f2328;
    
        }

        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 13px;
            background: var(--bg-base);
            color: var(--text-primary);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            line-height: 1.5;
        }

        /* ==========================================
           TOP HEADER - Ultra-Minimal
        ========================================== */
        header {
            height: 48px;
            background: var(--header-bg);
            backdrop-filter: var(--glass-blur);
            border-bottom: 1px solid var(--border-muted);
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 10px;
            z-index: 50;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-shrink: 0;
        }
        .header-brand h1 {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.2px;
            white-space: nowrap;
        }
        .header-divider {
            width: 1px;
            height: 20px;
            background: var(--border-muted);
            flex-shrink: 0;
        }
        .header-spacer { flex: 1; }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--accent-green);
            background: var(--accent-green-dim);
            border: 1px solid rgba(63,185,80,0.25);
            padding: 3px 9px;
            border-radius: 20px;
            flex-shrink: 0;
        }
        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent-green);
            box-shadow: 0 0 5px var(--accent-green);
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%,100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        .hbtn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            padding: 5px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            font-family: inherit;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .hbtn:hover {
            transform: translateY(-1px);
        }
        .hbtn:active {
            transform: translateY(0);
        }

        /* 📺 Grid (Blue) */
        .hbtn.btn-grid, .hbtn.primary {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.35);
            color: #60a5fa;
        }
        .hbtn.btn-grid:hover, .hbtn.primary:hover {
            background: rgba(59, 130, 246, 0.24);
            border-color: rgba(59, 130, 246, 0.6);
            color: #93c5fd;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
        }

        /* ➕ CCTV (Emerald Green) */
        .hbtn.btn-cctv, .hbtn.success {
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(16, 185, 129, 0.35);
            color: #34d399;
        }
        .hbtn.btn-cctv:hover, .hbtn.success:hover {
            background: rgba(16, 185, 129, 0.24);
            border-color: rgba(16, 185, 129, 0.6);
            color: #6ee7b7;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
        }

        /* ⚙ Pengaturan (Purple/Violet) */
        .hbtn.btn-settings {
            background: rgba(168, 85, 247, 0.12);
            border-color: rgba(168, 85, 247, 0.35);
            color: #c084fc;
        }
        .hbtn.btn-settings:hover {
            background: rgba(168, 85, 247, 0.24);
            border-color: rgba(168, 85, 247, 0.6);
            color: #e9d5ff;
            box-shadow: 0 0 12px rgba(168, 85, 247, 0.3);
        }

        /* 📋 Log (Amber/Gold) */
        .hbtn.btn-log {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.35);
            color: #fbbf24;
        }
        .hbtn.btn-log:hover {
            background: rgba(245, 158, 11, 0.24);
            border-color: rgba(245, 158, 11, 0.6);
            color: #fde68a;
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.3);
        }

        /* 🌐 Net (Cyan) */
        .hbtn.btn-net {
            background: rgba(6, 182, 212, 0.12);
            border-color: rgba(6, 182, 212, 0.35);
            color: #22d3ee;
        }
        .hbtn.btn-net:hover {
            background: rgba(6, 182, 212, 0.24);
            border-color: rgba(6, 182, 212, 0.6);
            color: #67e8f9;
            box-shadow: 0 0 12px rgba(6, 182, 212, 0.3);
        }

        /* 🔑 Password (Orange) */
        .hbtn.btn-password {
            background: rgba(249, 115, 22, 0.12);
            border-color: rgba(249, 115, 22, 0.35);
            color: #fb923c;
        }
        .hbtn.btn-password:hover {
            background: rgba(249, 115, 22, 0.24);
            border-color: rgba(249, 115, 22, 0.6);
            color: #fdba74;
            box-shadow: 0 0 12px rgba(249, 115, 22, 0.3);
        }

        /* 🚪 Logout (Red) */
        .hbtn.btn-logout {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.35);
            color: #f87171;
            text-decoration: none;
        }
        .hbtn.btn-logout:hover {
            background: rgba(239, 68, 68, 0.24);
            border-color: rgba(239, 68, 68, 0.6);
            color: #fca5a5;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
        }

        /* 🌙 Dark/Light Theme Button (Yellow/Gold) */
        .theme-toggle-btn {
            background: rgba(234, 179, 8, 0.12);
            border: 1px solid rgba(234, 179, 8, 0.35);
            color: #fde047;
            padding: 5px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .theme-toggle-btn:hover {
            background: rgba(234, 179, 8, 0.24);
            border-color: rgba(234, 179, 8, 0.6);
            color: #fef08a;
            transform: translateY(-1px);
            box-shadow: 0 0 12px rgba(234, 179, 8, 0.3);
        }
        .theme-toggle-btn:active {
            transform: translateY(0);
        }

        /* ==========================================
           MAIN APP CONTAINER
        ========================================== */
        .app-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ==========================================
           LEFT NAV-RAIL (Smooth Flow Shift - Never Covers Timestamp)
        ========================================== */
        .nav-rail {
            width: var(--nav-width);
            background: var(--bg-surface);
            border-right: 1px solid var(--border-muted);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 220ms cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            z-index: 20;
            position: relative;
        }
        .nav-rail:hover,
        .nav-rail.expanded,
        .nav-rail:focus-within {
            width: var(--nav-width-open);
        }
        .nav-rail-inner {
            width: var(--nav-width-open);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        /* Clean Dock Nav Header & Search */
        .nav-cam-header {
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-muted);
            height: 38px;
            flex-shrink: 0;
            overflow: hidden;
            padding: 0;
        }
        .nav-cam-header-icon {
            width: var(--nav-width);
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text-muted);
        }
        .nav-cam-header-text {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--text-muted);
            white-space: nowrap;
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity 200ms, transform 200ms;
            flex: 1;
        }
        .nav-cam-header-reset {
            font-size: 10px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 2px 8px;
            margin-right: 8px;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 200ms;
            font-family: inherit;
        }
        .nav-cam-header-reset:hover { color: var(--accent-gold); }
        .nav-rail:hover .nav-cam-header-text,
        .nav-rail.expanded .nav-cam-header-text,
        .nav-rail:focus-within .nav-cam-header-text { opacity: 1; transform: translateX(0); }
        .nav-rail:hover .nav-cam-header-reset,
        .nav-rail.expanded .nav-cam-header-reset,
        .nav-rail:focus-within .nav-cam-header-reset { opacity: 1; }

        /* Search Bar in Rail (Hidden when collapsed, smooth slide-in on hover) */
        .nav-cam-search {
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-muted);
            background: var(--bg-surface);
            height: 38px;
            position: relative;
            flex-shrink: 0;
            overflow: hidden;
            padding: 0;
        }
        .nav-cam-search-icon {
            width: var(--nav-width);
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text-muted);
        }
        .nav-cam-search-input-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            position: relative;
            min-width: 0;
            padding-right: 8px;
            opacity: 0;
            pointer-events: none;
            transform: translateX(-4px);
            transition: opacity 200ms ease, transform 200ms ease;
        }
        .nav-rail:hover .nav-cam-search-input-wrap,
        .nav-rail.expanded .nav-cam-search-input-wrap,
        .nav-rail:focus-within .nav-cam-search-input-wrap {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }
        .nav-cam-search-input-wrap input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border-default);
            border-radius: 5px;
            padding: 4px 22px 4px 8px;
            color: var(--text-primary);
            font-size: 11.5px;
            outline: none;
            font-family: inherit;
        }
        .search-clear-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 14px;
            cursor: pointer;
            line-height: 1;
            padding: 2px;
        }

        .nav-cam-list {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            list-style: none;
            padding: 4px 0;
        }
        .nav-cam-list::-webkit-scrollbar { width: 3px; }
        .nav-cam-list::-webkit-scrollbar-track { background: transparent; }
        .nav-cam-list::-webkit-scrollbar-thumb { background: var(--border-default); border-radius: 2px; }

        .cam-row {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: background 150ms;
            text-align: left;
        }
        .cam-row:hover { background: var(--btn-hover); }
        .cam-row.active { background: var(--accent-blue-dim); }
        .cam-row.active::before {
            content: '';
            position: absolute;
            left: 0; top: 5px; bottom: 5px;
            width: 2px;
            background: var(--accent-blue);
            border-radius: 0 2px 2px 0;
        }

        .cam-row-icon {
            width: var(--nav-width);
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            font-size: 16px;
        }
        .rec-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            position: absolute;
            top: 7px; right: 11px;
        }
        .rec-dot.on {
            background: var(--accent-red);
            box-shadow: 0 0 4px var(--accent-red);
            animation: rec-blink 1.5s infinite;
        }
        .rec-dot.off { background: var(--border-default); }
        @keyframes rec-blink {
            0%,100% { opacity: 1; }
            50% { opacity: 0.25; }
        }

        .cam-row-info {
            flex: 1;
            min-width: 0;
            padding: 4px 8px 4px 0;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 250ms, transform 250ms;
            white-space: nowrap;
            overflow: hidden;
        }
        .nav-rail:hover .cam-row-info,
        .nav-rail.expanded .cam-row-info {
            opacity: 1;
            transform: translateX(0);
        }

        .cam-row-name {
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            line-height: 1.3;
        }
        .cam-row.active .cam-row-name { color: var(--accent-blue); }

        .cam-row-sub {
            font-size: 10px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cam-row-actions {
            display: none;
            gap: 4px;
            padding: 2px 6px 2px 10px;
            flex-shrink: 0;
            align-items: center;
            background: linear-gradient(90deg, rgba(13,17,23,0) 0%, rgba(13,17,23,0.92) 20%);
            border-radius: 0 6px 6px 0;
            z-index: 5;
        }
        .nav-rail:hover .cam-row:hover .cam-row-actions,
        .nav-rail.expanded .cam-row:hover .cam-row-actions {
            display: flex;
        }
        .cam-action-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-muted);
            width: 22px;
            height: 22px;
            padding: 0;
            border-radius: 4px;
            cursor: pointer;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 150ms ease;
            flex-shrink: 0;
        }
        .cam-action-btn:hover {
            background: var(--bg-elevated);
            color: var(--text-primary);
            border-color: var(--border-default);
            transform: scale(1.08);
        }
        .cam-action-btn.rec-btn:hover,
        .cam-action-btn.rec-btn.is-active {
            border-color: rgba(239, 68, 68, 0.4);
            background: rgba(239, 68, 68, 0.15);
            color: var(--accent-red);
        }
        .cam-action-btn.share-btn:hover {
            border-color: rgba(212, 168, 67, 0.4);
            background: rgba(212, 168, 67, 0.15);
            color: var(--accent-gold);
        }

        /* Multi-select checkboxes (shown in grid mode) */
        .cam-row-check {
            display: none;
            align-items: center;
            justify-content: center;
            width: 20px;
            flex-shrink: 0;
            padding-right: 4px;
        }
        .cam-row-check.show { display: flex; }
        .cam-row-check input {
            width: 13px; height: 13px;
            cursor: pointer;
            accent-color: var(--accent-blue);
        }

        .nav-rail-bottom {
            border-top: 1px solid var(--border-muted);
            padding: 4px 0;
            flex-shrink: 0;
        }

        /* ==========================================
           MAIN CONTENT AREA
        ========================================== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg-base);
            min-width: 0;
            transition: all 220ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .player-wrap {
            flex: 1;
            display: flex;
            min-height: 0;
        }

        .player-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #000;
            position: relative;
            overflow: hidden;
            justify-content: space-between;
        }

        /* player-card fills vertical space cleanly above timeline */
        .player-card {
            flex: 1;
            width: 100%;
            min-height: 0;
            position: relative;
            background: #000;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* Floating glass header above the stream (Theme Adaptive) */
        .player-header {
            position: absolute;
            top: 10px; left: 10px; right: 10px;
            background: var(--player-header-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--player-header-border);
            border-radius: 8px;
            padding: 7px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 30;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 150ms, transform 150ms, background 200ms, border-color 200ms;
            pointer-events: none;
            box-shadow: var(--shadow-md);
        }
        .player-card:hover .player-header,
        .player-card.show-header .player-header {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .player-title {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--player-header-text);
            text-shadow: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 380px;
        }

        .player-action-btn, #headerMicBtn, #headerPtzToggleBtn {
            background: var(--player-btn-bg);
            border: 1px solid var(--player-btn-border);
            color: var(--player-btn-text);
            padding: 3px 9px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 150ms;
            backdrop-filter: blur(4px);
            font-family: inherit;
        }
        .player-action-btn:hover, #headerMicBtn:hover, #headerPtzToggleBtn:hover {
            background: var(--player-btn-hover-bg);
            transform: translateY(-1px);
        }

        .mode-badge, .camera-badge {
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .camera-badge.active {
            background: var(--accent-green-dim);
            color: var(--accent-green);
            border: 1px solid rgba(63,185,80,0.25);
        }
        .camera-badge.inactive {
            background: var(--accent-red-dim);
            color: var(--accent-red);
            border: 1px solid rgba(248,81,73,0.25);
        }

        .player-body {
            width: 100%;
            height: 100%;
            flex: 1;
            min-height: 0;
            background: #000;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
        }

        #liveStreamFrame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background: #000;
            object-fit: fill;
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
        }
        #playbackVideo {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
            background: #000;
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
        }

        /* ==========================================
           TIMELINE PANEL (Pinned flush to bottom)
        ========================================== */
        #unifiedTimelineContainer {
            background: var(--bg-surface);
            border-top: 1px solid var(--border-muted);
            padding: 8px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-shrink: 0;
            margin-top: auto;
            z-index: 10;
        }
        .timeline-toprow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .timeline-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .timeline-status-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .timeline-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tl-btn {
            background: var(--btn-bg);
            border: 1px solid var(--border-default);
            color: var(--text-secondary);
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 11px;
            cursor: pointer;
            font-family: inherit;
            transition: all 150ms;
        }
        .tl-btn:hover { background: var(--btn-hover); color: var(--text-primary); }
        .tl-btn.live {
            background: var(--accent-red-dim);
            border-color: rgba(248,81,73,0.3);
            color: var(--accent-red);
            font-weight: 600;
            display: none;
        }
        .date-nav {
            display: flex;
            align-items: center;
            gap: 2px;
            background: var(--input-bg);
            border: 1px solid var(--border-default);
            border-radius: 5px;
            padding: 2px 4px;
        }
        .date-nav-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 10px;
            line-height: 1.6;
        }
        .date-nav-btn:hover { background: var(--btn-hover); color: var(--text-primary); }
        #timelineDateLabel {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-primary);
            min-width: 62px;
            text-align: center;
            user-select: none;
        }

        /* Timeline track */
        .timeline-track-wrap {
            position: relative;
            height: 6px;
            background: var(--timeline-track);
            border-radius: 3px;
            overflow: visible;
        }
        #timelineCanvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border-radius: 3px;
            pointer-events: none;
        }
        .timeline-slider {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            -webkit-appearance: none;
            background: transparent;
            outline: none;
            cursor: pointer;
            margin: 0;
            z-index: 5;
        }
        .timeline-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 14px; height: 14px;
            border-radius: 50%;
            background: var(--thumb-bg);
            border: 2px solid var(--accent-blue);
            cursor: pointer;
            box-shadow: 0 0 0 3px var(--accent-blue-dim);
        }
        .timeline-labels {
            display: flex;
            justify-content: space-between;
            font-size: 9.5px;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
            margin-top: 1px;
        }

        /* ==========================================
           RIGHT PANEL: Recordings
        ========================================== */
        .recordings-panel {
            width: 270px;
            background: var(--bg-surface);
            border-left: 1px solid var(--border-muted);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
        }
        .panel-header, .panel-head {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .filter-panel {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-muted);
            display: flex;
            flex-direction: column;
            gap: 7px;
            flex-shrink: 0;
            background: var(--bg-surface);
        }
        .filter-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filter-label {
            font-size: 10.5px;
            color: var(--text-muted);
            width: 52px;
            font-weight: 500;
            flex-shrink: 0;
        }
        .filter-select, .filter-input {
            flex: 1;
            padding: 4px 7px;
            background: var(--input-bg);
            border: 1px solid var(--border-default);
            border-radius: 5px;
            color: var(--text-primary);
            font-size: 12px;
            outline: none;
            transition: border-color 150ms;
            font-family: inherit;
        }
        .filter-select:focus, .filter-input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px var(--accent-blue-dim);
        }
        .list-container {
            flex: 1;
            overflow-y: auto;
            list-style: none;
        }
        .list-container::-webkit-scrollbar { width: 3px; }
        .list-container::-webkit-scrollbar-thumb { background: var(--border-default); border-radius: 2px; }

        .recording-date-group {
            padding: 6px 12px;
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--bg-elevated);
            border-bottom: 1px solid var(--border-muted);
        }
        .recording-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            border-bottom: 1px solid var(--border-subtle);
            cursor: pointer;
            transition: background 150ms;
        }
        .recording-item:hover { background: var(--btn-hover); }
        .rec-info { flex: 1; overflow: hidden; }
        .rec-time {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rec-actions { margin-left: 8px; }
        .btn-download {
            background: var(--bg-overlay);
            border: 1px solid var(--border-default);
            color: var(--text-secondary);
            width: 24px; height: 24px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 11px;
            transition: all 150ms;
        }
        .btn-download:hover { background: var(--btn-hover); color: var(--text-primary); }

        /* ==========================================
           ENTERPRISE MATRIX GRID VIEW (VIDEO WALL)
        ========================================== */
        #gridPlayerView {
            display: none;
            width: 100%;
            height: 100%;
            min-height: 0;
            flex-direction: column;
            background: #05070b;
            overflow: hidden;
            position: relative;
        }

        .grid-control-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            background: rgba(13, 17, 23, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
            gap: 12px;
            z-index: 25;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            flex-wrap: wrap;
        }

        .grid-bar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .grid-brand-badge {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: var(--accent-gold);
            display: flex;
            align-items: center;
            gap: 6px;
            user-select: none;
        }
        .grid-status-pill {
            font-size: 10.5px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 12px;
            background: rgba(63, 185, 80, 0.12);
            color: var(--accent-green);
            border: 1px solid rgba(63, 185, 80, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .grid-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent-green);
            box-shadow: 0 0 6px var(--accent-green);
            animation: rec-blink 1.5s infinite;
        }
        .grid-clock-pill {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.04);
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid var(--border-muted);
        }

        .grid-bar-center {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .grid-layout-selector {
            display: flex;
            align-items: center;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-muted);
            border-radius: 8px;
            padding: 2px;
            gap: 2px;
        }
        .grid-layout-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 150ms ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .grid-layout-btn:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.06);
        }
        .grid-layout-btn.active {
            background: var(--accent-blue);
            color: #fff;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
        }

        .grid-bar-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .grid-tool-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-muted);
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 150ms ease;
        }
        .grid-tool-btn:hover {
            background: var(--bg-elevated);
            color: var(--text-primary);
            border-color: var(--border-default);
            transform: translateY(-1px);
        }
        .grid-tool-btn.btn-highlight {
            background: rgba(212, 168, 67, 0.12);
            border-color: rgba(212, 168, 67, 0.4);
            color: var(--accent-gold);
        }
        .grid-tool-btn.btn-highlight:hover {
            background: rgba(212, 168, 67, 0.22);
            border-color: var(--accent-gold);
        }

        .grid-player-tiles {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 10px;
            display: grid;
            gap: 10px;
            align-content: start;
            background: radial-gradient(circle at center, rgba(17, 24, 39, 0.4) 0%, #05070b 100%);
        }

        /* Professional Camera Cards */
        .grid-cam-card {
            position: relative;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.6);
            transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
            user-select: none;
        }
        .grid-cam-card:hover {
            border-color: rgba(59, 130, 246, 0.6);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);
            z-index: 10;
        }
        .grid-cam-card.focused {
            border-color: var(--accent-gold);
            box-shadow: 0 0 16px rgba(212, 168, 67, 0.5);
        }

        .grid-cam-header {
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 15;
            padding: 6px 10px;
            background: linear-gradient(180deg, rgba(5, 8, 13, 0.92) 0%, rgba(5, 8, 13, 0.45) 75%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            opacity: 0.85;
            transition: opacity 150ms ease;
            pointer-events: none;
        }
        .grid-cam-card:hover .grid-cam-header {
            opacity: 1;
        }
        .grid-cam-title-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }
        .grid-cam-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 1px 4px rgba(0,0,0,0.9);
            letter-spacing: 0.2px;
        }
        .grid-cam-live-badge {
            font-size: 8.5px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 8px;
            background: rgba(63, 185, 80, 0.2);
            color: #4ade80;
            border: 1px solid rgba(63, 185, 80, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 3px;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .grid-cam-offline-badge {
            font-size: 8.5px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 8px;
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .grid-cam-header-actions {
            pointer-events: auto;
            display: flex;
            gap: 3px;
            align-items: center;
            flex-shrink: 0;
        }
        .grid-action-chip {
            background: rgba(13, 17, 23, 0.85);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e6edf3;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: all 120ms ease;
        }
        .grid-action-chip:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: #fff;
            transform: scale(1.06);
        }

        .grid-cam-body {
            width: 100%;
            height: 100%;
            flex: 1;
            position: relative;
            background: #000;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .grid-cam-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #000;
            display: block;
            position: absolute;
            top: 0; left: 0;
        }

        .grid-cam-footer {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            z-index: 15;
            padding: 4px 10px;
            background: linear-gradient(0deg, rgba(5, 8, 13, 0.9) 0%, rgba(5, 8, 13, 0.4) 75%, transparent 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: rgba(255, 255, 255, 0.7);
            opacity: 0.8;
            transition: opacity 150ms ease;
            pointer-events: none;
        }
        .grid-cam-card:hover .grid-cam-footer {
            opacity: 1;
        }
        .grid-cam-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.08);
            padding: 1px 4px;
            border-radius: 3px;
        }

        /* Offline Placeholder Tile */
        .grid-cam-offline-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(30, 41, 59, 0.5) 0%, #030508 100%);
            color: var(--text-secondary);
            gap: 8px;
            text-align: center;
            padding: 20px;
        }
        .grid-radar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px dashed rgba(239, 68, 68, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            animation: radar-spin 6s linear infinite;
        }
        @keyframes radar-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Fullscreen Video Wall Mode */
        #gridPlayerView:fullscreen,
        #gridPlayerView:-webkit-full-screen,
        #gridPlayerView.pseudo-fullscreen,
        #gridPlayerView.is-fullscreen {
            width: 100vw !important;
            width: 100dvw !important;
            height: 100vh !important;
            height: 100dvh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            max-height: 100dvh !important;
            position: fixed !important;
            top: 0 !important; left: 0 !important;
            z-index: 999999 !important;
            background: #000 !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #gridPlayerView:fullscreen .grid-control-bar,
        #gridPlayerView:-webkit-full-screen .grid-control-bar,
        #gridPlayerView.pseudo-fullscreen .grid-control-bar,
        #gridPlayerView.is-fullscreen .grid-control-bar {
            position: absolute !important;
            top: max(8px, env(safe-area-inset-top, 8px)) !important;
            left: max(8px, env(safe-area-inset-left, 8px)) !important;
            right: max(8px, env(safe-area-inset-right, 8px)) !important;
            z-index: 100000 !important;
            opacity: 0 !important;
            transform: translateY(-8px) !important;
            pointer-events: none !important;
            transition: opacity 200ms ease, transform 200ms ease !important;
            background: rgba(13, 17, 23, 0.92) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-radius: 10px !important;
        }
        #gridPlayerView:fullscreen:hover .grid-control-bar,
        #gridPlayerView:-webkit-full-screen:hover .grid-control-bar,
        #gridPlayerView.pseudo-fullscreen:hover .grid-control-bar,
        #gridPlayerView.pseudo-fullscreen.show-header .grid-control-bar,
        #gridPlayerView:fullscreen.show-header .grid-control-bar,
        #gridPlayerView.is-fullscreen.show-header .grid-control-bar {
            opacity: 1 !important;
            transform: translateY(0) !important;
            pointer-events: auto !important;
        }
        #gridPlayerView:fullscreen .grid-player-tiles,
        #gridPlayerView:-webkit-full-screen .grid-player-tiles,
        #gridPlayerView.pseudo-fullscreen .grid-player-tiles,
        #gridPlayerView.is-fullscreen .grid-player-tiles {
            height: 100vh !important;
            height: 100dvh !important;
            padding: max(6px, env(safe-area-inset-top, 6px)) max(6px, env(safe-area-inset-right, 6px)) max(6px, env(safe-area-inset-bottom, 6px)) max(6px, env(safe-area-inset-left, 6px)) !important;
            gap: 6px !important;
            flex: 1 !important;
        }

        /* ==========================================
           PTZ OVERLAY
        ========================================== */
        #ptzControlOverlay {
            position: absolute;
            bottom: 18px; right: 18px;
            z-index: 25;
            background: rgba(1,4,9,0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(212,168,67,0.4);
            border-radius: 50%;
            width: 100px; height: 100px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.7), inset 0 0 12px rgba(212,168,67,0.1);
            pointer-events: auto;
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 250ms, transform 250ms;
        }
        .ptz-btn {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff !important;
            font-size: 13px;
            cursor: pointer;
            outline: none;
            user-select: none;
            display: flex; align-items: center; justify-content: center;
            width: 26px; height: 26px;
            border-radius: 50%;
            transition: all 150ms;
        }
        .ptz-btn:hover {
            background: rgba(212,168,67,0.3);
            border-color: var(--accent-gold);
            color: var(--accent-gold) !important;
            transform: scale(1.12);
        }
        .ptz-btn:active, .ptz-btn.active {
            background: var(--accent-gold);
            color: #0d1117 !important;
            transform: scale(0.95);
            box-shadow: 0 0 10px var(--accent-gold);
        }
        .ptz-btn.busy {
            background: rgba(212,168,67,0.5) !important;
            border-color: var(--accent-gold) !important;
            color: #fff !important;
            animation: ptz-pulse 0.6s infinite alternate;
        }
        @keyframes ptz-pulse {
            from { transform: scale(1); box-shadow: 0 0 4px rgba(212,168,67,0.4); }
            to { transform: scale(1.15); box-shadow: 0 0 12px rgba(212,168,67,0.9); }
        }

        /* ==========================================
           CUSTOM VIDEO CONTROLS
        ========================================== */
        .custom-controls-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(1,4,9,0.92));
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: opacity 150ms;
            opacity: 0;
            pointer-events: none;
            z-index: 10;
        }
        #customPlayerWrapper:hover .custom-controls-overlay,
        #customPlayerWrapper.show-controls .custom-controls-overlay {
            opacity: 1;
            pointer-events: auto;
        }
        .control-btn {
            background: transparent;
            border: none;
            color: #e6edf3;
            font-size: 14px;
            cursor: pointer;
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: all 150ms;
        }
        .control-btn:hover { background: rgba(255,255,255,0.1); }

        /* ==========================================
           MODALS
        ========================================== */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(1,4,9,0.82);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }
        .modal-card {
            background: var(--modal-bg, var(--bg-surface));
            border: 1px solid var(--border-default);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--modal-header-bg, var(--bg-elevated));
            flex-wrap: wrap;
            gap: 10px;
        }
        .modal-header h2, .modal-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-close {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
            padding: 2px 4px;
            border-radius: 4px;
            transition: all 150ms;
            font-family: inherit;
        }
        .modal-close:hover { background: var(--btn-hover); color: var(--text-primary); }

        /* Tab buttons in modals */
        .tab-group {
            display: flex;
            gap: 2px;
            background: var(--bg-base);
            padding: 3px;
            border-radius: 7px;
            border: 1px solid var(--border-muted);
        }
        .tab-btn, #tabBtnGeneral, #tabBtnGo2rtc {
            background: transparent !important;
            border: none !important;
            color: var(--text-secondary);
            padding: 4px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 150ms;
            font-family: inherit;
        }
        .tab-btn.active,
        #tabBtnGeneral[style*="var(--accent-blue)"],
        #tabBtnGo2rtc[style*="var(--accent-blue)"] {
            background: var(--accent-blue) !important;
            color: #fff !important;
        }

        /* General buttons */
        .header-nav button, button.header-nav {
            background: var(--btn-bg);
            border: 1px solid var(--border-default);
            color: var(--text-primary);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 150ms;
            font-family: inherit;
        }
        .header-nav button:hover, button.header-nav:hover {
            background: var(--btn-hover);
            color: var(--text-primary);
        }

        /* Skeleton loader */
        .skeleton-item, .skeleton {
            height: 36px;
            background: linear-gradient(90deg, var(--bg-elevated) 25%, var(--bg-overlay) 50%, var(--bg-elevated) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 5px;
            margin: 5px 10px;
        }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Disk status bar */
        .disk-bar-track {
            width: 100%; height: 6px;
            background: var(--bg-elevated);
            border-radius: 3px; overflow: hidden;
        }
        .disk-bar-fill {
            height: 100%;
            background: var(--accent-blue);
            border-radius: 3px;
            transition: width 0.5s ease, background 0.3s ease;
        }

        /* ==========================================
           🎬 ENTERPRISE FULLSCREEN SURVEILLANCE ENGINE
        ========================================== */
        :fullscreen, :-webkit-full-screen, :-moz-full-screen {
            background: #000 !important;
        }

        .player-card:fullscreen,
        .player-card:-webkit-full-screen,
        .player-card:-moz-full-screen,
        .player-card.pseudo-fullscreen,
        .player-card.is-fullscreen {
            width: 100vw !important;
            width: 100dvw !important;
            height: 100vh !important;
            height: 100dvh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            max-height: 100dvh !important;
            aspect-ratio: auto !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 999999 !important;
            background: #000 !important;
            border-radius: 0 !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .player-card:fullscreen .player-body,
        .player-card:-webkit-full-screen .player-body,
        .player-card.pseudo-fullscreen .player-body,
        .player-card.is-fullscreen .player-body {
            width: 100% !important;
            height: 100% !important;
            flex: 1 !important;
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #000 !important;
            overflow: hidden !important;
        }

        .player-card:fullscreen #liveStreamFrame,
        .player-card:-webkit-full-screen #liveStreamFrame,
        .player-card.pseudo-fullscreen #liveStreamFrame,
        .player-card.is-fullscreen #liveStreamFrame {
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            border: none !important;
            display: block !important;
            background: #000 !important;
            object-fit: contain !important;
        }

        .player-card:fullscreen #playbackVideo,
        .player-card:-webkit-full-screen #playbackVideo,
        .player-card.pseudo-fullscreen #playbackVideo,
        .player-card.is-fullscreen #playbackVideo {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            background: #000 !important;
        }

        /* Floating surveillance header in fullscreen (fades in on hover or tap) */
        .player-card:fullscreen .player-header,
        .player-card:-webkit-full-screen .player-header,
        .player-card.pseudo-fullscreen .player-header,
        .player-card.is-fullscreen .player-header {
            position: absolute !important;
            top: max(14px, env(safe-area-inset-top, 14px)) !important;
            left: max(14px, env(safe-area-inset-left, 14px)) !important;
            right: max(14px, env(safe-area-inset-right, 14px)) !important;
            padding: 8px 16px !important;
            background: rgba(13, 17, 23, 0.92) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.7) !important;
            z-index: 1000000 !important;
            opacity: 0 !important;
            transform: translateY(-8px) !important;
            pointer-events: none !important;
            transition: opacity 0.25s ease, transform 0.25s ease !important;
        }

        .player-card:fullscreen:hover .player-header,
        .player-card:-webkit-full-screen:hover .player-header,
        .player-card.pseudo-fullscreen:hover .player-header,
        .player-card.pseudo-fullscreen.show-header .player-header,
        .player-card:fullscreen.show-header .player-header,
        .player-card.is-fullscreen.show-header .player-header {
            opacity: 1 !important;
            transform: translateY(0) !important;
            pointer-events: auto !important;
        }

        /* Floating exit fullscreen button badge */
        .floating-fs-exit-btn {
            display: none;
            position: fixed;
            top: max(16px, env(safe-area-inset-top, 16px));
            right: max(16px, env(safe-area-inset-right, 16px));
            z-index: 1000002;
            background: rgba(239, 68, 68, 0.92);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .floating-fs-exit-btn:hover,
        .floating-fs-exit-btn:active {
            background: rgba(220, 38, 38, 1);
            transform: scale(1.05);
            box-shadow: 0 6px 24px rgba(239, 68, 68, 0.5);
        }

        .player-card:fullscreen .floating-fs-exit-btn,
        .player-card:-webkit-full-screen .floating-fs-exit-btn,
        .player-card.pseudo-fullscreen .floating-fs-exit-btn,
        .player-card.is-fullscreen .floating-fs-exit-btn,
        #gridPlayerView:fullscreen .floating-fs-exit-btn,
        #gridPlayerView:-webkit-full-screen .floating-fs-exit-btn,
        #gridPlayerView.pseudo-fullscreen .floating-fs-exit-btn,
        #gridPlayerView.is-fullscreen .floating-fs-exit-btn {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
        }

        :fullscreen #ptzControlOverlay,
        :-webkit-full-screen #ptzControlOverlay,
        .player-card:fullscreen #ptzControlOverlay,
        .player-card:-webkit-full-screen #ptzControlOverlay,
        .player-card.pseudo-fullscreen #ptzControlOverlay,
        .player-card.is-fullscreen #ptzControlOverlay {
            bottom: max(20px, env(safe-area-inset-bottom, 20px)) !important;
            right: max(20px, env(safe-area-inset-right, 20px)) !important;
            z-index: 1000000 !important;
        }

        /* ==========================================
           📱 ADVANCED RESPONSIVE DESIGN (TABLET & PHONE)
        ========================================== */
        /* ==========================================
           📱 ADVANCED MOBILE RESPONSIVE ENGINE (PHONE & TABLET)
        ========================================== */
        @media (max-width: 900px) {
            html, body {
                height: auto !important;
                min-height: 100% !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                position: relative !important;
            }
            .app-container {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                height: auto !important;
                min-height: calc(100vh - 44px) !important;
                overflow: visible !important;
            }
            
            /* Header */
            header {
                height: 44px !important;
                padding: 0 8px !important;
                gap: 6px !important;
                flex-shrink: 0 !important;
            }
            .header-brand h1 { font-size: 13px !important; }
            .header-brand img { height: 22px !important; }
            .status-pill, .header-divider { display: none !important; }
            .header-actions {
                display: flex !important;
                align-items: center !important;
                gap: 4px !important;
                overflow-x: auto !important;
                scrollbar-width: none !important;
                -webkit-overflow-scrolling: touch !important;
            }
            .header-actions::-webkit-scrollbar { display: none !important; }
            .hbtn {
                padding: 4px 8px !important;
                font-size: 11px !important;
                white-space: nowrap !important;
                border-radius: 5px !important;
                flex-shrink: 0 !important;
            }
            #themeText { display: none !important; }

            /* 1. Video Player First */
            .main-content {
                order: 1 !important;
                width: 100% !important;
                height: auto !important;
                flex: none !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
            }
            #singlePlayerView {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                height: auto !important;
                flex: none !important;
            }
            .player-wrap {
                width: 100% !important;
                height: auto !important;
                flex: none !important;
            }
            .player-area, #mainPlayerCard {
                width: 100% !important;
                height: auto !important;
                aspect-ratio: 16 / 9 !important;
                max-height: none !important;
                border-radius: 0 !important;
                border-left: none !important;
                border-right: none !important;
            }
            .player-body {
                width: 100% !important;
                height: 100% !important;
                aspect-ratio: 16 / 9 !important;
                border-radius: 0 !important;
            }
            .player-header {
                opacity: 1 !important;
                transform: none !important;
                pointer-events: auto !important;
                top: 6px !important; left: 6px !important; right: 6px !important;
                padding: 5px 8px !important;
            }
            .player-title {
                font-size: 11px !important;
                max-width: 160px !important;
            }

            /* 2. Timeline Bar directly under video */
            #unifiedTimelineContainer {
                order: 2 !important;
                width: 100% !important;
                padding: 8px 10px !important;
                flex-shrink: 0 !important;
                background: var(--bg-surface) !important;
                border-bottom: 1px solid var(--border-muted) !important;
            }
            .timeline-toprow {
                flex-wrap: wrap !important;
                gap: 6px !important;
            }
            .timeline-status { font-size: 10.5px !important; }
            .timeline-slider::-webkit-slider-thumb {
                width: 18px !important;
                height: 18px !important;
            }

            /* 3. Horizontal Camera Chips under timeline */
            .nav-rail {
                order: 3 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                border-right: none !important;
                border-bottom: 1px solid var(--border-muted) !important;
                background: var(--bg-surface) !important;
                position: relative !important;
                overflow: hidden !important;
                flex: none !important;
            }
            .nav-rail-inner {
                width: 100% !important;
                height: auto !important;
                flex-direction: column !important;
            }
            .nav-cam-header, .nav-rail-bottom {
                display: none !important;
            }
            .nav-cam-search {
                display: flex !important;
                width: 100% !important;
                padding: 4px 8px !important;
                height: 34px !important;
                border-bottom: 1px solid var(--border-subtle) !important;
                box-sizing: border-box !important;
            }
            .nav-cam-search-icon {
                width: 24px !important;
                height: 100% !important;
            }
            .nav-cam-search-input-wrap {
                opacity: 1 !important;
                pointer-events: auto !important;
                width: 100% !important;
            }
            .nav-cam-search-input-wrap input {
                opacity: 1 !important;
                pointer-events: auto !important;
                font-size: 11px !important;
                padding: 3px 20px 3px 8px !important;
            }
            .nav-cam-list {
                display: flex !important;
                flex-direction: row !important;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                padding: 6px 8px !important;
                gap: 6px !important;
                scrollbar-width: none !important;
                -webkit-overflow-scrolling: touch !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .nav-cam-list::-webkit-scrollbar { display: none !important; }
            .cam-row {
                flex: 0 0 auto !important;
                width: auto !important;
                min-width: auto !important;
                height: 32px !important;
                padding: 0 10px !important;
                border-radius: 16px !important;
                background: var(--bg-elevated) !important;
                border: 1px solid var(--border-default) !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 5px !important;
                text-align: left !important;
            }
            .cam-row.active {
                background: var(--accent-blue-dim) !important;
                border-color: var(--accent-blue) !important;
            }
            .cam-row.active::before { display: none !important; }
            .cam-row-icon {
                width: auto !important;
                height: auto !important;
                display: inline-flex !important;
                font-size: 13px !important;
                position: static !important;
            }
            .rec-dot {
                position: static !important;
                margin-left: 2px !important;
                width: 5px !important;
                height: 5px !important;
            }
            .cam-row-info {
                opacity: 1 !important;
                transform: none !important;
                display: inline-block !important;
                padding: 0 !important;
                white-space: nowrap !important;
            }
            .cam-row-name {
                font-size: 11px !important;
                font-weight: 600 !important;
                display: inline-block !important;
            }
            .cam-row-sub, .cam-row-meta, .cam-row-actions {
                display: none !important;
            }

            /* 4. Recordings Panel at the bottom */
            .recordings-panel {
                order: 4 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                max-height: 400px !important;
                border-left: none !important;
                border-top: 1px solid var(--border-muted) !important;
                flex: none !important;
                overflow-y: auto !important;
            }
            .filter-panel {
                padding: 8px 10px !important;
                gap: 6px !important;
            }
            .filter-row {
                gap: 6px !important;
            }
            .filter-label {
                width: 55px !important;
                font-size: 11px !important;
            }
            .filter-select, .filter-input {
                font-size: 11px !important;
                padding: 4px 6px !important;
            }

            /* Fullscreen mode on mobile */
            :fullscreen #mainPlayerCard,
            :-webkit-full-screen #mainPlayerCard,
            .player-card:fullscreen,
            .player-card:-webkit-full-screen {
                width: 100vw !important;
                height: 100vh !important;
                max-height: 100vh !important;
                aspect-ratio: auto !important;
                position: fixed !important;
                top: 0 !important; left: 0 !important;
                z-index: 99999 !important;
            }
            :fullscreen iframe#liveStreamFrame,
            :-webkit-full-screen iframe#liveStreamFrame {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
            }
        }
    
    </style>

</head>
<body>

    <!-- Top Header -->
    <header>
        <div class="header-brand">
            <img src="/assets/logo.png" alt="SMKN 2 Pekalongan" title="SMKN 2 Pekalongan" style="height:28px;width:auto;object-fit:contain;filter:drop-shadow(0 1px 3px rgba(0,0,0,0.5));">
            <h1>Clarity NVR</h1>
        </div>
        <div class="header-divider"></div>
        <div class="status-pill">
            <div class="status-dot"></div>
            <span>Live</span>
        </div>
        <div class="header-spacer"></div>
        <div class="header-actions">
            <button id="btnToggleView" class="hbtn btn-grid" onclick="toggleLayoutMode()">&#128250; Grid</button>
            <button class="hbtn btn-cctv" onclick="openAddCameraModal()">&#65291; CCTV</button>
            <button class="hbtn btn-settings" onclick="openGlobalSettingsModal('general')">&#9881; Pengaturan</button>
            <button class="hbtn btn-log" onclick="openConfigModal('log.html?embed=true&v=4', '&#128203; System Logs')">&#128203; Log</button>
            <button class="hbtn btn-net" onclick="openConfigModal('net.html?embed=true&v=4', '&#127760; Network')">&#127760; Net</button>
            <button class="hbtn btn-password" onclick="openChangePasswordModal()" title="Ganti Password Root">&#128273; Password</button>
            <a href="/logout" class="hbtn btn-logout" title="Keluar / Logout Root">&#128682; Logout</a>
            <div class="header-divider"></div>
            <button id="btnThemeToggle" class="theme-toggle-btn" onclick="toggleTheme()" title="Ganti Tema Tampilan"><span id="themeIcon">&#127769;</span> <span id="themeText">Dark</span></button>
        </div>
    </header>


    <!-- Main App Container -->
    <div class="app-container">

        <!-- Left Nav-Rail: Camera List (Dock Style) -->
        <nav class="nav-rail" id="navRail">
            <div class="nav-rail-inner">
                <div class="nav-cam-header">
                    <div class="nav-cam-header-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.9L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="nav-cam-header-text">Daftar Kamera</span>
                    <button onclick="restoreHiddenCameras()" class="nav-cam-header-reset" title="Tampilkan semua kamera tersembunyi">Reset</button>
                </div>
                <div class="nav-cam-search">
                    <div class="nav-cam-search-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>
                    <div class="nav-cam-search-input-wrap">
                        <input type="text" id="cameraSearchInput" placeholder="Cari nama CCTV..." oninput="handleCameraSearch(this.value)" autocomplete="off" spellcheck="false">
                        <button id="cameraSearchClearBtn" class="search-clear-btn" onclick="clearCameraSearch()" style="display:none;" title="Hapus pencarian">&times;</button>
                    </div>
                </div>
                <ul class="nav-cam-list" id="cameraListContainer">
                    <div class="skeleton-item"></div>
                    <div class="skeleton-item"></div>
                    <div class="skeleton-item" style="width:70%;"></div>
                </ul>
            </div>
        </nav>

        <!-- Center: Main Content -->
        <div class="main-content">
            <!-- Single Player View -->
            <div id="singlePlayerView" style="display:flex;flex-direction:column;flex:1;min-height:0;">
                <div class="player-wrap">
                    <div class="player-area">
                        <div class="player-card" id="mainPlayerCard">
                            <!-- Floating header over video -->
                            <div class="player-header">
                                <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;">
                                    <span class="player-title" id="playerTitle">Memuat Stream...</span>
                                    <span id="playerMode" class="mode-badge" style="background:var(--accent-blue-dim);color:var(--accent-blue);border:1px solid var(--accent-blue);flex-shrink:0;">LIVE</span>
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;">
                                    <button id="headerRecBtn" class="player-action-btn" onclick="toggleRecordingForCurrentCamera()" title="Ubah Status Perekaman 24/7 Kamera Ini" style="color:var(--accent-red);border-color:rgba(239,68,68,0.35);background:rgba(239,68,68,0.1);display:inline-flex;align-items:center;gap:4px;">
                                        <span id="headerRecDot" style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                        <span id="headerRecText">REC</span>
                                    </button>
                                    <button id="headerShareBtn" class="player-action-btn" onclick="openShareModalForCurrentCamera()" title="Bagikan Link CCTV Ini" style="color:var(--accent-gold);border-color:rgba(212,168,67,0.35);background:rgba(212,168,67,0.1);">&#128279; Share</button>
                                    <button id="headerMicBtn" class="player-action-btn" onmousedown="startIntercom()" onmouseup="stopIntercom()" onmouseleave="stopIntercom()" ontouchstart="startIntercom()" ontouchend="stopIntercom()" style="display:none;">&#127908; Mic</button>
                                    <button id="headerHomeBtn" class="player-action-btn" onclick="goToHomePosition()" title="Arahkan Kamera ke Posisi Tengah (Home)" style="color:#34d399;border-color:rgba(16,185,129,0.35);background:rgba(16,185,129,0.12);display:none;">🏠 Tengah</button>
                                    <button id="headerSetHomeBtn" class="player-action-btn" onclick="saveHomePosition()" title="Simpan Posisi Kamera Saat Ini Sebagai Titik Tengah Default" style="color:var(--text-muted);display:none;">💾 Set Tengah</button>
                                    <button id="headerAiCenterBtn" class="player-action-btn" onclick="triggerAiAutoCenter()" title="AI Auto-Framing: Fokuskan Arah Kamera ke Posisi Manusia" style="color:#60a5fa;border-color:rgba(59,130,246,0.35);background:rgba(59,130,246,0.12);display:none;">🎯 AI Focus</button>
                                    <button id="headerPtzToggleBtn" class="player-action-btn" onclick="togglePtzOverlayManual()" style="display:none;">&#127918; PTZ</button>
                                    <button id="playerFullscreenBtn" class="player-action-btn" onclick="togglePlayerFullscreen()" title="Layar Penuh (Klik 2x / Tombol F)" style="color:var(--accent-blue);border-color:rgba(59,130,246,0.35);background:rgba(59,130,246,0.1);">&#x26F6; Fullscreen</button>
                                </div>
                            </div>

                            <!-- Floating Exit Fullscreen Button in Fullscreen Mode -->
                            <button id="floatingExitFullscreenBtn" class="floating-fs-exit-btn" onclick="togglePlayerFullscreen()" title="Keluar Layar Penuh (Esc)">
                                ✕ Keluar Fullscreen (Esc)
                            </button>

                            <!-- Video body -->
                            <div class="player-body">
                                <iframe id="liveStreamFrame" src="about:blank" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen="true" style="width:100%; height:100%; border:none; display:block; background:#000; position:absolute; top:0; left:0;"></iframe>
                                <div id="iframeHoverOverlay" onclick="handlePlayerOverlayTap(event)" ondblclick="togglePlayerFullscreen()" title="Ketuk untuk Kontrol / Klik 2x untuk Layar Penuh" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:10;background:transparent;cursor:pointer;"></div>

                                <!-- Custom Playback Container -->
                                <div id="customPlayerWrapper" style="width:100%;height:100%;display:none;position:absolute;top:0;left:0;background:#000;overflow:hidden;justify-content:center;align-items:center;z-index:12;">
                                    <video id="playbackVideo" autoplay muted></video>
                                    <!-- Status overlay -->
                                    <div id="playbackStatusOverlay" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(1,4,9,0.88);z-index:13;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:20px;text-align:center;">
                                        <span style="font-size:28px;">&#8987;</span>
                                        <span id="playbackStatusText" style="font-size:13px;font-weight:500;color:var(--text-secondary);">Menginisialisasi pemutar...</span>
                                    </div>
                                    <!-- Video controls overlay -->
                                    <div id="customPlayerControls" class="custom-controls-overlay">
                                        <div style="display:flex;align-items:center;width:100%;">
                                            <input type="range" id="customProgressBar" min="0" max="100" value="0" style="width:100%;height:3px;cursor:pointer;accent-color:var(--accent-blue);outline:none;">
                                        </div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <div style="display:flex;gap:10px;align-items:center;">
                                                <button id="customPlayBtn" class="control-btn">&#9208;&#65039;</button>
                                                <button id="customSkipBackBtn" class="control-btn" style="font-size:11px;width:auto;padding:0 8px;border-radius:5px;">&#9194; 10s</button>
                                                <button id="customSkipForwardBtn" class="control-btn" style="font-size:11px;width:auto;padding:0 8px;border-radius:5px;">&#9193; 10s</button>
                                                <span id="customTimeDisplay" style="font-size:11px;color:var(--text-secondary);font-family:'JetBrains Mono',monospace;">00:00 / 00:00</span>
                                            </div>
                                            <div style="display:flex;gap:10px;align-items:center;">
                                                <span style="font-size:10px;color:var(--text-muted);">Speed</span>
                                                <select id="customSpeedSelect" class="filter-select" style="padding:2px 5px;font-size:11px;">
                                                    <option value="0.5">0.5x</option>
                                                    <option value="1" selected>1x</option>
                                                    <option value="1.5">1.5x</option>
                                                    <option value="2">2x</option>
                                                    <option value="4">4x</option>
                                                </select>
                                                <button id="customFullscreenBtn" class="control-btn">&#x26F6;</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PTZ Overlay -->
                                <div id="ptzControlOverlay" style="display:none;">
                                    <button class="ptz-btn" onclick="sendPTZ('up')"    style="top:4px;left:50%;transform:translateX(-50%);" title="Atas">&#9650;</button>
                                    <button class="ptz-btn" onclick="sendPTZ('left')"  style="top:50%;left:4px;transform:translateY(-50%);"  title="Kiri">&#9664;</button>
                                    <button class="ptz-btn" onclick="sendPTZ('right')" style="top:50%;right:4px;transform:translateY(-50%);" title="Kanan">&#9654;</button>
                                    <button class="ptz-btn" onclick="sendPTZ('down')"  style="bottom:4px;left:50%;transform:translateX(-50%);" title="Bawah">&#9660;</button>
                                    <div onclick="triggerAiAutoCenter()" title="AI Auto-Center: Klik untuk deteksi & fokus ke manusia" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:30px;height:30px;border-radius:50%;background:rgba(212,168,67,0.22);border:1px solid var(--accent-gold);display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:800;color:var(--accent-gold);cursor:pointer;user-select:none;transition:all 150ms;">🎯 AI</div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Panel -->
                        <div id="unifiedTimelineContainer">
                            <div class="timeline-toprow">
                                <div class="timeline-status">
                                    <span id="timelineStatusDot" class="timeline-status-dot" style="background:var(--accent-blue);"></span>
                                    <span id="timelineStatusText">Mode: LIVE Stream</span>
                                </div>
                                <div class="timeline-controls">
                                    <div class="date-nav">
                                        <button class="date-nav-btn" onclick="navigateTimelineDate(-1)" title="Kemarin">&#9664;</button>
                                        <span id="timelineDateLabel">Hari ini</span>
                                        <button class="date-nav-btn" onclick="navigateTimelineDate(1)" title="Besok">&#9654;</button>
                                    </div>
                                    <button id="btnGoLive" class="tl-btn live" onclick="switchToLiveMode()">&#11044; Kembali LIVE</button>
                                </div>
                            </div>
                            <div class="timeline-track-wrap">
                                <canvas id="timelineCanvas" height="6"></canvas>
                                <input type="range" id="unifiedTimelineSlider" class="timeline-slider" min="0" max="86400" value="86400">
                            </div>
                            <div class="timeline-labels">
                                <span>00:00</span><span>06:00</span><span>12:00</span><span>18:00</span><span>24:00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enterprise Matrix Grid View (Video Wall) -->
            <div id="gridPlayerView">
                <!-- Floating Exit Button for Grid Fullscreen -->
                <button id="floatingGridExitBtn" class="floating-fs-exit-btn" onclick="toggleGridFullscreen()" title="Keluar Layar Penuh (Esc)">
                    ✕ Keluar Fullscreen (Esc)
                </button>

                <!-- Top Grid Control Bar -->
                <div id="gridControlBar" class="grid-control-bar">
                    <div class="grid-bar-left">
                        <span class="grid-brand-badge">📹 MATRIX VIDEO WALL</span>
                        <span id="gridActiveCountBadge" class="grid-status-pill">
                            <span class="grid-status-dot"></span>
                            <span id="gridActiveCountText">0 Kamera</span>
                        </span>
                        <div id="gridWallClock" class="grid-clock-pill">--:--:--</div>
                    </div>
                    <div class="grid-bar-center">
                        <div class="grid-layout-selector" title="Pilih Format Matrix Grid">
                            <button class="grid-layout-btn active" data-layout="auto" onclick="setGridLayoutPreset('auto')">Auto</button>
                            <button class="grid-layout-btn" data-layout="1" onclick="setGridLayoutPreset('1')">1x1</button>
                            <button class="grid-layout-btn" data-layout="4" onclick="setGridLayoutPreset('4')">2x2 (4)</button>
                            <button class="grid-layout-btn" data-layout="9" onclick="setGridLayoutPreset('9')">3x3 (9)</button>
                            <button class="grid-layout-btn" data-layout="16" onclick="setGridLayoutPreset('16')">4x4 (16)</button>
                        </div>
                    </div>
                    <div class="grid-bar-right">
                        <button class="grid-tool-btn" onclick="selectAllGridCameras(true)" title="Centang Semua CCTV Online">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Semua</span>
                        </button>
                        <button class="grid-tool-btn" onclick="selectAllGridCameras(false)" title="Kosongkan Pilihan Kamera">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            <span>Reset</span>
                        </button>
                        <button class="grid-tool-btn btn-highlight" onclick="toggleGridFullscreen()" title="Tampilan Layar Penuh Video Wall">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                            <span>Fullscreen</span>
                        </button>
                    </div>
                </div>

                <!-- Dynamic Video Tiles Area -->
                <div id="gridPlayerTiles" class="grid-player-tiles"></div>
            </div>
        </div>

        <!-- Right Panel: Recordings -->
        <div class="recordings-panel" id="recordingsPanel">
            <div class="panel-header">&#128197; Arsip Rekaman</div>
            <div class="filter-panel">
                <div class="filter-row">
                    <span class="filter-label">Kamera:</span>
                    <select id="filterCamera" onchange="handleCameraFilterChange()" class="filter-select">
                        <option value="all">Semua</option>
                    </select>
                </div>
                <div class="filter-row">
                    <span class="filter-label">Tanggal:</span>
                    <input type="date" id="filterDate" onchange="handleDateChange()" class="filter-input">
                </div>
                <div class="filter-row">
                    <span class="filter-label">Waktu:</span>
                    <select id="filterHour" onchange="filterAndRenderRecordings()" class="filter-select">
                        <option value="all">Semua Jam</option>
                        <option value="morning">Pagi (06-12)</option>
                        <option value="afternoon">Siang (12-18)</option>
                        <option value="night">Malam (18-06)</option>
                    </select>
                </div>
            </div>
            <div class="list-container" id="recordingsContainer">
                <div style="padding:20px;text-align:center;color:var(--text-muted);">Memuat rekaman...</div>
            </div>
        </div>

    </div>


    <!-- Modal for Adding New Camera (Clean and Guided UI) -->
    <div id="addCameraModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 12px; width: 90%; max-width: 560px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8);">
                <h2 style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">➕ Tambah Kamera CCTV Baru</h2>
                <button class="modal-close" onclick="closeAddCameraModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 26px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <form id="addCameraForm" onsubmit="submitAddCamera(event)" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-field">
                    <label class="form-label">Nama / Lokasi Kamera *</label>
                    <input type="text" id="addCamName" required placeholder="Contoh: Lab Komputer 3, Ruang Guru, dsb" class="form-input">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-field">
                        <label class="form-label">Tipe CCTV</label>
                        <select id="addCamType" onchange="handleAddCamTypeChange()" class="form-input">
                            <option value="generic">IP Camera Biasa / ONVIF (Zitech, Dahua, dll)</option>
                            <option value="anyka">Anyka Smart PTZ (V380 / M3)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label">IP Address *</label>
                        <input type="text" id="addCamIp" required placeholder="192.168.22.XX" class="form-input">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-field">
                        <label class="form-label">RTSP Port</label>
                        <input type="number" id="addCamPort" value="554" class="form-input">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Username</label>
                        <input type="text" id="addCamUser" value="admin" class="form-input">
                    </div>
                    <div class="form-field">
                        <label class="form-label">Password</label>
                        <input type="password" id="addCamPass" placeholder="Kosong jika tdk ada" class="form-input">
                    </div>
                </div>

                <div id="addCamPathRow" class="form-field">
                    <label class="form-label">Custom Stream Path (Opsional)</label>
                    <input type="text" id="addCamPath" placeholder="Biarkan kosong untuk default" class="form-input">
                </div>

                <div style="display: flex; align-items: center; gap: 8px; padding: 10px; background: var(--bg-elevated); border-radius: 6px; border: 1px solid var(--border-muted);">
                    <input type="checkbox" id="addCamRec" checked style="width: 15px; height: 15px; accent-color: var(--accent-red); cursor: pointer;">
                    <label for="addCamRec" style="font-size: 12px; color: var(--text-primary); cursor: pointer;">Aktifkan Perekaman 24/7 (Recording Auto-Save)</label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px;">
                    <button type="button" onclick="closeAddCameraModal()" class="btn btn-ghost">Batal</button>
                    <button type="submit" id="btnAddCamSubmit" class="btn btn-primary">Simpan Kamera</button>
                </div>
            </form>
        </div>
    </div>

    <!-- System Config Modal Overlay -->
    <div class="modal-overlay" id="configModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); backdrop-filter: var(--glass-blur); align-items: center; justify-content: center; z-index: 1000;">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 12px; width: 90%; height: 90%; max-width: 1400px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8);">
                <h2 id="configModalTitle" style="font-size: 15px; font-weight: 600;">System Settings</h2>
                <button class="modal-close" onclick="closeConfigModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 26px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body" style="flex: 1; overflow: hidden;">
                <iframe id="configIframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>

    <!-- Modal for Unified Settings & Config (Dynamic Tabs) -->
    <div id="globalSettingsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 12px; width: 92%; height: 88%; max-width: 1100px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8); flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <h2 style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">⚙️ Pengaturan & Konfigurasi NVR</h2>
                    <div class="tab-group">
                        <button id="tabBtnGeneral" onclick="switchSettingsTab('general')" class="tab-btn active">📹 Rekaman & Kamera</button>
                        <button id="tabBtnGo2rtc" onclick="switchSettingsTab('go2rtc')" class="tab-btn">⚙️ System YAML Config</button>
                    </div>
                </div>
                <button class="modal-close" onclick="closeGlobalSettingsModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 26px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            
            <!-- Tab 1: General Settings (Disk, Camera Table, Retention) -->
            <div id="settingsTabGeneral" class="modal-body" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px;">
                <!-- Disk Space Widget -->
                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 600; color: var(--accent-blue); display: flex; align-items: center; gap: 6px;">💾 Penggunaan Ruang Penyimpanan (/var)</h4>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-secondary);">
                        <span id="diskModalInfoText">Memuat informasi disk...</span>
                        <span id="diskPercentText">0%</span>
                    </div>
                    <div class="disk-bar-track">
                        <div id="diskProgressBar" class="disk-bar-fill" style="width: 0%;"></div>
                    </div>
                </div>
                
                <!-- Camera Settings Table -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="padding: 12px 14px; border-bottom: 1px solid var(--border-muted); font-weight: 600; font-size: 12.5px; color: var(--accent-gold); background: var(--bg-elevated);">📹 Daftar Kamera & Pengaturan Rekaman</div>
                    <div style="overflow-x: auto;">
                        <table class="settings-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="min-width: 150px; text-align: left; padding: 10px 14px;">Nama Kamera</th>
                                    <th style="min-width: 130px; text-align: left; padding: 10px 14px;">ID Stream</th>
                                    <th style="width: 80px; text-align: center; padding: 10px 8px;">Status</th>
                                    <th style="width: 95px; text-align: center; padding: 10px 8px;" title="Status Rekaman 24/7">Rekam 24/7</th>
                                    <th style="width: 95px; text-align: center; padding: 10px 8px;" title="Akses Publik tanpa Login">Share Publik</th>
                                    <th style="min-width: 130px; text-align: center; padding: 10px 12px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cameraSettingsTableBody">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Cleanup/Retention Configuration -->
                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 600; color: var(--accent-green); display: flex; align-items: center; gap: 6px;">♻️ Pembersihan Otomatis (Auto-Cleanup)</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-field">
                            <label class="form-label">Simpan Rekaman Maksimal (Hari):</label>
                            <input type="number" id="retentionDaysInput" min="1" max="365" class="form-input" value="30">
                        </div>
                        <div class="form-field">
                            <label class="form-label">Batas Penggunaan Disk Maksimal (%):</label>
                            <input type="number" id="diskLimitInput" min="50" max="95" class="form-input" value="80">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; margin-top: 6px;">
                        <button onclick="saveCleanupConfig()" class="btn btn-success">Simpan Konfigurasi Cleanup</button>
                    </div>
                </div>
            </div>
            
            <!-- Tab 2: System YAML Config (go2rtc config editor) -->
            <div id="settingsTabGo2rtc" style="display: none; flex: 1; overflow: hidden;">
                <iframe id="go2rtcConfigIframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>


    <!-- Dashboard Share Modal -->
    <div id="dashboardShareModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 14px; width: 90%; max-width: 540px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8);">
                <h3 id="dashboardShareModalTitle" style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0;">🔗 Bagikan Link CCTV</h3>
                <button class="modal-close" onclick="closeDashboardShareModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-primary);">Status Hak Akses: <span id="shareModalAccessBadge" style="font-size: 11px; padding: 2px 7px; border-radius: 5px; font-weight: 700;">Publik</span></div>
                        <div style="font-size: 11.5px; color: var(--text-secondary); margin-top: 2px;" id="shareModalAccessDesc">Dapat ditonton siapa saja tanpa login root</div>
                    </div>
                    <button id="btnToggleShareAccess" onclick="toggleShareAccessFromModal()" class="btn btn-ghost" style="font-size: 11.5px; padding: 5px 10px;">Ubah ke Privat</button>
                </div>

                <div class="form-field">
                    <label class="form-label">Link Tampilan Standalone (Web Player / Browser):</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="dashShareLinkWeb" class="form-input" readonly style="font-size: 12px;">
                        <button class="btn btn-primary" onclick="copyDashInput('dashShareLinkWeb')" style="padding: 6px 14px; font-size: 12px; white-space: nowrap;">Salin</button>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label">Direct WebRTC Player URL:</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="dashShareLinkStream" class="form-input" readonly style="font-size: 12px;">
                        <button class="btn btn-primary" onclick="copyDashInput('dashShareLinkStream')" style="padding: 6px 14px; font-size: 12px; white-space: nowrap;">Salin</button>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label">Kode Embed Iframe (Website / Aplikasi Lain):</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="dashShareLinkEmbed" class="form-input" readonly style="font-size: 12px;">
                        <button class="btn btn-primary" onclick="copyDashInput('dashShareLinkEmbed')" style="padding: 6px 14px; font-size: 12px; white-space: nowrap;">Salin</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(12, 15, 19, 0.85); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="background-color: rgba(18, 22, 30, 0.98); border: 1px solid var(--border-color); border-radius: 14px; width: 90%; max-width: 440px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: rgba(28, 34, 46, 0.8);">
                <h3 style="font-size: 15px; font-weight: 600; color: var(--text-primary); margin: 0;">🔑 Ganti Password Akun Root</h3>
                <button class="modal-close" onclick="closeChangePasswordModal()" style="background: transparent; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <form onsubmit="handleChangePasswordSubmit(event)" class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-field">
                    <label class="form-label">Password Root Saat Ini *</label>
                    <input type="password" id="currentRootPass" required placeholder="Masukkan password lama" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Password Root Baru *</label>
                    <input type="password" id="newRootPass" required minlength="4" placeholder="Minimal 4 karakter" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Ulangi Password Baru *</label>
                    <input type="password" id="confirmNewRootPass" required minlength="4" placeholder="Ketik ulang password baru" class="form-input">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 6px;">
                    <button type="button" onclick="closeChangePasswordModal()" class="btn btn-ghost">Batal</button>
                    <button type="submit" id="btnSubmitChangePass" class="btn btn-primary">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // HTTPS Enforcer disabled to allow local IP access

        
        // ==========================================
        // 🔔 TOAST & CONFIRM DIALOG HELPERS
        // ==========================================
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            
            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
            const toast = document.createElement('div');
            toast.className = `nvr-toast ${type}`;
            toast.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${escapeHTML(message)}</span>`;
            
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 250);
            }, 3500);
        }

        function showConfirm(message, onConfirm, onCancel = null) {
            const modal = document.getElementById('customConfirmModal');
            const msgEl = document.getElementById('confirmMessage');
            const okBtn = document.getElementById('confirmBtnOk');
            const cancelBtn = document.getElementById('confirmBtnCancel');
            if (!modal || !msgEl || !okBtn || !cancelBtn) {
                if (confirm(message)) onConfirm();
                return;
            }

            msgEl.innerText = message;
            modal.style.display = 'flex';

            const cleanup = () => {
                modal.style.display = 'none';
                okBtn.onclick = null;
                cancelBtn.onclick = null;
            };

            okBtn.onclick = () => {
                cleanup();
                if (typeof onConfirm === 'function') onConfirm();
            };

            cancelBtn.onclick = () => {
                cleanup();
                if (typeof onCancel === 'function') onCancel();
            };
        }

        const fileServerUrl = "/recordings/";
        const go2rtcUrl = "/go2rtc/";
        
        
        // ==========================================
        // 🎯 CAMERA PREFIX & TIMELINE HELPERS
        // ==========================================
        function getCameraBasePrefix(camId) {
            if (!camId) return '';
            return camId.replace(/(_main_h264|_sub_h264|_main|_sub|_h264)$/i, '');
        }

        function getPrefixFromFilename(filename) {
            if (!filename) return '';
            const match = filename.match(/^(.+?)_rec_\d{8}_\d{6}\.mp4$/);
            if (match) return match[1];
            const parts = filename.split('_rec_');
            if (parts.length > 1) return parts[0];
            return filename.split('_')[0];
        }

        let selectedCamera = "";
        let allRecordings = [];
        let isGridMode = false;
        let ptzSupportedList = [];

        // Elements
        const liveFrame = document.getElementById('liveStreamFrame');
        const playbackVideo = document.getElementById('playbackVideo');
        const playerTitle = document.getElementById('playerTitle');
        const playerMode = document.getElementById('playerMode');
        const recordingsContainer = document.getElementById('recordingsContainer');
        const mainPlayerCard = document.getElementById('mainPlayerCard');
        
        // Custom Player Elements
        const customPlayerWrapper = document.getElementById('customPlayerWrapper');
        const customPlayerControls = document.getElementById('customPlayerControls');
        const customProgressBar = document.getElementById('customProgressBar');
        const customPlayBtn = document.getElementById('customPlayBtn');
        const customSkipBackBtn = document.getElementById('customSkipBackBtn');
        const customSkipForwardBtn = document.getElementById('customSkipForwardBtn');
        const customTimeDisplay = document.getElementById('customTimeDisplay');
        const customSpeedSelect = document.getElementById('customSpeedSelect');
        const customFullscreenBtn = document.getElementById('customFullscreenBtn');

        // Playback Status Elements
        const playbackStatusOverlay = document.getElementById('playbackStatusOverlay');
        const playbackStatusText = document.getElementById('playbackStatusText');

        // Escape HTML helper to prevent XSS
        function escapeHTML(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Formats time helper (supports HH:MM:SS or MM:SS)
        function formatTime(seconds, forceHours = false) {
            if (isNaN(seconds) || seconds === Infinity || seconds <= 0) return forceHours ? "00:00:00" : "00:00";
            const totalSecs = Math.floor(seconds);
            const h = Math.floor(totalSecs / 3600);
            const m = Math.floor((totalSecs % 3600) / 60);
            const s = totalSecs % 60;
            
            if (h > 0 || forceHours) {
                return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        // Update progress bar and time display
        playbackVideo.addEventListener('timeupdate', () => {
            if (playbackVideo.duration && !isNaN(playbackVideo.duration)) {
                const percentage = (playbackVideo.currentTime / playbackVideo.duration) * 100;
                customProgressBar.value = percentage;
                const hasHours = playbackVideo.duration >= 3600;
                customTimeDisplay.innerText = `${formatTime(playbackVideo.currentTime, hasHours)} / ${formatTime(playbackVideo.duration, hasHours)}`;
            }
        });

        // Seek video using scrubber
        customProgressBar.addEventListener('input', () => {
            if (playbackVideo.duration) {
                playbackVideo.currentTime = (customProgressBar.value / 100) * playbackVideo.duration;
            }
        });

        // Play/Pause toggler
        function togglePlayPause() {
            if (playbackVideo.paused) {
                playbackVideo.play();
                customPlayBtn.innerText = "⏸️";
                showFeedbackOverlay('▶️ Play');
            } else {
                playbackVideo.pause();
                customPlayBtn.innerText = "▶️";
                showFeedbackOverlay('⏸️ Pause');
            }
        }
        customPlayBtn.addEventListener('click', togglePlayPause);
        playbackVideo.addEventListener('play', () => { customPlayBtn.innerText = "⏸️"; });
        playbackVideo.addEventListener('pause', () => { customPlayBtn.innerText = "▶️"; });

        // Skip buttons
        customSkipBackBtn.addEventListener('click', () => {
            playbackVideo.currentTime = Math.max(0, playbackVideo.currentTime - 10);
            showFeedbackOverlay('⏪ Mundur 10s');
        });

        customSkipForwardBtn.addEventListener('click', () => {
            playbackVideo.currentTime = Math.min(playbackVideo.duration || 0, playbackVideo.currentTime + 10);
            showFeedbackOverlay('⏩ Maju 10s');
        });

        // Playback speed
        customSpeedSelect.addEventListener('change', () => {
            playbackVideo.playbackRate = parseFloat(customSpeedSelect.value);
            showFeedbackOverlay(`⚡ Speed: ${customSpeedSelect.value}x`);
        });

        // Fullscreen toggle on wrapper
        customFullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                customPlayerWrapper.requestFullscreen().catch(err => {
                    console.error("Fullscreen failed:", err);
                });
            } else {
                document.exitFullscreen();
            }
        });

        // Auto hide controls overlay
        let controlsTimeout;
        function showControls() {
            customPlayerWrapper.classList.add('show-controls');
            clearTimeout(controlsTimeout);
            if (!playbackVideo.paused) {
                controlsTimeout = setTimeout(() => {
                    customPlayerWrapper.classList.remove('show-controls');
                }, 2000);
            }
        }
        customPlayerWrapper.addEventListener('mousemove', showControls);
        customPlayerWrapper.addEventListener('mouseleave', () => {
            if (!playbackVideo.paused) {
                customPlayerWrapper.classList.remove('show-controls');
            }
        });

        // Inject custom CSS to eliminate all black bars & letterboxing
        liveFrame.addEventListener('load', () => {
            try {
                const iframeDoc = liveFrame.contentDocument || liveFrame.contentWindow.document;
                if (iframeDoc) {
                    const style = iframeDoc.createElement('style');
                    style.textContent = `
                        .error, .mode, .info { display: none !important; }
                        html, body { overflow: hidden !important; background: #000 !important; margin: 0 !important; padding: 0 !important; width: 100% !important; height: 100% !important; }
                        video { width: 100% !important; height: 100% !important; object-fit: contain !important; }
                    `;
                    iframeDoc.head.appendChild(style);
                }
            } catch (e) {}
        });

        // ==========================================
        // UNIFIED TIMESHIFT/DVR TIMELINE LOGIC
        // ==========================================
        const unifiedTimelineSlider = document.getElementById('unifiedTimelineSlider');
        const timelineStatusText = document.getElementById('timelineStatusText');
        const timelineStatusDot = document.getElementById('timelineStatusDot');
        const btnGoLive = document.getElementById('btnGoLive');

        let isLiveMode = true;
        let liveUpdateInterval = null;
        let parsedRecordings = [];
        let activeTimelineSegments = [];
        let timelineAbortController = null;
        let isPtzOverlayManuallyVisible = false;
        let hasManuallyToggledPtz = false;

        // Parse recording filename to time range
        function parseRecordingTimeRange(filename) {
            const regex = /^(.+?)_rec_(\d{8})_(\d{6})\.mp4$/;
            const match = filename.match(regex);
            if (!match) return null;
            
            const prefix = match[1];
            const dateStr = match[2]; // YYYYMMDD
            const timeStr = match[3]; // HHMMSS
            
            const hours = parseInt(timeStr.substring(0, 2));
            const minutes = parseInt(timeStr.substring(2, 4));
            const seconds = parseInt(timeStr.substring(4, 6));
            
            const startSeconds = hours * 3600 + minutes * 60 + seconds;
            const duration = 600; // 10 minutes segments
            
            return {
                prefix: prefix,
                dateStr: `${dateStr.substring(0, 4)}-${dateStr.substring(4, 6)}-${dateStr.substring(6, 8)}`,
                startSeconds: startSeconds,
                endSeconds: startSeconds + duration,
                filename: filename
            };
        }

        // Build index of recordings with dynamic duration
        function indexRecordings() {
            const grouped = {};
            allRecordings.forEach(rec => {
                const parsed = parseRecordingTimeRange(rec.filename);
                if (parsed) {
                    const key = `${parsed.prefix}_${parsed.dateStr}`;
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push(parsed);
                }
            });
            
            parsedRecordings = [];
            for (const key in grouped) {
                const list = grouped[key];
                list.sort((a, b) => a.startSeconds - b.startSeconds);
                
                for (let i = 0; i < list.length; i++) {
                    const file = list[i];
                    const nextFile = list[i + 1];
                    const diff = nextFile ? (nextFile.startSeconds - file.startSeconds) : 9999;
                    
                    let duration = 600;
                    if (diff < 150) {
                        duration = 60; // old 1-minute segments
                    }
                    
                    file.endSeconds = file.startSeconds + duration;
                    parsedRecordings.push(file);
                }
            }
        }

        // Format seconds to HH:MM:SS
        function secondsToTimeString(totalSeconds) {
            const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            const s = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        // Live stream timeline scrubber update
        function startLiveTimelineUpdates() {
            if (liveUpdateInterval) {
                clearInterval(liveUpdateInterval);
                liveUpdateInterval = null;
            }
            
            const updateScrubber = () => {
                if (isLiveMode) {
                    const now = new Date();
                    const currentSec = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
                    if (unifiedTimelineSlider && !isUserDraggingSlider) {
                        unifiedTimelineSlider.value = currentSec;
                    }
                    if (timelineStatusText) {
                        timelineStatusText.innerText = `Mode: LIVE Stream (${secondsToTimeString(currentSec)})`;
                    }
                }
            };
            updateScrubber();
            liveUpdateInterval = setInterval(updateScrubber, 1000);
        }

        // Go back to LIVE mode
        function switchToLiveMode() {
            isLiveMode = true;
            btnGoLive.style.display = 'none';
            timelineStatusDot.style.backgroundColor = 'var(--accent-blue)';
            
            playbackVideo.pause();
            playbackVideo.src = '';
            customPlayerWrapper.style.display = 'none';
            playbackStatusOverlay.style.display = 'none';
            
            const activeItem = document.querySelector('#cameraListContainer .cam-row.active');
            const titleVal = activeItem ? activeItem ? activeItem.dataset.title : 'CCTV Stream' : "CCTV Stream";
            showLive(selectedCamera, titleVal);
            startLiveTimelineUpdates();
        }

        // PTZ Action Helper with Debounce, Visual Busy State, and Keyboard Navigation
        let ptzLastSent = 0;
        let ptzActiveAbortController = null;

        function sendPTZ(direction) {
            if (!selectedCamera) return;
            
            const now = Date.now();
            if (now - ptzLastSent < 250) {
                // Throttle rapid spamming (<250ms)
                return;
            }
            ptzLastSent = now;

            const camPrefix = getCameraBasePrefix(selectedCamera);
            const overlay = document.getElementById('ptzControlOverlay');
            
            // Find target button
            let targetBtn = null;
            if (overlay) {
                const btnMap = { 'up': 0, 'left': 1, 'right': 2, 'down': 3 };
                const buttons = overlay.querySelectorAll('.ptz-btn');
                if (btnMap[direction] !== undefined && buttons[btnMap[direction]]) {
                    targetBtn = buttons[btnMap[direction]];
                }
            }

            if (targetBtn) {
                targetBtn.classList.add('busy');
            }

            if (ptzActiveAbortController) {
                try { ptzActiveAbortController.abort(); } catch(e) {}
            }
            ptzActiveAbortController = new AbortController();

            fetch(`/index.php/nvr/ptz?camera=${encodeURIComponent(camPrefix)}&action=${encodeURIComponent(direction)}&t=${now}`, {
                signal: ptzActiveAbortController.signal
            })
            .then(r => r.json())
            .then(res => {
                if (targetBtn) targetBtn.classList.remove('busy');
                if (res.status === 'success') {
                    console.log(`[PTZ] OK: ${direction} on ${camPrefix} (${res.elapsed_ms || 0}ms)`);
                } else {
                    console.warn(`[PTZ] Fail:`, res.message);
                    showToast(`PTZ ${direction} gagal: ${res.message || 'Error'}`, 'warning');
                }
            })
            .catch(err => {
                if (targetBtn) targetBtn.classList.remove('busy');
                if (err.name === 'AbortError') return;
                console.error('[PTZ] Network Error:', err);
                showToast(`Gagal kirim perintah PTZ: ${err.message}`, 'error');
            });
        }

        // Global Keyboard Shortcut for PTZ (Arrow Keys when PTZ mode is open and in Live mode)
        document.addEventListener('keydown', (e) => {
            if (!isPtzOverlayManuallyVisible || !isLiveMode) return;
            // Ignore if typing inside input, textarea, or select
            const tag = (document.activeElement && document.activeElement.tagName) ? document.activeElement.tagName.toLowerCase() : '';
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                sendPTZ('up');
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                sendPTZ('down');
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                sendPTZ('left');
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                sendPTZ('right');
            }
        });

        let pendingSeekOffset = null;
        
        function applyPendingSeek() {
            if (pendingSeekOffset !== null && playbackVideo.readyState >= 2) {
                const target = pendingSeekOffset;
                pendingSeekOffset = null;
                try {
                    if (playbackVideo.duration && !isNaN(playbackVideo.duration)) {
                        playbackVideo.currentTime = Math.min(Math.max(0, target), Math.max(0, playbackVideo.duration - 0.5));
                    } else {
                        playbackVideo.currentTime = Math.max(0, target);
                    }
                } catch (e) {
                    console.warn("Deferred seek exception:", e);
                }
            }
        }

        playbackVideo.addEventListener('loadedmetadata', () => {
            playbackStatusOverlay.style.display = 'none';
            applyPendingSeek();
        });

        playbackVideo.addEventListener('loadeddata', () => {
            playbackStatusOverlay.style.display = 'none';
            applyPendingSeek();
        });

        playbackVideo.addEventListener('canplay', () => {
            playbackStatusOverlay.style.display = 'none';
            applyPendingSeek();
        });

        playbackVideo.addEventListener('waiting', () => {
            // Never block the screen with full dark overlay during buffering
            if (playbackStatusOverlay) playbackStatusOverlay.style.display = 'none';
        });

        playbackVideo.addEventListener('playing', () => {
            playbackStatusOverlay.style.display = 'none';
        });

        // Playback error diagnostics (non-blocking graceful fallback)
        playbackVideo.addEventListener('error', () => {
            const error = playbackVideo.error;
            console.warn("Playback error encountered:", error);
            
            // If seek failed mid-stream, fallback to beginning of chunk
            if (pendingSeekOffset !== null) {
                pendingSeekOffset = null;
                try {
                    playbackVideo.currentTime = 0;
                    playbackVideo.play().catch(() => {});
                    return;
                } catch (e) {}
            }
            
            if (playbackStatusOverlay) playbackStatusOverlay.style.display = 'none';
            showToast("Tidak dapat memutar posisi rekaman ini", "warning");
        });

        // Segment visual canvas renderer
        function renderTimelineCanvas() {
            const canvas = document.getElementById('timelineCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const width = canvas.offsetWidth;
            const height = canvas.offsetHeight;
            canvas.width = width;
            canvas.height = height;

            ctx.clearRect(0, 0, width, height);

            // Draw available timeline block ranges (Gold color brand highlight)
            ctx.fillStyle = 'rgba(212, 168, 67, 0.4)'; 
            activeTimelineSegments.forEach(seg => {
                const xStart = (seg.startSeconds / 86400) * width;
                const xEnd = (seg.endSeconds / 86400) * width;
                ctx.fillRect(xStart, 0, Math.max(2, xEnd - xStart), height);
            });
        }

        // Fetch segment blocks from backend API for a target date with instant in-memory rendering & abort controller
        function loadTimelineSegments(dateStr) {
            if (!selectedCamera) return;

            const camPrefix = getCameraBasePrefix(selectedCamera);

            // 1. INSTANT LOCAL RENDER (0ms response time): Render from in-memory parsed index immediately!
            if (parsedRecordings && parsedRecordings.length > 0) {
                const localMatches = parsedRecordings.filter(rec =>
                    rec.prefix === camPrefix && rec.dateStr === dateStr
                );
                activeTimelineSegments = localMatches;
                renderTimelineCanvas();
            }

            // 2. Abort previous pending fetch to prevent race conditions during rapid camera switching
            if (timelineAbortController) {
                timelineAbortController.abort();
            }
            timelineAbortController = new AbortController();
            const signal = timelineAbortController.signal;

            const isAnyka = databaseCameraSettings && databaseCameraSettings.some(c => c.id === selectedCamera && c.cam_type === 'anyka');

            if (isAnyka) {
                const dateParts = dateStr.split('-');
                const formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                fetch(`/index.php/nvr/get_anyka_chunks?camera=${encodeURIComponent(selectedCamera)}&date=${encodeURIComponent(formattedDate)}&t=${Date.now()}`, { signal })
                    .then(r => r.json())
                    .then(data => {
                        activeTimelineSegments = data.map(item => {
                            const d = new Date(item.timestamp * 1000);
                            const localSeconds = d.getHours() * 3600 + d.getMinutes() * 60 + d.getSeconds();
                            return {
                                startSeconds: localSeconds,
                                endSeconds: localSeconds + (item.duration || 600),
                                filename: item.timestamp
                            };
                        });
                        renderTimelineCanvas();
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') return;
                        console.warn("Anyka timeline segments fallback:", err);
                    });
            } else {
                const apiDate = dateStr.replace(/-/g, '');
                fetch(`/index.php/nvr/get_timeline_recordings?camera=${encodeURIComponent(camPrefix)}&date=${encodeURIComponent(apiDate)}&t=${Date.now()}`, { signal })
                    .then(r => r.json())
                    .then(data => {
                        if (data && Array.isArray(data) && data.length > 0) {
                            activeTimelineSegments = data.map(item => ({
                                startSeconds: item.start,
                                endSeconds: item.end,
                                filename: item.filename
                            }));
                        } else if (!activeTimelineSegments || activeTimelineSegments.length === 0) {
                            activeTimelineSegments = parsedRecordings.filter(rec =>
                                rec.prefix === camPrefix && rec.dateStr === dateStr
                            );
                        }
                        renderTimelineCanvas();
                    })
                    .catch(err => {
                        if (err.name === 'AbortError') return;
                        console.warn("Timeline segments fetch fallback to local index:", err);
                        activeTimelineSegments = parsedRecordings.filter(rec =>
                            rec.prefix === camPrefix && rec.dateStr === dateStr
                        );
                        renderTimelineCanvas();
                    });
            }
        }

        function handleDateChange() {
            const dateVal = document.getElementById('filterDate').value;
            const filterCameraVal = document.getElementById('filterCamera').value;
            
            // Sync selectedCamera with filter camera if not 'all'
            if (filterCameraVal !== 'all') {
                const matchingCam = databaseCameraSettings.find(c =>
                    getCameraBasePrefix(c.id) === filterCameraVal &&
                    (c.id.endsWith('_sub') || c.id.endsWith('_main_h264'))
                ) || databaseCameraSettings.find(c => getCameraBasePrefix(c.id) === filterCameraVal);
                if (matchingCam) {
                    selectedCamera = matchingCam.id;
                }
            }
            
            if (dateVal) {
                loadTimelineSegments(dateVal);
                updateTimelineDateLabel(dateVal);
            }
            filterAndRenderRecordings();
        }

        function handleCameraFilterChange() {
            const filterCameraVal = document.getElementById('filterCamera').value;
            
            if (filterCameraVal !== 'all') {
                const matchingCam = databaseCameraSettings.find(c => 
                    getCameraBasePrefix(c.id) === filterCameraVal && 
                    (c.id.endsWith('_sub') || c.id.endsWith('_main_h264'))
                ) || databaseCameraSettings.find(c => getCameraBasePrefix(c.id) === filterCameraVal);

                if (matchingCam) {
                    selectedCamera = matchingCam.id;
                    
                    // Highlight selected camera in left sidebar
                    document.querySelectorAll('#cameraListContainer .cam-row').forEach(i => {
                        const isActive = (i.dataset.src === selectedCamera);
                        i.classList.toggle('active', isActive);
                        const cb = i.querySelector('.camera-select-checkbox');
                        if (cb) cb.checked = isActive;
                    });

                    // Switch the main player stream to the selected camera
                    const camName = matchingCam.name || "CCTV Stream";
                    showLive(selectedCamera, camName);
                }
            }
            
            const filterDateVal = document.getElementById('filterDate').value;
            if (filterDateVal) {
                loadTimelineSegments(filterDateVal);
            }
            
            filterAndRenderRecordings();
        }

        function navigateTimelineDate(offset) {
            const filterDateEl = document.getElementById('filterDate');
            let y, m, d;
            if (filterDateEl.value && filterDateEl.value.includes('-')) {
                const parts = filterDateEl.value.split('-').map(Number);
                y = parts[0];
                m = parts[1] - 1;
                d = parts[2];
            } else {
                const now = new Date();
                y = now.getFullYear();
                m = now.getMonth();
                d = now.getDate();
            }
            const targetDate = new Date(y, m, d + offset);
            const yyyy = targetDate.getFullYear();
            const mm = (targetDate.getMonth() + 1).toString().padStart(2, '0');
            const dd = targetDate.getDate().toString().padStart(2, '0');
            
            filterDateEl.value = `${yyyy}-${mm}-${dd}`;
            handleDateChange();
        }

        function updateTimelineDateLabel(dateStr) {
            const label = document.getElementById('timelineDateLabel');
            if (!label) return;
            
            const today = new Date();
            const todayStr = `${today.getFullYear()}-${(today.getMonth()+1).toString().padStart(2,'0')}-${today.getDate().toString().padStart(2,'0')}`;
            
            if (dateStr === todayStr) {
                label.innerText = 'Hari ini';
            } else {
                const parts = dateStr.split('-');
                label.innerText = `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
        }

        // Play segment at seconds offset (instant & non-blocking)
        function playRecordingAtSeconds(seconds) {
            const cameraPrefix = getCameraBasePrefix(selectedCamera);
            const filterDateVal = document.getElementById('filterDate').value;

            let targetDateStr = filterDateVal;
            if (!targetDateStr) {
                const now = new Date();
                targetDateStr = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')}`;
                document.getElementById('filterDate').value = targetDateStr;
            }

            const file = activeTimelineSegments.find(seg =>
                seconds >= seg.startSeconds && seconds < seg.endSeconds
            );

            if (file) {
                isLiveMode = false;
                btnGoLive.style.display = 'inline-block';
                timelineStatusDot.style.backgroundColor = 'var(--accent-green)';
                timelineStatusText.innerText = `Mode: PLAYBACK (${secondsToTimeString(seconds)})`;

                const ptzOverlay = document.getElementById('ptzControlOverlay');
                if (ptzOverlay) ptzOverlay.style.display = 'none';

                const offset = Math.max(0, seconds - file.startSeconds);
                let fileUrl = `${fileServerUrl}${file.filename}`;

                if (playbackStatusOverlay) playbackStatusOverlay.style.display = 'none';

                if (!playbackVideo.src.includes(file.filename)) {
                    liveFrame.src = 'about:blank';
                    customPlayerWrapper.style.display = 'flex';
                    playbackVideo.src = fileUrl;
                    playbackVideo.currentTime = offset;
                    playbackVideo.play().catch(err => console.log("DVR play catch:", err));
                } else {
                    try {
                        playbackVideo.currentTime = offset;
                    } catch(e) {}
                    if (playbackVideo.paused) {
                        playbackVideo.play().catch(err => {});
                    }
                }
            } else {
                timelineStatusText.innerText = `Tidak ada rekaman pada jam ${secondsToTimeString(seconds)}`;
                timelineStatusDot.style.backgroundColor = 'var(--accent-red)';
            }
        }

        // Segment chaining logic
        playbackVideo.addEventListener('ended', () => {
            if (!isLiveMode && playbackVideo.src) {
                let currentFile = playbackVideo.src.split('/').pop();
                if (selectedCamera.startsWith('cctv')) {
                    const urlParams = new URLSearchParams(playbackVideo.src.split('?')[1] || '');
                    currentFile = parseInt(urlParams.get('timestamp') || '0');
                }
                const currentSeg = activeTimelineSegments.find(seg => 
                    seg.filename === currentFile || String(seg.filename) === String(currentFile)
                );
                if (currentSeg) {
                    const nextSec = currentSeg.endSeconds + 1;
                    playRecordingAtSeconds(nextSec);
                }
            }
        });

        let isUserDraggingSlider = false;
        unifiedTimelineSlider.addEventListener('mousedown', () => { isUserDraggingSlider = true; });
        unifiedTimelineSlider.addEventListener('touchstart', () => { isUserDraggingSlider = true; });

        unifiedTimelineSlider.addEventListener('input', () => {
            const targetSec = parseInt(unifiedTimelineSlider.value);
            timelineStatusText.innerText = `Geser ke: ${secondsToTimeString(targetSec)}`;
            timelineStatusDot.style.backgroundColor = 'var(--accent-blue)';
        });

        unifiedTimelineSlider.addEventListener('change', () => {
            isUserDraggingSlider = false;
            const targetSec = parseInt(unifiedTimelineSlider.value);
            playRecordingAtSeconds(targetSec);
        });

        // Sync scrubber position with playback timeline
        playbackVideo.addEventListener('timeupdate', () => {
            if (!isLiveMode && playbackVideo.src && !isUserDraggingSlider) {
                let currentFileUrl = playbackVideo.src.split('/').pop();
                if (selectedCamera.startsWith('cctv')) {
                    const urlParams = new URLSearchParams(playbackVideo.src.split('?')[1] || '');
                    currentFileUrl = parseInt(urlParams.get('timestamp') || '0');
                }
                const file = activeTimelineSegments.find(f => 
                    f.filename === currentFileUrl || String(f.filename) === String(currentFileUrl)
                );
                if (file) {
                    const currentSec = file.startSeconds + playbackVideo.currentTime;
                    unifiedTimelineSlider.value = currentSec;
                    
                    const activeDate = document.getElementById('filterDate').value;
                    const dateParts = activeDate ? activeDate.split('-') : null;
                    const datePrefix = dateParts ? `${dateParts[2]}/${dateParts[1]}/${dateParts[0]} ` : '';
                    timelineStatusText.innerText = `Mode: PLAYBACK (${datePrefix}${secondsToTimeString(currentSec)})`;
                }
            }
        });

        // Modals management
        function openConfigModal(page, title) {
            const modal = document.getElementById('configModal');
            const iframe = document.getElementById('configIframe');
            const modalTitle = document.getElementById('configModalTitle');
            
            modalTitle.innerText = title;
            iframe.src = `${go2rtcUrl}${page}`;
            modal.style.display = 'flex';
        }

        function closeConfigModal() {
            const modal = document.getElementById('configModal');
            const iframe = document.getElementById('configIframe');
            
            iframe.src = 'about:blank';
            modal.style.display = 'none';
        }

        // ==========================================
        // ➕ ADD CAMERA LOGIC
        // ==========================================
        function openAddCameraModal() {
            const modal = document.getElementById('addCameraModal');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('addCamName').focus();
            }
        }

        function closeAddCameraModal() {
            const modal = document.getElementById('addCameraModal');
            if (modal) modal.style.display = 'none';
        }

        function handleAddCamTypeChange() {
            const type = document.getElementById('addCamType').value;
            const pathRow = document.getElementById('addCamPathRow');
            if (type === 'anyka') {
                if (pathRow) pathRow.style.display = 'none';
            } else {
                if (pathRow) pathRow.style.display = 'flex';
            }
        }

        function submitAddCamera(event) {
            event.preventDefault();
            const btn = document.getElementById('btnAddCamSubmit');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const payload = {
                name: document.getElementById('addCamName').value.trim(),
                cam_type: document.getElementById('addCamType').value,
                ip: document.getElementById('addCamIp').value.trim(),
                port: parseInt(document.getElementById('addCamPort').value) || 554,
                username: document.getElementById('addCamUser').value.trim(),
                password: document.getElementById('addCamPass').value,
                stream_path: document.getElementById('addCamPath') ? document.getElementById('addCamPath').value.trim() : '',
                is_recording: document.getElementById('addCamRec').checked ? 1 : 0
            };

            fetch('/index.php/nvr/add_camera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerText = originalText;
                if (res.status === 'success') {
                    showToast('Berhasil! ' + res.message, 'success');
                    closeAddCameraModal();
                    document.getElementById('addCameraForm').reset();
                    loadCustomCameraNames(); // refresh sidebar list
                    loadPtzSupport(); // refresh PTZ list
                } else {
                    showToast('Gagal menambah kamera: ' + (res.message || 'Unknown error'), 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = originalText;
                showToast('Terjadi kesalahan jaringan: ' + err.message, 'error');
            });
        }

        // ==========================================
        // GLOBAL SETTINGS & UNIFIED MODAL LOGIC
        // ==========================================
        function openGlobalSettingsModal(tab = 'general') {
            const modal = document.getElementById('globalSettingsModal');
            if (modal) modal.style.display = 'flex';
            
            switchSettingsTab(tab);
            
            // Fetch disk status
            fetch('/index.php/nvr/get_disk_status')
                .then(r => r.json())
                .then(data => {
                    const infoText = document.getElementById('diskModalInfoText');
                    const percentText = document.getElementById('diskPercentText');
                    const progress = document.getElementById('diskProgressBar');
                    if (infoText) infoText.innerText = `Terpakai: ${data.used_formatted} / Total: ${data.total_formatted} (Bebas: ${data.free_formatted})`;
                    if (percentText) percentText.innerText = `${data.percent}%`;
                    if (progress) {
                        progress.style.width = `${data.percent}%`;
                        if (data.percent > 90) progress.style.backgroundColor = 'var(--accent-red)';
                        else if (data.percent > 75) progress.style.backgroundColor = 'var(--accent-gold)';
                        else progress.style.backgroundColor = 'var(--accent-blue)';
                    }
                })
                .catch(err => console.error("Error fetching disk status:", err));
                
            // Fetch cleanup config
            fetch('/index.php/nvr/get_cleanup_config')
                .then(r => r.json())
                .then(data => {
                    const retention = document.getElementById('retentionDaysInput');
                    const diskLimit = document.getElementById('diskLimitInput');
                    if (retention) retention.value = data.retention_days;
                    if (diskLimit) diskLimit.value = data.disk_limit_percent;
                })
                .catch(err => console.error("Error fetching cleanup config:", err));
                
            renderCameraSettingsTable();
        }

        function switchSettingsTab(tab) {
            const tabGeneral = document.getElementById('settingsTabGeneral');
            const tabGo2rtc = document.getElementById('settingsTabGo2rtc');
            const btnGeneral = document.getElementById('tabBtnGeneral');
            const btnGo2rtc = document.getElementById('tabBtnGo2rtc');
            const iframe = document.getElementById('go2rtcConfigIframe');
            
            if (tab === 'go2rtc') {
                if (tabGeneral) tabGeneral.style.display = 'none';
                if (tabGo2rtc) tabGo2rtc.style.display = 'block';
                if (btnGeneral) btnGeneral.classList.remove('active');
                if (btnGo2rtc) btnGo2rtc.classList.add('active');
                if (iframe && (iframe.src === 'about:blank' || !iframe.src.includes('config.html'))) {
                    iframe.src = `${go2rtcUrl}config.html?embed=true&v=5`;
                }
            } else {
                if (tabGeneral) tabGeneral.style.display = 'flex';
                if (tabGo2rtc) tabGo2rtc.style.display = 'none';
                if (btnGeneral) btnGeneral.classList.add('active');
                if (btnGo2rtc) btnGo2rtc.classList.remove('active');
            }
        }

        function renderCameraSettingsTable() {
            const tbody = document.getElementById('cameraSettingsTableBody');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--text-secondary);">Memuat data kamera...</td></tr>';
            
            fetch('/index.php/nvr/get_settings')
                .then(r => r.json())
                .then(settings => {
                    databaseCameraSettings = settings;
                    let html = '';
                    settings.forEach(cam => {
                        const isRecording = cam.is_recording == 1;
                        const isPublic = parseInt(cam.is_public) === 1;
                        const isOnline = cam.is_online !== undefined ? (parseInt(cam.is_online) === 1) : !cam.id.startsWith('cctv2');
                        const statusText = isOnline ? 'Online' : 'Offline';
                        const badgeColor = isOnline ? 'var(--accent-green)' : 'var(--accent-red)';
                        
                        const switchChecked = isRecording ? 'checked' : '';
                        const sliderColor = isRecording ? 'var(--accent-red)' : 'rgba(255, 255, 255, 0.15)';
                        const circlePos = isRecording ? '16px' : '3px';

                        const publicChecked = isPublic ? 'checked' : '';
                        const publicSliderColor = isPublic ? 'var(--accent-blue)' : 'rgba(255, 255, 255, 0.15)';
                        const publicCirclePos = isPublic ? '16px' : '3px';
                        
                        html += `
                            <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255,255,255,0.01);">
                                <td style="padding: 12px 16px; font-weight: 500;">${escapeHTML(cam.name)}</td>
                                <td style="padding: 12px 16px; color: var(--text-secondary); font-family: monospace; font-size: 11.5px;">${cam.id}</td>
                                <td style="padding: 12px 16px;">
                                    <span style="font-size: 9.5px; font-weight: bold; color: ${badgeColor}; border: 1px solid ${badgeColor}33; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">${statusText}</span>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <label style="position: relative; display: inline-block; width: 34px; height: 20px; vertical-align: middle;">
                                        <input type="checkbox" ${switchChecked} onchange="toggleRecordingFromModal('${cam.id}', ${isRecording})" style="opacity: 0; width: 0; height: 0;">
                                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: ${sliderColor}; transition: .2s; border-radius: 20px;">
                                            <span style="position: absolute; content: ''; height: 14px; width: 14px; left: ${circlePos}; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%;"></span>
                                        </span>
                                    </label>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <label style="position: relative; display: inline-block; width: 34px; height: 20px; vertical-align: middle;" title="${isPublic ? 'Akses Publik Aktif (Bisa dibuka tanpa login)' : 'Akses Privat (Perlu login root)'}">
                                        <input type="checkbox" ${publicChecked} onchange="togglePublicShareFromModal('${cam.id}', ${isPublic})" style="opacity: 0; width: 0; height: 0;">
                                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: ${publicSliderColor}; transition: .2s; border-radius: 20px;">
                                            <span style="position: absolute; content: ''; height: 14px; width: 14px; left: ${publicCirclePos}; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%;"></span>
                                        </span>
                                    </label>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <button class="header-nav" onclick="openShareModalForCamera('${cam.id}', '${escapeHTML(cam.name)}')" style="padding: 4px 8px; font-size: 11px; border-color: var(--accent-gold); color: var(--accent-gold); background: rgba(212,168,67,0.1); border-radius: 4px; cursor: pointer;" title="Salin Tautan Share">&#128279; Share</button>
                                        <button class="header-nav" onclick="renameCameraFromModal('${cam.id}')" style="padding: 4px 8px; font-size: 11px; border-color: var(--border-color); color: var(--text-primary); background: transparent; border-radius: 4px; cursor: pointer;">Rename</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--accent-red);">Gagal memuat: ${err.message}</td></tr>`;
                });
        }

        function togglePublicShareFromModal(id, currentVal) {
            const newValue = currentVal ? 0 : 1;
            fetch('/index.php/nvr/save_setting', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, is_public: newValue })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    showToast(newValue ? 'Akses diubah ke Publik 🌐' : 'Akses diubah ke Privat 🔒', 'info');
                    renderCameraSettingsTable();
                    loadCustomCameraNames();
                }
            })
            .catch(err => showToast('Error: ' + err.message, 'error'));
        }

        function toggleRecordingFromModal(id, currentVal) {
            const newValue = currentVal ? 0 : 1;
            fetch('/index.php/nvr/save_setting', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, is_recording: newValue })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    renderCameraSettingsTable();
                    loadCustomCameraNames(); // refresh sidebar list
                }
            })
            .catch(err => showToast('Error: ' + err.message, 'error'));
        }

        function renameCameraFromModal(id) {
            const cam = databaseCameraSettings.find(c => c.id === id);
            const cleanBase = currentName.replace(/\s*\(?(sub|main|h264|main_h264|sub_h264)\)?/gi, '').replace(/\s*\(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\)/gi, '').trim();
            const newName = prompt("Masukkan nama baru untuk kamera:", cleanBase || currentName);
            if (newName !== null) {
                const trimmed = newName.trim();
                if (trimmed && trimmed !== currentName) {
                    fetch('/index.php/nvr/save_setting', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id, name: trimmed })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            renderCameraSettingsTable();
                            loadCustomCameraNames();
                        } else {
                            showToast('Gagal mengubah nama: ' + res.message, 'error');
                        }
                    })
                    .catch(err => showToast('Error: ' + err.message, 'error'));
                }
            }
        }

        function closeGlobalSettingsModal() {
            const modal = document.getElementById('globalSettingsModal');
            if (modal) modal.style.display = 'none';
        }

        function saveCleanupConfig() {
            const retention = parseInt(document.getElementById('retentionDaysInput').value);
            const diskLimit = parseInt(document.getElementById('diskLimitInput').value);
            
            fetch('/index.php/nvr/save_cleanup_config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ retention_days: retention, disk_limit_percent: diskLimit })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    showToast('Konfigurasi cleanup berhasil disimpan!', 'success');
                } else {
                    showToast('Gagal menyimpan: ' + res.message, 'error');
                }
            })
            .catch(err => showToast('Error: ' + err.message, 'error'));
        }

        // ==========================================
        // 🎤 INTERCOM / TWO-WAY AUDIO MICROPHONE
        // ==========================================
        let intercomPC = null;
        let intercomStream = null;

        async function startIntercom() {
            if (!selectedCamera) return;
            const camPrefix = getCameraBasePrefix(selectedCamera);
            const streamName = camPrefix + '_main';
            const micBtn = document.getElementById('headerMicBtn');
            
            try {
                intercomStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                
                intercomPC = new RTCPeerConnection({
                    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                });
                
                intercomStream.getTracks().forEach(track => {
                    intercomPC.addTransceiver(track, { direction: 'sendonly' });
                });
                
                const wsProtocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
                const wsUrl = `${wsProtocol}//${location.host}/go2rtc/api/ws?src=${encodeURIComponent(streamName)}`;
                const ws = new WebSocket(wsUrl);
                
                // Handle browser ICE candidates and send them to go2rtc
                intercomPC.onicecandidate = (event) => {
                    if (event.candidate && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'webrtc/candidate', value: event.candidate.candidate }));
                    }
                };
                
                ws.onopen = async () => {
                    const offer = await intercomPC.createOffer();
                    await intercomPC.setLocalDescription(offer);
                    ws.send(JSON.stringify({ type: 'webrtc/offer', value: offer.sdp }));
                };
                
                ws.onmessage = (ev) => {
                    const msg = JSON.parse(ev.data);
                    if (msg.type === 'webrtc/answer') {
                        intercomPC.setRemoteDescription({ type: 'answer', sdp: msg.value });
                    } else if (msg.type === 'webrtc/candidate') {
                        intercomPC.addIceCandidate(new RTCIceCandidate({
                            candidate: msg.value,
                            sdpMid: '',
                            sdpMLineIndex: 0
                        })).catch(e => console.warn("Error adding remote ICE candidate:", e));
                    }
                };
                
                if (micBtn) {
                    micBtn.style.background = 'rgba(196, 92, 92, 0.2)';
                    micBtn.style.borderColor = 'var(--accent-red)';
                    micBtn.style.color = 'var(--accent-red)';
                    micBtn.innerText = '🎤 BICARA...';
                }
            } catch (err) {
                console.error('Intercom error:', err);
                let msg = 'Gagal menggunakan Mic.\n\n';
                if (!navigator.mediaDevices) {
                    msg += 'Penyebab: navigator.mediaDevices tidak tersedia.\nSolusi: Buka chrome://flags/#unsafely-treat-insecure-origin-as-secure, tambahkan URL ini, lalu TUTUP SEMUA Chrome dan buka ulang.';
                } else if (err.name === 'NotAllowedError') {
                    msg += 'Penyebab: Izin mikrofon ditolak oleh browser.\nSolusi: Klik ikon gembok/info di address bar → izinkan Mikrofon.';
                } else if (err.name === 'NotFoundError') {
                    msg += 'Penyebab: Tidak ditemukan perangkat mikrofon.\nSolusi: Pastikan mic terpasang dan aktif.';
                } else {
                    msg += 'Error: ' + err.name + ' - ' + err.message;
                }
                showToast(msg, 'info');
            }
        }

        function stopIntercom() {
            if (intercomPC) {
                intercomPC.close();
                intercomPC = null;
            }
            if (intercomStream) {
                intercomStream.getTracks().forEach(track => track.stop());
                intercomStream = null;
            }
            
            const micBtn = document.getElementById('headerMicBtn');
            if (micBtn) {
                micBtn.style.background = 'transparent';
                micBtn.style.borderColor = 'var(--border-color)';
                micBtn.style.color = 'var(--text-primary)';
                micBtn.innerText = '🎤 Mic';
            }
        }

        function isCameraOnline(id) {
            if (!id) return false;
            const prefix = getCameraBasePrefix(id);
            const cam = databaseCameraSettings.find(c => c.id === id || c.id === prefix || getCameraBasePrefix(c.id) === prefix);
            if (cam && cam.is_online !== undefined) {
                return parseInt(cam.is_online) === 1;
            }
            return true;
        }

        // Dynamic PTZ support detection API
        function loadPtzSupport() {
            fetch('/index.php/nvr/get_ptz_support')
                .then(r => r.json())
                .then(data => {
                    ptzSupportedList = data;
                    updatePtzOverlayVisibility();
                })
                .catch(err => {
                    console.error("Failed to load PTZ support list:", err);
                    updatePtzOverlayVisibility();
                });
        }

        function checkSelectedCameraPtzSupport() {
            if (!selectedCamera) return false;
            const camLower = selectedCamera.toLowerCase();
            // All Anyka / Eyesec cameras
            if (camLower.startsWith('cctv1') || camLower.startsWith('cctv3') || camLower.startsWith('cctv4') || camLower.startsWith('cctv5') || camLower.includes('waka') || camLower.includes('anyka') || camLower.includes('eyesec') || camLower.includes('m3')) {
                return true;
            }
            if (ptzSupportedList && ptzSupportedList.length > 0) {
                return ptzSupportedList.some(camId => {
                    const cid = camId.toLowerCase();
                    return cid === camLower || camLower.startsWith(cid) || cid.startsWith(camLower);
                });
            }
            return false;
        }

        function updatePtzOverlayVisibility() {
            const ptzOverlay = document.getElementById('ptzControlOverlay');
            const headerPtzBtn = document.getElementById('headerPtzToggleBtn');
            const aiBtn = document.getElementById('headerAiCenterBtn');
            const micBtn = document.getElementById('headerMicBtn');
            const supportsPtz = checkSelectedCameraPtzSupport();
            
            const homeBtn = document.getElementById('headerHomeBtn');
            const setHomeBtn = document.getElementById('headerSetHomeBtn');

            // Show/hide mic button
            if (micBtn) {
                const isOnline = isCameraOnline(selectedCamera);
                micBtn.style.display = (isOnline && isLiveMode) ? 'inline-flex' : 'none';
            }

            // Show/hide AI Focus & Home buttons for PTZ-supported cameras in Live Mode
            if (aiBtn) {
                aiBtn.style.display = (supportsPtz && isLiveMode) ? 'inline-flex' : 'none';
            }
            if (homeBtn) {
                homeBtn.style.display = (supportsPtz && isLiveMode) ? 'inline-flex' : 'none';
            }
            if (setHomeBtn) {
                setHomeBtn.style.display = (supportsPtz && isLiveMode) ? 'inline-flex' : 'none';
            }
            
            if (supportsPtz && isLiveMode) {
                if (headerPtzBtn) {
                    headerPtzBtn.style.display = 'inline-flex';
                    if (isPtzOverlayManuallyVisible) {
                        headerPtzBtn.style.borderColor = 'var(--accent-gold)';
                        headerPtzBtn.style.background = 'rgba(212, 168, 67, 0.35)';
                        headerPtzBtn.style.color = 'var(--accent-gold)';
                    } else {
                        headerPtzBtn.style.borderColor = '';
                        headerPtzBtn.style.background = '';
                        headerPtzBtn.style.color = '';
                    }
                }
                
                // Keep PTZ wheel disabled/hidden by default unless user toggled it on
                if (ptzOverlay) {
                    if (isPtzOverlayManuallyVisible) {
                        ptzOverlay.style.display = 'block';
                        requestAnimationFrame(() => {
                            ptzOverlay.style.opacity = '1';
                            ptzOverlay.style.transform = 'scale(1)';
                        });
                    } else {
                        ptzOverlay.style.opacity = '0';
                        ptzOverlay.style.display = 'none';
                    }
                }
            } else {
                if (headerPtzBtn) headerPtzBtn.style.display = 'none';
                if (aiBtn) aiBtn.style.display = 'none';
                if (homeBtn) homeBtn.style.display = 'none';
                if (setHomeBtn) setHomeBtn.style.display = 'none';
                if (ptzOverlay) {
                    ptzOverlay.style.opacity = '0';
                    ptzOverlay.style.display = 'none';
                }
                isPtzOverlayManuallyVisible = false;
            }
        }

        function goToHomePosition() {
            if (!selectedCamera) return;
            const camPrefix = getCameraBasePrefix(selectedCamera);
            showToast('🏠 Mengarahkan kamera ke posisi tengah...', 'info');
            fetch(`/index.php/nvr/go_home_position?camera=${encodeURIComponent(camPrefix)}&t=${Date.now()}`)
                .then(r => r.json())
                .then(res => {
                    showToast(res.message || 'Kamera diarahkan ke tengah', 'success');
                })
                .catch(err => {
                    showToast('Gagal mengarahkan ke tengah: ' + err.message, 'error');
                });
        }

        function saveHomePosition() {
            if (!selectedCamera) return;
            const camPrefix = getCameraBasePrefix(selectedCamera);
            showConfirm("Jadikan sudut pandang kamera saat ini sebagai titik tengah default?", () => {
                fetch(`/index.php/nvr/save_home_position?camera=${encodeURIComponent(camPrefix)}&t=${Date.now()}`)
                    .then(r => r.json())
                    .then(res => {
                        showToast(res.message || 'Posisi tengah berhasil disimpan!', 'success');
                    })
                    .catch(err => {
                        showToast('Gagal menyimpan posisi: ' + err.message, 'error');
                    });
            });
        }

        function togglePtzOverlayManual() {
            isPtzOverlayManuallyVisible = !isPtzOverlayManuallyVisible;
            const ptzOverlay = document.getElementById('ptzControlOverlay');
            const btn = document.getElementById('headerPtzToggleBtn');
            
            if (isPtzOverlayManuallyVisible) {
                if (ptzOverlay) {
                    ptzOverlay.style.display = 'block';
                    requestAnimationFrame(() => {
                        ptzOverlay.style.opacity = '1';
                        ptzOverlay.style.transform = 'scale(1)';
                    });
                }
                if (btn) {
                    btn.style.background = 'rgba(212, 168, 67, 0.35)';
                    btn.style.borderColor = 'var(--accent-gold)';
                    btn.style.color = 'var(--accent-gold)';
                }
            } else {
                if (ptzOverlay) {
                    ptzOverlay.style.opacity = '0';
                    ptzOverlay.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        if (!isPtzOverlayManuallyVisible && ptzOverlay) {
                            ptzOverlay.style.display = 'none';
                        }
                    }, 250);
                }
                if (btn) {
                    btn.style.background = 'rgba(255, 255, 255, 0.12)';
                    btn.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                    btn.style.color = '#ffffff';
                }
            }
        }

        let isAiProcessing = false;
        function triggerAiAutoCenter() {
            if (!selectedCamera) {
                showToast('Pilih kamera CCTV terlebih dahulu', 'warning');
                return;
            }
            if (isAiProcessing) return;

            const camPrefix = getCameraBasePrefix(selectedCamera);
            const btn = document.getElementById('headerAiCenterBtn');
            const origText = btn ? btn.innerHTML : '';
            
            isAiProcessing = true;
            if (btn) {
                btn.innerHTML = '⏳ AI Scanning...';
                btn.style.opacity = '0.75';
            }
            showToast('🎯 AI sedang menganalisis posisi manusia...', 'info');

            fetch(`/index.php/nvr/ai_autocenter?camera=${encodeURIComponent(camPrefix)}&t=${Date.now()}`)
                .then(r => r.json())
                .then(res => {
                    isAiProcessing = false;
                    if (btn) {
                        btn.innerHTML = origText;
                        btn.style.opacity = '1';
                    }
                    if (res.status === 'success') {
                        showToast(res.message || '🎯 AI: Berhasil mengarahkan kamera ke kerumunan!', 'success');
                    } else if (res.status === 'ok') {
                        showToast(res.message || '🎯 AI: Tidak ada orang terdeteksi dalam frame.', 'info');
                    } else {
                        showToast(res.message || '🎯 AI: Gagal memproses gambar.', 'warning');
                    }
                })
                .catch(err => {
                    isAiProcessing = false;
                    if (btn) {
                        btn.innerHTML = origText;
                        btn.style.opacity = '1';
                    }
                    showToast('Gagal memproses AI Auto-Center: ' + err.message, 'error');
                });
        }

        // Render camera list from settings (Instant Synchronous)
        function renderCameraListFromSettings(settings) {
            if (!settings || !Array.isArray(settings) || settings.length === 0) return;
            databaseCameraSettings = settings;
            
            const filterCamera = document.getElementById('filterCamera');
            const currentFilterVal = filterCamera ? filterCamera.value : 'all';
            let filterHtml = '<option value="all">Semua Kamera</option>';
            
            // Group camera settings by base prefix to strictly eliminate duplicate items
            const cameraMap = new Map();
            settings.forEach(cam => {
                const prefix = getCameraBasePrefix(cam.id);
                if (!cameraMap.has(prefix)) {
                    cameraMap.set(prefix, []);
                }
                cameraMap.get(prefix).push(cam);
            });

            cameraMap.forEach((variants, prefix) => {
                const primaryCam = variants[0];
                if (parseInt(primaryCam.is_hidden) !== 1) {
                    let displayName = primaryCam.name || prefix;
                    displayName = displayName.replace(/\s*\(?(sub|main|h264|main_h264|sub_h264)\)?/gi, '').trim();
                    displayName = displayName.replace(/\s*\(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\)/gi, '').trim();
                    if (!displayName) displayName = primaryCam.name || prefix;
                    filterHtml += `<option value="${prefix}">${escapeHTML(displayName)}</option>`;
                }
            });

            if (filterCamera) {
                filterCamera.innerHTML = filterHtml;
                filterCamera.value = currentFilterVal || 'all';
            }
            
            const listContainer = document.getElementById('cameraListContainer');
            let html = '';
            let defaultOnlineCam = null;
            const savedCameraId = localStorage.getItem('nvr_selected_camera');

            cameraMap.forEach((variants, prefix) => {
                const chosenCam = variants.find(v => v.id.endsWith('_sub') || v.id.endsWith('_sub_h264'))
                    || variants.find(v => v.id.endsWith('_main_h264'))
                    || variants.find(v => v.id === prefix)
                    || variants[0];

                const isHidden = variants.some(v => parseInt(v.is_hidden) === 1);
                if (isHidden) return;

                const isOnline = variants.some(v => v.is_online !== undefined ? (parseInt(v.is_online) === 1) : true);
                const isRecording = variants.some(v => parseInt(v.is_recording) === 1);
                const isPublic = variants.some(v => parseInt(v.is_public) === 1);

                if (isOnline && !defaultOnlineCam) {
                    defaultOnlineCam = chosenCam;
                }

                const isSelected = selectedCamera && (
                    selectedCamera === chosenCam.id ||
                    selectedCamera === prefix ||
                    getCameraBasePrefix(selectedCamera) === prefix
                );
                const isActive = isSelected ? 'active' : '';

                let cleanName = chosenCam.name || prefix;
                cleanName = cleanName.replace(/\s*\(?(sub|main|h264|main_h264|sub_h264)\)?/gi, '').trim();
                cleanName = cleanName.replace(/\s*\(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\)/gi, '').trim();
                if (!cleanName) cleanName = chosenCam.name || prefix;

                html += `
                    <li class="cam-row ${isActive}" data-src="${chosenCam.id}" data-prefix="${prefix}" data-title="${escapeHTML(cleanName)}" onclick="handleListItemClick(this)">
                        <div class="cam-row-icon">
                            <svg id="cam-icon-${prefix}" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="${isOnline ? 'var(--accent-green)' : 'var(--text-muted)'}" stroke-width="2"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.9L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span class="rec-dot ${isRecording ? 'on' : 'off'}" title="${isRecording ? 'Perekaman Aktif' : 'Perekaman Nonaktif'}"></span>
                        </div>
                        <div class="cam-row-info">
                            <span class="cam-row-name" id="name-${prefix}">${escapeHTML(cleanName)}</span>
                            <div class="cam-row-sub">
                                <span id="badge-status-${prefix}" class="${isOnline ? 'badge-online' : 'badge-offline'}">${isOnline ? '● Online' : '○ Offline'}</span>
                                <span style="opacity:0.4;">·</span>
                                <span style="${isPublic ? 'color:var(--accent-blue);' : 'color:var(--text-muted);'}">${isPublic ? '🌐 Publik' : '🔒 Privat'}</span>
                                <span style="opacity:0.4;">·</span>
                                <span style="${isRecording ? 'color:var(--accent-red);font-weight:600;' : 'color:var(--text-muted);'}">${isRecording ? '⏺ REC' : '○ NO REC'}</span>
                            </div>
                        </div>
                        <div class="cam-row-actions">
                            <button class="cam-action-btn rec-btn ${isRecording ? 'is-active' : ''}" onclick="event.stopPropagation(); toggleRecording('${prefix}', this)" title="${isRecording ? 'Perekaman 24/7 Aktif (Klik untuk Matikan)' : 'Perekaman 24/7 Mati (Klik untuk Nyalakan)'}" style="color:${isRecording ? 'var(--accent-red)' : 'var(--text-muted)'};">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="${isRecording ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/></svg>
                            </button>
                            <button class="cam-action-btn share-btn" onclick="event.stopPropagation(); openShareModalForCamera('${prefix}', '${escapeHTML(cleanName)}')" title="Bagikan Link (Share)" style="color:var(--accent-gold);">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            </button>
                            <button class="cam-action-btn" onclick="event.stopPropagation(); startEditCameraName('${prefix}')" title="Ubah Nama">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
                            </button>
                            <button class="cam-action-btn" onclick="event.stopPropagation(); hideCamera('${prefix}')" title="Sembunyikan" style="color:var(--accent-red);">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </button>
                        </div>
                        <div class="cam-row-check">
                            <input type="checkbox" class="camera-select-checkbox" data-src="${chosenCam.id}" data-prefix="${prefix}" data-title="${escapeHTML(cleanName)}" onclick="event.stopPropagation(); updateGridFromCheckboxes();">
                        </div>
                    </li>
                `;
            });
            
            listContainer.innerHTML = html;
            
            // Select camera on initial load
            let targetCam = null;
            if (savedCameraId) {
                targetCam = settings.find(c => (c.id === savedCameraId || getCameraBasePrefix(c.id) === savedCameraId) && parseInt(c.is_hidden) !== 1);
            }
            if (!targetCam && defaultOnlineCam) {
                targetCam = defaultOnlineCam;
            }
            if (!targetCam && settings.length > 0) {
                targetCam = settings.find(c => parseInt(c.is_hidden) !== 1) || settings[0];
            }
            
            if (targetCam) {
                selectedCamera = targetCam.id;
                localStorage.setItem('nvr_selected_camera', selectedCamera);
                
                const targetItem = listContainer.querySelector(`[data-src="${selectedCamera}"]`);
                if (targetItem) {
                    targetItem.classList.add('active');
                    const cb = targetItem.querySelector('.camera-select-checkbox');
                    if (cb) cb.checked = true;
                }
                
                const prefix = getCameraBasePrefix(selectedCamera);
                if (filterCamera) filterCamera.value = prefix;
                
                if (!isGridMode && (!liveFrame.src || liveFrame.src === 'about:blank')) {
                    showLive(selectedCamera, targetCam.name);
                }
            }
            
            applyHiddenCameras();
            
            let filterDateVal = document.getElementById('filterDate').value;
            if (!filterDateVal) {
                const now = new Date();
                filterDateVal = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')}`;
                document.getElementById('filterDate').value = filterDateVal;
            }
            loadTimelineSegments(filterDateVal);
            updateTimelineDateLabel(filterDateVal);
        }

        // Load custom camera names with Instant 0ms cache + fast sync
        function loadCustomCameraNames() {
            // 1. Instant 0ms local storage render
            try {
                const cached = localStorage.getItem('nvr_cached_settings');
                if (cached) {
                    const parsed = JSON.parse(cached);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        renderCameraListFromSettings(parsed);
                    }
                }
            } catch(e) {}

            // 2. Fast network sync (<90ms)
            fetch('/index.php/nvr/get_settings')
                .then(r => r.json())
                .then(settings => {
                    if (Array.isArray(settings) && settings.length > 0) {
                        localStorage.setItem('nvr_cached_settings', JSON.stringify(settings));
                        renderCameraListFromSettings(settings);
                    }
                })
                .catch(err => {
                    console.error("Gagal memuat setting dari DB:", err);
                    const listContainer = document.getElementById('cameraListContainer');
                    if (!databaseCameraSettings || databaseCameraSettings.length === 0) {
                        listContainer.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--accent-red);">Gagal memuat: ${err.message}</div>`;
                    }
                });
        }

        function hideCamera(id) {
            showConfirm("Apakah Anda yakin ingin menyembunyikan kamera ini dari daftar?", () => {
                fetch('/index.php/nvr/save_setting', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, is_hidden: 1 })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        showToast('Kamera berhasil disembunyikan', 'info');
                        loadCustomCameraNames();
                    } else {
                        showToast('Gagal menyembunyikan kamera: ' + res.message, 'error');
                    }
                })
                .catch(err => showToast('Error DB: ' + err.message, 'error'));
            });
        }

        function applyHiddenCameras() {
            const items = document.querySelectorAll('#cameraListContainer .cam-row');
            items.forEach(item => {
                const id = item.dataset.src;
                const prefix = item.dataset.prefix || getCameraBasePrefix(id);
                const cam = databaseCameraSettings.find(c => c.id === id || c.id === prefix || getCameraBasePrefix(c.id) === prefix);
                const isHidden = cam && parseInt(cam.is_hidden) === 1;
                
                if (isHidden) {
                    item.style.display = 'none';
                    const checkbox = item.querySelector('.camera-select-checkbox');
                    if (checkbox) checkbox.checked = false;
                } else {
                    item.style.display = 'flex';
                }
            });
            if (isGridMode) {
                updateGridFromCheckboxes();
            }
        }

        function restoreHiddenCameras() {
            showConfirm("Apakah Anda ingin memulihkan semua kamera yang disembunyikan?", () => {
                fetch('/index.php/nvr/reset_settings', { method: 'POST' })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            showToast('Semua kamera berhasil dipulihkan', 'success');
                            loadCustomCameraNames();
                        } else {
                            showToast('Gagal mereset: ' + res.message, 'error');
                        }
                    })
                    .catch(err => showToast('Error DB: ' + err.message, 'error'));
            });
        }

        function startEditCameraName(id) {
            const nameSpan = document.getElementById(`name-${id}`);
            if (!nameSpan) return;
            
            let currentName = nameSpan.innerText.trim();
            currentName = currentName.replace(/\s*\(?(sub|main|h264|main_h264|sub_h264)\)?/gi, '').trim();
            currentName = currentName.replace(/\s*\(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\)/gi, '').trim();
            
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentName;
            input.style.width = '120px';
            input.style.padding = '2px 8px';
            input.style.backgroundColor = 'var(--input-bg)';
            input.style.color = 'var(--text-primary)';
            input.style.border = '1px solid var(--accent-gold)';
            input.style.borderRadius = '6px';
            input.style.fontSize = '12px';
            input.style.outline = 'none';
            input.style.boxShadow = '0 0 8px rgba(212, 168, 67, 0.3)';
            
            input.addEventListener('click', (e) => e.stopPropagation());
            
            function finishEdit() {
                const newName = input.value.trim() || currentName;
                nameSpan.innerText = newName;
                
                fetch('/index.php/nvr/save_setting', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, name: newName })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        loadCustomCameraNames();
                    } else {
                        showToast('Gagal menyimpan nama: ' + res.message, 'error');
                    }
                })
                .catch(err => showToast('Error DB: ' + err.message, 'error'));
                
                input.parentNode.replaceChild(nameSpan, input);
            }
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') finishEdit();
                if (e.key === 'Escape') {
                    input.parentNode.replaceChild(nameSpan, input);
                }
            });
            
            input.addEventListener('blur', finishEdit);
            nameSpan.parentNode.replaceChild(input, nameSpan);
            input.focus();
            input.select();
        }

        let currentGridLayoutPreset = 'auto';
        let gridClockInterval = null;

        function setGridLayoutPreset(preset) {
            currentGridLayoutPreset = preset;
            document.querySelectorAll('.grid-layout-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.layout === preset);
            });
            renderDynamicGridView();
        }

        function selectAllGridCameras(select) {
            const allCbs = Array.from(document.querySelectorAll('.camera-select-checkbox'));
            const selectedPrefixes = new Set();
            
            allCbs.forEach(cb => {
                if (!select) {
                    cb.checked = false;
                } else {
                    const src = cb.dataset.src;
                    const isOnline = isCameraOnline(src);
                    const prefix = getCameraBasePrefix(src);
                    
                    if (isOnline && !src.startsWith('cctv2') && !selectedPrefixes.has(prefix)) {
                        selectedPrefixes.add(prefix);
                        cb.checked = true;
                    }
                }
            });
            renderDynamicGridView();
        }

        function isGridFullscreen() {
            const gridEl = document.getElementById('gridPlayerView');
            return !!(document.fullscreenElement || document.webkitFullscreenElement || (gridEl && (gridEl.classList.contains('pseudo-fullscreen') || gridEl.classList.contains('is-fullscreen'))));
        }

        function toggleGridFullscreen() {
            const gridEl = document.getElementById('gridPlayerView');
            if (!gridEl) return;
            
            if (!isGridFullscreen()) {
                const reqFs = gridEl.requestFullscreen || gridEl.webkitRequestFullscreen || gridEl.mozRequestFullScreen || gridEl.msRequestFullscreen;
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                
                if (reqFs && !isIOS) {
                    reqFs.call(gridEl).then(() => {
                        gridEl.classList.add('is-fullscreen');
                        tryLockLandscape();
                        handleGridFullscreenStateChange();
                        showFloatingGridHeader();
                    }).catch(e => {
                        console.warn("Native grid fullscreen blocked, fallback to pseudo-fullscreen:", e);
                        enterPseudoGridFullscreen(gridEl);
                    });
                } else {
                    enterPseudoGridFullscreen(gridEl);
                }
            } else {
                exitGridFullscreen(gridEl);
            }
        }

        function enterPseudoGridFullscreen(gridEl) {
            gridEl.classList.add('pseudo-fullscreen', 'is-fullscreen');
            document.body.style.overflow = 'hidden';
            tryLockLandscape();
            handleGridFullscreenStateChange();
            showFloatingGridHeader();
        }

        function exitGridFullscreen(gridEl) {
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                if (document.exitFullscreen) document.exitFullscreen().catch(() => {});
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
            }
            if (gridEl) {
                gridEl.classList.remove('pseudo-fullscreen', 'is-fullscreen');
            }
            document.body.style.overflow = '';
            tryUnlockOrientation();
            handleGridFullscreenStateChange();
        }

        let gridHeaderTimeout = null;
        function showFloatingGridHeader() {
            const gridEl = document.getElementById('gridPlayerView');
            if (!gridEl) return;
            gridEl.classList.add('show-header');
            clearTimeout(gridHeaderTimeout);
            gridHeaderTimeout = setTimeout(() => {
                gridEl.classList.remove('show-header');
            }, 3500);
        }

        function handleGridFullscreenStateChange() {
            const isFs = isGridFullscreen();
            const exitBtn = document.getElementById('floatingGridExitBtn');
            if (exitBtn) {
                exitBtn.style.display = isFs ? 'inline-flex' : 'none';
            }
        }

        function toggleTileFullscreen(btn) {
            const card = btn.closest('.grid-cam-card');
            if (!card) return;
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !card.classList.contains('pseudo-fullscreen')) {
                if (card.requestFullscreen) {
                    card.requestFullscreen().catch(() => {
                        card.classList.add('pseudo-fullscreen');
                        document.body.style.overflow = 'hidden';
                    });
                } else if (card.webkitRequestFullscreen) {
                    card.webkitRequestFullscreen();
                } else {
                    card.classList.add('pseudo-fullscreen');
                    document.body.style.overflow = 'hidden';
                }
            } else {
                if (document.exitFullscreen) document.exitFullscreen().catch(() => {});
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                card.classList.remove('pseudo-fullscreen');
                document.body.style.overflow = '';
            }
        }

        function captureGridSnapshot(src, title) {
            const cleanTitle = (title || src).replace(/[^a-zA-Z0-9_-]/g, '_');
            const url = `/api/frame.jpeg?src=${encodeURIComponent(src)}&ts=${Date.now()}`;
            const link = document.createElement('a');
            link.href = url;
            link.download = `snapshot_${cleanTitle}_${Date.now()}.jpeg`;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast(`Snapshot diambil: ${title || src}`, 'info');
        }

        function updateGridClock() {
            const clockEl = document.getElementById('gridWallClock');
            if (!clockEl) return;
            const now = new Date();
            const timeStr = `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}:${now.getSeconds().toString().padStart(2,'0')} WIB`;
            clockEl.innerText = timeStr;
            
            document.querySelectorAll('.grid-live-time').forEach(el => {
                el.innerText = timeStr;
            });
        }

        function toggleLayoutMode() {
            setGridModeActive(!isGridMode);
        }

        function setGridModeActive(active) {
            isGridMode = active;
            const singleView = document.getElementById('singlePlayerView');
            const gridView = document.getElementById('gridPlayerView');
            const rightSidebar = document.getElementById('recordingsPanel');
            const btn = document.getElementById('btnToggleView');

            if (isGridMode) {
                playbackVideo.pause();
                playbackVideo.src = '';
                customPlayerWrapper.style.display = 'none';
                liveFrame.src = 'about:blank';
                playbackStatusOverlay.style.display = 'none';

                // Deduplicate: auto-select exactly 1 optimal stream per physical CCTV camera!
                const checkedBoxes = Array.from(document.querySelectorAll('.camera-select-checkbox:checked'));
                if (checkedBoxes.length < 2) {
                    const allCbs = Array.from(document.querySelectorAll('.camera-select-checkbox'));
                    const selectedPrefixes = new Set();
                    
                    allCbs.forEach(cb => cb.checked = false);
                    
                    allCbs.forEach(cb => {
                        const src = cb.dataset.src;
                        const prefix = getCameraBasePrefix(src);
                        if (!src.startsWith('cctv2') && !selectedPrefixes.has(prefix)) {
                            selectedPrefixes.add(prefix);
                            cb.checked = true;
                        }
                    });
                }

                if (rightSidebar) rightSidebar.style.display = 'none';
                singleView.style.display = 'none';
                gridView.style.display = 'flex';
                
                if (btn) {
                    btn.innerText = "🔲 Single";
                    btn.style.color = "#fbbf24";
                    btn.style.borderColor = "rgba(245, 158, 11, 0.4)";
                    btn.style.background = "rgba(245, 158, 11, 0.15)";
                }

                // Show checkboxes
                document.querySelectorAll('.cam-row-check').forEach(el => el.classList.add('show'));
                renderDynamicGridView();
            } else {
                if (gridClockInterval) {
                    clearInterval(gridClockInterval);
                    gridClockInterval = null;
                }

                // Hide checkboxes
                document.querySelectorAll('.cam-row-check').forEach(el => el.classList.remove('show'));
                const gridTiles = document.getElementById('gridPlayerTiles');
                if (gridTiles) gridTiles.innerHTML = '';

                if (rightSidebar) rightSidebar.style.display = 'flex';
                singleView.style.display = 'flex';
                gridView.style.display = 'none';
                
                if (btn) {
                    btn.innerText = "📺 Grid";
                    btn.style.color = "#60a5fa";
                    btn.style.borderColor = "rgba(59, 130, 246, 0.35)";
                    btn.style.background = "rgba(59, 130, 246, 0.12)";
                }
                
                // Select active camera checkbox
                document.querySelectorAll('.camera-select-checkbox').forEach(cb => {
                    cb.checked = (cb.dataset.src === selectedCamera);
                });
                
                const activeItem = document.querySelector('#cameraListContainer .cam-row.active');
                const camName = activeItem ? activeItem.dataset.title : "CCTV Stream";
                showLive(selectedCamera, camName);
            }
        }

        function updateGridFromCheckboxes() {
            if (isGridMode) {
                document.querySelectorAll('.cam-row-check').forEach(el => el.classList.add('show'));
                renderDynamicGridView();
            } else {
                document.querySelectorAll('.cam-row-check').forEach(el => el.classList.remove('show'));
                const checkedBoxes = Array.from(document.querySelectorAll('.camera-select-checkbox:checked'));
                if (checkedBoxes.length > 1) {
                    setGridModeActive(true);
                } else if (checkedBoxes.length === 1) {
                    const src = checkedBoxes[0].dataset.src;
                    const title = checkedBoxes[0].dataset.title;
                    handleCameraSelect(src, title);
                }
            }
        }

        function renderDynamicGridView() {
            const gridTiles = document.getElementById('gridPlayerTiles');
            const countText = document.getElementById('gridActiveCountText');
            if (!gridTiles) return;
            
            gridTiles.innerHTML = '';
            
            if (!gridClockInterval) {
                updateGridClock();
                gridClockInterval = setInterval(updateGridClock, 1000);
            }
            
            const rawCheckedBoxes = Array.from(document.querySelectorAll('.camera-select-checkbox:checked'));
            
            const distinctCameras = [];
            const seenPrefixes = new Set();
            rawCheckedBoxes.forEach(box => {
                const src = box.dataset.src;
                const prefix = getCameraBasePrefix(src);
                if (!seenPrefixes.has(prefix)) {
                    seenPrefixes.add(prefix);
                    distinctCameras.push(box);
                }
            });

            const count = distinctCameras.length;
            if (countText) countText.innerText = `${count} Kamera`;
            
            if (count === 0) {
                gridTiles.style.gridTemplateColumns = '1fr';
                gridTiles.style.gridTemplateRows = '1fr';
                gridTiles.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 420px; background: rgba(13, 17, 23, 0.6); border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; padding: 40px; text-align: center; gap: 16px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); display: flex; align-items: center; justify-content: center; font-size: 26px;">📹</div>
                        <div>
                            <h3 style="color: var(--text-primary); font-size: 16px; margin: 0 0 6px 0; font-weight: 700;">Belum Ada Kamera yang Dipilih untuk Matrix Wall</h3>
                            <p style="color: var(--text-secondary); font-size: 13px; margin: 0; max-width: 460px;">Centang kamera pada daftar di sidebar kiri atau klik tombol <b>"Semua"</b> di toolbar atas untuk memuat tayangan serentak.</p>
                        </div>
                        <button class="grid-tool-btn btn-highlight" onclick="selectAllGridCameras(true)" style="padding: 8px 18px; font-size: 12px; border-radius: 8px;">
                            ✓ Tampilkan Semua Kamera Online
                        </button>
                    </div>
                `;
                return;
            }
            
            // Layout calculations
            if (currentGridLayoutPreset === '1') {
                gridTiles.style.gridTemplateColumns = '1fr';
            } else if (currentGridLayoutPreset === '4') {
                gridTiles.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else if (currentGridLayoutPreset === '9') {
                gridTiles.style.gridTemplateColumns = 'repeat(3, 1fr)';
            } else if (currentGridLayoutPreset === '16') {
                gridTiles.style.gridTemplateColumns = 'repeat(4, 1fr)';
            } else {
                if (count === 1) {
                    gridTiles.style.gridTemplateColumns = '1fr';
                } else if (count === 2) {
                    gridTiles.style.gridTemplateColumns = 'repeat(2, 1fr)';
                } else if (count <= 4) {
                    gridTiles.style.gridTemplateColumns = 'repeat(2, 1fr)';
                } else if (count <= 9) {
                    gridTiles.style.gridTemplateColumns = 'repeat(3, 1fr)';
                } else if (count <= 16) {
                    gridTiles.style.gridTemplateColumns = 'repeat(4, 1fr)';
                } else {
                    gridTiles.style.gridTemplateColumns = 'repeat(5, 1fr)';
                }
            }
            gridTiles.style.gridAutoRows = 'min-content';
            
            const nowTime = new Date();
            const timeFormatted = `${nowTime.getHours().toString().padStart(2,'0')}:${nowTime.getMinutes().toString().padStart(2,'0')}:${nowTime.getSeconds().toString().padStart(2,'0')} WIB`;

            distinctCameras.forEach(box => {
                const src = box.dataset.src;
                const title = box.dataset.title;
                const prefix = getCameraBasePrefix(src);
                const isOnline = isCameraOnline(src);
                
                let cleanTitle = title || "CCTV Stream";
                cleanTitle = cleanTitle.replace(/\s*\(Tanpa Transcoding[^\)]*\)/gi, '');
                cleanTitle = cleanTitle.replace(/\s*\(Sub H264\)/gi, '');
                cleanTitle = cleanTitle.replace(/\s*\(Main H264\)/gi, '');
                cleanTitle = cleanTitle.replace(/\s*\(Sub\)/gi, '');
                cleanTitle = cleanTitle.replace(/\s*\(Main\)/gi, '');
                cleanTitle = cleanTitle.replace(/\s*\(?(sub|main|h264)\)?/gi, '').trim();
                if (!cleanTitle) cleanTitle = title;
                
                const cell = document.createElement('div');
                cell.className = 'grid-cam-card';
                cell.title = "Klik ganda untuk memperbesar kamera ini";
                
                cell.ondblclick = () => {
                    switchToSingleCameraView(src, title);
                };
                
                const header = document.createElement('div');
                header.className = 'grid-cam-header';
                
                const statusBadgeHtml = isOnline 
                    ? `<span class="grid-cam-live-badge"><span class="grid-status-dot"></span> LIVE</span>` 
                    : `<span class="grid-cam-offline-badge">OFFLINE</span>`;
                
                header.innerHTML = `
                    <div class="grid-cam-title-wrap">
                        <span class="grid-cam-title">${escapeHTML(cleanTitle)}</span>
                        ${statusBadgeHtml}
                    </div>
                    <div class="grid-cam-header-actions">
                        <button class="grid-action-chip" onclick="event.stopPropagation(); switchToSingleCameraView('${src}', '${escapeHTML(title)}')" title="Fokus Kamera Tunggal">
                            🔍 Fokus
                        </button>
                        <button class="grid-action-chip" onclick="event.stopPropagation(); captureGridSnapshot('${src}', '${escapeHTML(cleanTitle)}')" title="Ambil Foto Snapshot">
                            📷
                        </button>
                        <button class="grid-action-chip" onclick="event.stopPropagation(); openShareModalForCamera('${src}', '${escapeHTML(cleanTitle)}')" title="Bagikan Link (Share)">
                            🔗
                        </button>
                        <button class="grid-action-chip" onclick="event.stopPropagation(); toggleTileFullscreen(this)" title="Layar Penuh Kamera Ini">
                            ⛶
                        </button>
                    </div>
                `;
                
                const body = document.createElement('div');
                body.className = 'grid-cam-body';
                
                if (isOnline) {
                    let mode = "webrtc,mse,hls";
                    body.innerHTML = `<iframe src="/go2rtc/stream.html?src=${encodeURIComponent(src)}&mode=${mode}" style="width:100%; height:100%; border:none; display:block; background-color:#000; position:absolute; top:0; left:0;"></iframe>`;
                } else {
                    body.innerHTML = `
                        <div class="grid-cam-offline-wrap">
                            <div class="grid-radar-circle">📡</div>
                            <div style="font-size: 11.5px; font-weight: 600; color: #f87171;">Kamera Offline</div>
                            <div style="font-size: 10px; color: var(--text-muted); font-family: monospace;">ID: ${prefix}</div>
                            <button class="grid-action-chip" onclick="event.stopPropagation(); renderDynamicGridView()" style="margin-top: 4px;">
                                🔄 Coba Hubungkan
                            </button>
                        </div>
                    `;
                }
                
                const footer = document.createElement('div');
                footer.className = 'grid-cam-footer';
                footer.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <span class="grid-cam-tag">CAM: ${prefix}</span>
                        <span class="grid-cam-tag">H.264 WebRTC</span>
                    </div>
                    <span class="grid-live-time" style="font-family:'JetBrains Mono',monospace; font-size: 9.5px; color: rgba(255,255,255,0.7);">${timeFormatted}</span>
                `;
                
                cell.appendChild(header);
                cell.appendChild(body);
                cell.appendChild(footer);
                gridTiles.appendChild(cell);
            });
        }

        function switchToSingleCameraView(src, title) {
            selectedCamera = src;
            
            document.querySelectorAll('#cameraListContainer .cam-row').forEach(item => {
                if (item.dataset.src === src) {
                    item.classList.add('active');
                    const cb = item.querySelector('.camera-select-checkbox');
                    if (cb) cb.checked = true;
                } else {
                    item.classList.remove('active');
                    const cb = item.querySelector('.camera-select-checkbox');
                    if (cb) cb.checked = false;
                }
            });
            
            setGridModeActive(false);
            showLive(src, title);
        }

        function handleCameraSelect(src, title) {
            selectedCamera = src;
            document.querySelectorAll('#cameraListContainer .cam-row').forEach(item => {
                if (item.dataset.src === src) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
            showLive(selectedCamera, title);
            
            const filterDateVal = document.getElementById('filterDate').value;
            if (filterDateVal) {
                loadTimelineSegments(filterDateVal);
            }
            fetchRecordings();
        }

        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) 
                || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        }

        function tryLockLandscape() {
            if (screen.orientation && screen.orientation.lock && isMobileDevice()) {
                screen.orientation.lock('landscape').catch(() => {});
            }
        }

        function tryUnlockOrientation() {
            if (screen.orientation && screen.orientation.unlock && isMobileDevice()) {
                try { screen.orientation.unlock(); } catch (e) {}
            }
        }

        function isPlayerFullscreen() {
            const card = document.querySelector('.player-card');
            return !!(document.fullscreenElement || document.webkitFullscreenElement || (card && (card.classList.contains('pseudo-fullscreen') || card.classList.contains('is-fullscreen'))));
        }

        function togglePlayerFullscreen() {
            const card = document.querySelector('.player-card');
            if (!card) return;

            if (!isPlayerFullscreen()) {
                const reqFs = card.requestFullscreen || card.webkitRequestFullscreen || card.mozRequestFullScreen || card.msRequestFullscreen;
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                
                if (reqFs && !isIOS) {
                    reqFs.call(card).then(() => {
                        card.classList.add('is-fullscreen');
                        tryLockLandscape();
                        handleFullscreenStateChange();
                        showFloatingPlayerHeader();
                    }).catch(err => {
                        console.warn("Native fullscreen rejected, using pseudo-fullscreen fallback:", err);
                        enterPseudoFullscreen(card);
                    });
                } else {
                    enterPseudoFullscreen(card);
                }
            } else {
                exitPlayerFullscreen(card);
            }
        }

        function enterPseudoFullscreen(card) {
            card.classList.add('pseudo-fullscreen', 'is-fullscreen');
            document.body.style.overflow = 'hidden';
            tryLockLandscape();
            handleFullscreenStateChange();
            showFloatingPlayerHeader();
        }

        function exitPlayerFullscreen(card) {
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                if (document.exitFullscreen) document.exitFullscreen().catch(() => {});
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
            }
            if (card) {
                card.classList.remove('pseudo-fullscreen', 'is-fullscreen');
            }
            document.body.style.overflow = '';
            tryUnlockOrientation();
            handleFullscreenStateChange();
        }

        let lastOverlayTapTime = 0;
        function handlePlayerOverlayTap(e) {
            const now = Date.now();
            if (now - lastOverlayTapTime < 320) {
                togglePlayerFullscreen();
                lastOverlayTapTime = 0;
            } else {
                lastOverlayTapTime = now;
                showFloatingPlayerHeader();
            }
        }

        // Sync Fullscreen Button text & floating exit button on state changes
        document.addEventListener('fullscreenchange', handleFullscreenStateChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenStateChange);

        function handleFullscreenStateChange() {
            const isFs = isPlayerFullscreen();
            const fsBtn = document.getElementById('playerFullscreenBtn');
            const floatingExitBtn = document.getElementById('floatingExitFullscreenBtn');
            
            if (fsBtn) {
                if (isFs) {
                    fsBtn.innerHTML = '&#128471; Perkecil (Esc)';
                    fsBtn.style.color = '#f87171';
                    fsBtn.style.borderColor = 'rgba(239,68,68,0.4)';
                    fsBtn.style.background = 'rgba(239,68,68,0.15)';
                } else {
                    fsBtn.innerHTML = '&#x26F6; Fullscreen';
                    fsBtn.style.color = 'var(--accent-blue)';
                    fsBtn.style.borderColor = 'rgba(59,130,246,0.35)';
                    fsBtn.style.background = 'rgba(59,130,246,0.1)';
                }
            }

            if (floatingExitBtn) {
                floatingExitBtn.style.display = isFs ? 'inline-flex' : 'none';
            }
        }

        // Keyboard Shortcut: 'F' key toggles fullscreen for single player / matrix wall
        document.addEventListener('keydown', (e) => {
            // Ignore when typing inside input or textarea
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
            
            if (e.key === 'f' || e.key === 'F') {
                e.preventDefault();
                if (isGridMode) {
                    toggleGridFullscreen();
                } else {
                    togglePlayerFullscreen();
                }
            }
        });

        // Camera sidebar item click with instant recording sync
        
        // ==========================================
        // 🔍 CAMERA SEARCH FILTER
        // ==========================================
        function handleCameraSearch(query) {
            const clearBtn = document.getElementById('cameraSearchClearBtn');
            const q = (query || '').toLowerCase().trim();
            if (clearBtn) {
                clearBtn.style.display = q.length > 0 ? 'block' : 'none';
            }

            const items = document.querySelectorAll('#cameraListContainer .cam-row');
            let visibleCount = 0;

            items.forEach(item => {
                const id = (item.dataset.src || '').toLowerCase();
                const title = (item.dataset.title || '').toLowerCase();
                const cam = databaseCameraSettings.find(c => c.id === item.dataset.src);
                const isHidden = cam && parseInt(cam.is_hidden) === 1;

                if (isHidden) {
                    item.style.display = 'none';
                    return;
                }

                if (!q || id.includes(q) || title.includes(q)) {
                    item.classList.remove('search-hidden');
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.classList.add('search-hidden');
                    item.style.display = 'none';
                }
            });

            let noResEl = document.getElementById('cameraSearchNoResults');
            if (visibleCount === 0 && q.length > 0) {
                if (!noResEl) {
                    noResEl = document.createElement('li');
                    noResEl.id = 'cameraSearchNoResults';
                    noResEl.className = 'search-no-results';
                    const container = document.getElementById('cameraListContainer');
                    if (container) container.appendChild(noResEl);
                }
                if (noResEl) {
                    noResEl.innerText = `Kamera "${query}" tidak ditemukan.`;
                    noResEl.style.display = 'block';
                }
            } else if (noResEl) {
                noResEl.style.display = 'none';
            }
        }

        function clearCameraSearch() {
            const input = document.getElementById('cameraSearchInput');
            if (input) {
                input.value = '';
                input.focus();
                handleCameraSearch('');
            }
        }

        function handleListItemClick(item) {
            document.querySelectorAll('#cameraListContainer .cam-row').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            
            selectedCamera = item.dataset.src;
            
            const prefix = getCameraBasePrefix(selectedCamera);
            const filterCamera = document.getElementById('filterCamera');
            if (filterCamera) filterCamera.value = prefix;
            
            document.querySelectorAll('.camera-select-checkbox').forEach(cb => {
                cb.checked = (cb.dataset.src === selectedCamera);
            });
            
            if (isGridMode) {
                setGridModeActive(false);
            } else {
                const nameVal = item.dataset.title;
                showLive(selectedCamera, nameVal);
            }

            const filterDateVal = document.getElementById('filterDate').value;
            if (filterDateVal) {
                loadTimelineSegments(filterDateVal);
            }
            
            filterAndRenderRecordings();
        }

        // Show feedback overlay
        function showFeedbackOverlay(text) {
            let overlay = document.getElementById('video-feedback-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'video-feedback-overlay';
                overlay.style.position = 'absolute';
                overlay.style.top = '50%';
                overlay.style.left = '50%';
                overlay.style.transform = 'translate(-50%, -50%)';
                overlay.style.backgroundColor = 'rgba(18, 22, 30, 0.9)';
                overlay.style.color = '#e8ecf1';
                overlay.style.padding = '10px 20px';
                overlay.style.borderRadius = '30px';
                overlay.style.fontSize = '13px';
                overlay.style.fontWeight = '500';
                overlay.style.pointerEvents = 'none';
                overlay.style.zIndex = '100';
                overlay.style.opacity = '0';
                overlay.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
                playbackVideo.parentNode.appendChild(overlay);
            }
            
            overlay.innerText = text;
            overlay.style.opacity = '1';
            overlay.style.transform = 'translate(-50%, -50%) scale(1.05)';
            
            setTimeout(() => {
                overlay.style.opacity = '0';
                overlay.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 600);
        }

        // Double Click screen seek
        playbackVideo.addEventListener('dblclick', function(e) {
            const rect = playbackVideo.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            
            if (x < width / 2) {
                playbackVideo.currentTime = Math.max(0, playbackVideo.currentTime - 10);
                showFeedbackOverlay('⏪ Mundur 10s');
            } else {
                playbackVideo.currentTime = Math.min(playbackVideo.duration || 0, playbackVideo.currentTime + 10);
                showFeedbackOverlay('⏩ Maju 10s');
            }
        });

        // Keyboard hotkeys
        document.addEventListener('keydown', function(e) {
            if (customPlayerWrapper.style.display === 'flex' && document.activeElement.tagName !== 'INPUT') {
                if (e.code === 'ArrowLeft') {
                    e.preventDefault();
                    playbackVideo.currentTime = Math.max(0, playbackVideo.currentTime - 10);
                    showFeedbackOverlay('⏪ Mundur 10s');
                } else if (e.code === 'ArrowRight') {
                    e.preventDefault();
                    playbackVideo.currentTime = Math.min(playbackVideo.duration || 0, playbackVideo.currentTime + 10);
                    showFeedbackOverlay('⏩ Maju 10s');
                } else if (e.code === 'Space') {
                    e.preventDefault();
                    togglePlayPause();
                }
            }
        });

        function showLive(src, title) {
            if (!src) return;
            // Auto-resolve any raw H.265 stream (main or sub) to H.264 counterpart for 100% playable video
            if (databaseCameraSettings) {
                if ((src.endsWith('_main') || src.endsWith('_sub')) && databaseCameraSettings.some(c => c.id === src + '_h264')) {
                    src = src + '_h264';
                }
            }
            selectedCamera = src;
            localStorage.setItem('nvr_selected_camera', src);
            
            playbackVideo.pause();
            playbackVideo.src = '';
            customPlayerWrapper.style.display = 'none';
            playbackStatusOverlay.style.display = 'none';
            
            if (!isCameraOnline(src)) {
                liveFrame.src = 'about:blank';
                playerTitle.innerText = `Offline: ${title}`;
                playerMode.innerText = "OFFLINE";
                playerMode.style.backgroundColor = "rgba(196, 92, 92, 0.12)";
                playerMode.style.color = "var(--accent-red)";
                playerMode.style.borderColor = "rgba(196, 92, 92, 0.25)";
            } else {
                // Highly resilient streaming mode: webrtc first, MSE/HLS fallback on network delay/jitter
                let mode = "webrtc,mse,hls";
                liveFrame.src = `/go2rtc/stream.html?src=${encodeURIComponent(src)}&mode=${mode}&v=${Date.now()}`;
                let cleanTitle = title || "CCTV Stream";
                cleanTitle = cleanTitle.replace(/\s*\(Tanpa Transcoding[^\)]*\)/gi, '');
                cleanTitle = cleanTitle.replace(/\s*\(Sub H264\)/gi, ' (Sub)');
                cleanTitle = cleanTitle.replace(/\s*\(Main H264\)/gi, ' (Main)');
                cleanTitle = cleanTitle.replace(/\s*\(?(sub|main|h264)\)?/gi, '').trim();
                if (!cleanTitle) cleanTitle = title;

                playerTitle.innerText = `Live: ${cleanTitle}`;
                playerTitle.title = title;
                playerMode.innerText = "LIVE";
                playerMode.style.backgroundColor = "rgba(61, 112, 178, 0.12)";
                playerMode.style.color = "var(--accent-blue)";
                playerMode.style.borderColor = "rgba(61, 112, 178, 0.25)";
                
                loadPtzSupport();
                updateHeaderRecBtnState(src);
                startLiveTimelineUpdates();
            }
        }

        function playRecording(filename, displayTime) {
            liveFrame.src = 'about:blank';

            const ptzOverlay = document.getElementById('ptzControlOverlay');
            if (ptzOverlay) ptzOverlay.style.display = 'none';

            // Sync camera and date when playing a recording
            const recPrefix = getPrefixFromFilename(filename);
            let matchingCam = databaseCameraSettings.find(c => getCameraBasePrefix(c.id) === recPrefix && (c.id.endsWith('_sub') || c.id.endsWith('_main_h264')));
            if (!matchingCam) {
                matchingCam = databaseCameraSettings.find(c => getCameraBasePrefix(c.id) === recPrefix);
            }
            
            if (matchingCam) {
                selectedCamera = matchingCam.id;
                
                // Highlight the selected camera in the left sidebar
                document.querySelectorAll('#cameraListContainer .cam-row').forEach(i => {
                    if (i.dataset.src === selectedCamera) {
                        i.classList.add('active');
                    } else {
                        i.classList.remove('active');
                    }
                });
                
                // Sync the filter dropdown selector
                const filterCamera = document.getElementById('filterCamera');
                if (filterCamera) {
                    filterCamera.value = recPrefix;
                }
            }

            // Extract date from displayTime (format: "DD/MM/YYYY HH:MM:SS") or filename
            let dateYMD = null;
            if (displayTime && displayTime.includes(' ')) {
                const datePart = displayTime.split(' ')[0]; // "DD/MM/YYYY"
                const parts = datePart.split('/');
                if (parts.length === 3) {
                    dateYMD = `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
            }
            
            if (!dateYMD) {
                const matches = filename.match(/_rec_(\d{4})(\d{2})(\d{2})_/);
                if (matches) {
                    dateYMD = `${matches[1]}-${matches[2]}-${matches[3]}`;
                }
            }

            if (dateYMD) {
                // Update the date picker value
                const filterDateEl = document.getElementById('filterDate');
                if (filterDateEl) {
                    filterDateEl.value = dateYMD;
                }
                
                // Load timeline segments for this camera and date
                loadTimelineSegments(dateYMD);
            }

            // Show connecting status
            playbackStatusText.innerText = "Menghubungkan ke berkas rekaman...";
            playbackStatusOverlay.style.display = 'flex';

            playbackVideo.src = `${fileServerUrl}${filename}`;
            customPlayerWrapper.style.display = 'flex';
            playbackVideo.load();
            
            // Set playback slider to the start time of this recording segment
            const matchesTime = filename.match(/_rec_\d{8}_(\d{2})(\d{2})(\d{2})\.mp4/);
            if (matchesTime) {
                const startSecs = parseInt(matchesTime[1]) * 3600 + parseInt(matchesTime[2]) * 60 + parseInt(matchesTime[3]);
                unifiedTimelineSlider.value = startSecs;
            }

            playbackVideo.play().catch(err => console.log("Playback error:", err));
            
            customSpeedSelect.value = "1";
            playbackVideo.playbackRate = 1.0;
            
            isLiveMode = false;
            btnGoLive.style.display = 'inline-block';
            timelineStatusDot.style.backgroundColor = 'var(--accent-green)';
            
            // Format time header with date
            let displayTimeWithDate = displayTime;
            if (dateYMD && !displayTime.includes('/')) {
                const parts = dateYMD.split('-');
                displayTimeWithDate = `${parts[2]}/${parts[1]}/${parts[0]} ${displayTime}`;
            }
            
            timelineStatusText.innerText = `Mode: PLAYBACK (${displayTimeWithDate})`;
            playerTitle.innerText = `Playback: ${displayTimeWithDate}`;
            playerMode.innerText = "PLAYBACK";
            playerMode.style.backgroundColor = "rgba(74, 158, 107, 0.12)";
            playerMode.style.color = "var(--accent-green)";
            playerMode.style.borderColor = "rgba(74, 158, 107, 0.25)";
        }

        // Fetch recordings list
        function loadRecordings() {
            fetch('/index.php/nvr/get_recordings')
                .then(r => r.json())
                .then(data => {
                    allRecordings = data;
                    indexRecordings();
                    filterAndRenderRecordings();
                })
                .catch(err => {
                    recordingsContainer.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--accent-red);">Gagal memuat arsip: ${err.message}</div>`;
                });
        }

        function fetchRecordings() {
            loadRecordings();
        }

        function filterAndRenderRecordings() {
            const filterDateVal = document.getElementById('filterDate').value;
            const filterHourVal = document.getElementById('filterHour').value;
            const filterCameraVal = document.getElementById('filterCamera').value;
            
            let targetDate = "";
            if (filterDateVal) {
                const parts = filterDateVal.split('-');
                targetDate = `${parts[2]}/${parts[1]}/${parts[0]}`; // Convert to DD/MM/YYYY
            }
            
            let filtered = allRecordings;
            if (filterCameraVal !== 'all') {
                filtered = filtered.filter(rec => getPrefixFromFilename(rec.filename) === filterCameraVal);
            }
            
            if (targetDate) {
                filtered = filtered.filter(rec => rec.date === targetDate);
            }
            
            if (filterHourVal !== 'all') {
                filtered = filtered.filter(rec => {
                    const hour = parseInt(rec.time.split(':')[0]);
                    if (filterHourVal === 'morning') return (hour >= 6 && hour < 12);
                    if (filterHourVal === 'afternoon') return (hour >= 12 && hour < 18);
                    if (filterHourVal === 'night') return (hour >= 18 || hour < 6);
                    return true;
                });
            }
            
            if (filtered.length === 0) {
                recordingsContainer.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 12px;">Tidak ada rekaman yang cocok.</div>';
                return;
            }

            // Group by date
            const groups = {};
            filtered.forEach(rec => {
                if (!groups[rec.date]) {
                    groups[rec.date] = [];
                }
                groups[rec.date].push(rec);
            });

            let html = '';
            for (const date in groups) {
                html += `<div class="recording-date-group">📅 Tanggal ${date}</div>`;
                groups[date].forEach(rec => {
                    const recPrefix = getPrefixFromFilename(rec.filename);
                    const camSetting = databaseCameraSettings.find(c => getCameraBasePrefix(c.id) === recPrefix && (c.id.endsWith('_sub') || c.id.endsWith('_main_h264')))
                                    || databaseCameraSettings.find(c => getCameraBasePrefix(c.id) === recPrefix);
                    let cleanCamName = camSetting ? camSetting.name : recPrefix;
                    cleanCamName = cleanCamName.replace(/\s*\(?(sub|main|h264)\)?/gi, '').trim();
                    
                    html += `
                        <div class="recording-item" onclick="playRecording('${rec.filename}', '${rec.displayTime}')">
                            <div class="rec-info">
                                <span class="rec-time">📹 ${rec.time.substring(0, 5)} &mdash; ${escapeHTML(cleanCamName)}</span>
                            </div>
                            <div class="rec-actions">
                                <a href="${fileServerUrl}${rec.filename}" download class="btn-download" onclick="event.stopPropagation();" title="Download rekaman">📥</a>
                            </div>
                        </div>
                    `;
                });
            }
            recordingsContainer.innerHTML = html;
        }

        // Initialize today's date in filter input
        const now = new Date();
        const todayStr = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')}`;
        document.getElementById('filterDate').value = todayStr;

        function updateHeaderRecBtnState(src) {
            const recBtn = document.getElementById('headerRecBtn');
            const recText = document.getElementById('headerRecText');
            if (!recBtn || !recText) return;
            const cam = databaseCameraSettings ? databaseCameraSettings.find(c => c.id === src) : null;
            const isRec = cam ? (parseInt(cam.is_recording) === 1) : true;
            if (isRec) {
                recBtn.style.color = 'var(--accent-red)';
                recBtn.style.borderColor = 'rgba(239, 68, 68, 0.4)';
                recBtn.style.background = 'rgba(239, 68, 68, 0.12)';
                recBtn.title = 'Perekaman 24/7 Aktif (Klik untuk Matikan)';
                recText.innerText = 'REC ON';
            } else {
                recBtn.style.color = 'var(--text-muted)';
                recBtn.style.borderColor = 'var(--border-muted)';
                recBtn.style.background = 'rgba(255, 255, 255, 0.04)';
                recBtn.title = 'Perekaman 24/7 Nonaktif (Klik untuk Aktifkan)';
                recText.innerText = 'REC OFF';
            }
        }

        function toggleRecordingForCurrentCamera() {
            if (!selectedCamera) {
                showToast("Pilih kamera terlebih dahulu", "warning");
                return;
            }
            toggleRecording(selectedCamera, document.getElementById('headerRecBtn'));
        }

        function toggleRecording(id, toggleEl) {
            const cam = databaseCameraSettings.find(c => c.id === id);
            const isCurrentlyRecording = cam ? (cam.is_recording == 1) : (toggleEl && toggleEl.classList.contains('recording'));
            const newValue = isCurrentlyRecording ? 0 : 1;
            const actionText = newValue ? 'mengaktifkan' : 'menonaktifkan';
            
            showConfirm(`Yakin ingin ${actionText} rekaman 24/7 untuk kamera ini?`, () => {
                fetch('/index.php/nvr/save_setting', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, is_recording: newValue })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        showToast(`Perekaman berhasil di${newValue ? 'aktifkan' : 'nonaktifkan'}`, 'success');
                        if (cam) cam.is_recording = newValue;
                        loadCustomCameraNames();
                        if (selectedCamera === id) {
                            updateHeaderRecBtnState(id);
                        }
                    } else {
                        showToast('Gagal mengubah status rekaman: ' + res.message, 'error');
                    }
                })
                .catch(err => showToast('Error: ' + err.message, 'error'));
            });
        }

        // Initial Load
        initThemeUI();
        loadCustomCameraNames();
        loadRecordings();

        // Live Real-Time Socket Status Poller (Every 2s)
        function pollLiveCameraStatus() {
            fetch('/nvr/get_live_status')
                .then(r => r.json())
                .then(statusMap => {
                    if (!statusMap || typeof statusMap !== 'object') return;
                    
                    if (databaseCameraSettings && Array.isArray(databaseCameraSettings)) {
                        databaseCameraSettings.forEach(cam => {
                            const prefix = getCameraBasePrefix(cam.id);
                            if (statusMap[cam.id] !== undefined) {
                                cam.is_online = parseInt(statusMap[cam.id]);
                            } else if (statusMap[prefix] !== undefined) {
                                cam.is_online = parseInt(statusMap[prefix]);
                            }
                        });
                    }

                    Object.keys(statusMap).forEach(key => {
                        const prefix = getCameraBasePrefix(key);
                        const isOnline = parseInt(statusMap[key]) === 1;
                        
                        const badgeEl = document.getElementById(`badge-status-${prefix}`);
                        if (badgeEl) {
                            badgeEl.className = isOnline ? 'badge-online' : 'badge-offline';
                            badgeEl.textContent = isOnline ? '● Online' : '○ Offline';
                        }
                        
                        const iconEl = document.getElementById(`cam-icon-${prefix}`);
                        if (iconEl) {
                            iconEl.setAttribute('stroke', isOnline ? 'var(--accent-green)' : 'var(--text-muted)');
                        }

                        const cardEl = document.getElementById(`cam-card-${prefix}`);
                        if (cardEl) {
                            cardEl.style.opacity = isOnline ? '1' : '0.65';
                        }
                    });
                })
                .catch(() => {});
        }

        // Auto-refresh fast status every 2s & full list reload every 30s
        setInterval(pollLiveCameraStatus, 2000);
        setInterval(loadCustomCameraNames, 30000);

        // Auto-Hide Header on Player Area
        let playerHeaderTimeout = null;
        const mainPlayerCardEl = document.getElementById('mainPlayerCard');
        
        function showFloatingPlayerHeader() {
            if (!mainPlayerCardEl) return;
            mainPlayerCardEl.classList.add('show-header');
            clearTimeout(playerHeaderTimeout);
            playerHeaderTimeout = setTimeout(() => {
                mainPlayerCardEl.classList.remove('show-header');
            }, 3000);
        }
        
        if (mainPlayerCardEl) {
            mainPlayerCardEl.addEventListener('mousemove', showFloatingPlayerHeader);
            mainPlayerCardEl.addEventListener('touchstart', showFloatingPlayerHeader, { passive: true });
            mainPlayerCardEl.addEventListener('mouseleave', () => {
                clearTimeout(playerHeaderTimeout);
                mainPlayerCardEl.classList.remove('show-header');
            });
        }

        const gridPlayerViewEl = document.getElementById('gridPlayerView');
        if (gridPlayerViewEl) {
            gridPlayerViewEl.addEventListener('mousemove', showFloatingGridHeader);
            gridPlayerViewEl.addEventListener('touchstart', showFloatingGridHeader, { passive: true });
            gridPlayerViewEl.addEventListener('mouseleave', () => {
                clearTimeout(gridHeaderTimeout);
                gridPlayerViewEl.classList.remove('show-header');
            });
        }


        // ==========================================
        // 🔗 SHARE LINK MODAL LOGIC
        // ==========================================
        let activeShareCamId = null;

        function openShareModalForCurrentCamera() {
            if (!selectedCamera) {
                showToast("Pilih kamera terlebih dahulu", "warning");
                return;
            }
            const cam = databaseCameraSettings ? databaseCameraSettings.find(c => c.id === selectedCamera) : null;
            const title = cam ? cam.name : selectedCamera;
            openShareModalForCamera(selectedCamera, title);
        }

        function openShareModalForCamera(camId, camTitle) {
            activeShareCamId = camId;
            const prefix = getCameraBasePrefix(camId);
            const modal = document.getElementById('dashboardShareModal');
            const titleEl = document.getElementById('dashboardShareModalTitle');
            const linkWeb = document.getElementById('dashShareLinkWeb');
            const linkStream = document.getElementById('dashShareLinkStream');
            const linkEmbed = document.getElementById('dashShareLinkEmbed');

            if (titleEl) titleEl.innerText = `🔗 Bagikan: ${camTitle || prefix}`;

            const host = window.location.origin;
            const webUrl = `${host}/cam/${encodeURIComponent(prefix)}`;
            const streamUrl = `${host}/go2rtc/stream.html?src=${encodeURIComponent(camId)}&mode=webrtc,mse,hls`;
            const embedCode = `<iframe src="${webUrl}?embed=true" width="100%" height="480" frameborder="0" allowfullscreen></iframe>`;

            if (linkWeb) linkWeb.value = webUrl;
            if (linkStream) linkStream.value = streamUrl;
            if (linkEmbed) linkEmbed.value = embedCode;

            const cam = databaseCameraSettings ? databaseCameraSettings.find(c => c.id === camId) : null;
            const isPublic = cam ? (parseInt(cam.is_public) === 1) : false;

            updateShareModalAccessUI(isPublic);

            if (modal) modal.style.display = 'flex';
        }

        function updateShareModalAccessUI(isPublic) {
            const badge = document.getElementById('shareModalAccessBadge');
            const desc = document.getElementById('shareModalAccessDesc');
            const toggleBtn = document.getElementById('btnToggleShareAccess');

            if (isPublic) {
                if (badge) {
                    badge.innerText = '🌐 Publik';
                    badge.style.background = 'rgba(59, 130, 246, 0.2)';
                    badge.style.color = 'var(--accent-blue)';
                    badge.style.border = '1px solid rgba(59, 130, 246, 0.4)';
                }
                if (desc) desc.innerText = 'Dapat ditonton siapa saja secara langsung tanpa login root';
                if (toggleBtn) {
                    toggleBtn.innerText = '🔒 Ubah ke Privat';
                    toggleBtn.className = 'btn btn-ghost';
                }
            } else {
                if (badge) {
                    badge.innerText = '🔒 Privat';
                    badge.style.background = 'rgba(239, 68, 68, 0.2)';
                    badge.style.color = 'var(--accent-red)';
                    badge.style.border = '1px solid rgba(239, 68, 68, 0.4)';
                }
                if (desc) desc.innerText = 'Hanya dapat diakses setelah login sebagai Root';
                if (toggleBtn) {
                    toggleBtn.innerText = '🌐 Ubah ke Publik';
                    toggleBtn.className = 'btn btn-primary';
                }
            }
        }

        function toggleShareAccessFromModal() {
            if (!activeShareCamId) return;
            const cam = databaseCameraSettings ? databaseCameraSettings.find(c => c.id === activeShareCamId) : null;
            const isPublic = cam ? (parseInt(cam.is_public) === 1) : false;
            const nextVal = isPublic ? 0 : 1;

            fetch('/index.php/nvr/save_setting', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: activeShareCamId, is_public: nextVal })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    if (cam) cam.is_public = nextVal;
                    // update all variants for this camera in local array
                    const prefix = getCameraBasePrefix(activeShareCamId);
                    if (databaseCameraSettings) {
                        databaseCameraSettings.forEach(c => {
                            if (c.id === activeShareCamId || c.id.startsWith(prefix + '_')) {
                                c.is_public = nextVal;
                            }
                        });
                    }
                    updateShareModalAccessUI(nextVal === 1);
                    showToast(nextVal === 1 ? 'Akses diubah ke Publik 🌐' : 'Akses diubah ke Privat 🔒', 'info');
                    loadCustomCameraNames();
                } else {
                    showToast('Gagal mengubah hak akses: ' + res.message, 'error');
                }
            })
            .catch(err => showToast('Error: ' + err.message, 'error'));
        }

        function togglePublicShare(camId, el) {
            const cam = databaseCameraSettings ? databaseCameraSettings.find(c => c.id === camId) : null;
            const isPublic = cam ? (parseInt(cam.is_public) === 1) : false;
            const nextVal = isPublic ? 0 : 1;

            fetch('/index.php/nvr/save_setting', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: camId, is_public: nextVal })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    if (cam) cam.is_public = nextVal;
                    const prefix = getCameraBasePrefix(camId);
                    if (databaseCameraSettings) {
                        databaseCameraSettings.forEach(c => {
                            if (c.id === camId || c.id.startsWith(prefix + '_')) {
                                c.is_public = nextVal;
                            }
                        });
                    }
                    showToast(nextVal === 1 ? 'Akses diubah ke Publik 🌐' : 'Akses diubah ke Privat 🔒', 'info');
                    loadCustomCameraNames();
                } else {
                    showToast('Gagal mengubah: ' + res.message, 'error');
                }
            })
            .catch(err => showToast('Error: ' + err.message, 'error'));
        }

        function closeDashboardShareModal() {
            const modal = document.getElementById('dashboardShareModal');
            if (modal) modal.style.display = 'none';
        }

        function copyDashInput(id) {
            const input = document.getElementById(id);
            if (input) {
                input.select();
                input.setSelectionRange(0, 99999);
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(input.value).then(() => {
                        showToast('Tautan berhasil disalin ke clipboard! 📋', 'success');
                    }).catch(() => {
                        document.execCommand('copy');
                        showToast('Tautan berhasil disalin! 📋', 'success');
                    });
                } else {
                    document.execCommand('copy');
                    showToast('Tautan berhasil disalin! 📋', 'success');
                }
            }
        }

        // ==========================================
        // 🔑 CHANGE ROOT PASSWORD LOGIC
        // ==========================================
        function openChangePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            if (modal) {
                document.getElementById('currentRootPass').value = '';
                document.getElementById('newRootPass').value = '';
                document.getElementById('confirmNewRootPass').value = '';
                modal.style.display = 'flex';
                document.getElementById('currentRootPass').focus();
            }
        }

        function closeChangePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            if (modal) modal.style.display = 'none';
        }

        function handleChangePasswordSubmit(e) {
            e.preventDefault();
            const curr = document.getElementById('currentRootPass').value;
            const newPass = document.getElementById('newRootPass').value;
            const confirmPass = document.getElementById('confirmNewRootPass').value;
            const btn = document.getElementById('btnSubmitChangePass');

            if (newPass !== confirmPass) {
                showToast("Konfirmasi password baru tidak cocok!", "error");
                return;
            }

            if (newPass.length < 4) {
                showToast("Password baru minimal 4 karakter!", "warning");
                return;
            }

            btn.disabled = true;
            btn.innerText = "Menyimpan...";

            fetch('/index.php/auth/change_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current_password: curr, new_password: newPass })
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerText = "Simpan Password Baru";
                if (res.status === 'success') {
                    showToast("Password root berhasil diperbarui! ✅", "success");
                    closeChangePasswordModal();
                } else {
                    showToast("Gagal: " + res.message, "error");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = "Simpan Password Baru";
                showToast("Kesalahan jaringan: " + err.message, "error");
            });
        }

        // Real-time automatic background camera health polling every 8 seconds
        setInterval(() => {
            fetch('/index.php/nvr/get_settings')
                .then(r => r.json())
                .then(settings => {
                    if (!Array.isArray(settings)) return;
                    databaseCameraSettings = settings;
                    
                    settings.forEach(cam => {
                        const isOnline = parseInt(cam.is_online) === 1;
                        const row = document.querySelector(`.cam-row[data-src="${cam.id}"]`);
                        if (row) {
                            const badge = row.querySelector('.badge-online, .badge-offline');
                            if (badge) {
                                badge.className = isOnline ? 'badge-online' : 'badge-offline';
                                badge.innerText = isOnline ? '● Online' : '○ Offline';
                            }
                            const iconSvg = row.querySelector('.cam-row-icon svg');
                            if (iconSvg) {
                                iconSvg.setAttribute('stroke', isOnline ? 'var(--accent-green)' : 'var(--text-muted)');
                            }
                        }
                    });
                    
                    if (selectedCamera) {
                        const currentCam = settings.find(c => c.id === selectedCamera);
                        if (currentCam) {
                            const isOnline = parseInt(currentCam.is_online) === 1;
                            const headerBadge = document.getElementById('cameraStatusBadge');
                            if (headerBadge) {
                                headerBadge.className = `camera-badge ${isOnline ? 'active' : 'inactive'}`;
                                headerBadge.innerText = isOnline ? 'Online' : 'Offline';
                            }
                        }
                    }
                })
                .catch(() => {});
        }, 8000);

    </script>
    <!-- Floating Toast Notification System -->
    <div id="toastContainer"></div>

    <!-- Custom Modern Confirmation Modal -->
    <div id="customConfirmModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(1,4,9,0.8);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);z-index:10001;justify-content:center;align-items:center;">
        <div class="confirm-card" style="background:var(--bg-surface);border:1px solid var(--border-default);border-radius:10px;padding:20px;width:90%;max-width:380px;box-shadow:var(--shadow-lg);display:flex;flex-direction:column;gap:14px;animation:toastSlideIn 0.2s ease;">
            <div id="confirmMessage" class="confirm-msg" style="font-size:13px;color:var(--text-primary);line-height:1.5;font-weight:500;">Konfirmasi tindakan?</div>
            <div class="confirm-actions" style="display:flex;justify-content:flex-end;gap:8px;">
                <button id="confirmBtnCancel" class="btn btn-ghost" style="padding:6px 14px;font-size:12px;border-radius:6px;cursor:pointer;">Batal</button>
                <button id="confirmBtnOk" class="btn btn-primary" style="padding:6px 16px;font-size:12px;border-radius:6px;cursor:pointer;">Lanjutkan</button>
            </div>
        </div>
    </div>

</body>
</html>
