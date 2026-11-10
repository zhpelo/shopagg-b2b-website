<?php
declare(strict_types=1);

namespace App\Services\Updates;

final class UpdateEventReporter
{
    private const ENDPOINT = 'https://update.shopagg.org/api/v1';
    private string $stateFile;

    public function __construct()
    {
        $this->stateFile = APP_ROOT . '/storage/update-service.json';
    }

    public function report(string $eventType, string $currentVersion, ?string $targetVersion, string $source, array $details = []): void
    {
        try {
            $state = $this->state() ?? $this->register($currentVersion);
            if ($state === null) return;
            $payload = array_filter([
                'uuid' => self::uuid4(),
                'event_type' => $eventType,
                'current_version' => $currentVersion,
                'target_version' => $targetVersion,
                'source' => in_array($source, ['shopagg', 'github'], true) ? $source : 'shopagg',
                'error_code' => isset($details['error_code']) ? substr((string)preg_replace('/[^a-z0-9_.-]/', '_', strtolower((string)$details['error_code'])), 0, 64) : null,
                'bytes' => isset($details['bytes']) ? max(0, (int)$details['bytes']) : null,
                'duration_ms' => isset($details['duration_ms']) ? max(0, (int)$details['duration_ms']) : null,
            ], static fn($value) => $value !== null);
            $this->post('/update-events', $payload, (string)$state['token']);
        } catch (\Throwable) {
            // 统计上报失败不影响更新功能。
        }
    }

    private function state(): ?array
    {
        if (!is_file($this->stateFile)) return null;
        $data = json_decode((string)file_get_contents($this->stateFile), true);
        return is_array($data) && isset($data['uuid'], $data['token']) ? $data : null;
    }

    private function register(string $currentVersion): ?array
    {
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?: '';
        if ($host === '' || $host === 'localhost') return null;
        $response = $this->post('/installations', [
            'uuid' => self::uuid4(), 'domain' => $host, 'current_version' => $currentVersion, 'channel' => 'self-hosted',
        ]);
        if (!is_array($response) || empty($response['uuid']) || empty($response['token'])) return null;
        $state = ['uuid' => $response['uuid'], 'token' => $response['token']];
        if (file_put_contents($this->stateFile, json_encode($state, JSON_UNESCAPED_SLASHES), LOCK_EX) === false) return null;
        @chmod($this->stateFile, 0600);
        return $state;
    }

    private function post(string $path, array $payload, ?string $token = null): ?array
    {
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
        $ch = curl_init(self::ENDPOINT . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => $headers, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) return null;
        if ($body === '' || $body === false) return [];
        $data = json_decode((string)$body, true);
        return is_array($data) ? $data : null;
    }

    private static function uuid4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
