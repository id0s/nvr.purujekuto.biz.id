#!/usr/bin/env python3
import os, time, sys, glob, re, json, shutil

def run_cleanup():
    cfg_file = '/var/record/cctv/cleanup_config.json'
    retention_days = 3
    disk_limit_percent = 80
    
    if os.path.exists(cfg_file):
        try:
            with open(cfg_file, 'r') as f:
                data = json.load(f)
                retention_days = int(data.get('retention_days', 3))
                disk_limit_percent = int(data.get('disk_limit_percent', 80))
        except Exception as e:
            print(f"Error reading config: {e}")

    now = time.time()
    cutoff_time = now - (retention_days * 86400)
    cctv_dir = '/var/record/cctv'
    
    deleted_count = 0
    deleted_bytes = 0
    
    # 1. Retention-based cleanup (delete files older than retention_days)
    if os.path.exists(cctv_dir):
        for f in os.listdir(cctv_dir):
            if f.endswith('.mp4'):
                path = os.path.join(cctv_dir, f)
                match = re.search(r'(\d{8}_\d{6})', f)
                file_time = None
                if match:
                    try:
                        file_time = time.mktime(time.strptime(match.group(1), '%Y%m%d_%H%M%S'))
                    except Exception:
                        file_time = os.path.getmtime(path)
                else:
                    file_time = os.path.getmtime(path)
                
                if file_time < cutoff_time:
                    try:
                        sz = os.path.getsize(path)
                        os.remove(path)
                        deleted_count += 1
                        deleted_bytes += sz
                    except Exception as e:
                        pass

    # 2. Emergency Disk Limit Guard (FIFO auto-prune to protect 500GB HDD)
    total, used, free = shutil.disk_usage(cctv_dir)
    used_percent = (used / total) * 100
    
    if used_percent > disk_limit_percent or (free / (1024*1024*1024)) < 25:
        print(f"Disk alert: {used_percent:.1f}% used. Auto-pruning oldest segments...")
        all_files = []
        for f in os.listdir(cctv_dir):
            if f.endswith('.mp4'):
                path = os.path.join(cctv_dir, f)
                all_files.append((os.path.getmtime(path), path))
        all_files.sort() # Oldest first
        
        for _, path in all_files:
            try:
                sz = os.path.getsize(path)
                os.remove(path)
                deleted_count += 1
                deleted_bytes += sz
            except Exception:
                pass
            total, used, free = shutil.disk_usage(cctv_dir)
            if (used / total) * 100 < 75 and (free / (1024*1024*1024)) >= 35:
                break

    # 3. Clean MySQL DB
    try:
        import MySQLdb
        db = MySQLdb.connect(host="localhost", user="dos", passwd="1", db="verif_db")
        cur = db.cursor()
        cur.execute("DELETE FROM anyka_recordings WHERE segment_timestamp < %s", (int(cutoff_time),))
        db.commit()
        db.close()
    except Exception:
        pass

    if deleted_count > 0:
        print(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] Auto-cleanup freed {deleted_bytes/(1024*1024):.1f} MB ({deleted_count} files)")

if __name__ == '__main__':
    run_cleanup()
