<?php

namespace App\Controllers;

class Nvr extends BaseController
{
    private function ensure_database_schema()
    {
        $sentinel = '/var/record/cctv/.schema_ok';
        if (file_exists($sentinel) && (time() - filemtime($sentinel)) < 86400) {
            return;
        }

        try {
            $db = \Config\Database::connect();
            
            // Check camera_settings table columns
            $cols = $db->query("SHOW COLUMNS FROM camera_settings")->getResultArray();
            $colNames = array_map(fn($c) => $c['Field'], $cols);

            if (!in_array('is_public', $colNames)) {
                $db->query("ALTER TABLE camera_settings ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!in_array('share_token', $colNames)) {
                $db->query("ALTER TABLE camera_settings ADD COLUMN share_token VARCHAR(64) NULL");
            }

            // Check system_auth table
            $db->query("CREATE TABLE IF NOT EXISTS system_auth (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $check = $db->query("SELECT id FROM system_auth WHERE username = 'root' LIMIT 1");
            if ($check->getNumRows() === 0) {
                $defaultHash = password_hash('perintis29', PASSWORD_BCRYPT);
                $db->query("INSERT IGNORE INTO system_auth (username, password_hash) VALUES ('root', ?)", [$defaultHash]);
            }

            if (is_dir('/var/record/cctv') && is_writable('/var/record/cctv')) {
                @touch($sentinel);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Schema check error: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $this->ensure_database_schema();
        $this->sync_go2rtc_streams_cached();
        return view('dashboard');
    }

    private function sync_go2rtc_streams_cached()
    {
        $lockFile = '/var/record/cctv/.sync_lock';
        if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 60) {
            return;
        }
        if (is_dir('/var/record/cctv') && is_writable('/var/record/cctv')) {
            @touch($lockFile);
        }
        $this->sync_go2rtc_streams();
    }

    public function cam($cameraId = null)
    {
        $this->ensure_database_schema();
        if (empty($cameraId)) {
            return redirect()->to(site_url('/'));
        }

        $db = \Config\Database::connect();
        $rawId = trim($cameraId);
        $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $rawId);

        // Query camera settings
        $query = $db->query("SELECT id, name, is_hidden, is_recording, is_public, share_token FROM camera_settings WHERE id = ? OR id LIKE ? OR share_token = ?", [$rawId, $prefix . '_%', $rawId]);
        $results = $query->getResultArray();

        if (empty($results)) {
            return $this->response->setStatusCode(404)->setBody("Kamera CCTV dengan ID '{$rawId}' tidak ditemukan.");
        }

        // Pick best matching record (prefer h264 stream for smooth browser playback)
        $h264Record = null;
        $mainRecord = null;
        $subRecord = null;
        $firstRecord = $results[0];

        foreach ($results as $row) {
            if (str_ends_with($row['id'], '_main_h264')) {
                $h264Record = $row;
            } elseif (str_ends_with($row['id'], '_main')) {
                $mainRecord = $row;
            } elseif (str_ends_with($row['id'], '_sub_h264')) {
                $subRecord = $row;
            }
        }

        $selected = $h264Record ?: $mainRecord ?: $subRecord ?: $firstRecord;

        $isLoggedIn = session()->get('is_root_logged_in') ? true : false;
        $isPublic = !empty($selected['is_public']);

        // If camera is private and user is not logged in and token doesn't match, redirect to login
        $tokenMatch = (!empty($selected['share_token']) && ($rawId === $selected['share_token'] || $this->request->getGet('token') === $selected['share_token']));
        if (!$isLoggedIn && !$isPublic && !$tokenMatch) {
            return redirect()->to(site_url('login?redirect=' . rawurlencode((string)current_url())));
        }

        // Determine clean name
        $baseName = trim($selected['name']);
        $cleanBaseName = preg_replace('/\s*\(Tanpa Transcoding[^\)]*\)/i', '', $baseName);
        $cleanBaseName = preg_replace('/\s*\(?(main|sub|h264|main_h264|sub_h264)\)?/i', '', $cleanBaseName);
        $cleanBaseName = trim($cleanBaseName) ?: $baseName;

        $streamId = $selected['id'];

        // Check online status via go2rtc
        $isOnline = false;
        $ch = curl_init('http://127.0.0.1:1984/api/streams');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res) {
            $streamData = json_decode($res, true);
            if (is_array($streamData) && isset($streamData[$streamId])) {
                $isOnline = true;
            }
        }

        // Check PTZ support
        $hasPtz = false;
        $ptzCache = '/var/record/cctv/ptz_supported.json';
        if (file_exists($ptzCache)) {
            $ptzList = json_decode(file_get_contents($ptzCache), true) ?: [];
            if (in_array($prefix, $ptzList)) {
                $hasPtz = true;
            }
        }

        $streamUrl = "/go2rtc/stream.html?src=" . urlencode($streamId) . "&mode=webrtc,mse,hls";

        return view('single_camera', [
            'camera' => [
                'id' => $selected['id'],
                'prefix' => $prefix,
                'stream_id' => $streamId,
                'name' => $selected['name'],
                'clean_name' => $cleanBaseName,
                'is_public' => $isPublic,
            ],
            'is_online' => $isOnline,
            'has_ptz' => $hasPtz,
            'stream_url' => $streamUrl,
            'is_logged_in' => $isLoggedIn,
        ]);
    }

    public function get_settings()
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT id, name, is_hidden, is_recording, is_public, share_token FROM camera_settings ORDER BY id ASC");
        $settings = $query->getResultArray();

        $healthStatus = $this->get_realtime_camera_statuses();

        foreach ($settings as &$cam) {
            $cid = $cam['id'];
            $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $cid);

            $cam['is_online'] = (int)($healthStatus[$cid] ?? $healthStatus[$prefix] ?? 1);
            $cam['is_public'] = (int)($cam['is_public'] ?? 0);
        }

