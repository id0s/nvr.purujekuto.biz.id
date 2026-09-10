#!/usr/bin/env python3
import os
import sys
import time
import re
import socket
import telnetlib
import subprocess

EYESEC_CAMERAS = [
    ('cam_g7', '10.199.89.133'),
    ('cam_g1', '10.199.88.131'),
    ('cam_g2', '10.199.89.153'),
    ('cam_g3', '10.199.91.74'),
    ('cam_g4', '10.199.88.123'),
    ('cam_g5', '10.199.88.199'),
    ('cam_g6', '10.199.89.156'),
    ('cam_e1', '10.199.98.192'),
    ('cam_e2', '10.199.98.193'),
    ('cam_e3', '10.199.98.121'),
    ('cam_e4', '10.199.98.120'),
    ('cam_e5', '10.199.97.78'),
    ('cam_e6', '10.199.98.111'),
    ('cam_h3', '172.17.0.79'),
    ('cam_i2', '172.17.0.245'),
    ('cam_i4', '10.199.92.101'),
    ('cam_i5', '172.17.0.233'),
    ('cam_i6', '172.17.0.222'),
    ('cam_i7', '172.17.0.225'),
    ('cam_i9', '172.17.0.235'),
    ('cam_ib', '172.17.0.117'),
    ('cam_j1', '172.17.0.110'),
    ('cam_j2', '172.17.0.128'),
    ('cam_waka', '192.168.60.115'),
    ('cctv_94_190', '10.199.94.190'),
    ('cctv_98_174', '10.199.98.174'),
    ('cctv_98_176', '10.199.98.176')
]

SERVER_IP = '192.168.11.220'
PORT = 9985
RECORD_DIR = '/var/record/cctv'

def strip_ansi(text):
    return re.sub(r'\x1b\[[0-9;]*[a-zA-Z]', '', text)

def convert_chunk(cam_id, cam_ip, day_dir, chunk_folder):
    clean_folder = strip_ansi(chunk_folder).strip()
    m = re.search(r'(\d{9,10})_', clean_folder)
    if not m:
        return False
    
    unix_ts = int(m.group(1))

    # Convert UTC timestamp to local Jakarta time (UTC+7)
    t = time.gmtime(unix_ts + 25200)
    date_str = time.strftime('%Y%m%d_%H%M%S', t)
    target_mp4 = os.path.join(RECORD_DIR, f"{cam_id}_rec_{date_str}.mp4")

    if os.path.exists(target_mp4) and os.path.getsize(target_mp4) > 100000:
        print(f"[{cam_id}] Already converted: {target_mp4}")
        return True

    print(f"[{cam_id}] Pulling & converting SD card chunk {clean_folder} -> {target_mp4}")

    full_chunk_path = f"{day_dir}/{clean_folder}"

    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    sock.bind(('0.0.0.0', PORT))
    sock.listen(1)

    try:
        tn = telnetlib.Telnet(cam_ip, 23, timeout=3)
        time.sleep(0.3)
        tn.read_until(b"login: ", timeout=2)
        tn.write(b"root\n")
        time.sleep(0.3)
        cmd = f"cat {full_chunk_path}/*.media | nc {SERVER_IP} {PORT}\n"
        tn.write(cmd.encode())
    except Exception as e:
        print(f"[{cam_id}] Telnet connection error: {e}")
        sock.close()
        return False

    try:
        conn, addr = sock.accept()
    except Exception as e:
        print(f"[{cam_id}] Socket accept error: {e}")
        sock.close()
        tn.close()
        return False

    ffmpeg_cmd = [
        'ffmpeg', '-y',
        '-f', 'hevc',
        '-i', 'pipe:0',
        '-c:v', 'libx264',
        '-preset', 'ultrafast',
        '-pix_fmt', 'yuv420p',
        '-movflags', '+faststart',
        target_mp4
    ]

    proc = subprocess.Popen(ffmpeg_cmd, stdin=subprocess.PIPE, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

    try:
        while True:
            data = conn.recv(65536)
            if not data:
                break
            proc.stdin.write(data)
    except Exception as e:
        print(f"[{cam_id}] Pipe error: {e}")

    try:
        proc.stdin.close()
    except:
        pass

    proc.wait()
    conn.close()
    sock.close()
    try:
        tn.close()
    except:
        pass

    if os.path.exists(target_mp4) and os.path.getsize(target_mp4) > 100000:
        os.chmod(target_mp4, 0o644)
        print(f"[{cam_id}] SUCCESS: Created {target_mp4} ({os.path.getsize(target_mp4)} bytes)")
        return True
    else:
        print(f"[{cam_id}] FAILED to convert {target_mp4}")
        if os.path.exists(target_mp4):
            os.remove(target_mp4)
        return False

def sync_all():
    print(f"=== EYESEC SDCARD RECONVERSION SYNC ({time.strftime('%Y-%m-%d %H:%M:%S')}) ===")
    for cam_id, cam_ip in EYESEC_CAMERAS:
        try:
            tn = telnetlib.Telnet(cam_ip, 23, timeout=2)
            time.sleep(0.3)
            tn.read_until(b"login: ", timeout=1.5)
            tn.write(b"root\n")
            time.sleep(0.3)
            tn.write(b"find /tmp/sdcard/DCIM -type d -mindepth 3 -maxdepth 3 2>/dev/null\n")
            time.sleep(1.0)
            out = tn.read_very_eager().decode('utf-8', errors='ignore')
            tn.close()
        except Exception as e:
            continue

        day_dirs = []
        for line in out.splitlines():
            line = strip_ansi(line).strip()
            if line.startswith('/tmp/sdcard/DCIM/'):
                day_dirs.append(line)

        if not day_dirs:
            continue

        day_dirs.sort(reverse=True)

        for day_dir in day_dirs[:2]:
            try:
                tn = telnetlib.Telnet(cam_ip, 23, timeout=2)
                time.sleep(0.3)
                tn.read_until(b"login: ", timeout=1.5)
                tn.write(b"root\n")
                time.sleep(0.3)
                tn.write(f"ls --color=never -1 {day_dir}\n".encode())
                time.sleep(1.0)
                chunk_out = tn.read_very_eager().decode('utf-8', errors='ignore')
                tn.close()
            except Exception as e:
                continue

            chunk_folders = []
            for line in chunk_out.splitlines():
                line = strip_ansi(line).strip()
                if '_' in line and not line.endswith('.bin'):
                    chunk_folders.append(line)

            chunk_folders.sort(reverse=True)

            count = 0
            for cf in chunk_folders:
                if convert_chunk(cam_id, cam_ip, day_dir, cf):
                    count += 1
                if count >= 3:
                    break

if __name__ == '__main__':
    sync_all()
