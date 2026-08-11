<?php
declare(strict_types=1);

namespace App\Plugins\Http;

final class Response {
    private function __construct(
        private string $type,
        private mixed $body,
        private int $status = 200,
        private array $headers = []
    ) {}

    public static function html(string $html, int $status = 200, array $headers = []): self { return new self('body', $html, $status, ['Content-Type' => 'text/html; charset=utf-8'] + $headers); }
    public static function json(array $data, int $status = 200, array $headers = []): self { return new self('body', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $status, ['Content-Type' => 'application/json; charset=utf-8'] + $headers); }
    public static function redirect(string $url, int $status = 302): self { return new self('body', '', $status, ['Location' => $url]); }
    public static function download(string $path, ?string $filename = null, string $mime = 'application/octet-stream'): self { return new self('file', ['path' => $path, 'filename' => $filename ?? basename($path)], 200, ['Content-Type' => $mime]); }
    public static function stream(callable $writer, string $mime = 'application/octet-stream', int $status = 200): self { return new self('stream', $writer, $status, ['Content-Type' => $mime]); }

    public function send(): void {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) header($name . ': ' . $value);
        if ($this->type === 'file') {
            $path = (string)$this->body['path'];
            if (!is_file($path)) { http_response_code(404); echo 'File not found'; return; }
            header('Content-Length: ' . filesize($path));
            header('Content-Disposition: attachment; filename="' . addcslashes((string)$this->body['filename'], '"\\') . '"');
            readfile($path);
            return;
        }
        if ($this->type === 'stream') { ($this->body)(); return; }
        echo (string)$this->body;
    }
}