        return $this->response->setJSON($settings);
    }

    public function get_live_status()
    {
        $healthStatus = $this->get_realtime_camera_statuses();
        return $this->response->setJSON($healthStatus);
    }

    private function get_realtime_camera_statuses(): array
    {
        $statusFile = '/var/record/cctv/camera_status.json';
        
        // 1. Return cached status if less than 15 seconds old (ultra-fast <1ms)
        if (file_exists($statusFile) && (time() - filemtime($statusFile) < 15)) {
            $cached = json_decode(@file_get_contents($statusFile), true);
            if (is_array($cached) && !empty($cached)) {
                return $cached;
            }
        }

        $results = [];

        // 2. Query go2rtc active streams (in-memory fast API < 5ms)
        $go2rtcStreams = [];
        $ch = curl_init('http://127.0.0.1:1984/api/streams');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res) {
            $go2rtcStreams = json_decode($res, true) ?: [];
        }

        // Fast status determination from go2rtc streams
        foreach ($go2rtcStreams as $streamName => $streamData) {
            $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $streamName);
            $hasProducer = !empty($streamData['producers']);
            $results[$streamName] = $hasProducer ? 1 : 0;
            if ($hasProducer) {
                $results[$prefix] = 1;
            }
        }

        // Default all known cameras in DB to 1 (online) if go2rtc has them
        $db = \Config\Database::connect();
        $cams = $db->query("SELECT id FROM camera_settings")->getResultArray();
        foreach ($cams as $cam) {
            $cid = $cam['id'];
            $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $cid);
            if (!isset($results[$cid])) {
                $results[$cid] = isset($go2rtcStreams[$cid]) || isset($go2rtcStreams[$prefix]) ? 1 : 0;
            }
            if (!isset($results[$prefix])) {
                $results[$prefix] = $results[$cid];
            }
        }

        if (is_dir('/var/record/cctv') && is_writable('/var/record/cctv')) {
            @file_put_contents($statusFile, json_encode($results));
        }

        return $results;
    }

    public function save_setting()
    {
        $this->ensure_database_schema();
        $db = \Config\Database::connect();
        
        $json = null;
        if (str_contains($this->request->getHeaderLine('content-type'), 'application/json')) {
            try {
                $json = $this->request->getJSON();
            } catch (\Throwable $e) {
                $json = null;
            }
        }
        if (!$json) {
            $json = (object)$this->request->getPost();
        }

        if (!$json || !isset($json->id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid parameters']);
        }

        $id = $json->id;
        $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $id);
        
        if (isset($json->name)) {
            $baseName = trim($json->name);
            $cleanBaseName = preg_replace('/\s*\(Tanpa Transcoding[^\)]*\)/i', '', $baseName);
            $cleanBaseName = preg_replace('/\s*\(?(main|sub|h264|main_h264|sub_h264)\)?/i', '', $cleanBaseName);
            $cleanBaseName = preg_replace('/\s*\(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\)/i', '', $cleanBaseName);
            $cleanBaseName = trim($cleanBaseName);
            if (empty($cleanBaseName)) $cleanBaseName = trim($baseName);

            $db->query("UPDATE camera_settings SET name = ? WHERE id = ? OR id = ? OR id LIKE ?", [$cleanBaseName, $id, $prefix, $prefix . '_%']);
        }
        if (isset($json->is_hidden)) {
            $db->query("UPDATE camera_settings SET is_hidden = ? WHERE id = ? OR id = ? OR id LIKE ?", [$json->is_hidden, $id, $prefix, $prefix . '_%']);
        }
        if (isset($json->is_recording)) {
            $db->query("UPDATE camera_settings SET is_recording = ? WHERE id = ? OR id = ? OR id LIKE ?", [$json->is_recording, $id, $prefix, $prefix . '_%']);
        }
        if (isset($json->is_public)) {
            $db->query("UPDATE camera_settings SET is_public = ? WHERE id = ? OR id = ? OR id LIKE ?", [$json->is_public, $id, $prefix, $prefix . '_%']);
        }

        $this->export_record_config();

        return $this->response->setJSON(['status' => 'success']);
    }

    public function reset_settings()
    {
        $this->ensure_database_schema();
        $db = \Config\Database::connect();
        $db->query("UPDATE camera_settings SET is_hidden = 0");
        return $this->response->setJSON(['status' => 'success']);
    }

    public function add_camera()
    {
        try {
            $json = null;
            if (str_contains($this->request->getHeaderLine('content-type'), 'application/json')) {
                try {
                    $json = $this->request->getJSON();
                } catch (\Throwable $e) {
                    $json = null;
                }
            }
            if (!$json) {
                $json = (object)$this->request->getPost();
            }

            if (!$json || empty($json->name) || empty($json->ip)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Nama kamera dan IP wajib diisi']);
            }

            $name = trim($json->name);
            $ip = trim($json->ip);
            $port = !empty($json->port) ? (int)$json->port : 554;
            $username = trim($json->username ?? 'admin');
            $password = trim($json->password ?? '');
            $camType = trim($json->cam_type ?? 'generic');
            $streamPath = trim($json->stream_path ?? '');
            $isRecording = isset($json->is_recording) ? (int)$json->is_recording : 1;

            // Generate clean unique alphanumeric prefix ID
            $cleanId = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
            if (empty($cleanId)) $cleanId = 'cam' . rand(100, 999);
            $prefix = 'cam_' . $cleanId;

            $db = \Config\Database::connect();
            $exists = $db->query("SELECT id FROM camera_settings WHERE id LIKE ?", [$prefix . '%'])->getNumRows();
            if ($exists > 0) {
                $prefix = $prefix . '_' . rand(10, 99);
            }

            $auth = !empty($username) ? ($username . ($password !== '' ? ':' . $password : '') . '@') : '';

            // Form stream URLs with TCP transport
            if ($camType === 'anyka') {
                $mainRtsp = "rtsp://{$ip}:{$port}/stream0:0#rtsp=tcp#backchannel=1";
                $subRtsp = "rtsp://{$ip}:{$port}/stream0:1#rtsp=tcp";
            } else {
                if (empty($streamPath)) {
                    $mainRtsp = "rtsp://{$auth}{$ip}:{$port}/user={$username}&password={$password}&channel=1&stream=0.sdp?real_stream#rtsp=tcp#backchannel=1";
                    $subRtsp = "rtsp://{$auth}{$ip}:{$port}/user={$username}&password={$password}&channel=1&stream=1.sdp?real_stream#rtsp=tcp";
                } else {
                    $streamPath = ltrim($streamPath, '/');
                    $mainRtsp = "rtsp://{$auth}{$ip}:{$port}/{$streamPath}#rtsp=tcp#backchannel=1";
                    $subRtsp = "rtsp://{$auth}{$ip}:{$port}/{$streamPath}#rtsp=tcp";
                }
            }

            // 1. Append to go2rtc yaml
            $yamlCandidates = ['/mnt/go2rtc/go2rtc.yaml', '/etc/go2rtc.yaml'];
            $newStreamYaml = "\n  # {$name} ({$ip})\n";
            $newStreamYaml .= "  {$prefix}: ffmpeg:{$prefix}_sub#video=h264\n";
            $newStreamYaml .= "  {$prefix}_main: {$mainRtsp}\n";
            $newStreamYaml .= "  {$prefix}_main_h264: ffmpeg:{$prefix}_main#video=h264\n";
            $newStreamYaml .= "  {$prefix}_sub: {$subRtsp}\n";
            $newStreamYaml .= "  {$prefix}_sub_h264: ffmpeg:{$prefix}_sub#video=h264\n";

            foreach ($yamlCandidates as $yFile) {
                if (file_exists($yFile)) {
                    file_put_contents($yFile, file_get_contents($yFile) . $newStreamYaml);
                }
            }

            // 2. Insert single clean camera record into MySQL camera_settings
            $db->query("INSERT INTO camera_settings (id, name, is_hidden, is_recording, is_public) VALUES (?, ?, 0, ?, 0)
                ON DUPLICATE KEY UPDATE name=VALUES(name), is_recording=VALUES(is_recording)", [
                $prefix, $name, $isRecording
            ]);

            // Clean up any old stream variant records from DB to ensure strictly 1 item per camera
            $db->query("DELETE FROM camera_settings WHERE id IN (?, ?, ?, ?)", [
                "{$prefix}_main", "{$prefix}_main_h264", "{$prefix}_sub", "{$prefix}_sub_h264"
            ]);

            $this->export_record_config();

            // 3. Trigger go2rtc reload & probe PTZ in background
            exec("sudo /usr/bin/systemctl restart go2rtc >/dev/null 2>&1 &");
            exec("python3 /home/nvr/app/scripts/probe_ptz_support.py >/dev/null 2>&1 &");

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Kamera '{$name}' berhasil ditambahkan ke NVR!",
                'prefix' => $prefix
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error adding camera: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambah kamera: ' . $e->getMessage()
            ]);
        }
    }

    private function is_valid_mp4($filePath)
    {
        if (!file_exists($filePath)) return false;
        $size = @filesize($filePath);
        if ($size === false || $size < 2097152) { // Less than 2MB is corrupt/empty
            return false;
        }

        // Fast check: Read first 32 bytes for 'ftyp'
        $fp = @fopen($filePath, 'rb');
        if (!$fp) return false;
        $header = fread($fp, 32);
        fclose($fp);

        if (strpos($header, 'ftyp') === false) {
            return false;
        }

        return true;
    }

    public function get_recordings()
    {
        date_default_timezone_set('Asia/Jakarta');
        $cacheFile = '/var/record/cctv/.recordings_cache.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 30) {
            $cached = json_decode(@file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $this->response->setJSON($cached);
            }
        }

        $dir = '/var/record/cctv/';
        $files = [];

        if (is_dir($dir)) {
            $dh = opendir($dir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                        $files[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        $now = time();
        $parsedFiles = [];
        foreach ($files as $file) {
            $filePath = $dir . $file;
            
            // Skip files < 1MB
            $size = @filesize($filePath);
            if ($size === false || $size < 1048576) {
                continue;
            }

            // Skip files actively being written (modified in last 20 seconds)
            $mtime = @filemtime($filePath);
            if ($mtime !== false && ($now - $mtime) < 20) {
                continue;
            }

            if (preg_match('/^([a-zA-Z0-9_]+)_rec_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.mp4$/', $file, $matches)) {
                $camera = $matches[1] . '_main';
                $year = $matches[2];
                $month = $matches[3];
                $day = $matches[4];
                $hour = $matches[5];
                $minute = $matches[6];
                $second = $matches[7];

                $timestamp = strtotime("$year-$month-$day $hour:$minute:$second");

                // STRICT: If recording started less than 9.5 minutes ago, it is STILL ACTIVE / UNFINISHED -> DO NOT DISPLAY!
                if (($timestamp + 570) > $now) {
                    continue;
                }

                $displayTime = "$day/$month/$year $hour:$minute:$second";

                $parsedFiles[] = [
                    'filename' => $file,
                    'camera' => $camera,
                    'time' => "$hour:$minute:$second",
                    'date' => "$day/$month/$year",
                    'displayTime' => $displayTime,
                    'timestamp' => $timestamp
                ];
            }
        }

        usort($parsedFiles, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        if (is_dir('/var/record/cctv') && is_writable('/var/record/cctv')) {
            @file_put_contents($cacheFile, json_encode($parsedFiles));
        }

        return $this->response->setJSON($parsedFiles);
    }

    public function get_timeline_recordings()
    {
        date_default_timezone_set('Asia/Jakarta');
        $camera = $this->request->getGet('camera');
        $date = $this->request->getGet('date'); // YYYYMMDD

        $dir = '/var/record/cctv/';
        $files = [];

        if (is_dir($dir)) {
            $dh = opendir($dir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                        $files[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        $cameraPrefix = preg_replace('/(_main_h264|_sub_h264|_main|_sub|_h264)$/i', '', (string)$camera);
        $matchedFiles = [];

        foreach ($files as $file) {
            if (preg_match('/^([a-zA-Z0-9_]+)_rec_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.mp4$/', $file, $matches)) {
                $filePrefix = $matches[1];
                $year = $matches[2];
                $month = $matches[3];
                $day = $matches[4];
                $hour = (int)$matches[5];
                $minute = (int)$matches[6];
                $second = (int)$matches[7];

                if ($filePrefix !== $cameraPrefix) {
                    continue;
                }

                $fileDate = $year . $month . $day;
                if ($fileDate !== $date) {
                    continue;
                }

                // Skip unfinished active chunk
                $timestamp = strtotime("$year-$month-$day $hour:$minute:$second");
                if (($timestamp + 570) > time()) {
                    continue;
                }

                $startSecs = ($hour * 3600) + ($minute * 60) + $second;
                $matchedFiles[] = [
                    'filename' => $file,
                    'start' => $startSecs
                ];
            }
        }

        // Sort chronologically
        usort($matchedFiles, function ($a, $b) {
            return $a['start'] - $b['start'];
        });

        $timeline = [];
        $count = count($matchedFiles);
        for ($i = 0; $i < $count; $i++) {
            $curr = $matchedFiles[$i];
            $duration = 600; // 10 minutes default
            if ($i + 1 < $count) {
                $diff = $matchedFiles[$i + 1]['start'] - $curr['start'];
                if ($diff > 10 && $diff <= 1800) {
                    $duration = $diff;
                }
            }
            $timeline[] = [
                'filename' => $curr['filename'],
                'start' => $curr['start'],
                'end' => $curr['start'] + $duration,
                'duration' => $duration
            ];
        }

        return $this->response->setJSON($timeline);
    }

    private function sync_go2rtc_streams()
    {
        $yamlFile = '/mnt/go2rtc/go2rtc.yaml';
        if (!file_exists($yamlFile)) {
            $yamlFile = '/etc/go2rtc.yaml';
        }
        if (!file_exists($yamlFile)) {
            return;
        }

        $lines = file($yamlFile);
        $go2rtcPrimaryCameras = [];
        $inStreams = false;
        $lastComment = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '#') === 0) {
                $lastComment = trim(str_replace('#', '', $trimmed));
                continue;
            }

            if (strpos($line, 'streams:') === 0) {
                $inStreams = true;
                $lastComment = '';
                continue;
            }

            if ($inStreams) {
                if (line_is_top_level($line)) {
                    $inStreams = false;
                    continue;
                }

                if (preg_match('/^\s+([a-zA-Z0-9_-]+)\s*:/', $line, $matches)) {
                    $streamId = $matches[1];
                    
                    // Skip stream variants so each physical camera is strictly SINGLE
                    if (preg_match('/_(main|sub|main_h264|sub_h264)$/i', $streamId)) {
                        continue;
                    }
                    
                    // Clean comment to extract pure camera name
                    $cleanName = $lastComment;
                    $cleanName = preg_replace('/\s*\(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\)/', '', $cleanName);
                    $cleanName = preg_replace('/\s*\(Tanpa Transcoding[^\)]*\)/i', '', $cleanName);
                    $cleanName = trim($cleanName);
                    if (empty($cleanName)) {
                        $cleanName = ucwords(str_replace(['cam_', '_', '-'], ['', ' ', ' '], $streamId));
                    }

                    $go2rtcPrimaryCameras[$streamId] = $cleanName;
                    $lastComment = '';
                }
            }
        }

        if (empty($go2rtcPrimaryCameras)) {
            return;
        }

        $db = \Config\Database::connect();
        
        $query = $db->query("SELECT id, name FROM camera_settings");
        $dbStreams = [];
        foreach ($query->getResultArray() as $row) {
            $dbStreams[$row['id']] = $row['name'];
        }
        
        foreach ($go2rtcPrimaryCameras as $streamId => $cleanName) {
            if (!isset($dbStreams[$streamId])) {
                $db->query("INSERT INTO camera_settings (id, name, is_hidden, is_recording, is_public) VALUES (?, ?, 0, 0, 0)", [$streamId, $cleanName]);
            }
        }
        
        // Remove old variant IDs or deleted cameras from DB
        foreach ($dbStreams as $dbId => $dbName) {
            if (preg_match('/_(main|sub|main_h264|sub_h264)$/i', $dbId) || !isset($go2rtcPrimaryCameras[$dbId])) {
                $db->query("DELETE FROM camera_settings WHERE id = ?", [$dbId]);
            }
        }

        $this->export_record_config();

        exec("python3 /home/nvr/app/scripts/probe_ptz_support.py > /dev/null 2>&1 &");
    }

    public function get_ptz_support()
    {
        $cache_file = '/var/record/cctv/ptz_supported.json';
        if (!file_exists($cache_file)) {
            exec("python3 /home/nvr/app/scripts/probe_ptz_support.py > /dev/null 2>&1 &");
            return $this->response->setJSON([]);
        }
        $data = json_decode(file_get_contents($cache_file), true);
        return $this->response->setJSON($data ?? []);
    }

    public function ptz()
    {
        $camera = trim((string)$this->request->getGet('camera'));
        $action = trim((string)$this->request->getGet('action'));

        if (empty($camera) || empty($action)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing camera or action parameter']);
        }

        $cache_file = '/var/record/cctv/ptz_supported.json';
        $ptz_supported = [];
        if (file_exists($cache_file)) {
            $ptz_supported = json_decode(file_get_contents($cache_file), true) ?? [];
        }

        $is_supported = false;
        if (!empty($ptz_supported)) {
            foreach ($ptz_supported as $stream_id) {
                if ($stream_id === $camera || strpos($stream_id, $camera) === 0 || strpos($camera, $stream_id) === 0) {
                    $is_supported = true;
                    break;
                }
            }
        } else {
            // If cache not yet created, allow probe fallback
            $is_supported = true;
        }

        $ip = $this->resolve_camera_ip($camera);
        if (!$ip) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to resolve camera IP']);
        }

        // Lock mechanism per camera IP to prevent overlapping telnet/soap sessions
        $safeIp = preg_replace('/[^0-9\.]/', '', $ip);
        $lockFile = sys_get_temp_dir() . "/ptz_{$safeIp}.lock";
        $fp = @fopen($lockFile, 'w+');
        $locked = false;
        if ($fp) {
            $locked = flock($fp, LOCK_EX | LOCK_NB);
        }

        if (!$locked && $fp) {
            // Another command is actively being sent to this camera, wait briefly
            usleep(150000);
            $locked = flock($fp, LOCK_EX | LOCK_NB);
        }

        $output = [];
        $return_var = 0;
        $startTime = microtime(true);

        // Execute PTZ script with 5s timeout
        $cmd = "timeout 5 python3 /home/nvr/app/scripts/ptz_control.py " . escapeshellarg($ip) . " " . escapeshellarg($action) . " 2>&1";
        exec($cmd, $output, $return_var);

        if ($fp) {
            if ($locked) {
                flock($fp, LOCK_UN);
            }
            fclose($fp);
            @unlink($lockFile);
        }

        $elapsed = round((microtime(true) - $startTime) * 1000, 1);
        $outStr = implode("\n", $output);

        if ($return_var === 0 && (strpos($outStr, 'Success') !== false || empty($outStr))) {
            return $this->response->setJSON([
                'status' => 'success',
                'ip' => $ip,
                'action' => $action,
                'elapsed_ms' => $elapsed,
                'output' => $outStr
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'ip' => $ip,
                'action' => $action,
                'elapsed_ms' => $elapsed,
                'message' => !empty($outStr) ? $outStr : "PTZ command failed with exit code {$return_var}"
            ]);
        }
    }

    public function ai_autocenter()
    {
        $camera = trim((string)$this->request->getGet('camera'));
        if (empty($camera)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter kamera wajib diisi']);
        }

        $ip = $this->resolve_camera_ip($camera);
        if (!$ip) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menemukan IP kamera']);
        }

        $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $camera);

        // Try downloading snapshot from go2rtc
        $sources = [$prefix . '_sub', $prefix . '_main_h264', $prefix . '_sub_h264', $prefix, $camera];
        $snapshot = null;
        foreach ($sources as $src) {
            $ch = curl_init("http://127.0.0.1:1984/api/frame.jpeg?src=" . rawurlencode($src));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $data = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200 && strlen($data) > 1000) {
                $snapshot = $data;
                break;
            }
        }

        if (!$snapshot) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengambil snapshot gambar dari kamera']);
        }

        $tmpImg = sys_get_temp_dir() . '/snap_' . uniqid() . '.jpg';
        file_put_contents($tmpImg, $snapshot);

        // Python code for ultra-lightweight detection
        $pyCode = <<<'PY'
