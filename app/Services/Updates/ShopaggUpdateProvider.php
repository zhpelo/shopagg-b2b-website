<?php
declare(strict_types=1);

namespace App\Services\Updates;

final class ShopaggUpdateProvider implements ReleaseProvider
{
    private const BASE_URL = 'https://update.shopagg.org';
    private const KEY_ID = 'shopagg-release-2026-01';
    private const PUBLIC_KEY = 'UvGIWYbFW-mcPqPM5VXg-5vPrUVz5Yo3lW7NonvN7NA';

    public function source(): string { return 'shopagg'; }

    public function latest(): ?array
    {
        return self::releaseFromEnvelope($this->requestJson(self::BASE_URL . '/'), self::BASE_URL);
    }

    public function release(string $version): ?array
    {
        $data = $this->requestJson(self::BASE_URL . '/api/v1/releases/' . rawurlencode($version));
        if (!is_array($data['envelope'] ?? null)) {
            throw new UpdateSecurityException('SHOPAGG 更新服务返回的版本清单不完整');
        }
        return self::releaseFromEnvelope($data['envelope'], self::BASE_URL);
    }

    public function releases(int $page = 1, int $perPage = 20): array
    {
        $data = $this->requestJson(self::BASE_URL . '/api/v1/releases?limit=' . min(50, max(1, $perPage)));
        $items = is_array($data['releases'] ?? null) ? $data['releases'] : [];
        return array_map(static function (array $release): array {
            return [
                'version' => (string)($release['version'] ?? ''),
                'name' => (string)($release['name'] ?? ('v' . ($release['version'] ?? ''))),
                'body' => (string)($release['notes'] ?? ''),
                'published_at' => (string)($release['published_at'] ?? ''),
                'html_url' => (string)($release['github_url'] ?? ''),
                'author' => 'SHOPAGG',
                'is_prerelease' => false,
                'is_draft' => false,
                'assets_count' => empty($release['artifact']) ? 0 : 1,
                'source' => 'shopagg',
            ];
        }, array_filter($items, 'is_array'));
    }

    public static function releaseFromEnvelope(array $envelope, string $baseUrl = self::BASE_URL): array
    {
        if (($envelope['schema'] ?? null) !== 1
            || ($envelope['key_id'] ?? '') !== self::KEY_ID
            || !is_string($envelope['signed_payload'] ?? null)
            || !is_string($envelope['signature'] ?? null)) {
            throw new UpdateSecurityException('版本清单格式或签名密钥无效');
        }
        $encodedPayload = $envelope['signed_payload'];
        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);
        if (!is_array($payload) || ($payload['product'] ?? '') !== 'shopagg-cms') {
            throw new UpdateSecurityException('版本清单产品标识无效');
        }

        $signatureAvailable = extension_loaded('sodium') && function_exists('sodium_crypto_sign_verify_detached');
        if ($signatureAvailable) {
            $publicKey = self::base64UrlDecode(self::PUBLIC_KEY);
            $signature = self::base64UrlDecode($envelope['signature']);
            if (strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
                || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES
                || !sodium_crypto_sign_verify_detached($signature, $encodedPayload, $publicKey)) {
                throw new UpdateSecurityException('版本清单签名验证失败，已禁止自动更新');
            }
        }

        $version = (string)($payload['version'] ?? '');
        $artifact = is_array($payload['artifact'] ?? null) ? $payload['artifact'] : [];
        $compatibility = is_array($payload['compatibility'] ?? null) ? $payload['compatibility'] : [];
        if (preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/D', $version) !== 1
            || ($payload['channel'] ?? '') !== 'stable'
            || ($payload['force_update'] ?? null) !== false
            || preg_match('/^[a-f0-9]{64}$/D', (string)($artifact['sha256'] ?? '')) !== 1
            || (int)($artifact['size'] ?? 0) <= 0
            || !str_starts_with((string)($artifact['path'] ?? ''), '/downloads/')) {
            throw new UpdateSecurityException('版本清单内容无效，已禁止自动更新');
        }

        return [
            'version' => $version,
            'name' => 'SHOPAGG CMS v' . $version,
            'body' => (string)($payload['notes'] ?? ''),
            'published_at' => (string)($payload['published_at'] ?? ''),
            'html_url' => (string)($payload['github_fallback_url'] ?? ''),
            'assets' => [],
            'is_prerelease' => false,
            'is_draft' => false,
            'source' => 'shopagg',
            'signature_verified' => $signatureAvailable,
            'automatic_install_allowed' => $signatureAvailable,
            'download_url' => rtrim($baseUrl, '/') . (string)$artifact['path'],
            'github_fallback_url' => (string)($payload['github_fallback_url'] ?? ''),
            'size' => (int)$artifact['size'],
            'sha256' => (string)$artifact['sha256'],
            'compatibility' => $compatibility,
        ];
    }

    private function requestJson(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'SHOPAGG-CMS-Updater/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if (!is_string($response) || $response === '' || $status >= 500 || $status === 0) {
            throw new UpdateProviderUnavailable('SHOPAGG 更新服务暂时不可用' . ($error !== '' ? '：' . $error : ''));
        }
        if ($status === 404) return [];
        if ($status >= 400 && $status < 500) throw new UpdateSecurityException('SHOPAGG 更新服务拒绝了版本请求（HTTP ' . $status . '）');
        if ($status !== 200) throw new UpdateProviderUnavailable('SHOPAGG 更新服务返回 HTTP ' . $status);
        $data = json_decode($response, true);
        if (!is_array($data)) throw new UpdateSecurityException('SHOPAGG 更新服务返回了无效 JSON');
        return $data;
    }

    private static function base64UrlDecode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
        if ($decoded === false) throw new UpdateSecurityException('版本清单 Base64 编码无效');
        return $decoded;
    }
}
