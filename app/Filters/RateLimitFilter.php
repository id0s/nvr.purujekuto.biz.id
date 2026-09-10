<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    private const LIMITS = [
        'stream' => ['max' => 15,  'window' => 60],
        'api'    => ['max' => 120, 'window' => 60],
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $type = $arguments[0] ?? 'api';
        $limit = self::LIMITS[$type] ?? self::LIMITS['api'];

        $ip = $request->getIPAddress();
        
        // Whitelist localhost / local loopback
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return;
        }

        $cacheDir = '/var/record/cctv';
        if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
            $cacheDir = sys_get_temp_dir();
        }

        $key = $cacheDir . '/.ratelimit_' . $type . '_' . md5($ip) . '.json';

        $data = ['count' => 0, 'reset_at' => time() + $limit['window']];
        if (file_exists($key)) {
            $stored = json_decode(@file_get_contents($key), true);
            if (is_array($stored) && isset($stored['reset_at']) && $stored['reset_at'] > time()) {
                $data = $stored;
            }
        }

        $data['count']++;
        @file_put_contents($key, json_encode($data));

        if ($data['count'] > $limit['max']) {
            $retryAfter = max(1, $data['reset_at'] - time());
            return service('response')
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string)$retryAfter)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa detik.',
                    'retry_after' => $retryAfter
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
