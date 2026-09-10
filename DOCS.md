# 🛡️ Panduan Teknis & Keamanan Clarity NVR

Dokumen ini berisi spesifikasi teknis mendalam, checklist pengerasan keamanan (Security Hardening), konfigurasi reverse proxy (Apache / Nginx), manajemen firewall, dan blueprint arsitektur untuk sistem NVR.

---

## 📑 Daftar Isi
1. [Security Hardening Checklist](#1-security-hardening-checklist)
2. [Konfigurasi Server & Reverse Proxy](#2-konfigurasi-server--reverse-proxy)
3. [Arsitektur Autentikasi & Otorisasi](#3-arsitektur-autentikasi--otorisasi)
4. [Optimasi Aliran Data & Bandwidth](#4-optimasi-aliran-data--bandwidth)
5. [Struktur Database & Manajemen Akun](#5-struktur-database--manajemen-akun)
6. [Layanan Background (Systemd & Cron)](#6-layanan-background-systemd--cron)

---

## 1. Security Hardening Checklist

Sebelum mengekspos server NVR ke internet atau jaringan publik, pastikan seluruh poin pada checklist berikut telah terpenuhi:

- [x] **go2rtc API Isolate to Localhost**:
  - Konfigurasi `api.listen` di `/etc/go2rtc.yaml` harus bernilai `"127.0.0.1:1984"`.
  - Jangan gunakan `0.0.0.0:1984` atau `:1984` agar API backend streaming tidak dapat diakses langsung tanpa melewati reverse proxy.
- [x] **Proteksi Seluruh Route Sensitif dengan Auth Middleware**:
  - Endpoint manajemen kamera (`/nvr/save_setting`, `/nvr/reset_settings`, `/nvr/add_camera`), PTZ (`/nvr/ptz`, `/nvr/ai_autocenter`), dan stream rekaman (`/nvr/anyka_stream`) harus berada di dalam filter grup `auth`.
- [x] **Enkripsi Password Bcrypt & Rate Limiting Brute Force**:
  - Password disimpan dengan algoritma `PASSWORD_BCRYPT` cost 10.
  - Terapkan delay pada percobaan login yang gagal untuk mencegah serangan brute force berbasis dictionary.
- [x] **Rate Limiter Anti-DoS**:
  - `RateLimitFilter` aktif membatasi request per IP address (15 req/menit untuk stream chunk, 120 req/menit untuk API umum).
- [x] **HTTP Security Headers**:
  - `X-Frame-Options: SAMEORIGIN` (mencegah serangan Clickjacking dalam iframe).
  - `X-Content-Type-Options: nosniff` (mencegah eksploitasi MIME type confusion).
  - `X-XSS-Protection: 1; mode=block` (perlindungan XSS pada legacy browser).
  - `Referrer-Policy: strict-origin-when-cross-origin` (mencegah kebocoran parameter URL internal).
  - `ServerSignature Off` dan `Header unset X-Powered-By` (menyembunyikan sidik jari versi software server).
- [x] **File Permission Lockdown**:
  - Direktori aplikasi `/home/nvr` disetel ke izin `755` (bukan 777).
  - Hanya folder `/home/nvr/writable` dan `/var/record` yang diberikan izin tulis penuh (`777` / `775`).
  - Cegah eksekusi script PHP di dalam folder upload / rekaman statis.
- [x] **Firewall Network Rules**:
  - Port yang diizinkan dari luar: `80/tcp` (HTTP), `443/tcp` (HTTPS), port kustom SSH (contoh: `220/tcp`).
  - Port internal yang diblokir dari luar: `1984/tcp` (go2rtc API), `8554/tcp` (RTSP server), `3306/tcp` (MySQL).

---

## 2. Konfigurasi Server & Reverse Proxy

### A. Konfigurasi Apache 2.4 (`/etc/apache2/vhosts.d/000-nvr.conf`)
```apache
<VirtualHost *:80>
    ServerName nvr.purujekuto.biz.id
    DocumentRoot /home/nvr/public

    <Directory "/home/nvr/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Proxy WebSocket & HTTP ke go2rtc
    ProxyRequests Off
    <Proxy *>
        Require all granted
    </Proxy>
    ProxyPass /go2rtc/ http://127.0.0.1:1984/ upgrade=websocket
    ProxyPassReverse /go2rtc/ http://127.0.0.1:1984/
</VirtualHost>

<VirtualHost *:443>
    ServerName nvr.purujekuto.biz.id
    DocumentRoot /home/nvr/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/purujekuto.biz.id/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/purujekuto.biz.id/privkey.pem

    <Directory "/home/nvr/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ProxyRequests Off
    <Proxy *>
        Require all granted
    </Proxy>
    ProxyPass /go2rtc/ http://127.0.0.1:1984/ upgrade=websocket
    ProxyPassReverse /go2rtc/ http://127.0.0.1:1984/
</VirtualHost>
```

### B. Konfigurasi Alternatif Nginx (`/etc/nginx/sites-available/nvr.conf`)
Jika menggunakan Nginx sebagai web server utama:
```nginx
server {
    listen 80;
    server_name nvr.purujekuto.biz.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name nvr.purujekuto.biz.id;

    ssl_certificate /etc/letsencrypt/live/purujekuto.biz.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/purujekuto.biz.id/privkey.pem;

    root /home/nvr/public;
    index index.php index.html;

    # Gzip Compression
    gzip on;
    gzip_types application/json text/css application/javascript image/svg+xml;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Handler
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Reverse Proxy go2rtc WebRTC/WebSocket
    location /go2rtc/ {
        proxy_pass http://127.0.0.1:1984/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
    }
}
```

---

## 3. Arsitektur Autentikasi & Otorisasi

```
                      ┌───────────────────────────────┐
                      │    Incoming HTTP Request      │
                      └──────────────┬────────────────┘
                                     │
                        Is Route in Auth Filter?
                                     │
                      ┌──────────────┴───────────────┐
                     YES                             NO
                      │                              │
        ┌─────────────┴──────────────┐       ┌───────┴──────────────┐
        │ Cek Session `is_root...`   │       │ Allow Request        │
        └─────────────┬──────────────┘       │ (Login / Share View) │
                      │                      └──────────────────────┘
         Is Logged In? ───► YES ───► Allow Access
                      │
                      NO
                      │
        ┌─────────────┴──────────────┐
        │ Cek Validitas Share Token  │
        └─────────────┬──────────────┘
                      │
         Valid Token? ────► YES ───► Allow Access (Single Camera)
                      │
                      NO
                      │
         Is API / JSON Request?
          ├──► YES ──► Return HTTP 401 JSON (`{"status":"error"}`)
          └──► NO  ──► Redirect to `/login?redirect=...`
```

---

## 4. Optimasi Aliran Data & Bandwidth

### Streaming Video Range Request (HTTP 206)
Pada pemutaran rekaman Micro-SD kamera Anyka, controller `Nvr::anyka_stream()` menangani header `Range: bytes=start-end`.

- **Proses Transaksi**:
  1. Browser meminta segmen byte tertentu (misal: `bytes=0-65535`).
  2. Server merespon dengan header `HTTP/1.1 206 Partial Content` dan `Content-Range: bytes 0-65535/5242880`.
  3. Server membaca file menggunakan pointer file (`fseek`) dan mengirim chunk 64KB melalui output buffer (`flush()`).
  4. Penggunaan RAM server konstan (< 2MB) terlepas dari ukuran video ratusan MB.

---

## 5. Struktur Database & Manajemen Akun

### Tabel: `system_auth`
Menyimpan kredensial autentikasi pengguna root.
```sql
CREATE TABLE IF NOT EXISTS `system_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabel: `camera_settings`
Menyimpan konfigurasi nama kamera, status privasi, share token, dan rekaman.
```sql
CREATE TABLE IF NOT EXISTS `camera_settings` (
  `id` varchar(64) NOT NULL,
  `name` varchar(128) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `is_recording` tinyint(1) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `share_token` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 6. Layanan Background (Systemd & Cron)

### Monitoring Status Daemon:
```bash
# Cek status go2rtc stream server
systemctl status go2rtc

# Cek log error Apache
tail -f /var/log/apache2/nvr-error_log

# Cek log auto cleanup disk
tail -f /var/log/cctv_cleanup.log
```
