#!/usr/bin/env python3
import os
import time
import subprocess
import signal
import sys
import json
import yaml
import socket
import re

CONFIG_PATH = '/etc/go2rtc.yaml'
RECORD_DIR = '/var/record/cctv/'
RECORD_CONFIG_PATH = '/var/record/cctv/record_config.json'
CLEANUP_CONFIG_PATH = '/var/record/cctv/cleanup_config.json'
ANYKA_CACHE_DIR = '/var/record/anyka_cache/'

active_recordings = {}
failed_attempts = {}
retry_after = {}
last_cleanup_time = 0

def probe_camera_online(ip, port=554, timeout=0.8):
    """Fast check if camera RTSP port is reachable before spawning FFmpeg."""
    if not ip or ip == '127.0.0.1':
        return True
    try:
        s = socket.create_connection((ip, port), timeout=timeout)
        s.close()
        return True
    except Exception:
        return False

def extract_ip_from_url(url_val):
    if isinstance(url_val, list):
        for u in url_val:
            m = re.search(r'@?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})', str(u))
            if m and m.group(1) != '127.0.0.1':
                return m.group(1)
    elif isinstance(url_val, str):
        m = re.search(r'@?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})', url_val)
        if m and m.group(1) != '127.0.0.1':
            return m.group(1)
    return None

def load_record_config():
    if not os.path.exists(RECORD_CONFIG_PATH):
        return {}
    try:
        with open(RECORD_CONFIG_PATH, 'r') as f:
            return json.load(f)
    except Exception as e:
        print(f"Error reading record config JSON: {e}", flush=True)
        return {}

def parse_go2rtc_streams(filepath):
    streams = {}
    if not os.path.exists(filepath):
        return streams
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            data = yaml.safe_load(f)
        if data and 'streams' in data and isinstance(data['streams'], dict):
            for k, v in data['streams'].items():
                if isinstance(k, str) and not k.startswith('-') and not k.startswith('#'):
                    streams[k] = v
    except Exception as e:
        print(f"Error reading go2rtc streams from {filepath}: {e}", flush=True)
    return streams

def resolve_camera_targets(streams, record_config):
    camera_streams = {}
    camera_ips = {}

    for key, val in streams.items():
        base_prefix = None
        stream_type = 'raw'

        if key.endswith('_main_h264'):
            base_prefix = key[:-len('_main_h264')]
            stream_type = 'main_h264'
        elif key.endswith('_sub_h264'):
            base_prefix = key[:-len('_sub_h264')]
            stream_type = 'sub_h264'
        elif key.endswith('_sub'):
            base_prefix = key[:-len('_sub')]
            stream_type = 'sub'
        elif key.endswith('_main'):
            base_prefix = key[:-len('_main')]
            stream_type = 'main'
        else:
            base_prefix = key
            stream_type = 'raw'

        if not base_prefix:
            base_prefix = key

        if base_prefix not in camera_streams:
            camera_streams[base_prefix] = {}
        camera_streams[base_prefix][stream_type] = key

        ip = extract_ip_from_url(val)
        if ip and base_prefix not in camera_ips:
            camera_ips[base_prefix] = ip

    targets = {}
    for base_prefix, variants in camera_streams.items():
        if not record_config.get(base_prefix, False):
            continue

        chosen_key = None
        for candidate_type in ['sub_h264', 'sub', 'main_h264', 'h264', 'main', 'raw']:
            if candidate_type in variants:
                chosen_key = variants[candidate_type]
                break

        if chosen_key:
            targets[base_prefix] = {
                'url': f"rtsp://127.0.0.1:8554/{chosen_key}",
                'transport': 'tcp',
                'ip': camera_ips.get(base_prefix)
            }

    return targets

