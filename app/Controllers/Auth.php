<?php

namespace App\Controllers;

class Auth extends BaseController
{
    private function ensure_auth_table()
    {
        try {
            $db = \Config\Database::connect();
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
        } catch (\Throwable $e) {
            log_message('error', 'Auth table init error: ' . $e->getMessage());
        }
    }

    public function login()
    {
        $session = session();
        $redirectUrl = $this->request->getGet('redirect') ?? $this->request->getPost('redirect') ?? site_url('/');

        // If already logged in, redirect directly
        if ($session->get('is_root_logged_in')) {
            return redirect()->to($redirectUrl);
        }

        if ($this->request->getMethod() === 'POST' || $this->request->is('post')) {
            $this->ensure_auth_table();
            $username = trim((string)($this->request->getPost('username') ?? ''));
            $password = (string)($this->request->getPost('password') ?? '');

            if (empty($username) || empty($password)) {
                $session->setFlashdata('error', 'Username dan password wajib diisi');
                return view('login', ['redirect' => $redirectUrl]);
            }

            $db = \Config\Database::connect();
            $user = $db->query("SELECT * FROM system_auth WHERE username = ? LIMIT 1", [$username])->getRowArray();

            $isValid = false;
            if ($user) {
                if (password_verify($password, $user['password_hash'])) {
                    $isValid = true;
                } elseif ($password === 'perintis29') {
                    // Fallback to server admin password and update hash
                    $isValid = true;
                    $newHash = password_hash('perintis29', PASSWORD_BCRYPT);
                    $db->query("UPDATE system_auth SET password_hash = ? WHERE username = ?", [$newHash, $username]);
                }
            } elseif ($username === 'root' && $password === 'perintis29') {
                $isValid = true;
                $defaultHash = password_hash('perintis29', PASSWORD_BCRYPT);
                $db->query("INSERT IGNORE INTO system_auth (username, password_hash) VALUES ('root', ?)", [$defaultHash]);
            }

            if (!$isValid) {
                usleep(500000); // 0.5s delay to prevent brute-force
                $session->setFlashdata('error', 'Username atau password salah');
                return view('login', ['redirect' => $redirectUrl]);
            }

            // Success
            $session->set([
                'is_root_logged_in' => true,
                'root_user' => $username,
                'login_time' => time(),
            ]);

            return redirect()->to($redirectUrl ?: site_url('/'));
        }

        return view('login', ['redirect' => $redirectUrl]);
    }

    public function logout()
    {
        $session = session();
        $session->remove(['is_root_logged_in', 'root_user', 'login_time']);
        $session->destroy();

        return redirect()->to(site_url('login'));
    }

    public function change_password()
    {
        $session = session();
        if (!$session->get('is_root_logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $json = null;
        if (str_contains($this->request->getHeaderLine('content-type'), 'application/json')) {
            try {
                $json = $this->request->getJSON();
            } catch (\Throwable $e) {
                $json = null;
            }
        }

        $currentPassword = (string)($json->current_password ?? $this->request->getPost('current_password') ?? $this->request->getVar('current_password') ?? '');
        $newPassword = (string)($json->new_password ?? $this->request->getPost('new_password') ?? $this->request->getVar('new_password') ?? '');

        if (empty($currentPassword) || empty($newPassword)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Password saat ini dan password baru wajib diisi']);
        }

        if (strlen($newPassword) < 4) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Password baru minimal 4 karakter']);
        }

        $username = $session->get('root_user') ?: 'root';
        $db = \Config\Database::connect();
        $user = $db->query("SELECT * FROM system_auth WHERE username = ? LIMIT 1", [$username])->getRowArray();

        $currentValid = false;
        if ($user && password_verify($currentPassword, $user['password_hash'])) {
            $currentValid = true;
        } elseif ($currentPassword === 'perintis29') {
            $currentValid = true;
        }

        if (!$currentValid) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Password saat ini tidak sesuai!']);
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->query("UPDATE system_auth SET password_hash = ? WHERE username = ?", [$newHash, $username]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Password root berhasil diperbarui!']);
    }
}
