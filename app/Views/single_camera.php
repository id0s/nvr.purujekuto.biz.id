<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($camera['name'] ?? 'CCTV Stream') ?> - Live View</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #070a12;
            --bg-panel: rgba(15, 21, 35, 0.85);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(212, 168, 67, 0.4);
            --accent-gold: #d4a843;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Top Header Bar */
        .top-bar {
            height: 56px;
            background: var(--bg-panel);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 20;
            user-select: none;
        }

        .top-bar.embed-mode {
            display: none !important;
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .cam-icon {
            font-size: 20px;
            background: rgba(212, 168, 67, 0.15);
            border: 1px solid rgba(212, 168, 67, 0.3);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cam-details {
            overflow: hidden;
        }

        .cam-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cam-meta {
            font-size: 11.5px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-online {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34d399;
        }

        .badge-offline {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #f87171;
        }

        .badge-public {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #60a5fa;
        }

        .badge-root {
            background: rgba(212, 168, 67, 0.15);
            border: 1px solid rgba(212, 168, 67, 0.35);
            color: #fbbf24;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        .pulse-dot.online {
            background-color: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: pulseAnim 2s infinite;
        }

        .pulse-dot.offline {
            background-color: #ef4444;
        }

        @keyframes pulseAnim {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 7px 12px;
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--border-hover);
            transform: translateY(-1px);
        }

        .action-btn.btn-gold {
            background: linear-gradient(135deg, rgba(212, 168, 67, 0.2), rgba(212, 168, 67, 0.1));
            border-color: rgba(212, 168, 67, 0.4);
            color: #fde68a;
        }

        .action-btn.btn-gold:hover {
            background: linear-gradient(135deg, rgba(212, 168, 67, 0.35), rgba(212, 168, 67, 0.2));
        }

        /* Player View Area */
        .player-viewport {
            flex: 1;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .stream-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background-color: #000;
        }

        /* Floating Overlays */
        .floating-clock {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #fff;
            letter-spacing: 0.5px;
            pointer-events: none;
            z-index: 10;
        }

        .floating-controls {
            position: absolute;
            bottom: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 15;
        }

        .float-btn {
            background: rgba(15, 21, 35, 0.85);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        .float-btn:hover {
            background: var(--accent-gold);
            color: #000;
            border-color: var(--accent-gold);
            transform: scale(1.05);
        }

        /* PTZ Floating Overlay */
        .ptz-panel {
            position: absolute;
            bottom: 64px;
            right: 16px;
            background: rgba(15, 21, 35, 0.9);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(212, 168, 67, 0.3);
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            z-index: 20;
            animation: fadeIn 0.2s ease-out;
        }

        .ptz-grid {
            display: grid;
            grid-template-columns: repeat(3, 40px);
            grid-template-rows: repeat(3, 40px);
            gap: 6px;
        }

        .ptz-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
        }

        .ptz-btn:hover {
            background: rgba(212, 168, 67, 0.3);
            border-color: var(--accent-gold);
            color: #fde68a;
        }

        .ptz-btn:active {
            transform: scale(0.92);
        }

        .offline-placeholder {
            position: absolute;
            inset: 0;
            background: #090d16;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            z-index: 5;
        }

        .offline-icon {
            font-size: 48px;
            opacity: 0.6;
        }

        .offline-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .offline-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Modal Share */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }

        .modal-box {
            background: #111726;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 18px;
            cursor: pointer;
        }

        .share-item {
            margin-bottom: 14px;
        }

        .share-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            display: block;
        }

        .share-input-row {
            display: flex;
            gap: 8px;
        }

        .share-input {
            flex: 1;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            color: #fff;
            font-size: 12.5px;
            outline: none;
        }

        .btn-copy {
            background: rgba(212, 168, 67, 0.2);
            border: 1px solid rgba(212, 168, 67, 0.4);
            color: #fde68a;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-copy:hover {
            background: var(--accent-gold);
            color: #000;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: rgba(16, 185, 129, 0.95);
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s;
            z-index: 200;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

    <!-- Header bar -->
    <header class="top-bar" id="topHeaderBar">
        <div class="brand-info">
            <div class="cam-icon">📹</div>
            <div class="cam-details">
                <div class="cam-title">
                    <?= esc($camera['clean_name'] ?? $camera['name']) ?>
                    <?php if (!empty($is_online)): ?>
                        <span class="badge badge-online"><span class="pulse-dot online"></span> Live</span>
                    <?php else: ?>
                        <span class="badge badge-offline"><span class="pulse-dot offline"></span> Offline</span>
                    <?php endif; ?>
                </div>
                <div class="cam-meta">
                    <span>ID: <code><?= esc($camera['id']) ?></code></span>
                    <?php if (!empty($camera['is_public'])): ?>
                        <span class="badge badge-public">🌐 Akses Publik</span>
                    <?php else: ?>
                        <span class="badge badge-root">🔒 Mode Root</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="top-actions">
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?= site_url('/') ?>" class="action-btn" title="Kembali ke Dashboard Utama NVR">
                    🏠 Dashboard
                </a>
            <?php endif; ?>

            <button class="action-btn btn-gold" onclick="openShareModal()" title="Bagikan / Salin link CCTV">
                🔗 Bagikan Link
            </button>

            <button class="action-btn" onclick="toggleFullscreen()" title="Layar Penuh">
                ⛶ Fullscreen
            </button>
        </div>
    </header>

    <!-- Main Live Player Viewport -->
    <main class="player-viewport" id="playerContainer">
        <div class="floating-clock" id="liveClockDisplay">--:--:--</div>

        <?php if (!empty($is_online)): ?>
            <iframe id="liveFrame" 
                    src="<?= esc($stream_url) ?>" 
                    class="stream-frame" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen>
            </iframe>
        <?php else: ?>
            <div class="offline-placeholder">
                <div class="offline-icon">📡</div>
                <div class="offline-title">Kamera Sedang Offline</div>
                <div class="offline-sub">Mencoba menghubungkan kembali ke stream RTSP...</div>
            </div>
        <?php endif; ?>

        <!-- Floating Action Overlay -->
        <div class="floating-controls">
            <?php if (!empty($has_ptz)): ?>
                <button class="float-btn" id="ptzToggleBtn" onclick="togglePtzPanel()" title="Kontrol Gerak PTZ">
                    🕹️
                </button>
            <?php endif; ?>

            <button class="float-btn" onclick="reloadStream()" title="Muat Ulang Stream">
                🔄
            </button>
            <button class="float-btn" onclick="takeSnapshot()" title="Ambil Foto Snapshot">
                📷
            </button>
            <button class="float-btn" onclick="toggleFullscreen()" title="Layar Penuh">
                ⛶
            </button>
        </div>

        <!-- PTZ Panel Overlay (if supported) -->
        <?php if (!empty($has_ptz)): ?>
            <div class="ptz-panel" id="ptzPanel">
                <div style="font-size: 11px; font-weight: 700; color: var(--accent-gold); margin-bottom: 2px;">KONTROL PTZ</div>
                <div class="ptz-grid">
                    <div></div>
                    <button class="ptz-btn" onclick="sendPtz('up')">▲</button>
                    <div></div>
                    <button class="ptz-btn" onclick="sendPtz('left')">◀</button>
                    <button class="ptz-btn" onclick="sendPtz('stop')" style="font-size: 10px;">⏹</button>
                    <button class="ptz-btn" onclick="sendPtz('right')">▶</button>
                    <div></div>
                    <button class="ptz-btn" onclick="sendPtz('down')">▼</button>
                    <div></div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Share Link Modal -->
    <div class="modal-backdrop" id="shareModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">🔗 Bagikan Link Kamera</h3>
                <button class="modal-close" onclick="closeShareModal()">✕</button>
            </div>

            <div class="share-item">
                <label class="share-label">Link Tampilan Web Langsung (Standalone / Browser):</label>
                <div class="share-input-row">
                    <input type="text" id="linkWeb" class="share-input" readonly value="<?= site_url('cam/' . esc($camera['prefix'] ?? $camera['id'])) ?>">
                    <button class="btn-copy" onclick="copyToClipboard('linkWeb')">Salin</button>
                </div>
            </div>

            <div class="share-item">
                <label class="share-label">Link WebRTC Player Langsung:</label>
                <div class="share-input-row">
                    <input type="text" id="linkStream" class="share-input" readonly value="<?= esc($stream_url) ?>">
                    <button class="btn-copy" onclick="copyToClipboard('linkStream')">Salin</button>
                </div>
            </div>

            <div class="share-item">
                <label class="share-label">Kode Embed Iframe (Untuk Web / Dashboard Lain):</label>
                <div class="share-input-row">
                    <input type="text" id="linkEmbed" class="share-input" readonly value='<iframe src="<?= site_url('cam/' . esc($camera['prefix'] ?? $camera['id'])) ?>?embed=true" width="100%" height="480" frameborder="0" allowfullscreen></iframe>'>
                    <button class="btn-copy" onclick="copyToClipboard('linkEmbed')">Salin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toastMessage">Tautan berhasil disalin!</div>

    <script>
        const camPrefix = "<?= esc($camera['prefix'] ?? $camera['id']) ?>";
        const streamSrc = "<?= esc($camera['stream_id'] ?? $camera['id']) ?>";

        // Detect embed mode in URL params or iframe
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('embed') === 'true' || window.self !== window.top) {
            const header = document.getElementById('topHeaderBar');
            if (header) header.classList.add('embed-mode');
        }

        // Live Clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
            const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const el = document.getElementById('liveClockDisplay');
            if (el) el.innerText = `${dateStr} ${timeStr}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Fullscreen Toggle
        function toggleFullscreen() {
            const elem = document.getElementById('playerContainer') || document.documentElement;
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen().catch(err => console.warn(err));
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        }

        // Reload Stream
        function reloadStream() {
            const iframe = document.getElementById('liveFrame');
            if (iframe) {
                const currentSrc = iframe.src.split('&v=')[0];
                iframe.src = `${currentSrc}&v=${Date.now()}`;
                showToast("Memuat ulang video...");
            }
        }

        // Snapshot
        function takeSnapshot() {
            const snapshotUrl = `/go2rtc/api/frame.jpeg?src=${encodeURIComponent(streamSrc)}&t=${Date.now()}`;
            const link = document.createElement('a');
            link.href = snapshotUrl;
            link.download = `snapshot_${camPrefix}_${Date.now()}.jpg`;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast("Snapshot foto diunduh!");
        }

        // PTZ Panel
        function togglePtzPanel() {
            const panel = document.getElementById('ptzPanel');
            if (panel) {
                panel.style.display = (panel.style.display === 'flex') ? 'none' : 'flex';
            }
        }

        let ptzLastSent = 0;
        function sendPtz(action) {
            const now = Date.now();
            if (now - ptzLastSent < 250) return;
            ptzLastSent = now;

            fetch(`/nvr/ptz?camera=${encodeURIComponent(camPrefix)}&action=${encodeURIComponent(action)}&t=${now}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        console.log("PTZ action success:", res);
                    } else {
                        showToast("PTZ gagal: " + (res.message || 'Error'));
                    }
                })
                .catch(err => {
                    console.error("PTZ error:", err);
                    showToast("Gagal mengirim perintah PTZ");
                });
        }

        // Modal Share
        function openShareModal() {
            const modal = document.getElementById('shareModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeShareModal() {
            const modal = document.getElementById('shareModal');
            if (modal) modal.style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('shareModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        function copyToClipboard(elementId) {
            const copyText = document.getElementById(elementId);
            if (copyText) {
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value).then(() => {
                    showToast("Tautan berhasil disalin!");
                }).catch(() => {
                    document.execCommand("copy");
                    showToast("Tautan berhasil disalin!");
                });
            }
        }

        function showToast(msg) {
            const toast = document.getElementById('toastMessage');
            if (toast) {
                toast.innerText = msg;
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 2500);
            }
        }
    </script>
</body>
</html>