def start_recording(camera_prefix, rtsp_url, transport):
    os.makedirs(RECORD_DIR, exist_ok=True)
    output_pattern = os.path.join(RECORD_DIR, f"{camera_prefix}_rec_%Y%m%d_%H%M%S.mp4")
    
    cmd = [
        '/usr/local/bin/ffmpeg',
        '-nostats',
        '-loglevel', 'warning',
        '-use_wallclock_as_timestamps', '1',
        '-fflags', '+genpts+igndts+discardcorrupt',
        '-rtsp_transport', transport,
        '-timeout', '4000000',
        '-rtsp_flags', 'prefer_tcp',
        '-reorder_queue_size', '1000',
        '-max_delay', '500000',
        '-y',
        '-i', rtsp_url,
        '-c:v', 'copy',
        '-an',
        '-map', '0:v:0?',
        '-avoid_negative_ts', 'make_zero',
        '-max_muxing_queue_size', '1024',
        '-f', 'segment',
        '-segment_time', '600',
        '-segment_atclocktime', '1',
        '-segment_format', 'mp4',
        '-reset_timestamps', '1',
        '-movflags', '+faststart',
        '-strftime', '1',
        output_pattern
    ]
    
    try:
        proc = subprocess.Popen(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        active_recordings[camera_prefix] = {
            'proc': proc,
            'url': rtsp_url,
            'transport': transport,
            'start_time': time.time()
        }
        print(f"Started recording for {camera_prefix} using local stream: {rtsp_url}", flush=True)
    except Exception as e:
        print(f"Failed to start recording for {camera_prefix}: {e}", flush=True)

def stop_recording(camera_prefix):
    if camera_prefix in active_recordings:
        item = active_recordings[camera_prefix]
        proc = item['proc']
        try:
            print(f"Stopping recording for {camera_prefix}...", flush=True)
            proc.terminate()
            try:
                proc.wait(timeout=2)
            except subprocess.TimeoutExpired:
                proc.kill()
                proc.wait(timeout=1)
        except Exception as e:
            print(f"Error stopping process for {camera_prefix}: {e}", flush=True)
        del active_recordings[camera_prefix]

def monitor_and_sync():
    streams = parse_go2rtc_streams(CONFIG_PATH)
    record_config = load_record_config()
    
    targets = resolve_camera_targets(streams, record_config)
    now = time.time()
                
    # Stop recordings for cameras that are no longer targets
    for active_prefix in list(active_recordings.keys()):
        if active_prefix not in targets:
            stop_recording(active_prefix)
            
    # Check dead processes
    for prefix in list(active_recordings.keys()):
        item = active_recordings[prefix]
        proc = item['proc']
        if proc.poll() is not None:
            uptime = now - item.get('start_time', now)
            try:
                proc.wait(timeout=1)
            except Exception:
                pass
            del active_recordings[prefix]
            
            fails = failed_attempts.get(prefix, 0) + 1
            failed_attempts[prefix] = fails
            # Exponential backoff: 45s, 90s, 180s, up to 300s
            backoff = min(300, 45 * (2 ** min(fails - 1, 3)))
            retry_after[prefix] = now + backoff
            print(f"Recording for {prefix} stopped after {uptime:.1f}s (fail #{fails}). Backoff cooldown: {backoff}s.", flush=True)
        else:
            uptime = now - item.get('start_time', now)
            if uptime > 300: # Stable for 5 minutes -> reset fail counter
                failed_attempts[prefix] = 0
                
    # Start recordings for enabled targets
    for prefix, info in targets.items():
        url = info['url']
        transport = info['transport']
        cam_ip = info.get('ip')
        
        if prefix in active_recordings:
            item = active_recordings[prefix]
            if item['url'] != url or item['transport'] != transport:
                print(f"Config changed for {prefix}. Restarting stream...", flush=True)
                stop_recording(prefix)
                start_recording(prefix, url, transport)
        else:
            if now < retry_after.get(prefix, 0):
                continue
                
            # Pre-probe camera port to avoid connection storm if camera is offline
            if cam_ip and not probe_camera_online(cam_ip):
                fails = failed_attempts.get(prefix, 0) + 1
                failed_attempts[prefix] = fails
                backoff = min(300, 45 * (2 ** min(fails - 1, 3)))
                retry_after[prefix] = now + backoff
                print(f"Camera {prefix} ({cam_ip}) unreachable via TCP port 554. Skipping (cooldown {backoff}s).", flush=True)
                continue
                
            start_recording(prefix, url, transport)

def cleanup_all(signum, frame):
    print("Stopping all active recordings...", flush=True)
    for prefix in list(active_recordings.keys()):
        stop_recording(prefix)
    sys.exit(0)

def cleanup_anyka_cache():
    if not os.path.exists(ANYKA_CACHE_DIR):
        return
    now = time.time()
    retention_seconds = 86400
    try:
        files = [os.path.join(ANYKA_CACHE_DIR, f) for f in os.listdir(ANYKA_CACHE_DIR) if f.endswith('.mp4')]
        for f in files:
            mtime = os.path.getmtime(f)
            if now - mtime > retention_seconds:
                try:
                    os.remove(f)
                except Exception:
                    pass
    except Exception as e:
        print(f"Error in cleanup_anyka_cache: {e}", flush=True)

def cleanup_old_recordings():
    global last_cleanup_time
    now = time.time()
    if now - last_cleanup_time < 3600:
        return
    last_cleanup_time = now
    
    try:
        cleanup_anyka_cache()
    except Exception:
        pass
        
    retention_days = 3
    disk_limit_percent = 80
    
    if os.path.exists(CLEANUP_CONFIG_PATH):
        try:
            with open(CLEANUP_CONFIG_PATH, 'r') as f:
                cfg = json.load(f)
                retention_days = int(cfg.get('retention_days', 3))
                disk_limit_percent = int(cfg.get('disk_limit_percent', 80))
        except Exception:
            pass
            
    retention_seconds = retention_days * 86400
    try:
        files = [os.path.join(RECORD_DIR, f) for f in os.listdir(RECORD_DIR) if f.endswith('.mp4')]
        for f in files:
            mtime = os.path.getmtime(f)
            if now - mtime > retention_seconds:
                try:
                    os.remove(f)
                except Exception:
                    pass
    except Exception:
        pass
        
    try:
        stat = os.statvfs(RECORD_DIR)
        total = stat.f_blocks * stat.f_frsize
        free = stat.f_bfree * stat.f_frsize
        used = total - free
        percent = (used / total) * 100
        
        if percent > disk_limit_percent:
            files = []
            for f in os.listdir(RECORD_DIR):
                if f.endswith('.mp4'):
                    path = os.path.join(RECORD_DIR, f)
                    files.append((path, os.path.getmtime(path)))
            files.sort(key=lambda x: x[1])
            for f_path, _ in files:
                try:
                    os.remove(f_path)
                except Exception:
                    pass
                stat = os.statvfs(RECORD_DIR)
                free = stat.f_bfree * stat.f_frsize
                used = total - free
                if (used / total) * 100 <= disk_limit_percent - 5:
                    break
    except Exception:
        pass

def main():
    signal.signal(signal.SIGTERM, cleanup_all)
    signal.signal(signal.SIGINT, cleanup_all)
    print("NVR Smart Low-Bandwidth Recording Daemon Started.", flush=True)
    
    while True:
        try:
            monitor_and_sync()
        except Exception as e:
            print(f"Error in monitor loop: {e}", flush=True)
        try:
            cleanup_old_recordings()
        except Exception as e:
            print(f"Error in cleanup loop: {e}", flush=True)
        time.sleep(20)

if __name__ == '__main__':
    main()
