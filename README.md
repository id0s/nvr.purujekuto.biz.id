# 📹 Clarity NVR — High Performance CCTV & Multistream Monitoring System

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)](https://php.net/)
[![CodeIgniter 4](https://img.shields.io/badge/Framework-CodeIgniter%204-EF4223?style=flat&logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![go2rtc](https://img.shields.io/badge/Stream%20Engine-go2rtc%20v1.8%2B-00ADD8?style=flat&logo=go&logoColor=white)](https://github.com/AlexxIT/go2rtc)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**Clarity NVR** adalah sistem Network Video Recorder (NVR) dan dashboard pemantauan CCTV multistream cerdas berbasis web, dirancang untuk performa tinggi, latensi ultra-rendah (<200ms), efisiensi bandwidth jaringan lokal/sekolah, dan keamanan berlapis pada akses publik.

---

## 📑 Daftar Isi
1. [Fitur Unggulan](#-fitur-unggulan)
2. [Arsitektur Sistem](#-arsitektur-sistem)
3. [Prasyarat Sistem](#-prasyarat-sistem)
4. [Panduan Instalasi Lengkap](#-panduan-instalasi-lengkap)
5. [Konfigurasi Keamanan (Security Hardening)](#-konfigurasi-keamanan-security-hardening)
6. [Optimasi Latensi & Bandwidth](#-optimasi-latensi--bandwidth)
7. [Dokumentasi Rute & API](#-dokumentasi-rute--api)
8. [Panduan Backup & Restore](#-panduan-backup--restore)
9. [Troubleshooting & FAQ](#-troubleshooting--faq)

---

## 🌟 Fitur Unggulan

- **Ultra Low Latency Streaming**: Streaming WebRTC / MSE / HLS berbasis go2rtc dengan latensi sub-detik (<200ms) di jaringan lokal.
- **Efisiensi Bandwidth Jaringan Sekolah**:
  - Dukungan streaming adaptif (prioritas sub-stream 640x360 untuk live grid view, main-stream 1080p untuk fullscreen).
  - **HTTP Range Streaming (206 Partial Content)** untuk rekaman Anyka MicroSD dalam chunk 64KB (seek video instan tanpa re-download penuh).
- **Keamanan Web Ekstra pada Akses Publik**:
  - Proteksi seluruh endpoint & API melalui middleware session `AuthFilter` dengan autentikasi enkripsi bcrypt.
  - Sistem **Share Token** aman untuk membuka akses kamera tertentu ke publik tanpa membuka seluruh dashboard.
  - **Rate Limiting Middleware (`RateLimitFilter`)** untuk mencegah serangan flood / DoS.
  - **Security Headers** (`X-Frame-Options`, `nosniff`, `X-XSS-Protection`, `Referrer-Policy`).
  - Isolasi port internal streaming (`go2rtc` hanya listen di `127.0.0.1:1984`).
- **AI Human Detection & Smart Auto-Center PTZ**:
  - Integrasi Python + OpenCV HOG & Haar Cascade untuk deteksi kehadiran orang dan koreksi posisi kamera otomatis.
- **Multi-Tier Caching System**:
  - Cache struktur database DDL 24 jam (`.schema_ok`).
  - Cache sinkronisasi stream YAML 60 detik (`.sync_lock`).
  - Cache listing rekaman 30 detik (`.recordings_cache.json`).
  - Gzip / Deflate compression untuk respon JSON dan aset web.
- **Manajemen Disk Otomatis**: Script background auto-cleanup berdasarkan ambang batas kapasitas disk (contoh: 80%) dan batas umur rekaman.

---

## 🏗 Arsitektur Sistem

```
[ CCTV IP Cameras (RTSP/H.264/H.265/Anyka) ]
                  │
                  │ RTSP (TCP)
                  ▼
┌──────────────────────────────────────────────┐
│        go2rtc Streaming Server Daemon        │
│          (Listen: 127.0.0.1:1984)            │
└──────────────────────┬───────────────────────┘
                       │ Localhost WebSocket / MSE
                       ▼
┌──────────────────────────────────────────────┐
│        Apache 2.4 Web Server (Reverse Proxy) │
│       (SSL 443 + Deflate + Security Headers) │
└──────────────────────┬───────────────────────┘
                       │ HTTP / WebSocket
                       ▼
┌──────────────────────────────────────────────┐
│   CodeIgniter 4 Backend (PHP 8.2+)           │
│  - AuthFilter (Session + Public Share Token) │
│  - RateLimitFilter (Anti-DoS)                │
│  - Range Streamer (Anyka SD Chunks)          │
│  - Multi-tier Caching & AI PTZ Engine        │
└──────────────────────┬───────────────────────┘
                       │
                       ▼
[ Client Web Browser (Desktop / Tablet / Smartphone) ]
```

---

## 💻 Prasyarat Sistem

### 1. Sistem Operasi & Hardware
- **OS**: Linux (openSUSE Leap/Tumbleweed, Ubuntu 20.04/22.04/24.04 LTS, Debian 11/12)
- **CPU**: Minimal 2 Core (Rekomendasi 4 Core jika ada transcode FFmpeg)
- **RAM**: Minimal 2 GB (Rekomendasi 4 GB+)
- **Penyimpanan**: SSD/HDD khusus untuk direktori rekaman `/var/record/cctv/`

### 2. Software Dependencies
- **PHP**: `8.2` atau lebih tinggi
  - Extensions: `php-intl`, `php-mbstring`, `php-mysqli`, `php-curl`, `php-json`, `php-gd`, `php-xml`, `php-zip`
- **Web Server**: Apache `2.4+` (dengan modul `mod_rewrite`, `mod_proxy`, `mod_proxy_wstunnel`, `mod_deflate`, `mod_headers`, `mod_expires`)
- **Database**: MySQL `8.0+` atau MariaDB `10.5+`
- **Streaming Engine**: `go2rtc` v1.8+ ([Download Binary](https://github.com/AlexxIT/go2rtc/releases))
- **Media Tools**: `ffmpeg` (dengan codec `libx264` dan `libopus`)
- **Python**: Python `3.8+` dengan package: `opencv-python-headless`, `pyyaml`, `requests`

---

## 🚀 Panduan Instalasi Lengkap

### Langkah 1: Kloning / Salin Kode Sumber
```bash
# Buat user & direktori aplikasi
mkdir -p /home/nvr/
cd /home/nvr/

# Clone repositori
git clone https://github.com/username/clarity-nvr.git .
```

### Langkah 2: Atur Izin File & Direktori
```bash
# Pastikan web server (wwwrun / www-data) dapat mengakses public dan writable
chmod 755 /home/nvr
chmod -R 755 /home/nvr/public
chmod -R 777 /home/nvr/writable

# Buat direktori penyimpanan rekaman dan cache
mkdir -p /var/record/cctv /var/record/anyka_cache
chmod -R 777 /var/record
```

### Langkah 3: Setup Database MySQL
```bash
# Masuk ke MySQL CLI
mysql -u root -p

# Buat database dan user
CREATE DATABASE verif_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'dos'@'localhost' IDENTIFIED BY '1';
GRANT ALL PRIVILEGES ON verif_db.* TO 'dos'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import struktur tabel awal
mysql -u dos -p1 verif_db < /home/nvr/schema_structure.sql
```

### Langkah 4: Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env` dan sesuaikan nilainya:
```bash
cp .env.example .env
nano .env
```
Contoh isi `.env`:
```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://nvr.purujekuto.biz.id/'
app.indexPage = ''
app.forceGlobalSecureRequests = true

database.default.hostname = localhost
database.default.database = verif_db
database.default.username = dos
database.default.password = 1
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### Langkah 5: Instalasi & Konfigurasi `go2rtc`
```bash
# 1. Unduh binary go2rtc ke /usr/local/bin
wget -O /usr/local/bin/go2rtc https://github.com/AlexxIT/go2rtc/releases/latest/download/go2rtc_linux_amd64
chmod +x /usr/local/bin/go2rtc

# 2. Buat konfigurasi /etc/go2rtc.yaml
nano /etc/go2rtc.yaml
```
Contoh isi `/etc/go2rtc.yaml`:
```yaml
webrtc:
  candidates:
    - nvr.purujekuto.biz.id:8555
    - 192.168.11.220:8555
    - stun:8555
    - stun:stun.l.google.com:19302

api:
  listen: "127.0.0.1:1984" # Wajib 127.0.0.1 untuk keamanan
  origin: "*"
  static_dir: /etc/go2rtc/www
  username: "admin"
  password: "your_go2rtc_password"

streams:
  # Contoh Stream Kamera:
  # Kamera Ruang 1 (192.168.60.115)
  cam_r1:
    - rtsp://admin:pass@192.168.60.115:554/stream0:1#rtsp=tcp
    - ffmpeg:rtsp://admin:pass@192.168.60.115:554/stream0:0#video=h264#audio=opus
  cam_r1_main: rtsp://admin:pass@192.168.60.115:554/stream0:0#rtsp=tcp#backchannel=1
  cam_r1_sub: rtsp://admin:pass@192.168.60.115:554/stream0:1#rtsp=tcp
  cam_r1_main_h264: ffmpeg:cam_r1_main#video=h264#audio=opus
```

Buat systemd service `/etc/systemd/system/go2rtc.service`:
```ini
[Unit]
Description=go2rtc WebRTC stream server
After=network.target

[Service]
Type=simple
ExecStart=/usr/local/bin/go2rtc -config /etc/go2rtc.yaml
Restart=always
RestartSec=5
User=root

[Install]
WantedBy=multi-user.target
```

Aktifkan service go2rtc:
```bash
systemctl daemon-reload
systemctl enable --now go2rtc
```

### Langkah 6: Konfigurasi Apache VirtualHost
Buat file konfigurasi `/etc/apache2/vhosts.d/000-nvr.conf`:
```apache
<VirtualHost *:80>
    ServerName nvr.purujekuto.biz.id
    DocumentRoot /home/nvr/public
    <Directory "/home/nvr/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /var/log/apache2/nvr-error_log
    CustomLog /var/log/apache2/nvr-access_log combined

    # Reverse Proxy ke go2rtc
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
    <Directory "/home/nvr/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /var/log/apache2/nvr-ssl-error_log
    CustomLog /var/log/apache2/nvr-ssl-access_log combined

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/purujekuto.biz.id/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/purujekuto.biz.id/privkey.pem

    ProxyRequests Off
    <Proxy *>
        Require all granted
    </Proxy>
    ProxyPass /go2rtc/ http://127.0.0.1:1984/ upgrade=websocket
    ProxyPassReverse /go2rtc/ http://127.0.0.1:1984/
</VirtualHost>
```

Restart Apache:
```bash
systemctl restart apache2
```

### Langkah 7: Setup Cron Otomatisasi & Pembersihan Disk
Tambahkan jadwal cron di root crontab (`crontab -e`):
```cron
# Post-process segment rekaman setiap 5 menit
*/5 * * * * /usr/bin/python3 /usr/local/bin/cctv_postprocess.py >> /var/log/cctv_postprocess.log 2>&1

# Auto disk cleanup & retensi setiap 15 menit
*/15 * * * * /usr/bin/python3 /home/nvr/app/scripts/auto_disk_cleanup.py >> /var/log/cctv_cleanup.log 2>&1

# Auto renew SSL certificate
0 0,12 * * * certbot renew -q
```

---

## 🔒 Konfigurasi Keamanan (Security Hardening)

Untuk memastikan sistem aman saat diakses dari jaringan publik / internet:

1. **Isolasi Port Streaming Backend**:
   - Port `1984` pada `go2rtc` **WAJIB** disetel ke `listen: "127.0.0.1:1984"`. Jangan gunakan `0.0.0.0:1984` agar tidak ada celah bypass autentikasi dari luar.
2. **Proteksi Akses Root & Password Bcrypt**:
   - Password default root tersimpan dengan enkripsi bcrypt pada tabel `system_auth`.
   - Ubah password default segera melalui menu Pengaturan setelah instalasi.
3. **Penyaringan Middleware Rate Limit**:
   - Endpoint streaming dibatasi maksimal 15 request/menit/IP.
   - Endpoint API dibatasi maksimal 120 request/menit/IP.
4. **Header Keamanan HTTP (`.htaccess`)**:
   - Otomatis menyuntikkan `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, dan `Referrer-Policy`.
   - Menonaktifkan `ServerSignature` dan menyembunyikan header `X-Powered-By`.
5. **Firewall (UFW / Iptables)**:
   ```bash
   # Buka hanya port web & SSH
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw allow 220/tcp # Port SSH kustom
   ufw deny 1984/tcp # Blokir akses luar ke go2rtc
   ufw deny 8554/tcp # Blokir RTSP luar jika tidak dipakai
   ```

---

## ⚡ Optimasi Latensi & Bandwidth

1. **Sub-Stream untuk Tampilan Grid Multiview**:
   - Tampilan grid dashboard memprioritaskan stream `_sub` (resolusi 640x360 @ 300-512kbps) sehingga 16+ kamera dapat dipantau bersamaan tanpa membebani bandwidth jaringan sekolah.
2. **HTTP 206 Partial Content untuk Video Anyka**:
   - Saat memutar rekaman dari kartu memori micro-SD Anyka, video dialirkan per chunk 64KB melalui socket buffer dan mendukung header HTTP Range. Browser dapat melompat ke menit berapa pun tanpa mengunduh seluruh file 50-200MB ke memori RAM server.
3. **Lock & Sentinel File Cache**:
   - `.schema_ok`: Mencegah eksekusi query DDL MySQL berulang (hemat 50-100ms per request).
   - `.sync_lock`: Membatasi sinkronisasi YAML stream maksimal 1x per 60 detik (hemat 300-400ms per request).
   - `.recordings_cache.json`: Cache direktori rekaman selama 30 detik (menghilangkan I/O disk tinggi saat multi-user membuka timeline).
4. **Kompresi Gzip**:
   - Seluruh payload API JSON dikompresi otomatis via `mod_deflate`, menghemat ukuran transfer data hingga 70-80%.

---

## 🛣 Dokumentasi Rute & API

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` / `POST` | `/login` | Public | Halaman login root & handler autentikasi |
| `GET` | `/logout` | Public | Keluar dari sesi & hapus cookies |
| `GET` | `/cam/(:segment)` | Public/Auth | Live view single camera (Publik jika `is_public=1` atau membawa valid `token`) |
| `GET` | `/share/(:segment)` | Public | Link bagikan kamera publik via token |
| `GET` | `/` atau `/nvr` | **Protected** | Dashboard utama monitoring CCTV |
| `GET` | `/nvr/get_settings` | **Protected** | Mengambil data konfigurasi semua kamera & status online |
| `GET` | `/nvr/get_live_status` | **Protected** | Status realtime producer go2rtc per stream |
| `POST` | `/nvr/save_setting` | **Protected** | Menyimpan nama, visibility, public share, recording status |
| `GET` | `/nvr/get_recordings` | **Protected** | Daftar file rekaman video MP4 terindeks |
| `GET` | `/nvr/get_timeline_recordings` | **Protected** | Timeline data rekaman kamera per tanggal |
| `GET` | `/nvr/get_disk_status` | **Protected** | Informasi penggunaan ruang harddisk `/var/record/cctv/` |
| `GET` | `/nvr/ptz` | **Protected** | Mengirimkan perintah PTZ fisik (up, down, left, right, zoom) |
| `GET` | `/nvr/ai_autocenter` | **Protected** | AI vision auto centering kamera berbasis deteksi manusia |
| `GET` | `/nvr/anyka_stream` | **Protected** | HTTP Range streaming rekaman video chunk kamera Anyka |
| `POST` | `/auth/change_password` | **Protected** | Mengubah password akun root |

---

## 💾 Panduan Backup & Restore

### Membuat Backup Manual
Jalankan perintah berikut pada server:
```bash
# Buat folder backup
mkdir -p /home/nvr/backups

# 1. Dump database schema & data
mysqldump -u dos -p1 verif_db camera_settings system_auth > /home/nvr/backups/db_backup_$(date +%Y%m%d).sql

# 2. Buat arsip terkompresi kode & konfigurasi
tar --exclude='writable/cache*' --exclude='writable/session*' --exclude='writable/logs*' --exclude='*.jpg' --exclude='*.mp4' \
    -czvf /home/nvr/backups/nvr_backup_$(date +%Y%m%d).tar.gz \
    /home/nvr/app \
    /home/nvr/public \
    /home/nvr/schema_structure.sql \
    /home/nvr/composer.json \
    /etc/go2rtc.yaml \
    /etc/apache2/vhosts.d/000-nvr.conf \
    /etc/systemd/system/go2rtc.service
```

### Melakukan Restore dari Backup
```bash
# 1. Ekstrak arsip backup
tar -xzvf nvr_backup_YYYYMMDD.tar.gz -C /

# 2. Restore database
mysql -u dos -p1 verif_db < db_backup_YYYYMMDD.sql

# 3. Reload systemd & restart service
systemctl daemon-reload
systemctl restart go2rtc apache2
```

---

## ❓ Troubleshooting & FAQ

#### Q: Kamera statusnya offline di dashboard padahal IP aktif?
> **A**: Pastikan port RTSP kamera (`554`) dapat dijangkau dari server NVR. Uji dengan perintah:
> ```bash
> nc -zv <IP_KAMERA> 554
> ```
> Untuk kamera Anyka/Eyesec, pastikan daemon `mo_rtsp` sudah aktif di kamera melalui Telnet.

#### Q: Video rekaman Anyka tidak bisa diputar atau error 500?
> **A**: Pastikan server NVR dapat melakukan koneksi telnet port `23` ke kamera untuk mengambil file video chunk via netcat.

#### Q: Halaman dashboard redirect terus ke login?
> **A**: Pastikan folder `/home/nvr/writable/session` memiliki izin tulis penuh (`chmod -R 777 /home/nvr/writable`).

---

## 📜 Lisensi
Proyek ini didistribusikan di bawah lisensi **MIT License**.
