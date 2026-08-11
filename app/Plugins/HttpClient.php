<?php
declare(strict_types=1);

namespace App\Plugins;

final class HttpClient {
    public function request(string $method, string $url, array $options = []): array {
        if (!function_exists('curl_init')) throw new \RuntimeException('当前环境未启用 cURL');
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_FOLLOWLOCATION => (bool)($options['follow_redirects'] ?? false),
            CURLOPT_CONNECTTIMEOUT => (int)($options['connect_timeout'] ?? 8),
            CURLOPT_TIMEOUT => (int)($options['timeout'] ?? 30),
            CURLOPT_HTTPHEADER => (array)($options['headers'] ?? []),
        ]);
        if (array_key_exists('body', $options)) curl_setopt($curl, CURLOPT_POSTFIELDS, $options['body']);
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($body === false) throw new \RuntimeException($error ?: 'HTTP 请求失败');
        return ['status' => $status, 'body' => (string)$body];
    }
}
