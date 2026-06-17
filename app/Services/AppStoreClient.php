<?php
declare(strict_types=1);

namespace App\Services;

final class AppStoreClient {
    private const TYPE_B2B_THEME = 'b2b_theme';
    private const USER_AGENT = 'ShopAGG-B2B-Website-AppStore/1.0';

    private string $baseUrl;
    private string $token;

    public function __construct(string $baseUrl, string $token = '') {
        $this->baseUrl = $this->normalizeBaseUrl($baseUrl);
        $this->token = trim($token);
    }

    public function baseUrl(): string {
        return $this->baseUrl;
    }

    public function hasToken(): bool {
        return $this->token !== '';
    }

    public function maskedToken(): string {
        if ($this->token === '') {
            return '';
        }

        if (strlen($this->token) <= 12) {
            return str_repeat('*', strlen($this->token));
        }

        return substr($this->token, 0, 6) . str_repeat('*', 8) . substr($this->token, -6);
    }

    public function listB2BThemes(): array {
        $response = $this->request('GET', '/resources', ['type' => self::TYPE_B2B_THEME], false);
        if (!$response['ok']) {
            return [
                'ok' => false,
                'themes' => [],
                'message' => $this->messageFromResponse($response),
                'status' => $response['status'],
            ];
        }

        $payload = is_array($response['data']) ? $response['data'] : [];
        $themes = $payload['data'] ?? [];

        return [
            'ok' => true,
            'themes' => is_array($themes) ? $themes : [],
            'message' => '',
            'status' => $response['status'],
        ];
    }

    public function getB2BTheme(int $resourceId): array {
        $response = $this->request('GET', '/resources/' . $resourceId, [], false);
        if (!$response['ok']) {
            return [
                'ok' => false,
                'resource' => null,
                'message' => $this->messageFromResponse($response),
                'status' => $response['status'],
            ];
        }

        $payload = is_array($response['data']) ? $response['data'] : [];
        $resource = $payload['resource'] ?? null;
        if (!is_array($resource)) {
            return [
                'ok' => false,
                'resource' => null,
                'message' => 'App Store 未返回主题详情',
                'status' => $response['status'],
            ];
        }

        if (($resource['type'] ?? '') !== self::TYPE_B2B_THEME) {
            return [
                'ok' => false,
                'resource' => null,
                'message' => '该资源不是 B2B 网站主题',
                'status' => $response['status'],
            ];
        }

        return [
            'ok' => true,
            'resource' => $resource,
            'message' => '',
            'status' => $response['status'],
        ];
    }

    public function me(): array {
        return $this->request('GET', '/me', [], true);
    }

    public function downloadResource(int $resourceId, string $domain): array {
        return $this->request('GET', '/download/' . $resourceId, ['domain' => $domain], true);
    }

    public function createOrder(int $resourceId): array {
        return $this->request('POST', '/orders', ['resource_id' => $resourceId], true);
    }

    public function payOrder(string $orderId, string $paymentMethod): array {
        return $this->request('POST', '/orders/' . rawurlencode($orderId) . '/pay', [
            'payment_method' => $paymentMethod,
        ], true);
    }

    public function downloadFile(string $url, string $targetPath, int $maxBytes = 104857600): void {
        $directory = dirname($targetPath);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('无法创建下载目录');
        }

        $handle = fopen($targetPath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('无法写入下载文件');
        }

        $bytes = 0;
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_FAILONERROR => false,
            CURLOPT_WRITEFUNCTION => static function ($curlHandle, string $chunk) use ($handle, &$bytes, $maxBytes): int {
                $length = strlen($chunk);
                $bytes += $length;
                if ($bytes > $maxBytes) {
                    return 0;
                }

                $written = fwrite($handle, $chunk);
                return $written === false ? 0 : $written;
            },
        ]);

        $result = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        fclose($handle);

        if ($result === false || $status < 200 || $status >= 300) {
            @unlink($targetPath);
            throw new \RuntimeException($error !== '' ? $error : '下载安装包失败，HTTP ' . $status);
        }

        if ($bytes <= 0 || !is_file($targetPath)) {
            @unlink($targetPath);
            throw new \RuntimeException('下载的主题包为空');
        }
    }

    private function request(string $method, string $path, array $data = [], bool $requiresToken = true): array {
        if ($this->baseUrl === '') {
            return [
                'ok' => false,
                'status' => 0,
                'data' => null,
                'raw' => '',
                'message' => 'App Store API 地址未配置',
            ];
        }

        if ($requiresToken && $this->token === '') {
            return [
                'ok' => false,
                'status' => 401,
                'data' => null,
                'raw' => '',
                'message' => '请先配置 App Store API Token',
            ];
        }

        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $headers = [
            'Accept: application/json',
            'User-Agent: ' . self::USER_AGENT,
        ];

        if ($this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $curl = curl_init();
        $method = strtoupper($method);

        if ($method === 'GET' && $data !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($method !== 'GET') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $raw = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($raw === false) {
            return [
                'ok' => false,
                'status' => $status,
                'data' => null,
                'raw' => '',
                'message' => $error !== '' ? $error : 'App Store 请求失败',
            ];
        }

        $decoded = json_decode((string)$raw, true);

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($decoded) ? $decoded : null,
            'raw' => (string)$raw,
            'message' => $error,
        ];
    }

    private function normalizeBaseUrl(string $baseUrl): string {
        $baseUrl = trim($baseUrl);
        if ($baseUrl === '') {
            return '';
        }

        $baseUrl = rtrim($baseUrl, '/');
        if (str_ends_with($baseUrl, '/api')) {
            return $baseUrl . '/shopagg-app-store';
        }

        if (!str_contains($baseUrl, '/shopagg-app-store')) {
            return $baseUrl . '/api/shopagg-app-store';
        }

        return $baseUrl;
    }

    private function messageFromResponse(array $response): string {
        $data = $response['data'] ?? null;
        if (is_array($data) && isset($data['message'])) {
            return (string)$data['message'];
        }

        if (($response['message'] ?? '') !== '') {
            return (string)$response['message'];
        }

        $status = (int)($response['status'] ?? 0);
        return $status > 0 ? 'App Store 请求失败，HTTP ' . $status : 'App Store 请求失败';
    }
}
