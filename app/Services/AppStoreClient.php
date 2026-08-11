<?php
declare(strict_types=1);

namespace App\Services;

final class AppStoreClient {
    private const TYPE_B2B_THEME = 'b2b_theme';
    private const TYPE_B2B_PLUGIN = 'b2b_plugin';
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
        return $this->formatResourceList(
            $this->request('GET', '/resources', ['type' => self::TYPE_B2B_THEME], false, true),
            'themes'
        );
    }

    public function listB2BPlugins(): array {
        return $this->formatResourceList(
            $this->request('GET', '/resources', ['type' => self::TYPE_B2B_PLUGIN], false, true),
            'plugins'
        );
    }

    /**
     * Fetch both public catalogs concurrently so a slow endpoint does not block
     * the other one. The short timeout is intentional: callers keep a local
     * stale cache for shared-hosting environments with unreliable outbound HTTP.
     */
    public function listCatalog(): array {
        if (!function_exists('curl_multi_init') || $this->baseUrl === '') {
            return [
                'themes' => $this->listB2BThemes(),
                'plugins' => $this->listB2BPlugins(),
            ];
        }

        $requests = [
            'themes' => self::TYPE_B2B_THEME,
            'plugins' => self::TYPE_B2B_PLUGIN,
        ];
        $multi = curl_multi_init();
        $handles = [];

        foreach ($requests as $key => $type) {
            $url = $this->baseUrl . '/resources?' . http_build_query(['type' => $type]);
            $headers = ['Accept: application/json', 'User-Agent: ' . self::USER_AGENT];
            if ($this->token !== '') {
                $headers[] = 'Authorization: Bearer ' . $this->token;
            }
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_HTTPHEADER => $headers,
            ]);
            curl_multi_add_handle($multi, $handle);
            $handles[$key] = $handle;
        }

        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM);

        while ($active && $status === CURLM_OK) {
            if (curl_multi_select($multi, 1.0) === -1) {
                usleep(10000);
            }
            do {
                $status = curl_multi_exec($multi, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM);
        }

        $responses = [];
        foreach ($handles as $key => $handle) {
            $raw = curl_multi_getcontent($handle);
            $responses[$key] = $this->responseFromCurl(
                $raw,
                (int)curl_getinfo($handle, CURLINFO_HTTP_CODE),
                curl_error($handle)
            );
            curl_multi_remove_handle($multi, $handle);
            curl_close($handle);
        }
        curl_multi_close($multi);

        return [
            'themes' => $this->formatResourceList($responses['themes'], 'themes'),
            'plugins' => $this->formatResourceList($responses['plugins'], 'plugins'),
        ];
    }

    public function getB2BPlugin(int $resourceId): array {
        $response = $this->request('GET', '/resources/' . $resourceId, [], false, true);
        if (!$response['ok']) return ['ok' => false, 'resource' => null, 'message' => $this->messageFromResponse($response), 'status' => $response['status']];
        $payload = is_array($response['data']) ? $response['data'] : [];
        $resource = $payload['resource'] ?? null;
        if (!is_array($resource) || ($resource['type'] ?? '') !== self::TYPE_B2B_PLUGIN) {
            return ['ok' => false, 'resource' => null, 'message' => '该资源不是 B2B 网站插件', 'status' => $response['status']];
        }
        return ['ok' => true, 'resource' => $resource, 'message' => '', 'status' => $response['status']];
    }

    public function getB2BTheme(int $resourceId): array {
        $response = $this->request('GET', '/resources/' . $resourceId, [], false, true);
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

    private function request(string $method, string $path, array $data = [], bool $requiresToken = true, bool $fast = false): array {
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
            CURLOPT_CONNECTTIMEOUT => $fast ? 3 : 8,
            CURLOPT_TIMEOUT => $fast ? 8 : 30,
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

        return $this->responseFromCurl($raw, $status, $error);
    }

    private function responseFromCurl(string|bool $raw, int $status, string $error): array {
        if ($raw === false) {
            return [
                'ok' => false,
                'status' => $status,
                'data' => null,
                'raw' => '',
                'message' => $error !== '' ? $error : 'App Store 请求失败',
            ];
        }

        $decoded = json_decode($raw, true);
        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($decoded) ? $decoded : null,
            'raw' => $raw,
            'message' => $error,
        ];
    }

    private function formatResourceList(array $response, string $key): array {
        if (!$response['ok']) {
            return [
                'ok' => false,
                $key => [],
                'message' => $this->messageFromResponse($response),
                'status' => $response['status'],
            ];
        }

        $payload = is_array($response['data']) ? $response['data'] : [];
        return [
            'ok' => true,
            $key => is_array($payload['data'] ?? null) ? $payload['data'] : [],
            'message' => '',
            'status' => $response['status'],
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
