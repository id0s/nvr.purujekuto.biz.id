#!/usr/bin/env python3
import sys
import socket
import urllib.request
import telnetlib
import time
import re

def send_soap_ptz(ip, action):
    url = f'http://{ip}:8899/onvif/ptz_service'
    headers = {'Content-Type': 'application/soap+xml; charset=utf-8'}

    if action == 'stop':
        soap_body = (
            '<?xml version="1.0" encoding="utf-8"?>'
            '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:tptz="http://www.onvif.org/ver20/ptz/wsdl">'
            '<soap:Body>'
            '<tptz:Stop>'
            '<tptz:ProfileToken>000</tptz:ProfileToken>'
            '<tptz:PanTilt>true</tptz:PanTilt>'
            '<tptz:Zoom>true</tptz:Zoom>'
            '</tptz:Stop>'
            '</soap:Body>'
            '</soap:Envelope>'
        )
    else:
        x_speed = "0.0"
        y_speed = "0.0"
        if action == 'left': x_speed = "-1.0"
        elif action == 'right': x_speed = "1.0"
        elif action == 'up': y_speed = "1.0"
        elif action == 'down': y_speed = "-1.0"

        soap_body = (
            '<?xml version="1.0" encoding="utf-8"?>'
            '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:tptz="http://www.onvif.org/ver20/ptz/wsdl" xmlns:tt="http://www.onvif.org/ver10/schema">'
            '<soap:Body>'
            '<tptz:ContinuousMove>'
            '<tptz:ProfileToken>000</tptz:ProfileToken>'
            '<tptz:Velocity>'
            f'<tt:PanTilt x="{x_speed}" y="{y_speed}" space="http://www.onvif.org/ver10/tptz/PanTiltSpaces/VelocityGenericSpace"/>'
            '</tptz:Velocity>'
            '</tptz:ContinuousMove>'
            '</soap:Body>'
            '</soap:Envelope>'
        )

    try:
        req = urllib.request.Request(url, data=soap_body.encode('utf-8'), headers=headers, method='POST')
        with urllib.request.urlopen(req, timeout=2.5) as r:
            print("Success (ONVIF SOAP)")
            return True
    except Exception as e:
        print(f"Failed SOAP PTZ ({ip}): {e}")
        return False

def send_anyka_ptz(ip, action):
    # Try up to 2 attempts
    for attempt in range(1, 3):
        try:
            tn = telnetlib.Telnet(ip, 23, timeout=2.5)
            time.sleep(0.15)
            
            # Read until login prompt or timeout
            login_banner = tn.read_until(b"login: ", timeout=1.5)
            tn.write(b"root\n")
            time.sleep(0.15)
            
            # Check if password is requested
            res = tn.read_very_eager()
            if b"Password:" in res or b"password:" in res or b"Password" in res:
                tn.write(b"\n")
                time.sleep(0.15)
                
            # Wait for shell prompt (#, $, or ]$)
            tn.read_until(b"#", timeout=1.0)
            
            # Force kill any hung motor processes first
            tn.write(b"killall -9 motor_control_v2 2>/dev/null\n")
            time.sleep(0.05)
            tn.read_very_eager()

            # Check if motor_control_v2 binary exists and is executable
            tn.write(b"[ -x /tmp/motor_control_v2 ] && echo 'OK' || echo 'MISSING'\n")
            time.sleep(0.15)
            res_chk = tn.read_very_eager().decode('utf-8', errors='ignore')
            
            if 'MISSING' in res_chk or 'OK' not in res_chk:
                tn.write(b"rm -f /tmp/motor_control_v2 && wget -O /tmp/motor_control_v2 http://192.168.11.220/recordings/motor_control_v2 && chmod +x /tmp/motor_control_v2\n")
                time.sleep(0.6)
                tn.read_very_eager()

            # Step counts: 350 steps for pan, 250 steps for tilt
            if action == 'left':
                tn.write(b"/tmp/motor_control_v2 /dev/motor0 350 >/dev/null 2>&1 &\n")
            elif action == 'right':
                tn.write(b"/tmp/motor_control_v2 /dev/motor0 -350 >/dev/null 2>&1 &\n")
            elif action == 'up':
                tn.write(b"/tmp/motor_control_v2 /dev/motor1 -250 >/dev/null 2>&1 &\n")
            elif action == 'down':
                tn.write(b"/tmp/motor_control_v2 /dev/motor1 250 >/dev/null 2>&1 &\n")
            elif action in ['center', 'home', 'reset']:
                tn.write(b"/tmp/motor_control_v2 /dev/motor0 0 2>/dev/null; /tmp/motor_control_v2 /dev/motor1 0 2>/dev/null\n")
            elif action == 'stop':
                tn.write(b"killall -9 motor_control_v2 2>/dev/null\n")
            
            time.sleep(0.15)
            print(f"Success (Anyka PTZ on {ip})")
            tn.close()
            return True
        except Exception as e:
            if attempt == 2:
                print(f"Failed Telnet Anyka ({ip}): {e}")
                return False
            time.sleep(0.2)
    return False

def check_port(ip, port, timeout=0.4):
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(timeout)
        res = s.connect_ex((ip, port))
        s.close()
        return res == 0
    except:
        return False

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print("Usage: ptz_control.py <ip> <action>")
        sys.exit(1)
    
    ip = sys.argv[1].strip()
    action = sys.argv[2].strip().lower()
    
    # Check if ONVIF camera
    if ip.startswith('192.168.22.') or check_port(ip, 8899, 0.3):
        success = send_soap_ptz(ip, action)
        if not success and check_port(ip, 23, 0.3):
            send_anyka_ptz(ip, action)
    else:
        # Default to Anyka telnet for all Eyesec / Anyka cameras
        send_anyka_ptz(ip, action)