import sys, json, os, time, subprocess

img_path = sys.argv[1]
cam_ip = sys.argv[2] if len(sys.argv) > 2 else ""

try:
    import cv2
    import numpy as np

    img = cv2.imread(img_path)
    if img is None:
        print(json.dumps({"status": "error", "message": "Gagal membaca gambar"}))
        sys.exit(0)

    h, w = img.shape[:2]
    scale = 480.0 / max(w, 1)
    proc_img = cv2.resize(img, (int(w * scale), int(h * scale))) if scale < 1.0 else img
    gray = cv2.cvtColor(proc_img, cv2.COLOR_BGR2GRAY)
    gray = cv2.equalizeHist(gray)

    # 1. HOG Person Detector
    hog = cv2.HOGDescriptor()
    hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())
    found, weights = hog.detectMultiScale(gray, winStride=(8, 8), padding=(4, 4), scale=1.08)

    boxes = []
    for (bx, by, bw, bh), wt in zip(found, weights):
        if wt > 0.12:
            boxes.append((int(bx / scale), int(by / scale), int(bw / scale), int(bh / scale)))

    # Fallback to Haar Cascade if 0
    if len(boxes) == 0:
        for cpath in ['/usr/share/opencv4/haarcascades/haarcascade_upperbody.xml', '/usr/share/opencv/haarcascades/haarcascade_upperbody.xml', '/usr/share/opencv4/haarcascades/haarcascade_frontalface_default.xml']:
            if os.path.exists(cpath):
                cascade = cv2.CascadeClassifier(cpath)
                faces = cascade.detectMultiScale(gray, 1.1, 4, minSize=(30, 30))
                for (fx, fy, fw, fh) in faces:
                    boxes.append((int(fx / scale), int(fy / scale), int(fw / scale), int(fh / scale)))
                if len(boxes) > 0:
                    break

    person_count = len(boxes)
    if person_count == 0:
        if cam_ip:
            script = '/home/nvr/app/scripts/ptz_control.py'
            try:
                subprocess.run(['python3', script, cam_ip, 'center'], timeout=3)
            except:
                pass
        print(json.dumps({"status": "ok", "person_count": 0, "action": "center", "message": "🎯 AI: Ruangan kosong (0 orang). Kamera otomatis diarahkan ke posisi tengah."}))
        sys.exit(0)

    cx = sum(b[0] + b[2]/2.0 for b in boxes) / person_count
    cy = sum(b[1] + b[3]/2.0 for b in boxes) / person_count

    off_x = (cx - w/2.0) / w
    off_y = (cy - h/2.0) / h

    actions = []
    if off_x > 0.13: actions.append('right')
    elif off_x < -0.13: actions.append('left')

    if off_y < -0.13: actions.append('up')
    elif off_y > 0.13: actions.append('down')

    executed = []
    if cam_ip and len(actions) > 0:
        script = '/home/nvr/app/scripts/ptz_control.py'
        for act in actions:
            try:
                subprocess.run(['python3', script, cam_ip, act], timeout=3)
                executed.append(act)
            except:
                pass
            time.sleep(0.1)

    act_dict = {'up': 'Atas', 'down': 'Bawah', 'left': 'Kiri', 'right': 'Kanan'}
    if len(actions) == 0:
        msg = f"🎯 AI: Posisi kamera sudah pas! Terdeteksi {person_count} orang berada di tengah."
    else:
        act_str = " & ".join(act_dict.get(a, a) for a in actions)
        msg = f"🎯 AI: Terdeteksi {person_count} orang. Kamera digeser ke {act_str}."

    print(json.dumps({
        "status": "success",
        "person_count": person_count,
        "actions": actions,
        "executed": executed,
        "message": msg
    }))
