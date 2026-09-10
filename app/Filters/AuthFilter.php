<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Allow if root is logged in
        if ($session->get('is_root_logged_in')) {
            return;
        }

        // 2. Allow token-based public camera share links
        $shareToken = $request->getGet('token') ?? $request->getPost('token');
        if (!empty($shareToken)) {
            try {
                $db = \Config\Database::connect();
                $cam = $db->query("SELECT id FROM camera_settings WHERE share_token = ? AND is_public = 1 LIMIT 1", [$shareToken])->getRowArray();
                if ($cam) {
                    return; // Token valid
                }
            } catch (\Throwable $e) {
                // Ignore DB error and fallback to auth check
            }
        }

        // 3. API / AJAX requests -> return JSON 401 Unauthorized
        $accept = (string)$request->getHeaderLine('Accept');
        $contentType = (string)$request->getHeaderLine('Content-Type');
        $requestedWith = (string)$request->getHeaderLine('X-Requested-With');

        if (str_contains($accept, 'application/json') ||
            str_contains($contentType, 'application/json') ||
            strtolower($requestedWith) === 'xmlhttprequest') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthorized: Silakan login terlebih dahulu'
                ]);
        }

        // 4. Browser page requests -> redirect to login with return URL
        $currentUri = (string)current_url();
        return redirect()->to(site_url('login?redirect=' . rawurlencode($currentUri)));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
