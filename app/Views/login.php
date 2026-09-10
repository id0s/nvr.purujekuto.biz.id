<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Root Login - Antigravity NVR System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #090d16;
            --bg-card: rgba(18, 24, 38, 0.85);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(212, 168, 67, 0.4);
            --accent-gold: #d4a843;
            --accent-gold-glow: rgba(212, 168, 67, 0.25);
            --accent-blue: #3b82f6;
            --accent-red: #ef4444;
            --text-primary: #f1f5f9;
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
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(212, 168, 67, 0.1) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.6) 0px, transparent 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-primary);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.4s ease-out;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-gold), var(--accent-blue));
            background-size: 200% auto;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, rgba(212, 168, 67, 0.15), rgba(59, 130, 246, 0.15));
            border: 1px solid var(--border-color);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .brand-title span {
            color: var(--accent-gold);
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 16px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            background: rgba(10, 15, 26, 0.7);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 14px 12px 42px;
            color: var(--text-primary);
            font-size: 14.5px;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--accent-gold);
            background: rgba(10, 15, 26, 0.9);
            box-shadow: 0 0 0 3px var(--accent-gold-glow);
        }

        .btn-toggle-pwd {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 14px;
        }

        .btn-toggle-pwd:hover {
            color: var(--text-primary);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #d4a843 0%, #b88d30 100%);
            color: #0f172a;
            font-size: 14.5px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(212, 168, 67, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(212, 168, 67, 0.45);
            background: linear-gradient(135deg, #e5b74d 0%, #c59734 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 24px;
        }

        .footer-text a {
            color: var(--accent-gold);
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon">📹</div>
            <h1 class="brand-title">NVR <span>ROOT</span> ACCESS</h1>
            <p class="brand-subtitle">Silakan login sebagai Root untuk mengelola sistem</p>
        </div>

        <?php if (!empty($redirect) && str_contains($redirect, 'cam')): ?>
            <div class="alert-info">
                🔒 <span>Kamera ini bersifat <strong>Privat</strong>. Anda perlu login root untuk melihat tayangan.</span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-error">
                ⚠️ <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif; ?>

        <?php 
            $formAction = site_url('login');
            if (!empty($redirect) && $redirect !== '/' && $redirect !== site_url('/')) {
                $formAction .= '?redirect=' . rawurlencode($redirect);
            }
        ?>
        <form action="<?= $formAction ?>" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Username Root</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" id="username" name="username" class="form-input" value="root" required autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔑</span>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password root" required autofocus autocomplete="current-password">
                    <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility()" title="Lihat/Sembunyikan password">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Masuk Sistem ➔
            </button>
        </form>

        <div class="footer-text">
            Sistem Pemantauan CCTV & NVR Multistream
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }
    </script>
</body>
</html>