except Exception as e:
    print(json.dumps({"status": "error", "message": f"AI Engine error: {str(e)}"}))
PY;

        $output = [];
        $cmd = "python3 -c " . escapeshellarg($pyCode) . " " . escapeshellarg($tmpImg) . " " . escapeshellarg($ip) . " 2>&1";
        exec($cmd, $output);
        @unlink($tmpImg);

        $outStr = trim(implode("\n", $output));
        $resJson = json_decode($outStr, true);

        if (is_array($resJson)) {
            return $this->response->setJSON($resJson);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'raw_output' => $outStr,
                'message' => !empty($outStr) ? $outStr : 'AI processing error'
            ]);
        }
    }

    public function save_home_position()
    {
        $camera = trim((string)$this->request->getGet('camera'));
        if (empty($camera)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter kamera wajib diisi']);
        }
        $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $camera);

        $file = '/var/record/cctv/camera_home_positions.json';
        $data = [];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
        }
        $data[$prefix] = [
            'set_at' => time(),
            'formatted' => date('Y-m-d H:i:s')
        ];
        @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Posisi tengah (Home) berhasil disimpan untuk kamera '{$prefix}'!"
        ]);
    }

    public function go_home_position()
    {
        $camera = trim((string)$this->request->getGet('camera'));
        if (empty($camera)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter kamera wajib diisi']);
        }

        $ip = $this->resolve_camera_ip($camera);
        if (!$ip) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menemukan IP kamera']);
        }

        $output = [];
        $return_var = 0;
        exec("timeout 4 python3 /home/nvr/app/scripts/ptz_control.py " . escapeshellarg($ip) . " center 2>&1", $output, $return_var);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Kamera diarahkan ke posisi tengah (Home)'
        ]);
    }

    private function resolve_camera_ip($camera)
    {
        $yamlFile = '/mnt/go2rtc/go2rtc.yaml';
        if (!file_exists($yamlFile)) {
            $yamlFile = '/etc/go2rtc.yaml';
        }
        if (!file_exists($yamlFile)) {
            return null;
        }

        $lines = file($yamlFile);
        $source = '';
        $in_streams = false;

        foreach ($lines as $i => $line) {
            if (strpos($line, 'streams:') === 0) {
                $in_streams = true;
                continue;
            }
            if ($in_streams) {
                if (line_is_top_level($line)) {
                    $in_streams = false;
                    continue;
                }
                if (preg_match('/^\s+([a-zA-Z0-9_-]+)\s*:\s*(.*)$/', $line, $matches)) {
                    $stream_id = $matches[1];
                    if ($stream_id === $camera || strpos($camera, $stream_id) === 0 || strpos($stream_id, $camera) === 0) {
                        $val = trim($matches[2]);
                        if (!empty($val)) {
                            $source = $val;
                        } else {
                            // Multi-line YAML array, grab sub-lines
                            for ($j = $i + 1; $j < min($i + 10, count($lines)); $j++) {
                                $subline = trim($lines[$j]);
                                if (!empty($subline) && strlen($subline) > 0 && $subline[0] === '-') {
                                    $source .= ' ' . $subline;
                                } elseif (!empty($subline) && preg_match('/^\s+[a-zA-Z0-9_-]+\s*:/', $lines[$j])) {
                                    break;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }

        if (!$source) {
            return null;
        }

        if (preg_match('/@?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $source, $ip_matches)) {
            return $ip_matches[1];
        }

        $loops = 0;
        while (strpos($source, 'ffmpeg:') !== false && $loops < 5) {
            if (preg_match('/ffmpeg:([^\s#]+)/', $source, $m)) {
                $nested = trim($m[1]);
                if (preg_match('/@?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $nested, $ip_matches)) {
                    return $ip_matches[1];
                }
                $found_nested = false;
                foreach ($lines as $line) {
                    if (preg_match('/^\s+' . preg_quote($nested, '/') . '\s*:\s*(.+)$/', $line, $m2)) {
                        $source = trim($m2[1]);
                        $found_nested = true;
                        break;
                    }
                }
                if (!$found_nested) break;
            } else {
                break;
            }
            $loops++;
        }

        if (preg_match('/@?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $source, $ip_matches)) {
            return $ip_matches[1];
        }
        return null;
    }

    private function export_record_config()
    {
        $db = \Config\Database::connect();
        $results = $db->table('camera_settings')->select('id, is_recording')->get()->getResultArray();
        $config = [];
        foreach ($results as $row) {
            $prefix = preg_replace('/_(main|sub|main_h264|sub_h264)$/', '', $row['id']);
            if (isset($config[$prefix]) && $config[$prefix] === false) continue;
            $config[$prefix] = (int)$row['is_recording'] === 1;
        }
        file_put_contents('/var/record/cctv/record_config.json', json_encode($config, JSON_PRETTY_PRINT));
    }

    public function get_disk_status()
    {
        $dir = '/var/record/cctv/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $total = @disk_total_space($dir) ?: 1;
        $free = @disk_free_space($dir) ?: 0;
        $used = $total - $free;
        $percent = round(($used / $total) * 100, 1);
        
        return $this->response->setJSON([
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percent' => $percent,
            'total_formatted' => $this->format_bytes($total),
            'used_formatted' => $this->format_bytes($used),
            'free_formatted' => $this->format_bytes($free),
        ]);
    }

    private function format_bytes($bytes, $precision = 1) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function get_cleanup_config()
    {
        $file = '/var/record/cctv/cleanup_config.json';
        $default = ['retention_days' => 30, 'disk_limit_percent' => 80];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return $this->response->setJSON($data ?? $default);
        }
        return $this->response->setJSON($default);
    }

    public function save_cleanup_config()
    {
        $file = '/var/record/cctv/cleanup_config.json';
        $json = $this->request->getJSON();
        if (!$json) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid parameters']);
        }
        
        $data = [
            'retention_days' => (int)($json->retention_days ?? 30),
            'disk_limit_percent' => (int)($json->disk_limit_percent ?? 80)
        ];
        
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        return $this->response->setJSON(['status' => 'success']);
    }

    public function get_anyka_available_dates()
    {
        $camera = $this->request->getGet('camera');
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT DISTINCT DATE(FROM_UNIXTIME(segment_timestamp)) as rec_date 
            FROM anyka_recordings 
            WHERE camera_id = ? 
            ORDER BY rec_date DESC", [$camera]);
        
        $results = $query->getResultArray();
        $dates = [];
        foreach ($results as $row) {
            $dates[] = date('d/m/Y', strtotime($row['rec_date']));
        }
        return $this->response->setJSON($dates);
    }

    public function get_anyka_chunks()
    {
        $camera = $this->request->getGet('camera');
        $dateStr = $this->request->getGet('date'); // Format: DD/MM/YYYY
        
        $parts = explode('/', $dateStr);
        if (count($parts) === 3) {
            $formattedDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
        } else {
            return $this->response->setJSON([]);
        }
        
        $startTs = strtotime("$formattedDate 00:00:00");
        $endTs = strtotime("$formattedDate 23:59:59");
        
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT segment_timestamp, chunk_offset, duration, filename 
            FROM anyka_recordings 
            WHERE camera_id = ? 
              AND segment_timestamp >= ? 
              AND segment_timestamp <= ? 
            ORDER BY segment_timestamp ASC, chunk_offset ASC", [$camera, $startTs, $endTs]);
        
        $results = $query->getResultArray();
        
        $chunks = [];
        foreach ($results as $row) {
            $chunkStartTs = (int)$row['segment_timestamp'] + (int)$row['chunk_offset'];
            $displayTime = date('d/m/Y H:i:s', $chunkStartTs);
            
            $chunks[] = [
                'timestamp' => $chunkStartTs,
                'duration' => (int)$row['duration'],
                'displayTime' => $displayTime,
                'time' => date('H:i:s', $chunkStartTs),
                'date' => date('d/m/Y', $chunkStartTs)
            ];
        }
        
        return $this->response->setJSON($chunks);
    }

    public function anyka_stream()
    {
        $camera = $this->request->getGet('camera');
        $timestamp = (int)$this->request->getGet('timestamp');
        
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT segment_timestamp, chunk_offset, duration, filename 
            FROM anyka_recordings 
            WHERE camera_id = ? 
              AND (segment_timestamp + chunk_offset) <= ?
            ORDER BY segment_timestamp DESC, chunk_offset DESC 
            LIMIT 1", [$camera, $timestamp]);
        
        $row = $query->getRowArray();
        if (!$row) {
            return $this->response->setStatusCode(404)->setBody("Recording chunk not found");
        }
        
        // --- CACHE LAYER ---
        $cacheDir = '/var/record/anyka_cache';
        $cacheFile = "{$cacheDir}/{$camera}_{$row['segment_timestamp']}_{$row['chunk_offset']}.mp4";
        
        if (file_exists($cacheFile) && filesize($cacheFile) > 0) {
            return $this->stream_file_with_range($cacheFile, false);
        }
        // --------------------
        
        $ip = $this->resolve_camera_ip($camera);
        if (!$ip) {
            return $this->response->setStatusCode(500)->setBody("Failed to resolve camera IP");
        }
        
        $year = date('Y', $row['segment_timestamp']);
        $month = date('m', $row['segment_timestamp']);
        $day = date('d', $row['segment_timestamp']);
        
        $durQuery = $db->query("
            SELECT MAX(chunk_offset + duration) as seg_dur 
            FROM anyka_recordings 
            WHERE camera_id = ? AND segment_timestamp = ?", [$camera, $row['segment_timestamp']]);
        $durRow = $durQuery->getRowArray();
        $segDur = $durRow && isset($durRow['seg_dur']) ? (int)$durRow['seg_dur'] : 600;
        $segDurStr = str_pad($segDur, 4, '0', STR_PAD_LEFT);
        
        $folderName = "{$row['segment_timestamp']}_{$segDurStr}";
        $cameraFilePath = "/tmp/sdcard/DCIM/{$year}/{$month}/{$day}/{$folderName}/{$row['filename']}";
        
        $tempId = uniqid('anyka_', true);
        $tempMedia = "/tmp/{$tempId}.media";
        $tempMp4 = "/tmp/{$tempId}.mp4";
        
        $port = rand(10000, 20000);
        exec("nc -l -p {$port} > " . escapeshellarg($tempMedia) . " &");
        
        usleep(150000);
        
        $serverIp = "192.168.11.220";
        $cmds = "sleep 1; echo \"root\"; sleep 1; echo \"\"; sleep 1; echo \"nc {$serverIp} {$port} < {$cameraFilePath}\"; sleep 5; echo \"exit\"";
        $telnetCmd = "timeout 8 bash -c " . escapeshellarg("({$cmds}) | telnet {$ip} 2>/dev/null");
        exec($telnetCmd);
        
        $fileReceived = false;
        for ($idx = 0; $idx < 25; $idx++) {
            if (file_exists($tempMedia) && filesize($tempMedia) > 0) {
                $fileReceived = true;
                break;
            }
            usleep(200000);
        }
        
        if (!$fileReceived) {
            @unlink($tempMedia);
            return $this->response->setStatusCode(500)->setBody("Failed to fetch media chunk from camera");
        }
        
        $transcodeCmd = "ffmpeg -y -i " . escapeshellarg($tempMedia) . " -c:v libx264 -preset ultrafast -tune zerolatency -an " . escapeshellarg($tempMp4) . " 2>&1";
        exec($transcodeCmd);
        
        if (!file_exists($tempMp4) || filesize($tempMp4) === 0) {
            @unlink($tempMedia);
            @unlink($tempMp4);
            return $this->response->setStatusCode(500)->setBody("Failed to transcode media chunk");
        }
        
        @unlink($tempMedia);
        
        // Save to cache before sending
        if (is_writable($cacheDir) || (!file_exists($cacheFile) && is_writable(dirname($cacheFile)))) {
            copy($tempMp4, $cacheFile);
            @chmod($cacheFile, 0664);
            @unlink($tempMp4);
            return $this->stream_file_with_range($cacheFile, false);
        }
        
        return $this->stream_file_with_range($tempMp4, true);
    }

    private function stream_file_with_range(string $filePath, bool $isTemp = false)
    {
        if (!file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setBody("Video file not found");
        }

        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        $rangeHeader = $this->request->getHeaderLine('Range');
        if (!empty($rangeHeader) && preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $m)) {
            $start = (int)$m[1];
            if (!empty($m[2])) {
                $end = (int)$m[2];
            }
            if ($start > $end || $start >= $fileSize) {
                return $this->response->setStatusCode(416)->setHeader('Content-Range', "bytes */{$fileSize}");
            }
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
        } else {
            header('HTTP/1.1 200 OK');
        }

        $length = $end - $start + 1;
        header('Content-Type: video/mp4');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $length);
        header('Cache-Control: public, max-age=86400');

        $fp = fopen($filePath, 'rb');
        if ($fp) {
            fseek($fp, $start);
            $remaining = $length;
            while ($remaining > 0 && !feof($fp) && !connection_aborted()) {
                $chunkSize = min(65536, $remaining);
                $buffer = fread($fp, $chunkSize);
                echo $buffer;
                flush();
                $remaining -= strlen($buffer);
            }
            fclose($fp);
        }

        if ($isTemp && file_exists($filePath)) {
            @unlink($filePath);
        }
        exit;
    }

}
function line_is_top_level($line) {
    $trimmed = trim($line);
    return ($trimmed !== '' && strpos($line, ' ') !== 0 && strpos($line, '#') !== 0 && strpos($line, ':') !== false);
}
