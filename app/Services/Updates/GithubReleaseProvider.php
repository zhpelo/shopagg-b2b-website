<?php
declare(strict_types=1);

namespace App\Services\Updates;

final class GithubReleaseProvider implements ReleaseProvider
{
    private const API = 'https://api.github.com/repos/zhpelo/shopagg-b2b-website';

    public function source(): string { return 'github'; }

    public function latest(): ?array
    {
        return $this->normalizeRelease($this->requestJson(self::API . '/releases/latest'));
    }

    public function release(string $version): ?array
    {
        return $this->normalizeRelease($this->requestJson(self::API . '/releases/tags/v' . rawurlencode($version)));
    }

    public function releases(int $page = 1, int $perPage = 20): array
    {
        $data = $this->requestJson(self::API . '/releases?page=' . max(1, $page) . '&per_page=' . min(50, max(1, $perPage)));
        $items = [];
        foreach ($data as $release) {
            if (!is_array($release)) continue;
            $items[] = [
                'version' => ltrim((string)($release['tag_name'] ?? ''), 'v'),
                'name' => (string)($release['name'] ?? $release['tag_name'] ?? 'Unknown'),
                'body' => (string)($release['body'] ?? ''),
                'published_at' => (string)($release['published_at'] ?? ''),
                'html_url' => (string)($release['html_url'] ?? ''),
                'author' => (string)($release['author']['login'] ?? 'unknown'),
                'is_prerelease' => (bool)($release['prerelease'] ?? false),
                'is_draft' => (bool)($release['draft'] ?? false),
                'assets_count' => count($release['assets'] ?? []),
                'source' => 'github',
            ];
        }
        return $items;
    }

    private function normalizeRelease(array $release): array
    {
        $version = ltrim((string)($release['tag_name'] ?? ''), 'v');
        $manifestAsset = null;
        foreach (is_array($release['assets'] ?? null) ? $release['assets'] : [] as $asset) {
            if (is_array($asset) && ($asset['name'] ?? '') === 'manifest.json') $manifestAsset = $asset;
        }
        if ($manifestAsset === null) {
            throw new UpdateSecurityException('GitHub Release 没有签名清单，只允许手动下载');
        }
        $manifest = $this->requestJson((string)$manifestAsset['browser_download_url'], 'application/json');
        $normalized = ShopaggUpdateProvider::releaseFromEnvelope($manifest, 'https://update.shopagg.org');
        if ($normalized['version'] !== $version) throw new UpdateSecurityException('GitHub Release 标签与签名版本不一致');
        $normalized['source'] = 'github';
        $normalized['download_url'] = (string)$normalized['github_fallback_url'];
        $normalized['html_url'] = (string)($release['html_url'] ?? $normalized['html_url']);
        $normalized['name'] = (string)($release['name'] ?? ('SHOPAGG CMS v' . $version));
        $normalized['body'] = (string)($release['body'] ?? $normalized['body']);
        return $normalized;
    }

    private function requestJson(string $url, string $accept = 'application/vnd.github+json'): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'SHOPAGG-CMS-Updater/1.0',
            CURLOPT_HTTPHEADER => ['Accept: ' . $accept, 'X-GitHub-Api-Version: 2022-11-28'],
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if (!is_string($response) || $status === 0 || $status >= 500) {
            throw new UpdateProviderUnavailable('GitHub 备用源不可用' . ($error ? '：' . $error : ''));
        }
        if ($status !== 200) throw new UpdateProviderUnavailable('GitHub 备用源返回 HTTP ' . $status);
        $data = json_decode($response, true);
        if (!is_array($data)) throw new UpdateSecurityException('GitHub 签名清单格式无效');
        return $data;
    }
}
